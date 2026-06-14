<?php
// modules/structure/services/CorbeilleService.php
// Squelette de restauration pour les tables du module Structure.

namespace structure\services;

require_once ROOT . '/core/Auth.php';
require_once ROOT . '/core/Corbeille.php';
require_once ROOT . '/modules/structure/models/ProduitModel.php';
require_once ROOT . '/modules/structure/models/FractionModel.php';

use Corbeille;
use Database;
use RuntimeException;
use ProduitModel;
use FractionModel;
use Exception;

class CorbeilleService {

    public function restaurerBanque(string $fichier): void {
        $donnees = \Corbeille::charger($fichier);
        if ($donnees['table'] !== 'banques') {
            throw new \RuntimeException('Ce fichier ne correspond pas à une banque.');
        }

        $p = $donnees['principal'];

        // Vérifier unicité par code ou nom
        $sqlVerif = "SELECT 1 FROM banques WHERE code = :code OR nom = :nom";
        $existe = \Database::query($sqlVerif, [':code' => $p['code'] ?? '', ':nom' => $p['nom'] ?? ''])->fetch();
        if ($existe) {
            throw new \RuntimeException("Impossible de restaurer : une banque avec ce code/nom existe déjà.");
        }

        // Insertion
        $sql = "INSERT INTO banques (code, nom, adresse, telephone, numero_compte)
                VALUES (:code, :nom, :adresse, :telephone, :numero_compte) RETURNING id";
        $res = \Database::query($sql, [
            ':code' => $p['code'] ?? null,
            ':nom'  => $p['nom'] ?? null,
            ':adresse' => $p['adresse'] ?? null,
            ':telephone' => $p['telephone'] ?? null,
            ':numero_compte' => $p['numero_compte'] ?? null,
        ])->fetch();

        $nouvelId = (int)($res['id'] ?? 0);

        // Restaurer les versements liés si présents
        if (!empty($donnees['liees']['versements'])) {
            $pdo = \Database::connexion();
            try {
                $pdo->beginTransaction();
                $sqlV = "INSERT INTO versements (banque_id, montant, date_versement, reference, motif)
                         VALUES (:banque_id, :montant, :date_versement, :reference, :motif)";
                foreach ($donnees['liees']['versements'] as $v) {
                    \Database::query($sqlV, [
                        ':banque_id' => $nouvelId,
                        ':montant' => $v['montant'] ?? 0,
                        ':date_versement' => $v['date_versement'] ?: null,
                        ':reference' => $v['reference'] ?? null,
                        ':motif' => $v['motif'] ?? null,
                    ]);
                }
                $pdo->commit();
            } catch (\Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        }

        \Corbeille::supprimerFichier($fichier);
    }

    public function restaurerClient(string $fichier): void {
        $donnees = \Corbeille::charger($fichier);
        if ($donnees['table'] !== 'clients') {
            throw new \RuntimeException('Ce fichier ne correspond pas à un client.');
        }

        $p = $donnees['principal'];

        // Vérifier unicité par code ou raison_sociale
        $sqlVerif = "SELECT 1 FROM clients WHERE code = :code OR raison_sociale = :rs";
        $existe = \Database::query($sqlVerif, [':code' => $p['code'] ?? '', ':rs' => $p['raison_sociale'] ?? ''])->fetch();
        if ($existe) {
            throw new \RuntimeException('Impossible de restaurer : un client similaire existe déjà.');
        }

        $sql = "INSERT INTO clients (code, raison_sociale, contact, telephone, email, adresse, ville, pays, categorie_id, actif)
                VALUES (:code, :raison_sociale, :contact, :telephone, :email, :adresse, :ville, :pays, :categorie_id, :actif) RETURNING id";
        $res = \Database::query($sql, [
            ':code' => $p['code'] ?? null,
            ':raison_sociale' => $p['raison_sociale'] ?? null,
            ':contact' => $p['contact'] ?? null,
            ':telephone' => $p['telephone'] ?? null,
            ':email' => $p['email'] ?? null,
            ':adresse' => $p['adresse'] ?? null,
            ':ville' => $p['ville'] ?? null,
            ':pays' => $p['pays'] ?? 'Cameroun',
            ':categorie_id' => $p['categorie_id'] ?? null,
            ':actif' => ($p['actif'] === 't' || $p['actif'] === '1') ? true : false,
        ])->fetch();

        \Corbeille::supprimerFichier($fichier);
    }

    /**
     * Restaure une catégorie client et rattache tous les clients qui lui appartenaient
     */
    public function restaurerCategorieClient(string $fichier): void {
        $donnees = \Corbeille::charger($fichier);
        if ($donnees['table'] !== 'categories_clients') {
            throw new \RuntimeException('Ce fichier ne correspond pas à une catégorie client.');
        }

        $p = $donnees['principal'];
        $pdo = \Database::connexion();
        
        try {
            $pdo->beginTransaction();

            // 1. Vérifier si la catégorie existe déjà (par code)
            $sqlVerif = "SELECT id FROM categories_clients WHERE code = :code";
            $categorieExistante = \Database::query($sqlVerif, [':code' => $p['code']])->fetch();
            
            $nouvelleCategorieId = null;
            
            if ($categorieExistante) {
                // La catégorie existe déjà, on l'utilise
                $nouvelleCategorieId = $categorieExistante['id'];
            } else {
                // Créer une nouvelle catégorie (laisse PostgreSQL générer l'ID)
                $sql = "INSERT INTO categories_clients (code, libelle, remise_pct, description)
                        VALUES (:code, :libelle, :remise_pct, :description) RETURNING id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':code' => $p['code'] ?? null,
                    ':libelle' => $p['libelle'] ?? null,
                    ':remise_pct' => isset($p['remise_pct']) ? (float)$p['remise_pct'] : 0,
                    ':description' => $p['description'] ?? null,
                ]);
                $res = $stmt->fetch();
                $nouvelleCategorieId = (int)$res['id'];
            }
            
            // 2. Récupérer les IDs des clients depuis l'archive
            $clientsIds = [];
            if (!empty($donnees['liees']['clients_ids'])) {
                foreach ($donnees['liees']['clients_ids'] as $clientData) {
                    if (isset($clientData['id'])) {
                        $clientsIds[] = (int)$clientData['id'];
                    }
                }
            }
            
            // 3. Mettre à jour les clients avec la nouvelle catégorie
            if (!empty($clientsIds)) {
                $placeholders = implode(',', array_fill(0, count($clientsIds), '?'));
                $sqlUpdate = "UPDATE clients SET categorie_id = ? WHERE id IN ({$placeholders})";
                $stmt = $pdo->prepare($sqlUpdate);
                $params = array_merge([$nouvelleCategorieId], $clientsIds);
                $stmt->execute($params);
                
                $_SESSION['succes'] = "Catégorie restaurée et " . count($clientsIds) . " client(s) rattachés.";
            } else {
                $_SESSION['succes'] = "Catégorie restaurée (aucun client à rattacher).";
            }

            $pdo->commit();
            \Corbeille::supprimerFichier($fichier);
            
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw new \RuntimeException("Erreur lors de la restauration : " . $e->getMessage());
        }
    }

    public function restaurerFamille(string $fichier): void {
        $donnees = \Corbeille::charger($fichier);
        // Selon votre structure, le tag peut être 'familles_produits' ou 'familles'
        if ($donnees['table'] !== 'familles_produits' && $donnees['table'] !== 'familles') {
            throw new \RuntimeException("Ce fichier ne correspond pas à une famille.");
        }

        $pdo = \Database::connexion();
        $famille = $donnees['principal'];
        $liees = $donnees['liees'] ?? [];

        try {
            $pdo->beginTransaction();

            // 1. Restaurer la famille elle-même avec son ID d'origine
            $sqlFamille = "INSERT INTO familles_produits (id, code, libelle, description)
                           VALUES (:id, :code, :libelle, :description)
                           ON CONFLICT (id) DO UPDATE SET code = EXCLUDED.code, libelle = EXCLUDED.libelle";
            $stmtF = $pdo->prepare($sqlFamille);
            $stmtF->execute([
                ':id'          => (int)$famille['id'],
                ':code'        => $famille['code'],
                ':libelle'     => $famille['libelle'],
                ':description' => $famille['description'] ?? null
            ]);

            // 2. Restaurer les produits liés à cette famille
            if (!empty($liees['produits'])) {
                foreach ($liees['produits'] as $p) {
                    // On vérifie si le produit n'a pas déjà été recréé entre temps
                    $sqlCheck = "SELECT 1 FROM produits WHERE id = :id";
                    $existe = \Database::query($sqlCheck, [':id' => (int)$p['id']])->fetch();

                    if (!$existe) {
                        // Attention : On exclut STRICTEMENT produit_pere_id de l'insertion !
                        $sqlProd = "INSERT INTO produits (
                                        id, code, designation, famille_id, est_pere,
                                        unite, prix_achat_ht, prix_vente_ht, tva,
                                        stock_actuel, stock_minimum, date_peremption, actif
                                    ) VALUES (
                                        :id, :code, :designation, :famille_id, :est_pere,
                                        :unite, :prix_achat_ht, :prix_vente_ht, :tva,
                                        :stock_actuel, :stock_minimum, :date_peremption, :actif
                                    )";
                        
                        $stmtP = $pdo->prepare($sqlProd);
                        $stmtP->execute([
                            ':id'               => (int)$p['id'],
                            ':code'             => $p['code'],
                            ':designation'      => $p['designation'],
                            ':famille_id'       => (int)$p['famille_id'],
                            ':est_pere'         => ($p['est_pere'] === 't' || $p['est_pere'] === '1' || $p['est_pere'] === true) ? 1 : 0,
                            ':unite'            => $p['unite'] ?? null,
                            ':prix_achat_ht'    => $p['prix_achat_ht'] ?? 0,
                            ':prix_vente_ht'    => $p['prix_vente_ht'] ?? 0,
                            ':tva'              => $p['tva'] ?? 0,
                            ':stock_actuel'     => $p['stock_actuel'] ?? 0,
                            ':stock_minimum'    => $p['stock_minimum'] ?? 0,
                            ':date_peremption'  => !empty($p['date_peremption']) ? $p['date_peremption'] : null,
                            ':actif'            => ($p['actif'] === 't' || $p['actif'] === '1' || $p['actif'] === true) ? 1 : 0
                        ]);
                    }
                }
            }

            // 3. Recaler les séquences PostgreSQL
            $pdo->query("SELECT setval('familles_produits_id_seq', COALESCE((SELECT MAX(id) FROM familles_produits), 1))");
            $pdo->query("SELECT setval('produits_id_seq', COALESCE((SELECT MAX(id) FROM produits), 1))");

            $pdo->commit();
            \Corbeille::supprimerFichier($fichier);

            $_SESSION['succes'] = "La famille '" . $famille['libelle'] . "' et ses produits ont été restaurés.";

        } catch (\Exception $e) {
            $pdo->rollBack();
            throw new \RuntimeException("Erreur lors de la restauration de la famille : " . $e->getMessage());
        }
    }

    public function restaurerFournisseur(string $fichier): void {
        $donnees = \Corbeille::charger($fichier);
        if ($donnees['table'] !== 'fournisseurs') {
            throw new \RuntimeException('Ce fichier ne correspond pas à un fournisseur.');
        }

        $p = $donnees['principal'];

        $sqlVerif = "SELECT 1 FROM fournisseurs WHERE code = :code OR raison_sociale = :rs";
        $existe = \Database::query($sqlVerif, [':code' => $p['code'] ?? '', ':rs' => $p['raison_sociale'] ?? ''])->fetch();
        if ($existe) {
            throw new \RuntimeException('Impossible de restaurer : fournisseur existant.');
        }

        $sql = "INSERT INTO fournisseurs (code, raison_sociale, contact, telephone, email, adresse, ville, pays, actif)
                VALUES (:code, :raison_sociale, :contact, :telephone, :email, :adresse, :ville, :pays, :actif)";
        \Database::query($sql, [
            ':code' => $p['code'] ?? null,
            ':raison_sociale' => $p['raison_sociale'] ?? null,
            ':contact' => $p['contact'] ?? null,
            ':telephone' => $p['telephone'] ?? null,
            ':email' => $p['email'] ?? null,
            ':adresse' => $p['adresse'] ?? null,
            ':ville' => $p['ville'] ?? null,
            ':pays' => $p['pays'] ?? 'Cameroun',
            ':actif' => ($p['actif'] === 't' || $p['actif'] === '1') ? true : false,
        ]);

        \Corbeille::supprimerFichier($fichier);
    }

    public function restaurerProduit(string $fichier): void {
        $donnees = \Corbeille::charger($fichier);
        if ($donnees['table'] !== 'produits') {
            throw new \RuntimeException("Ce fichier ne correspond pas à un produit.");
        }

        $pdo = Database::connexion();
        $produitModel = new ProduitModel();
        $fractionModel = new FractionModel();

        $p = $donnees['principal'];
        $liees = $donnees['liees'] ?? [];

        try {
            $pdo->beginTransaction();

            // --- DIRECTIVE 3 : MODIFICATION DU CODE À LA VOLÉE EN CAS DE CONFLIT ---
            // On vérifie le produit principal
            while ($produitModel->existeParCode($p['code'])) {
                // On ajoute un suffixe au code pour débloquer la restauration à la volée
                $p['code'] .= '_REST';
            }

            // On vérifie les codes des produits fils s'il y en a
            if (!empty($liees['produits_fils'])) {
                foreach ($liees['produits_fils'] as $index => $fils) {
                    while ($produitModel->existeParCode($fils['code'])) {
                        $liees['produits_fils'][$index]['code'] .= '_REST';
                    }
                }
            }

            // --- RESTAURATION DU PRODUIT PRINCIPAL (ID D'ORIGINE) ---
            $produitModel->restaurerAvecId($p);

            // --- RESTAURATION DES PRODUITS FILS EN CASCADE (Si le produit est un père) ---
            if (!empty($liees['produits_fils'])) {
                foreach ($liees['produits_fils'] as $fils) {
                    // Si le fils n'existe plus du tout en BDD, on le réinsère avec son ID d'origine
                    if (!$produitModel->existe((int)$fils['id'])) {
                        $produitModel->restaurerAvecId($fils);
                    }
                }
            }

            // --- RESTAURATION DES LIAISONS DANS FRACTIONS_PRODUIT ---
            if (!empty($liees['fractions_produit'])) {
                foreach ($liees['fractions_produit'] as $liaison) {
                    $fractionModel->restaurerLiaison($liaison);
                }
            }

            // --- MISE À JOUR STRUGTE DES SÉQUENCES POSTGRESQL (Évite les plantages futurs) ---
            // On recale la séquence sur le maximum actuel pour éviter les conflits d'auto-incrément futurs
            $pdo->query("SELECT setval('produits_id_seq', COALESCE((SELECT MAX(id) FROM produits), 1))");
            $pdo->query("SELECT setval('fractions_produit_id_seq', COALESCE((SELECT MAX(id) FROM fractions_produit), 1))");

            $pdo->commit();
            \Corbeille::supprimerFichier($fichier);

            $_SESSION['succes'] = "Le produit '" . $p['designation'] . "' ainsi que ses dépendances ont été restaurés avec succès.";

        } catch (\Exception $e) {
            $pdo->rollBack();
            throw new \RuntimeException("Erreur lors de la restauration du produit : " . $e->getMessage());
        }
    }

    public function restaurerVersement(string $fichier): void {
        $donnees = \Corbeille::charger($fichier);
        if ($donnees['table'] !== 'versements') {
            throw new \RuntimeException('Ce fichier ne correspond pas à un versement.');
        }

        $p = $donnees['principal'];

        // Vérifier la banque existe
        $banqueId = $p['banque_id'] ?? null;
        if (!$banqueId || !\Database::query("SELECT 1 FROM banques WHERE id = :id", [':id' => $banqueId])->fetch()) {
            throw new \RuntimeException('Impossible de restaurer : la banque associée est introuvable.');
        }

        $sql = "INSERT INTO versements (banque_id, montant, date_versement, reference, motif)
                VALUES (:banque_id, :montant, :date_versement, :reference, :motif)";
        \Database::query($sql, [
            ':banque_id' => $banqueId,
            ':montant' => $p['montant'] ?? 0,
            ':date_versement' => $p['date_versement'] ?: null,
            ':reference' => $p['reference'] ?? null,
            ':motif' => $p['motif'] ?? null,
        ]);

        \Corbeille::supprimerFichier($fichier);
    }
}
