<?php
// modules/Utilisateurs/Controllers/AuthController.php

namespace Utilisateurs\Controllers;

use Utilisateurs\Models\Utilisateur;

class AuthController {

    public function afficherLogin(): void {
        \Auth::demarrer();

        if (\Auth::estConnecte()) {
            header('Location: ' . BASE_URL . '/tableau-de-bord');
            exit;
        }

        $erreur = $_SESSION['erreur_connexion'] ?? null;
        unset($_SESSION['erreur_connexion']);

        require ROOT . '/modules/Utilisateurs/views/login.php';
    }

    public function traiterLogin(): void {
        \Auth::demarrer();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Méthode non autorisée.');
        }

        $login      = trim($_POST['login'] ?? '');
        $motDePasse = $_POST['mot_de_passe'] ?? '';

        if ($login === '' || $motDePasse === '') {
            $_SESSION['erreur_connexion'] = 'Veuillez renseigner votre login et votre mot de passe.';
            header('Location: ' . BASE_URL . '/connexion');
            exit;
        }

        $modeleUtilisateur = new Utilisateur();
        $utilisateur = $modeleUtilisateur->trouverParLogin($login);

        if (!$utilisateur || !password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
            $_SESSION['erreur_connexion'] = 'Login ou mot de passe incorrect.';
            header('Location: ' . BASE_URL . '/connexion');
            exit;
        }

        \Auth::connecter($utilisateur);

        \Database::query(
            "UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = :id",
            [':id' => $utilisateur['id']]
        );

        header('Location: ' . BASE_URL . '/tableau-de-bord');
        exit;
    }

    public function deconnecter(): void {
        \Auth::demarrer();
        \Auth::deconnecter();
        header('Location: ' . BASE_URL . '/connexion');
        exit;
    }

    public function accesRefuse(): void {
        \Auth::demarrer();
        http_response_code(403);
        require ROOT . '/modules/Utilisateurs/views/acces_refuse.php';
    }
}
