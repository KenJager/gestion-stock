<?php
// core/Corbeille.php — Gestion générique de la corbeille XML
// Chaque suppression "logique" est archivée dans un fichier XML avec
// possibilité de restauration. Les données liées (cascade) sont incluses.

class Corbeille {

    private static function dossier(): string {
        $dossier = ROOT . '/storage/corbeille';
        if (!is_dir($dossier)) {
            mkdir($dossier, 0775, true);
        }
        return $dossier;
    }

    /**
     * Archive un enregistrement (et ses données liées) dans un fichier XML
     * AVANT suppression en base.
     *
     * @param string $table        Nom de la table principale (ex: 'utilisateurs')
     * @param array  $donnees      Données de l'enregistrement principal
     * @param array  $liees        Données liées, format ['nom_table' => [ ...lignes... ], ...]
     * @param string $libelle      Libellé lisible pour l'affichage (ex: nom complet)
     * @return string              Nom du fichier XML créé
     */
    public static function archiver(string $table, array $donnees, array $liees = [], string $libelle = ''): string {
        $xml = new SimpleXMLElement('<corbeille/>');
        $xml->addChild('table', htmlspecialchars($table));
        $xml->addChild('libelle', htmlspecialchars($libelle));
        $xml->addChild('date_suppression', date('c'));

        $utilisateur = class_exists('Auth') ? Auth::utilisateurCourant() : null;
        $xml->addChild('supprime_par', htmlspecialchars($utilisateur['login'] ?? 'système'));

        // Enregistrement principal
        $principal = $xml->addChild('principal');
        self::ajouterChamps($principal, $donnees);

        // Données liées (cascade)
        if (!empty($liees)) {
            $liesNode = $xml->addChild('liees');
            foreach ($liees as $nomTable => $lignes) {
                $tableNode = $liesNode->addChild('table');
                $tableNode->addAttribute('nom', $nomTable);
                foreach ($lignes as $ligne) {
                    $ligneNode = $tableNode->addChild('ligne');
                    self::ajouterChamps($ligneNode, $ligne);
                }
            }
        }

        $id = $donnees['id'] ?? uniqid();
        $nomFichier = sprintf('%s_%s_%s.xml', $table, $id, date('Ymd_His'));
        $chemin = self::dossier() . '/' . $nomFichier;

        $xml->asXML($chemin);

        return $nomFichier;
    }

    private static function ajouterChamps(SimpleXMLElement $noeud, array $donnees): void {
        foreach ($donnees as $cle => $valeur) {
            $champ = $noeud->addChild('champ');
            $champ->addAttribute('nom', $cle);
            $champ[0] = $valeur === null ? '' : htmlspecialchars((string) $valeur);
            if ($valeur === null) {
                $champ->addAttribute('null', 'true');
            }
        }
    }

    /**
     * Liste les éléments de la corbeille, filtrés par table (optionnel).
     * Retourne un tableau de résumés (sans charger tout le contenu).
     */
    public static function lister(?string $table = null): array {
        $resultats = [];
        foreach (glob(self::dossier() . '/*.xml') as $chemin) {
            $xml = simplexml_load_file($chemin);
            if ($xml === false) continue;

            $nomTable = (string) $xml->table;
            if ($table !== null && $nomTable !== $table) continue;

            $resultats[] = [
                'fichier'          => basename($chemin),
                'table'            => $nomTable,
                'libelle'          => (string) $xml->libelle,
                'date_suppression' => (string) $xml->date_suppression,
                'supprime_par'     => (string) $xml->supprime_par,
                'nb_liees'         => isset($xml->liees) ? count($xml->liees->table) : 0,
            ];
        }

        // Tri par date décroissante
        usort($resultats, fn($a, $b) => strcmp($b['date_suppression'], $a['date_suppression']));
        return $resultats;
    }

    /**
     * Charge le contenu complet d'un fichier corbeille.
     * Retourne ['table' => ..., 'principal' => [...], 'liees' => ['table' => [...lignes]]]
     */
    public static function charger(string $fichier): array {
        $chemin = self::dossier() . '/' . basename($fichier);
        if (!file_exists($chemin)) {
            throw new RuntimeException("Fichier corbeille introuvable : {$fichier}");
        }

        $xml = simplexml_load_file($chemin);
        if ($xml === false) {
            throw new RuntimeException("Fichier corbeille invalide : {$fichier}");
        }

        $principal = self::lireChamps($xml->principal);

        $liees = [];
        if (isset($xml->liees)) {
            foreach ($xml->liees->table as $tableNode) {
                $nomTable = (string) $tableNode['nom'];
                $liees[$nomTable] = [];
                foreach ($tableNode->ligne as $ligneNode) {
                    $liees[$nomTable][] = self::lireChamps($ligneNode);
                }
            }
        }

        return [
            'table'    => (string) $xml->table,
            'libelle'  => (string) $xml->libelle,
            'principal' => $principal,
            'liees'    => $liees,
        ];
    }

    private static function lireChamps(SimpleXMLElement $noeud): array {
        $donnees = [];
        foreach ($noeud->champ as $champ) {
            $nom = (string) $champ['nom'];
            $estNull = isset($champ['null']) && (string) $champ['null'] === 'true';
            $donnees[$nom] = $estNull ? null : (string) $champ;
        }
        return $donnees;
    }

    /**
     * Supprime définitivement un fichier de la corbeille (après restauration ou purge).
     */
    public static function supprimerFichier(string $fichier): void {
        $chemin = self::dossier() . '/' . basename($fichier);
        if (file_exists($chemin)) {
            unlink($chemin);
        }
    }
}
