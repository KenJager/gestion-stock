<?php
// modules/utilisateurs/Controllers/DashboardController.php

namespace utilisateurs\controllers;

class DashboardController extends \Controller {

    public function index(): void {
        $aujourd_hui = date('Y-m-d');

        // KPI 1 – Ventes comptant du jour
        $ventesJour = \Database::query(
            "SELECT COALESCE(SUM(montant_ttc), 0) AS total, COUNT(*) AS nb
             FROM ventes_comptant WHERE date_vente = :date",
            [':date' => $aujourd_hui]
        )->fetch();

        // KPI 2 – Commandes fournisseurs en attente (brouillon ou validé)
        $commandesEnCours = (int) \Database::query(
            "SELECT COUNT(*) FROM bons_commande_fournisseur
             WHERE statut IN ('BROUILLON', 'VALIDÉ')"
        )->fetchColumn();

        // KPI 3 – Produits en rupture ou sous le seuil minimum
        $produitsCritiques = (int) \Database::query(
            "SELECT COUNT(*) FROM produits
             WHERE actif = true AND stock_actuel <= stock_minimum"
        )->fetchColumn();

        // KPI 4 – Total clients actifs
        $totalClients = (int) \Database::query(
            "SELECT COUNT(*) FROM clients WHERE actif = true"
        )->fetchColumn();

        // Ventes des 7 derniers jours pour le graphique
        $ventes7Jours = \Database::query(
            "SELECT date_vente::text AS jour,
                    COALESCE(SUM(montant_ttc), 0) AS total,
                    COUNT(*) AS nb
             FROM ventes_comptant
             WHERE date_vente >= CURRENT_DATE - INTERVAL '6 days'
             GROUP BY date_vente
             ORDER BY date_vente ASC"
        )->fetchAll();

        // Construire tableau complet sur 7 jours (avec jours sans ventes = 0)
        $graphique = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $graphique[$date] = ['jour' => $date, 'total' => 0, 'nb' => 0];
        }
        foreach ($ventes7Jours as $v) {
            if (isset($graphique[$v['jour']])) {
                $graphique[$v['jour']]['total'] = (float) $v['total'];
                $graphique[$v['jour']]['nb']    = (int)   $v['nb'];
            }
        }
        $graphique   = array_values($graphique);
        $maxGraphique = max(array_column($graphique, 'total')) ?: 1;

        // 5 dernières ventes comptant
        $dernieresVentes = \Database::query(
            "SELECT vc.id, vc.numero,
                    COALESCE(c.raison_sociale, 'Anonyme') AS client_nom,
                    vc.montant_ttc, vc.date_vente, vc.mode_paiement
             FROM ventes_comptant vc
             LEFT JOIN clients c ON c.id = vc.client_id
             ORDER BY vc.date_vente DESC, vc.id DESC
             LIMIT 5"
        )->fetchAll();

        // 5 dernières commandes fournisseurs
        $dernieresCommandes = \Database::query(
            "SELECT bcf.id, bcf.numero, f.raison_sociale AS fournisseur_nom,
                    bcf.montant_ttc, bcf.date_commande, bcf.statut
             FROM bons_commande_fournisseur bcf
             JOIN fournisseurs f ON f.id = bcf.fournisseur_id
             ORDER BY bcf.date_commande DESC, bcf.id DESC
             LIMIT 5"
        )->fetchAll();

        // Produits en alerte de stock (les plus critiques en premier)
        $produitsAlerte = \Database::query(
            "SELECT p.code, p.designation, p.stock_actuel, p.stock_minimum, p.unite
             FROM produits p
             WHERE p.actif = true AND p.stock_actuel <= p.stock_minimum
             ORDER BY (p.stock_actuel - p.stock_minimum) ASC
             LIMIT 8"
        )->fetchAll();

        // Réceptions du mois en cours
        $receptionsTotal = \Database::query(
            "SELECT COUNT(DISTINCT br.id) AS nb,
                    COALESCE(SUM(lr.quantite_recue * lr.prix_unitaire_ht * (1 + lr.tva / 100)), 0) AS total
             FROM bons_reception br
             JOIN lignes_reception lr ON lr.bon_reception_id = br.id
             WHERE EXTRACT(MONTH FROM br.date_reception) = EXTRACT(MONTH FROM CURRENT_DATE)
               AND EXTRACT(YEAR  FROM br.date_reception) = EXTRACT(YEAR  FROM CURRENT_DATE)"
        )->fetch();

        $this->rendrePage('utilisateurs', 'tableau_de_bord', [
            'titre'              => 'Tableau de bord',
            'moduleActif'        => 'tableau-de-bord',
            'ventesJour'         => $ventesJour,
            'commandesEnCours'   => $commandesEnCours,
            'produitsCritiques'  => $produitsCritiques,
            'totalClients'       => $totalClients,
            'graphique'          => $graphique,
            'maxGraphique'       => $maxGraphique,
            'dernieresVentes'    => $dernieresVentes,
            'dernieresCommandes' => $dernieresCommandes,
            'produitsAlerte'     => $produitsAlerte,
            'receptionsTotal'    => $receptionsTotal,
            'aujourd_hui'        => $aujourd_hui,
        ]);
    }
}
