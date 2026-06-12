<?php
// modules/Utilisateurs/Controllers/ParametreController.php

namespace Utilisateurs\Controllers;

use Utilisateurs\Models\Utilisateur;

class ParametreController extends \Controller {

    private Utilisateur $modele;

    public function __construct() {
        parent::__construct();
        $this->modele = new Utilisateur();
    }

    // GET /parametres
    public function index(): void {
        $id = (int) \Auth::utilisateurCourant()['id'];
        $utilisateur = $this->modele->trouverParId($id);

        $this->rendrePage('Utilisateurs', 'parametres', [
            'titre'       => 'Mes paramètres',
            'moduleActif' => 'parametres',
            'utilisateur' => $utilisateur,
            'succes'      => $_SESSION['succes'] ?? null,
            'erreur'      => $_SESSION['erreur'] ?? null,
        ]);
        unset($_SESSION['succes'], $_SESSION['erreur']);
    }

    // POST /parametres
    public function mettreAJour(): void {
        $this->verifierMethode('POST');

        $id = (int) \Auth::utilisateurCourant()['id'];
        $donnees = $this->nettoyerPost(['nom', 'prenom', 'email']);

        if ($donnees['nom'] === '') {
            $_SESSION['erreur'] = 'Le nom est obligatoire.';
            $this->redirigerVers('/parametres');
        }

        // Récupérer les valeurs actuelles pour conserver login/groupe/actif
        $actuel = $this->modele->trouverParId($id);

        $this->modele->mettreAJour($id, [
            'nom'       => $donnees['nom'],
            'prenom'    => $donnees['prenom'],
            'login'     => $actuel['login'],
            'email'     => $donnees['email'],
            'groupe_id' => $actuel['groupe_id'],
            'actif'     => $actuel['actif'],
        ]);

        // Changement de mot de passe (optionnel)
        $ancien   = $_POST['ancien_mot_de_passe'] ?? '';
        $nouveau  = $_POST['nouveau_mot_de_passe'] ?? '';
        $confirme = $_POST['confirmer_mot_de_passe'] ?? '';

        if ($nouveau !== '' || $confirme !== '' || $ancien !== '') {
            if (!password_verify($ancien, $actuel['mot_de_passe'])) {
                $_SESSION['erreur'] = 'L\'ancien mot de passe est incorrect.';
                $this->redirigerVers('/parametres');
            }
            if ($nouveau === '' || $nouveau !== $confirme) {
                $_SESSION['erreur'] = 'Le nouveau mot de passe et sa confirmation ne correspondent pas.';
                $this->redirigerVers('/parametres');
            }
            $this->modele->changerMotDePasse($id, password_hash($nouveau, PASSWORD_DEFAULT));
        }

        // Mettre à jour la session (nom affiché)
        \Auth::connecter($this->modele->trouverParId($id) + ['groupe_nom' => \Auth::utilisateurCourant()['groupe_nom']]);

        $_SESSION['succes'] = 'Paramètres mis à jour avec succès.';
        $this->redirigerVers('/parametres');
    }
}
