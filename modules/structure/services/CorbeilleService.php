<?php
// modules/structure/services/CorbeilleService.php
// Squelette de restauration pour les tables du module Structure.

namespace structure\services;

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
        if ($donnees['table'] !== 'familles_produits') {
            throw new \RuntimeException('Ce fichier ne correspond pas à une famille.');
        }

        $p = $donnees['principal'];
        $pdo = \Database::connexion();
        
        try {
            $pdo->beginTransaction();

            // 1. Récupérer l'ID d'origine de la famille
            $ancienFamilleId = $p['id'] ?? null;
            
            // 2. Vérifier si la famille existe déjà
            $sqlVerif = "SELECT id FROM familles_produits WHERE code = :code";
            $familleExistante = \Database::query($sqlVerif, [':code' => $p['code']])->fetch();
            
            if ($familleExistante) {
                // La famille existe déjà, on utilise son ID
                $nouvelleFamilleId = $familleExistante['id'];
            } else {
                // 3. Désactiver temporairement l'auto-incrément pour insérer un ID spécifique
                $pdo->exec("SET CONSTRAINTS ALL DEFERRED");
                
                // Permettre l'insertion d'un ID spécifique (PostgreSQL)
                $pdo->exec("SELECT setval('familles_produits_id_seq', (SELECT MAX(id) FROM familles_produits))");
                
                // Insérer la famille avec SON ID D'ORIGINE
                $sql = "INSERT INTO familles_produits (id, code, libelle, description, date_creation)
                        VALUES (:id, :code, :libelle, :description, :date_creation)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':id' => $ancienFamilleId,
                    ':code' => $p['code'] ?? null,
                    ':libelle' => $p['libelle'] ?? null,
                    ':description' => $p['description'] ?? null,
                    ':date_creation' => $p['date_creation'] ?? date('Y-m-d H:i:s'),
                ]);
                $nouvelleFamilleId = $ancienFamilleId;
                
                // Réactiver l'auto-incrément à la bonne valeur
                $pdo->exec("SELECT setval('familles_produits_id_seq', (SELECT MAX(id) FROM familles_produits))");
            }
            
            // 4. Restaurer les produits avec leurs IDs d'origine
            if (!empty($donnees['liees']['produits'])) {
                // Désactiver l'auto-incrément pour les produits
                $pdo->exec("SELECT setval('produits_id_seq', (SELECT MAX(id) FROM produits))");
                
                $sqlProd = "INSERT INTO produits (
                                id, code, designation, famille_id, produit_pere_id, est_pere,
                                unite, prix_achat_ht, prix_vente_ht, tva,
                                stock_actuel, stock_minimum, date_peremption, actif, date_creation
                            ) VALUES (
                                :id, :code, :designation, :famille_id, :produit_pere_id, :est_pere,
                                :unite, :prix_achat_ht, :prix_vente_ht, :tva,
                                :stock_actuel, :stock_minimum, :date_peremption, :actif, :date_creation
                            )";
                
                foreach ($donnees['liees']['produits'] as $prod) {
                    // Vérifier si le produit existe déjà
                    $verifProd = \Database::query("SELECT id FROM produits WHERE code = :code", [':code' => $prod['code']])->fetch();
                    if ($verifProd) {
                        continue;
                    }
                    
                    // Utiliser le NOUVEL ID de la famille
                    $familleId = $nouvelleFamilleId;
                    
                    // Remplacer l'ancien ID du produit père s'il existe
                    $produitPereId = null;
                    if (!empty($prod['produit_pere_id'])) {
                        // Chercher si le produit père a été restauré avec son ID d'origine
                        $pere = \Database::query("SELECT id FROM produits WHERE id = :id", [':id' => $prod['produit_pere_id']])->fetch();
                        if ($pere) {
                            $produitPereId = $pere['id'];
                        }
                    }
                    
                    \Database::query($sqlProd, [
                        ':id' => $prod['id'],
                        ':code' => $prod['code'] ?? null,
                        ':designation' => $prod['designation'] ?? null,
                        ':famille_id' => $familleId,
                        ':produit_pere_id' => $produitPereId,
                        ':est_pere' => ($prod['est_pere'] === 't' || $prod['est_pere'] === '1' || $prod['est_pere'] === true) ? 1 : 0,
                        ':unite' => $prod['unite'] ?? null,
                        ':prix_achat_ht' => $prod['prix_achat_ht'] ?? 0,
                        ':prix_vente_ht' => $prod['prix_vente_ht'] ?? 0,
                        ':tva' => $prod['tva'] ?? 0,
                        ':stock_actuel' => $prod['stock_actuel'] ?? 0,
                        ':stock_minimum' => $prod['stock_minimum'] ?? 0,
                        ':date_peremption' => $prod['date_peremption'] ?? null,
                        ':actif' => ($prod['actif'] === 't' || $prod['actif'] === '1' || $prod['actif'] === true) ? 1 : 0,
                        ':date_creation' => $prod['date_creation'] ?? date('Y-m-d H:i:s'),
                    ]);
                }
                
                // Réactiver l'auto-incrément
                $pdo->exec("SELECT setval('produits_id_seq', (SELECT MAX(id) FROM produits))");
            }

            $pdo->commit();
            \Corbeille::supprimerFichier($fichier);
            
            $_SESSION['succes'] = "Famille et ses produits restaurés avec leurs IDs d'origine.";
            
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw new \RuntimeException("Erreur lors de la restauration : " . $e->getMessage());
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

    /**
     * Restaure un produit et ses produits fils
     */
    public function restaurerProduit(string $fichier): void {
        $donnees = \Corbeille::charger($fichier);
        if ($donnees['table'] !== 'produits') {
            throw new \RuntimeException('Ce fichier ne correspond pas à un produit.');
        }

        $p = $donnees['principal'];
        $pdo = \Database::connexion();
        
        try {
            $pdo->beginTransaction();

            // Vérifier si le produit existe déjà
            $sqlVerif = "SELECT id FROM produits WHERE code = :code";
            $produitExistant = \Database::query($sqlVerif, [':code' => $p['code']])->fetch();
            
            if ($produitExistant) {
                \Corbeille::supprimerFichier($fichier);
                $_SESSION['info'] = "Le produit existe déjà, restauration ignorée.";
                return;
            }

            // Restaurer le produit principal
            $sql = "INSERT INTO produits (
                        id, code, designation, famille_id, est_pere,
                        unite, prix_achat_ht, prix_vente_ht, tva,
                        stock_actuel, stock_minimum, date_peremption, actif, date_creation
                    ) VALUES (
                        :id, :code, :designation, :famille_id, :est_pere,
                        :unite, :prix_achat_ht, :prix_vente_ht, :tva,
                        :stock_actuel, :stock_minimum, :date_peremption, :actif, :date_creation
                    )";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id' => $p['id'],
                ':code' => $p['code'] ?? null,
                ':designation' => $p['designation'] ?? null,
                ':famille_id' => $p['famille_id'] ?? null,
                ':est_pere' => ($p['est_pere'] === 't' || $p['est_pere'] === '1' || $p['est_pere'] === true) ? 1 : 0,
                ':unite' => $p['unite'] ?? null,
                ':prix_achat_ht' => $p['prix_achat_ht'] ?? 0,
                ':prix_vente_ht' => $p['prix_vente_ht'] ?? 0,
                ':tva' => $p['tva'] ?? 0,
                ':stock_actuel' => $p['stock_actuel'] ?? 0,
                ':stock_minimum' => $p['stock_minimum'] ?? 0,
                ':date_peremption' => $p['date_peremption'] ?? null,
                ':actif' => ($p['actif'] === 't' || $p['actif'] === '1' || $p['actif'] === true) ? 1 : 0,
                ':date_creation' => $p['date_creation'] ?? date('Y-m-d H:i:s'),
            ]);
            
            // Mettre à jour la séquence
            $pdo->exec("SELECT setval('produits_id_seq', (SELECT MAX(id) FROM produits))");
            
            // Restaurer les produits fils
            if (!empty($donnees['liees']['produits_fils'])) {
                foreach ($donnees['liees']['produits_fils'] as $fils) {
                    // Vérifier si le produit fils existe déjà
                    $verifFils = \Database::query("SELECT id FROM produits WHERE code = :code", [':code' => $fils['code']])->fetch();
                    if ($verifFils) {
                        continue;
                    }
                    
                    $sqlFils = "INSERT INTO produits (
                                    id, code, designation, famille_id, est_pere,
                                    unite, prix_achat_ht, prix_vente_ht, tva,
                                    stock_actuel, stock_minimum, date_peremption, actif, date_creation
                                ) VALUES (
                                    :id, :code, :designation, :famille_id, :est_pere,
                                    :unite, :prix_achat_ht, :prix_vente_ht, :tva,
                                    :stock_actuel, :stock_minimum, :date_peremption, :actif, :date_creation
                                )";
                    $stmt = $pdo->prepare($sqlFils);
                    $stmt->execute([
                        ':id' => $fils['id'],
                        ':code' => $fils['code'] ?? null,
                        ':designation' => $fils['designation'] ?? null,
                        ':famille_id' => $fils['famille_id'] ?? null,
                        ':est_pere' => 0,
                        ':unite' => $fils['unite'] ?? null,
                        ':prix_achat_ht' => $fils['prix_achat_ht'] ?? 0,
                        ':prix_vente_ht' => $fils['prix_vente_ht'] ?? 0,
                        ':tva' => $fils['tva'] ?? 0,
                        ':stock_actuel' => $fils['stock_actuel'] ?? 0,
                        ':stock_minimum' => $fils['stock_minimum'] ?? 0,
                        ':date_peremption' => $fils['date_peremption'] ?? null,
                        ':actif' => ($fils['actif'] === 't' || $fils['actif'] === '1' || $fils['actif'] === true) ? 1 : 0,
                        ':date_creation' => $fils['date_creation'] ?? date('Y-m-d H:i:s'),
                    ]);
                }
            }
            
            // Restaurer les fractions
            if (!empty($donnees['liees']['fractions_produit'])) {
                foreach ($donnees['liees']['fractions_produit'] as $fraction) {
                    $sqlFraction = "INSERT INTO fractions_produit (produit_pere_id, produit_fils_id, quantite_fils)
                                    VALUES (:produit_pere_id, :produit_fils_id, :quantite_fils)
                                    ON CONFLICT (produit_pere_id, produit_fils_id) DO UPDATE 
                                    SET quantite_fils = EXCLUDED.quantite_fils";
                    $stmt = $pdo->prepare($sqlFraction);
                    $stmt->execute([
                        ':produit_pere_id' => $fraction['produit_pere_id'],
                        ':produit_fils_id' => $fraction['produit_fils_id'],
                        ':quantite_fils' => $fraction['quantite_fils'] ?? 1,
                    ]);
                }
            }
            
            // Mettre à jour la séquence des produits
            $pdo->exec("SELECT setval('produits_id_seq', (SELECT MAX(id) FROM produits))");

            $pdo->commit();
            \Corbeille::supprimerFichier($fichier);
            
            $_SESSION['succes'] = "Produit et ses " . count($donnees['liees']['produits_fils'] ?? []) . " fils restaurés.";
            
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw new \RuntimeException("Erreur lors de la restauration : " . $e->getMessage());
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
