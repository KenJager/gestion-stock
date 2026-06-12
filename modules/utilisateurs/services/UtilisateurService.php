<?php
// modules/Utilisateurs/services/UtilisateurService.php

namespace utilisateurs\services;

use utilisateurs\models\Utilisateur;

class UtilisateurService {

    private Utilisateur $modele;

    public function __construct() {
        $this->modele = new Utilisateur();
    }

    /**
     * Crée un utilisateur en hashant le mot de passe.
     */
    public function creerUtilisateur(array $donnees): int {
        if ($this->modele->loginExiste($donnees['login'])) {
            throw new \RuntimeException("Le login « {$donnees['login']} » est déjà utilisé.");
        }

        $donnees['mot_de_passe'] = password_hash($donnees['mot_de_passe'], PASSWORD_DEFAULT);

        return $this->modele->creer($donnees);
    }

    /**
     * Met à jour un utilisateur. Change le mot de passe seulement s'il est fourni.
     */
    public function mettreAJourUtilisateur(int $id, array $donnees): void {
        if ($this->modele->loginExiste($donnees['login'], $id)) {
            throw new \RuntimeException("Le login « {$donnees['login']} » est déjà utilisé par un autre utilisateur.");
        }

        $this->modele->mettreAJour($id, $donnees);

        if (!empty($donnees['mot_de_passe'])) {
            $hash = password_hash($donnees['mot_de_passe'], PASSWORD_DEFAULT);
            $this->modele->changerMotDePasse($id, $hash);
        }
    }
}
