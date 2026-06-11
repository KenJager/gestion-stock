# MANUEL DU PROJET — SYSTÈME DE GESTION
## Document officiel de référence — À lire obligatoirement avant tout commit

**Version** : 1.0  
**Stack** : PHP pur · PostgreSQL · CSS natif · MVC  
**Équipes** : 4 groupes · 1 dépôt Git commun  

---

## TABLE DES MATIÈRES

1. [Structure du dépôt Git](#1-structure-du-dépôt-git)
2. [Règles Git — Branches & Commits](#2-règles-git--branches--commits)
3. [Architecture MVC — Dossiers & Fichiers](#3-architecture-mvc--dossiers--fichiers)
4. [Conventions de code PHP](#4-conventions-de-code-php)
5. [Système de layout — Sidebar / Header / Content](#5-système-de-layout--sidebar--header--content)
6. [CSS — Règles d'utilisation](#6-css--règles-dutilisation)
7. [Base de données — Règles d'accès](#7-base-de-données--règles-daccès)
8. [Système d'authentification & droits](#8-système-dauthentification--droits)
9. [Génération PDF — FPDF/TCPDF](#9-génération-pdf--fpdf--tcpdf)
10. [Gestion des stubs inter-modules](#10-gestion-des-stubs-inter-modules)
11. [Tableau des responsabilités](#11-tableau-des-responsabilités)
12. [Checklist avant chaque Pull Request](#12-checklist-avant-chaque-pull-request)

---

## 1. STRUCTURE DU DÉPÔT GIT

```
/
├── config/
│   ├── app.php                  ← constantes globales (APP_NAME, BASE_URL, USE_STUBS…)
│   └── database.php             ← connexion PDO PostgreSQL (NE PAS MODIFIER sans accord)
│
├── core/
│   ├── Database.php             ← classe singleton PDO — Groupe 4
│   ├── Controller.php           ← classe mère de tous les controllers — Groupe 4
│   ├── Model.php                ← classe mère de tous les models — Groupe 4
│   ├── Router.php               ← routeur central — Groupe 4
│   └── Auth.php                 ← sessions & droits — Groupe 4 (PRIORITÉ ABSOLUE)
│
├── modules/
│   ├── approvisionnements/      ← Groupe 1
│   │   ├── controllers/
│   │   ├── models/
│   │   ├── views/
│   │   ├── services/
│   │   ├── pdf/
│   │   └── stubs/               ← stubs temporaires (supprimés à la livraison réelle)
│   │
│   ├── ventes/                  ← Groupe 2
│   │   ├── controllers/
│   │   ├── models/
│   │   ├── views/
│   │   ├── services/
│   │   ├── pdf/
│   │   └── stubs/
│   │
│   ├── structure/               ← Groupe 3
│   │   ├── controllers/
│   │   ├── models/
│   │   ├── views/
│   │   ├── services/
│   │   └── stubs/
│   │
│   └── utilisateurs/            ← Groupe 4
│       ├── controllers/
│       ├── models/
│       ├── views/
│       └── services/
│
├── views/
│   └── layouts/
│       ├── base.php             ← layout principal (sidebar + header + content)
│       ├── sidebar.php          ← sidebar commune à tous les modules
│       ├── header.php           ← header commun
│       └── login.php            ← layout sans sidebar (page de connexion)
│
├── public/
│   ├── index.php                ← point d'entrée UNIQUE — NE PAS DUPLIQUER
│   └── assets/
│       ├── css/
│       │   └── app.css          ← fichier CSS natif (éditer directement si nécessaire)
│       ├── js/
│       │   └── app.js           ← scripts globaux
│       └── images/
│           └── logo.png
│
├── vendor/
│   └── fpdf/                    ← librairie PDF (commitée dans le dépôt)
│
├── (Le projet n'utilise plus Tailwind — styles dans `public/assets/css/app.css`)
├── .gitignore
└── README.md
```

---

## 2. RÈGLES GIT — BRANCHES & COMMITS

### 2.1 Branches autorisées

| Branche | Rôle | Qui peut merger dedans |
|---|---|---|
| `main` | Version finale livrée au professeur | Chef de projet uniquement |
| `develop` | Intégration de tous les modules | Un représentant par groupe, après validation |
| `feat/appros` | Travail Groupe 1 | Membres Groupe 1 uniquement |
| `feat/ventes` | Travail Groupe 2 | Membres Groupe 2 uniquement |
| `feat/structure` | Travail Groupe 3 | Membres Groupe 3 uniquement |
| `feat/users` | Travail Groupe 4 | Membres Groupe 4 uniquement |

> ⛔ **Interdit** : pousser directement sur `main` ou `develop` sans Pull Request validée.  
> ⛔ **Interdit** : modifier un fichier appartenant à un autre groupe sans son accord écrit (message dans le groupe).

### 2.2 Initialisation du dépôt (à faire une seule fois)

```bash
git clone https://github.com/votre-org/votre-repo.git
cd votre-repo

# Créer sa branche depuis develop
git checkout develop
git checkout -b feat/appros   # remplacer selon son groupe
git push -u origin feat/appros
```

### 2.3 Routine de travail quotidienne

```bash
# 1. Toujours mettre à jour develop avant de travailler
git checkout develop
git pull origin develop

# 2. Revenir sur sa branche et récupérer les mises à jour de develop
git checkout feat/appros
git merge develop

# 3. Travailler, puis committer
git add .
git commit -m "[APPROS] feat : formulaire création bon de commande fournisseur"

# 4. Pousser sa branche
git push origin feat/appros
```

### 2.4 Format obligatoire des messages de commit

```
[MODULE] type : description courte en français (max 72 caractères)
```

**Modules autorisés** : `APPROS` · `VENTES` · `STRUCTURE` · `USERS` · `CORE` · `LAYOUT` · `PDF`

**Types autorisés** :

| Type | Utilisation |
|---|---|
| `feat` | Nouvelle fonctionnalité |
| `fix` | Correction de bug |
| `style` | Modification CSS uniquement |
| `refactor` | Restructuration de code sans changement de comportement |
| `chore` | Tâche technique (suppression stub, renommage fichier…) |
| `docs` | Modification de documentation uniquement |

**Exemples corrects :**
```
[APPROS] feat : ajout controller bon de réception
[VENTES] fix : correction calcul remise sur facture client
[CORE] feat : méthode Auth::can() implémentée
[LAYOUT] style : ajout classe active sur item sidebar
[PDF] feat : génération bon de livraison client FPDF
[APPROS] chore : suppression stub AuthStub.php — core/Auth.php intégré
```

**Exemples incorrects :**
```
correction bug          ← pas de module, pas de type
[appros] Ajout form     ← module en minuscules
feat: fixed stuff       ← pas de module, description en anglais
update                  ← ne dit rien
```

### 2.5 Procédure de Pull Request vers develop

1. S'assurer que sa branche est à jour avec `develop` (voir 2.3)
2. Créer une Pull Request sur GitHub : `feat/monmodule` → `develop`
3. Le titre de la PR suit le même format que les commits
4. Ajouter en description : la liste des fonctionnalités ajoutées
5. Un membre d'un **autre groupe** doit approuver avant le merge
6. Résoudre tous les conflits **sur sa propre branche** avant de demander la review

### 2.6 Fichiers à ne jamais committer

Le fichier `.gitignore` doit contenir au minimum :

```
/vendor/composer/
.env
config/database.local.php
*.log
.DS_Store
Thumbs.db
node_modules/
```

> Les identifiants de base de données ne vont **jamais** dans un fichier commité.  
> Chaque développeur crée son propre `config/database.local.php` (ignoré par Git)  
> et `config/database.php` contient les valeurs par défaut pour le serveur commun.

---

## 3. ARCHITECTURE MVC — DOSSIERS & FICHIERS

### 3.1 Nommage des fichiers

| Type | Format | Exemple |
|---|---|---|
| Controller | `NomEntiteController.php` | `CommandeFournisseurController.php` |
| Model | `NomEntite.php` | `CommandeFournisseur.php` |
| Service | `NomEntiteService.php` | `StockService.php` |
| Vue liste | `nom_entite_liste.php` | `commande_liste.php` |
| Vue formulaire | `nom_entite_formulaire.php` | `commande_formulaire.php` |
| Vue détail | `nom_entite_detail.php` | `commande_detail.php` |
| Classe PDF | `NomEntitePDF.php` | `BonReceptionPDF.php` |
| Stub | `NomClasseStub.php` | `AuthStub.php` |

### 3.2 Structure d'un Controller

Chaque controller hérite de `Controller` et expose exactement ces méthodes :

```php
<?php
// modules/approvisionnements/controllers/CommandeFournisseurController.php

require_once ROOT . '/core/Controller.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/modules/approvisionnements/models/CommandeFournisseur.php';
require_once ROOT . '/modules/approvisionnements/services/CommandeService.php';

class CommandeFournisseurController extends Controller {

    private CommandeFournisseur $modele;
    private CommandeService $service;

    public function __construct() {
        Auth::verifierConnexion(); // redirige si non connecté
        $this->modele  = new CommandeFournisseur();
        $this->service = new CommandeService();
    }

    // GET — liste de toutes les commandes
    public function index(): void {
        Auth::verifierDroit('APPRO_LIRE');
        $commandes = $this->modele->listerTout();
        $this->rendrePage('approvisionnements', 'commande_liste', [
            'titre'     => 'Bons de commande fournisseur',
            'commandes' => $commandes,
        ]);
    }

    // GET — formulaire de création
    public function creer(): void {
        Auth::verifierDroit('APPRO_CREER');
        $fournisseurs = (new Fournisseur())->listerTout();
        $this->rendrePage('approvisionnements', 'commande_formulaire', [
            'titre'        => 'Nouvelle commande fournisseur',
            'fournisseurs' => $fournisseurs,
            'commande'     => null,
        ]);
    }

    // POST — enregistrement création
    public function enregistrer(): void {
        Auth::verifierDroit('APPRO_CREER');
        $this->verifierMethode('POST');
        $donnees = $this->nettoyerPost(['fournisseur_id', 'date_commande', 'notes']);
        $this->service->creerCommande($donnees);
        $this->redirigerVers('/approvisionnements/commandes');
    }

    // GET — formulaire d'édition
    public function editer(int $identifiant): void {
        Auth::verifierDroit('APPRO_MODIFIER');
        $commande = $this->modele->trouverParId($identifiant);
        $this->rendrePage('approvisionnements', 'commande_formulaire', [
            'titre'    => 'Modifier la commande',
            'commande' => $commande,
        ]);
    }

    // POST — enregistrement modification
    public function mettreAJour(int $identifiant): void {
        Auth::verifierDroit('APPRO_MODIFIER');
        $this->verifierMethode('POST');
        $donnees = $this->nettoyerPost(['fournisseur_id', 'date_commande', 'notes']);
        $this->service->mettreAJourCommande($identifiant, $donnees);
        $this->redirigerVers('/approvisionnements/commandes');
    }

    // POST — suppression
    public function supprimer(int $identifiant): void {
        Auth::verifierDroit('APPRO_SUPPRIMER');
        $this->verifierMethode('POST');
        $this->modele->supprimer($identifiant);
        $this->redirigerVers('/approvisionnements/commandes');
    }

    // GET — génération PDF
    public function imprimerPdf(int $identifiant): void {
        Auth::verifierDroit('APPRO_LIRE');
        require_once ROOT . '/modules/approvisionnements/pdf/BonCommandePDF.php';
        $commande = $this->modele->trouverComplet($identifiant);
        $pdf = new BonCommandePDF($commande);
        $pdf->generer(); // envoie le PDF au navigateur
    }
}
```

### 3.3 Structure d'un Model

```php
<?php
// modules/approvisionnements/models/CommandeFournisseur.php

require_once ROOT . '/core/Model.php';

class CommandeFournisseur extends Model {

    protected string $table = 'bons_commande_fournisseur';

    // Récupérer toutes les commandes avec le nom du fournisseur
    public function listerTout(): array {
        $sql = "SELECT bcf.*, f.raison_sociale AS fournisseur_nom
                FROM bons_commande_fournisseur bcf
                JOIN fournisseurs f ON f.id = bcf.fournisseur_id
                ORDER BY bcf.date_commande DESC";
        return $this->bd()->requete($sql)->toutRecuperer();
    }

    // Récupérer une commande avec ses lignes
    public function trouverComplet(int $identifiant): array {
        $sql = "SELECT bcf.*, f.raison_sociale AS fournisseur_nom
                FROM bons_commande_fournisseur bcf
                JOIN fournisseurs f ON f.id = bcf.fournisseur_id
                WHERE bcf.id = :id";
        $commande = $this->bd()->requete($sql, [':id' => $identifiant])->unRecuperer();

        $sqlLignes = "SELECT lcf.*, p.designation, p.unite
                      FROM lignes_commande_fournisseur lcf
                      JOIN produits p ON p.id = lcf.produit_id
                      WHERE lcf.bon_commande_id = :id";
        $commande['lignes'] = $this->bd()->requete($sqlLignes, [':id' => $identifiant])->toutRecuperer();

        return $commande;
    }

    // Créer une commande — retourne l'ID inséré
    public function creer(array $donnees): int {
        $sql = "INSERT INTO bons_commande_fournisseur
                    (numero, fournisseur_id, date_commande, statut, notes, utilisateur_id)
                VALUES
                    (:numero, :fournisseur_id, :date_commande, 'BROUILLON', :notes, :utilisateur_id)
                RETURNING id";
        $resultat = $this->bd()->requete($sql, $donnees)->unRecuperer();
        return (int) $resultat['id'];
    }

    // Supprimer (ON DELETE CASCADE gère les lignes enfants)
    public function supprimer(int $identifiant): void {
        $sql = "DELETE FROM bons_commande_fournisseur WHERE id = :id";
        $this->bd()->requete($sql, [':id' => $identifiant]);
    }
}
```

### 3.4 Structure d'un Service

Les services contiennent **toute la logique métier**. Ni les controllers ni les models n'effectuent de calculs.

```php
<?php
// modules/approvisionnements/services/CommandeService.php

require_once ROOT . '/modules/approvisionnements/models/CommandeFournisseur.php';
require_once ROOT . '/modules/approvisionnements/models/LigneCommandeFournisseur.php';

class CommandeService {

    private CommandeFournisseur $modeleCommande;

    public function __construct() {
        $this->modeleCommande = new CommandeFournisseur();
    }

    public function creerCommande(array $donnees): int {
        // Générer le numéro automatiquement
        $donnees['numero']        = $this->genererNumero('BCF');
        $donnees['utilisateur_id'] = $_SESSION['utilisateur_id'];

        $identifiant = $this->modeleCommande->creer($donnees);

        // Enregistrer les lignes si présentes
        if (!empty($donnees['lignes'])) {
            $modeleLigne = new LigneCommandeFournisseur();
            foreach ($donnees['lignes'] as $ligne) {
                $ligne['bon_commande_id'] = $identifiant;
                $modeleLigne->creer($ligne);
            }
        }

        return $identifiant;
    }

    private function genererNumero(string $prefixe): string {
        return $prefixe . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
    }
}
```

---

## 4. CONVENTIONS DE CODE PHP

### 4.1 Règles fondamentales

```php
<?php
// ✅ CORRECT — une classe par fichier, nom identique au fichier
class CommandeFournisseur extends Model { ... }

// ✅ CORRECT — nommage en français, camelCase pour méthodes et variables
$nombreLignes = 0;
function calculerTotalTTC(array $lignes): float { ... }

// ✅ CORRECT — constantes en MAJUSCULES_AVEC_TIRET_BAS
define('TAUX_TVA_DEFAUT', 19.25);

// ✅ CORRECT — commentaire de bloc pour chaque méthode publique
/**
 * Calcule le montant TTC d'une ligne à partir du HT et de la TVA.
 *
 * @param float $montantHT   Montant hors taxe
 * @param float $tauxTVA     Taux de TVA en pourcentage (ex: 19.25)
 * @return float             Montant toutes taxes comprises
 */
public function calculerTTC(float $montantHT, float $tauxTVA): float {
    return $montantHT * (1 + $tauxTVA / 100);
}
```

### 4.2 Règles de sécurité obligatoires

```php
// ✅ TOUJOURS utiliser des requêtes préparées — JAMAIS d'interpolation SQL
// CORRECT
$sql = "SELECT * FROM produits WHERE id = :id AND actif = true";
$this->bd()->requete($sql, [':id' => $identifiant]);

// ⛔ INTERDIT — injection SQL possible
$sql = "SELECT * FROM produits WHERE id = " . $identifiant;

// ✅ TOUJOURS échapper les sorties HTML
echo htmlspecialchars($produit['designation'], ENT_QUOTES, 'UTF-8');

// ✅ TOUJOURS valider et nettoyer les données POST avant traitement
$identifiant = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($identifiant === false || $identifiant === null) {
    // erreur — identifiant invalide
}

// ✅ TOUJOURS vérifier la méthode HTTP dans les actions d'écriture
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Méthode non autorisée');
}
```

### 4.3 Interdictions formelles

```php
// ⛔ INTERDIT — requête SQL dans une vue
// fichier : views/commande_liste.php
$resultat = pg_query("SELECT * FROM ...");  // NON

// ⛔ INTERDIT — logique métier dans un controller
public function enregistrer(): void {
    $total = 0;
    foreach ($lignes as $ligne) {            // NON — ça appartient au Service
        $total += $ligne['prix'] * $ligne['quantite'];
    }
}

// ⛔ INTERDIT — accès direct à $_POST dans un model
public function creer(): void {
    $nom = $_POST['nom'];  // NON — les données arrivent en paramètre
}

// ⛔ INTERDIT — echo ou print dans un model ou service
public function listerTout(): array {
    echo "chargement...";  // NON
}

// ⛔ INTERDIT — var_dump ou print_r laissé dans le code commité
var_dump($resultat);  // NON — utiliser uniquement en développement local, jamais commiter
```

### 4.4 Gestion des erreurs

```php
// ✅ CORRECT — toujours vérifier le retour des opérations critiques
public function trouverParId(int $identifiant): array {
    $resultat = $this->bd()->requete($sql, [':id' => $identifiant])->unRecuperer();
    if (!$resultat) {
        throw new RuntimeException("Enregistrement introuvable : id = {$identifiant}");
    }
    return $resultat;
}

// ✅ CORRECT — attraper les exceptions dans le controller
public function editer(int $identifiant): void {
    try {
        $commande = $this->modele->trouverParId($identifiant);
        $this->rendrePage(...);
    } catch (RuntimeException $erreur) {
        $this->rendrePage('erreurs', '404', ['message' => $erreur->getMessage()]);
    }
}
```

---

## 5. SYSTÈME DE LAYOUT — SIDEBAR / HEADER / CONTENT

### 5.1 Fonctionnement général

Toutes les pages du système utilisent **un seul layout central** (`views/layouts/base.php`). Chaque vue de module s'insère dans la zone `content` grâce à `ob_start()` / `ob_get_clean()`.

```
┌─────────────────────────────────────────────────────────┐
│                        base.php                         │
│  ┌──────────┬────────────────────────────────────────┐  │
│  │          │           header.php                   │  │
│  │          ├────────────────────────────────────────┤  │
│  │ sidebar  │                                        │  │
│  │  .php    │         $contenuPage                   │  │
│  │ (h-full) │     ← ob_start() dans chaque vue       │  │
│  │          │                                        │  │
│  └──────────┴────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
```

### 5.2 Fichier `views/layouts/base.php`

```php
<?php
// views/layouts/base.php
// Reçoit : $titrePage, $moduleActif, $contenuPage
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titrePage ?? 'Système de gestion') ?> — SGestion</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="flex h-screen bg-gray-100 font-sans overflow-hidden">

    <!-- SIDEBAR -->
    <?php require_once ROOT . '/views/layouts/sidebar.php'; ?>

    <!-- ZONE PRINCIPALE -->
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

        <!-- HEADER -->
        <?php require_once ROOT . '/views/layouts/header.php'; ?>

        <!-- CONTENU DE LA PAGE -->
        <main class="flex-1 overflow-y-auto p-6">
            <?= $contenuPage ?>
        </main>

    </div>

</body>
</html>
```

### 5.3 Fichier `views/layouts/sidebar.php`

```php
<?php
// views/layouts/sidebar.php
// Reçoit : $moduleActif (ex: 'approvisionnements', 'ventes', 'structure', 'utilisateurs')

$menuItems = [
    [
        'module'  => 'tableau-de-bord',
        'libelle' => 'Tableau de bord',
        'icone'   => 'fa-solid fa-gauge',
        'lien'    => BASE_URL . '/tableau-de-bord',
        'droit'   => null, // accessible à tous les connectés
    ],
    [
        'module'  => 'approvisionnements',
        'libelle' => 'Approvisionnements',
        'icone'   => 'fa-solid fa-boxes-stacked',
        'lien'    => BASE_URL . '/approvisionnements',
        'droit'   => 'APPRO_LIRE',
    ],
    [
        'module'  => 'ventes',
        'libelle' => 'Ventes',
        'icone'   => 'fa-solid fa-cart-shopping',
        'lien'    => BASE_URL . '/ventes',
        'droit'   => 'VENTE_LIRE',
    ],
    [
        'module'  => 'structure',
        'libelle' => 'Structure',
        'icone'   => 'fa-solid fa-layer-group',
        'lien'    => BASE_URL . '/structure',
        'droit'   => 'STRUCTURE_LIRE',
    ],
    [
        'module'  => 'utilisateurs',
        'libelle' => 'Utilisateurs',
        'icone'   => 'fa-solid fa-users',
        'lien'    => BASE_URL . '/utilisateurs',
        'droit'   => 'USERS_LIRE',
    ],
];
?>
<aside class="w-64 bg-gray-900 text-white flex flex-col h-screen flex-shrink-0">

    <!-- Logo -->
    <div class="px-6 py-5 border-b border-gray-700">
        <span class="text-lg font-semibold tracking-wide">SGestion</span>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        <?php foreach ($menuItems as $item): ?>
            <?php if ($item['droit'] === null || Auth::peut($item['droit'])): ?>
                <a href="<?= $item['lien'] ?>"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-colors
                          <?= ($moduleActif ?? '') === $item['module']
                              ? 'bg-blue-600 text-white font-medium'
                              : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                    <i class="<?= $item['icone'] ?> w-5 text-center"></i>
                    <?= htmlspecialchars($item['libelle']) ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <!-- Utilisateur connecté -->
    <div class="px-4 py-4 border-t border-gray-700">
        <p class="text-xs text-gray-400 mb-1">Connecté en tant que</p>
        <p class="text-sm font-medium text-white truncate">
            <?= htmlspecialchars(Auth::utilisateurCourant()['nom'] ?? '') ?>
        </p>
        <a href="<?= BASE_URL ?>/deconnexion"
           class="mt-2 flex items-center gap-2 text-xs text-gray-400 hover:text-red-400 transition-colors">
            <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
        </a>
    </div>

</aside>
```

### 5.4 Fichier `views/layouts/header.php`

```php
<?php
// views/layouts/header.php
// Reçoit : $titrePage
?>
<header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between flex-shrink-0">

    <h1 class="text-xl font-semibold text-gray-800">
        <?= htmlspecialchars($titrePage ?? '') ?>
    </h1>

    <div class="flex items-center gap-4 text-sm text-gray-500">
        <span><i class="fa-regular fa-calendar mr-1"></i><?= date('d/m/Y') ?></span>
        <span class="bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-xs font-medium">
            <?= htmlspecialchars(Auth::utilisateurCourant()['groupe_nom'] ?? 'Non défini') ?>
        </span>
    </div>

</header>
```

### 5.5 Méthode `rendrePage()` dans `core/Controller.php`

```php
<?php
// core/Controller.php

class Controller {

    /**
     * Rend une vue dans le layout principal.
     *
     * @param string $module     Nom du module (ex: 'approvisionnements')
     * @param string $vue        Nom du fichier vue sans .php (ex: 'commande_liste')
     * @param array  $donnees    Variables passées à la vue et au layout
     */
    protected function rendrePage(string $module, string $vue, array $donnees = []): void {
        // Rendre les variables disponibles dans la vue
        extract($donnees);

        // Capturer le contenu de la vue avec ob_start
        ob_start();
        $cheminVue = ROOT . "/modules/{$module}/views/{$vue}.php";
        if (!file_exists($cheminVue)) {
            throw new RuntimeException("Vue introuvable : {$cheminVue}");
        }
        require $cheminVue;
        $contenuPage = ob_get_clean();

        // Injecter dans le layout principal
        require ROOT . '/views/layouts/base.php';
    }

    protected function redirigerVers(string $chemin): void {
        header('Location: ' . BASE_URL . $chemin);
        exit;
    }

    protected function verifierMethode(string $methode): void {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($methode)) {
            http_response_code(405);
            exit('Méthode non autorisée');
        }
    }

    protected function nettoyerPost(array $champs): array {
        $donnees = [];
        foreach ($champs as $champ) {
            $donnees[$champ] = isset($_POST[$champ])
                ? trim(htmlspecialchars($_POST[$champ], ENT_QUOTES, 'UTF-8'))
                : null;
        }
        return $donnees;
    }
}
```

### 5.6 Structure d'une vue de module

```php
<?php
// modules/approvisionnements/views/commande_liste.php
// Variables disponibles : $commandes, $titrePage (injectées par rendrePage)
// NE PAS inclure de balises <html>, <head>, <body> ici
// NE PAS appeler ob_start() manuellement — c'est fait par rendrePage()
?>

<div class="space-y-4">

    <!-- Barre d'actions -->
    <div class="flex justify-between items-center">
        <p class="text-sm text-gray-500"><?= count($commandes) ?> commande(s) trouvée(s)</p>
        <?php if (Auth::peut('APPRO_CREER')): ?>
            <a href="<?= BASE_URL ?>/approvisionnements/commandes/creer"
               class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-md
                      flex items-center gap-2 transition-colors">
                <i class="fa-solid fa-plus"></i> Nouvelle commande
            </a>
        <?php endif; ?>
    </div>

    <!-- Tableau -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Numéro</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Fournisseur</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Statut</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($commandes as $commande): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">
                            <?= htmlspecialchars($commande['numero']) ?>
                        </td>
                        <td class="px-4 py-3">
                            <?= htmlspecialchars($commande['fournisseur_nom']) ?>
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            <?= date('d/m/Y', strtotime($commande['date_commande'])) ?>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                <?= match($commande['statut']) {
                                    'BROUILLON'    => 'bg-gray-100 text-gray-700',
                                    'VALIDÉ'       => 'bg-blue-50 text-blue-700',
                                    'REÇU_TOTAL'   => 'bg-green-50 text-green-700',
                                    'ANNULÉ'       => 'bg-red-50 text-red-700',
                                    default        => 'bg-gray-100 text-gray-600',
                                } ?>">
                                <?= htmlspecialchars($commande['statut']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="<?= BASE_URL ?>/approvisionnements/commandes/editer/<?= $commande['id'] ?>"
                               class="text-blue-600 hover:text-blue-800">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="<?= BASE_URL ?>/approvisionnements/commandes/pdf/<?= $commande['id'] ?>"
                               class="text-gray-500 hover:text-gray-700" target="_blank">
                                <i class="fa-solid fa-file-pdf"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>
```

---

## 6. CSS — RÈGLES D'UTILISATION

### 6.1 Gestion des styles

Le projet utilise du CSS natif pour les styles. Modifiez directement `public/assets/css/app.css` pour ajuster l'apparence globale. Il n'y a pas d'étape de compilation Tailwind dans ce dépôt.

> Le fichier `public/assets/css/app.css` doit être commité dans le dépôt.  
> Le dossier `node_modules/` ne doit jamais être commité.

### 6.2 Classes utilitaires communes — à utiliser de manière cohérente

| Élément | Classes utilitaires |
|---|---|
| Carte/panneau | `bg-white rounded-lg border border-gray-200 p-4` |
| Bouton primaire | `bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm transition-colors` |
| Bouton secondaire | `bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm transition-colors` |
| Bouton danger | `bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm transition-colors` |
| Champ input | `w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500` |
| Label | `block text-sm font-medium text-gray-700 mb-1` |
| Message succès | `bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md text-sm` |
| Message erreur | `bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md text-sm` |
| En-tête tableau | `bg-gray-50 border-b border-gray-200` · cellules : `px-4 py-3 text-left font-medium text-gray-600` |
| Ligne tableau | `hover:bg-gray-50` · cellules : `px-4 py-3` |

---

## 7. BASE DE DONNÉES — RÈGLES D'ACCÈS

### 7.1 `core/Database.php` — Singleton PDO

```php
<?php
// core/Database.php

class Database {

    private static ?Database $instance = null;
    private PDO $connexion;

    private function __construct() {
        require_once ROOT . '/config/database.php';
        try {
            $this->connexion = new PDO(
                "pgsql:host=" . DB_HOTE . ";dbname=" . DB_NOM . ";port=" . DB_PORT,
                DB_UTILISATEUR,
                DB_MOT_DE_PASSE,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $erreur) {
            error_log($erreur->getMessage());
            die("Erreur de connexion à la base de données.");
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function requete(string $sql, array $parametres = []): self {
        $this->dernierResultat = $this->connexion->prepare($sql);
        $this->dernierResultat->execute($parametres);
        return $this;
    }

    public function toutRecuperer(): array {
        return $this->dernierResultat->fetchAll();
    }

    public function unRecuperer(): array|false {
        return $this->dernierResultat->fetch();
    }

    public function dernierIdentifiant(): string {
        return $this->connexion->lastInsertId();
    }
}
```

### 7.2 Règles SQL

- Toujours utiliser des **paramètres nommés** (`:nom`) plutôt que `?`
- Toujours utiliser `RETURNING id` après un `INSERT` pour récupérer l'identifiant
- Ne jamais construire une requête SQL par concaténation de variables
- Les transactions (`BEGIN` / `COMMIT` / `ROLLBACK`) sont obligatoires quand plusieurs tables sont modifiées ensemble (ex: créer une facture + ses lignes)

```php
// ✅ CORRECT — transaction pour opération multi-tables
public function creerAvecLignes(array $entete, array $lignes): int {
    $bd = Database::getInstance();
    try {
        $bd->connexion->beginTransaction();
        // ... insertions ...
        $bd->connexion->commit();
        return $identifiant;
    } catch (Exception $erreur) {
        $bd->connexion->rollBack();
        throw $erreur;
    }
}
```

---

## 8. SYSTÈME D'AUTHENTIFICATION & DROITS

### 8.1 Contrat `core/Auth.php` (Groupe 4 — livraison semaine 1)

```php
// Méthodes garanties disponibles

Auth::verifierConnexion(): void
// Redirige vers /connexion si aucune session active

Auth::verifierDroit(string $codeDroit): void
// Redirige vers /acces-refuse si l'utilisateur n'a pas le droit

Auth::peut(string $codeDroit): bool
// Retourne true/false — pour les affichages conditionnels dans les vues

Auth::utilisateurCourant(): array
// Retourne ['id', 'nom', 'prenom', 'login', 'groupe_id', 'groupe_nom']
```

### 8.2 Codes de droits par module

| Code | Module | Action |
|---|---|---|
| `APPRO_LIRE` | Approvisionnements | Consulter |
| `APPRO_CREER` | Approvisionnements | Créer |
| `APPRO_MODIFIER` | Approvisionnements | Éditer |
| `APPRO_SUPPRIMER` | Approvisionnements | Supprimer |
| `VENTE_LIRE` | Ventes | Consulter |
| `VENTE_CREER` | Ventes | Créer |
| `VENTE_MODIFIER` | Ventes | Éditer |
| `VENTE_SUPPRIMER` | Ventes | Supprimer |
| `STRUCTURE_LIRE` | Structure | Consulter |
| `STRUCTURE_CREER` | Structure | Créer |
| `STRUCTURE_MODIFIER` | Structure | Éditer |
| `STRUCTURE_SUPPRIMER` | Structure | Supprimer |
| `USERS_LIRE` | Utilisateurs | Consulter |
| `USERS_CREER` | Utilisateurs | Créer |
| `USERS_MODIFIER` | Utilisateurs | Éditer |
| `USERS_SUPPRIMER` | Utilisateurs | Supprimer |

### 8.3 Stub à utiliser en attendant `Auth.php`

```php
<?php
// modules/[votre_module]/stubs/AuthStub.php
// ⚠️ FICHIER TEMPORAIRE — supprimer dès que core/Auth.php est livré

class Auth {
    public static function verifierConnexion(): void { /* rien */ }
    public static function verifierDroit(string $code): void { /* rien */ }
    public static function peut(string $code): bool { return true; }
    public static function utilisateurCourant(): array {
        return [
            'id'         => 1,
            'nom'        => 'Utilisateur',
            'prenom'     => 'Test',
            'login'      => 'test',
            'groupe_id'  => 1,
            'groupe_nom' => 'Administrateurs',
        ];
    }
}
```

---

## 9. GÉNÉRATION PDF — FPDF/TCPDF

### 9.1 Structure d'une classe PDF

```php
<?php
// modules/approvisionnements/pdf/BonCommandePDF.php

require_once ROOT . '/vendor/fpdf/fpdf.php';

class BonCommandePDF extends FPDF {

    private array $commande;

    public function __construct(array $commande) {
        parent::__construct('P', 'mm', 'A4');
        $this->commande = $commande;
        $this->SetMargins(15, 15, 15);
        $this->SetAutoPageBreak(true, 20);
    }

    // En-tête de page (appelé automatiquement par FPDF)
    public function Header(): void {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'BON DE COMMANDE FOURNISSEUR', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, 'N° ' . $this->commande['numero'], 0, 1, 'C');
        $this->Ln(5);
    }

    // Pied de page
    public function Footer(): void {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // Méthode principale — envoie le PDF au navigateur
    public function generer(): void {
        $this->AliasNbPages();
        $this->AddPage();
        $this->construireCorps();
        $this->Output('I', 'bon_commande_' . $this->commande['numero'] . '.pdf');
    }

    private function construireCorps(): void {
        // Informations fournisseur
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 6, 'Fournisseur : ' . $this->commande['fournisseur_nom'], 0, 1);
        $this->Ln(4);

        // En-tête du tableau des lignes
        $this->SetFillColor(230, 230, 230);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(80, 7, 'Désignation', 1, 0, 'L', true);
        $this->Cell(25, 7, 'Qté', 1, 0, 'C', true);
        $this->Cell(30, 7, 'PU HT', 1, 0, 'R', true);
        $this->Cell(35, 7, 'Montant HT', 1, 1, 'R', true);

        // Lignes
        $this->SetFont('Arial', '', 9);
        foreach ($this->commande['lignes'] as $ligne) {
            $this->Cell(80, 6, $ligne['designation'], 1);
            $this->Cell(25, 6, $ligne['quantite_commandee'], 1, 0, 'C');
            $this->Cell(30, 6, number_format($ligne['prix_unitaire_ht'], 0, ',', ' '), 1, 0, 'R');
            $this->Cell(35, 6, number_format($ligne['montant_ht'], 0, ',', ' '), 1, 1, 'R');
        }
    }
}
```

---

## 10. GESTION DES STUBS INTER-MODULES

### 10.1 Principe

Un stub est un **remplacement temporaire** d'une dépendance non encore livrée. Il respecte exactement la même signature que la vraie classe.

### 10.2 Déclarer une dépendance dans `config/app.php`

```php
<?php
// config/app.php

define('APP_NOM',    'Système de gestion');
define('BASE_URL',   'http://localhost/projet/public');
define('ROOT',       dirname(__DIR__));

// ---- Stubs actifs (mettre à false dès que le vrai code est livré) ----
define('STUB_AUTH',        false);  // true = utilise AuthStub.php
define('STUB_PRODUIT',     false);  // true = utilise ProduitStub.php
define('STUB_FOURNISSEUR', false);
define('STUB_CLIENT',      false);
define('STUB_BANQUE',      false);
```

### 10.3 Chargement conditionnel dans `public/index.php`

```php
// Chargement Auth
if (STUB_AUTH) {
    require_once ROOT . '/modules/approvisionnements/stubs/AuthStub.php';
} else {
    require_once ROOT . '/core/Auth.php';
}
```

### 10.4 Règle de suppression des stubs

Quand le vrai code est livré sur `develop` :
1. Mettre la constante à `false` dans `config/app.php`
2. Supprimer le fichier stub
3. Committer avec le message : `[MODULE] chore : stub NomClasse supprimé — core livré`

---

## 11. TABLEAU DES RESPONSABILITÉS

| Fichier / Fonctionnalité | Responsable | Dépend de | Délai cible |
|---|---|---|---|
| `core/Auth.php` complet | Groupe 4 | — | Semaine 1 |
| `core/Database.php` | Groupe 4 | — | Semaine 1 |
| `core/Controller.php` | Groupe 4 | — | Semaine 1 |
| `views/layouts/base.php` | Groupe 4 | — | Semaine 1 |
| `views/layouts/sidebar.php` | Groupe 4 | Auth.php | Semaine 1 |
| `StockService.php` | Groupes 1 + 2 (co-écrit) | Produit | Semaine 2 |
| Module Structure complet | Groupe 3 | Auth.php | Semaines 2-3 |
| Module Approvisionnements | Groupe 1 | Auth + Produit + Fournisseur | Semaines 2-4 |
| Module Ventes | Groupe 2 | Auth + Produit + Client | Semaines 2-4 |
| PDFs tous modules | Chaque groupe | Son module complet | Semaine 5 |

---

## 12. CHECKLIST AVANT CHAQUE PULL REQUEST

Avant d'ouvrir une PR vers `develop`, vérifier chaque point :

**Git**
- [ ] Ma branche est à jour avec `develop` (`git merge develop` effectué)
- [ ] Tous mes commits respectent le format `[MODULE] type : description`
- [ ] Aucun fichier de configuration sensible n'est commité
- [ ] Le fichier `public/assets/css/app.css` compilé est inclus

**Code**
- [ ] Aucun `var_dump`, `print_r` ou `echo` de debug laissé dans le code
- [ ] Toutes les sorties HTML utilisent `htmlspecialchars()`
- [ ] Toutes les requêtes SQL utilisent des paramètres préparés
- [ ] Aucune requête SQL directement dans une vue
- [ ] Chaque action d'écriture vérifie la méthode HTTP (POST)
- [ ] Chaque page protégée appelle `Auth::verifierConnexion()`

**Structure**
- [ ] Chaque controller hérite de `Controller`
- [ ] Chaque model hérite de `Model`
- [ ] Les vues n'appellent pas `ob_start()` manuellement
- [ ] Les vues ne contiennent pas de balises `<html>`, `<head>`, `<body>`
- [ ] Les stubs non nécessaires ont été supprimés

**Interface**
- [ ] Les classes CSS des boutons/tableaux/formulaires respectent le tableau de la section 6.2
- [ ] Les messages d'erreur et de succès utilisent les classes définies
- [ ] Le paramètre `$moduleActif` est bien transmis pour la surbrillance du menu

---

*Toute question sur ce document → ouvrir une issue GitHub avec le label `question-doc`.*
