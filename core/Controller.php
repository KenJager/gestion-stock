<?php
// core/Controller.php — Classe mère de tous les controllers

abstract class Controller {

    protected ?array $utilisateurCourant = null;

    public function __construct() {
        Auth::verifierConnexion();
        $this->utilisateurCourant = Auth::utilisateurCourant();
    }
    ///bonjour

    /**
     * Rend une vue dans le layout principal via ob_start().
     *
     * @param string $module   Nom du module  (ex: 'approvisionnements')
     * @param string $vue      Nom de la vue  (ex: 'commande_liste')
     * @param array  $donnees  Variables injectées dans la vue ET le layout
     */
    protected function rendrePage(string $module, string $vue, array $donnees = []): void {
        extract($donnees);

        $cheminVue = ROOT . "/modules/{$module}/views/{$vue}.php";
        if (!file_exists($cheminVue)) {
            $this->erreur404("Vue introuvable : modules/{$module}/views/{$vue}.php");
        }

        ob_start();
        require $cheminVue;
        $contenuPage = ob_get_clean();

        // Variables disponibles dans base.php
        $titrePage   = $donnees['titre'] ?? APP_NOM;
        $moduleActif = $donnees['moduleActif'] ?? $module;

        require ROOT . '/views/layouts/base.php';
    }

    /**
     * Rend une vue SANS layout (ex: page de connexion).
     */
    protected function rendreVueSeule(string $cheminRelatif, array $donnees = []): void {
        extract($donnees);
        $chemin = ROOT . '/' . ltrim($cheminRelatif, '/');
        if (!file_exists($chemin)) {
            $this->erreur404("Vue introuvable : {$cheminRelatif}");
        }
        require $chemin;
    }

    protected function redirigerVers(string $chemin): void {
        header('Location: ' . BASE_URL . '/' . ltrim($chemin, '/'));
        exit;
    }

    protected function json(array $donnees, int $statut = 200): void {
        http_response_code($statut);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($donnees, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function verifierMethode(string $methode): void {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($methode)) {
            http_response_code(405);
            exit('Méthode non autorisée.');
        }
    }

    /**
     * Nettoie et récupère les champs POST demandés.
     * Les champs absents valent null.
    protected function nettoyerPost(array $champs): array {
        $donnees = [];
        foreach ($champs as $champ) {
            $donnees[$champ] = isset($_POST[$champ])
                ? trim(htmlspecialchars($_POST[$champ], ENT_QUOTES, 'UTF-8'))
                : null;
        }
        return $donnees;
    }
    */

    protected function nettoyerPost(array $cles): array {
        $resultat = [];
        foreach ($cles as $cle) {
            $valeur = $_POST[$cle] ?? null;
            
            // Si la valeur est un tableau (ex: lignes de commande), on nettoie récursivement
            if (is_array($valeur)) {
                $resultat[$cle] = array_map(function($item) {
                    if (is_array($item)) {
                        return array_map(function($v) {
                            return is_string($v) ? htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8') : $v;
                        }, $item);
                    }
                    return is_string($item) ? htmlspecialchars(trim($item), ENT_QUOTES, 'UTF-8') : $item;
                }, $valeur);
            } else {
                $resultat[$cle] = is_string($valeur) ? htmlspecialchars(trim($valeur), ENT_QUOTES, 'UTF-8') : $valeur;
            }
        }
        return $resultat;
    }

    protected function erreur404(string $message = 'Page introuvable'): never {
        http_response_code(404);
        exit("404 — {$message}");
    }
}