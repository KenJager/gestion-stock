<?php
// modules/structure/services/ClientService.php

require_once ROOT . '/modules/structure/traitements/models/ClientModel.php';
require_once ROOT . '/modules/structure/traitements/models/CategorieClientModel.php';

class ClientService
{
    private ClientModel $clientModel;
    private CategorieClientModel $categorieModel;

    public function __construct() {
        $this->clientModel = new ClientModel();
        $this->categorieModel = new CategorieClientModel();
    }

    /**
     * Calcule le prix avec remise en fonction de la catégorie du client
     */
    public function appliquerRemise(float $prixBase, ?int $categorieId): float {
        if (!$categorieId) return $prixBase;
        $categorie = $this->categorieModel->trouverParId($categorieId);
        if (!$categorie) return $prixBase;
        $remise = (float)($categorie['remise_pct'] ?? 0);
        return $prixBase * (1 - $remise / 100);
    }

    /**
     * Met à jour la catégorie de tous les clients (exemple de batch)
     */
    public function reclasserClients(array $ids, int $nouvelleCategorieId): void {
        foreach ($ids as $id) {
            $this->clientModel->mettreAJour($id, ['categorie_id' => $nouvelleCategorieId]);
        }
    }
}