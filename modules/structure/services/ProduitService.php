<?php
// modules/structure/services/ProduitService.php

namespace structure\services;

use Database;

class ProduitService
{
    /**
     * Crée les produits fils et les fractions pour un produit père existant
     */
    public function creerProduitAvecFractions(array $donneesPere, array $fils): int {
        $pdo = Database::connexion();
        
        try {
            $pdo->beginTransaction();

            // Vérifier si le produit père existe
            $sqlVerif = "SELECT id FROM produits WHERE code = :code";
            $pereExistant = Database::query($sqlVerif, [':code' => $donneesPere['code']])->fetch();
            
            if (!$pereExistant) {
                throw new \RuntimeException("Produit père introuvable.");
            }
            $idPere = $pereExistant['id'];
            
            // Créer chaque produit fils et la fraction
            foreach ($fils as $f) {
                // Vérifier si le produit fils existe déjà
                $sqlVerifFils = "SELECT id FROM produits WHERE code = :code";
                $filsExistant = Database::query($sqlVerifFils, [':code' => $f['code']])->fetch();
                
                if ($filsExistant) {
                    $idFils = $filsExistant['id'];
                } else {
                    $sqlFils = "INSERT INTO produits (
                                    code, designation, famille_id, est_pere,
                                    unite, prix_vente_ht, tva, actif
                                ) VALUES (
                                    :code, :designation, :famille_id, :est_pere,
                                    :unite, :prix_vente_ht, :tva, :actif
                                ) RETURNING id";
                    $stmt = $pdo->prepare($sqlFils);
                    $stmt->execute([
                        ':code' => $f['code'],
                        ':designation' => $f['designation'],
                        ':famille_id' => $f['famille_id'],
                        ':est_pere' => 0,
                        ':unite' => $f['unite'] ?? null,
                        ':prix_vente_ht' => $f['prix_vente_ht'] ?? 0,
                        ':tva' => $donneesPere['tva'] ?? 0,
                        ':actif' => 1,
                    ]);
                    $idFils = (int)$pdo->lastInsertId();
                }
                
                // Créer ou mettre à jour la fraction
                $sqlFraction = "INSERT INTO fractions_produit (produit_pere_id, produit_fils_id, quantite_fils)
                                VALUES (:produit_pere_id, :produit_fils_id, :quantite_fils)
                                ON CONFLICT (produit_pere_id, produit_fils_id) DO UPDATE 
                                SET quantite_fils = EXCLUDED.quantite_fils";
                $stmt = $pdo->prepare($sqlFraction);
                $stmt->execute([
                    ':produit_pere_id' => $idPere,
                    ':produit_fils_id' => $idFils,
                    ':quantite_fils' => $f['quantite_fils'] ?? 1,
                ]);
            }

            $pdo->commit();
            return $idPere;
            
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw new \RuntimeException("Erreur lors du fractionnement : " . $e->getMessage());
        }
    }
}