<?php
// modules/structure/services/ProduitService.php

namespace structure\traitements\services;

use Database;

class ProduitService
{
    /**
     * Cas A & B : Enregistre ou met à jour un produit père et crée ses fils à la volée
     */
    public function creerProduitAvecFractions(array $donneesPere, array $fils): int {
        $pdo = Database::connexion();
        
        try {
            $pdo->beginTransaction();

            // 1. Vérifier si le produit père existe déjà (Fractionnement a posteriori - Cas A)
            $sqlVerif = "SELECT id FROM produits WHERE code = :code";
            $pereExistant = Database::query($sqlVerif, [':code' => $donneesPere['code']])->fetch();
            
            if ($pereExistant) {
                $idPere = (int)$pereExistant['id'];
                // Mettre à jour le produit existant pour qu'il devienne père (Directive 4)
                $sqlUp = "UPDATE produits SET est_pere = true WHERE id = :id";
                $stmtUp = $pdo->prepare($sqlUp);
                $stmtUp->execute([':id' => $idPere]);
            } else {
                // Création à la volée du père (Cas B)
                $sqlIn = "INSERT INTO produits (
                            code, designation, famille_id, est_pere, unite, 
                            prix_achat_ht, prix_vente_ht, tva, actif
                          ) VALUES (
                            :code, :designation, :famille_id, true, :unite, 
                            :prix_achat_ht, :prix_vente_ht, :tva, 1
                          ) RETURNING id";
                $stmtIn = $pdo->prepare($sqlIn);
                $stmtIn->execute([
                    ':code'          => $donneesPere['code'],
                    ':designation'   => $donneesPere['designation'],
                    ':famille_id'    => $donneesPere['famille_id'],
                    ':unite'         => $donneesPere['unite'] ?? null,
                    ':prix_achat_ht' => $donneesPere['prix_achat_ht'] ?? 0,
                    ':prix_vente_ht' => $donneesPere['prix_vente_ht'] ?? 0,
                    ':tva'           => $donneesPere['tva'] ?? 0,
                ]);
                $resPere = $stmtIn->fetch();
                $idPere = (int)$resPere['id'];
            }
            
            // 2. Traiter les produits fils à la volée
            foreach ($fils as $f) {
                $sqlVerifFils = "SELECT id FROM produits WHERE code = :code";
                $filsExistant = Database::query($sqlVerifFils, [':code' => $f['code']])->fetch();
                
                if ($filsExistant) {
                    $idFils = (int)$filsExistant['id'];
                } else {
                    // Création du produit fils à la volée
                    $sqlFils = "INSERT INTO produits (
                                    code, designation, famille_id, est_pere,
                                    unite, prix_vente_ht, tva, actif
                                ) VALUES (
                                    :code, :designation, :famille_id, false,
                                    :unite, :prix_vente_ht, :tva, 1
                                ) RETURNING id";
                    $stmtFils = $pdo->prepare($sqlFils);
                    $stmtFils->execute([
                        ':code'          => $f['code'],
                        ':designation'   => $f['designation'],
                        ':famille_id'    => $donneesPere['famille_id'], // hérite de la famille du père
                        ':unite'         => $f['unite'] ?? null,
                        ':prix_vente_ht' => $f['prix_vente_ht'] ?? 0,
                        ':tva'           => $donneesPere['tva'] ?? 0,
                    ]);
                    $resFils = $stmtFils->fetch();
                    $idFils = (int)$resFils['id'];
                }
                
                // 3. Créer ou mettre à jour la ligne dans fractions_produit
                $sqlFraction = "INSERT INTO fractions_produit (produit_pere_id, produit_fils_id, quantite_fils)
                                VALUES (:produit_pere_id, :produit_fils_id, :quantite_fils)
                                ON CONFLICT (produit_pere_id, produit_fils_id) DO UPDATE 
                                SET quantite_fils = EXCLUDED.quantite_fils";
                $stmtFrac = $pdo->prepare($sqlFraction);
                $stmtFrac->execute([
                    ':produit_pere_id' => $idPere,
                    ':produit_fils_id' => $idFils,
                    ':quantite_fils'   => $f['quantite_fils'] ?? 1,
                ]);
            }

            $pdo->commit();
            return $idPere;
            
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw new \RuntimeException("Erreur lors du traitement du produit et ses fractions : " . $e->getMessage());
        }
    }
}