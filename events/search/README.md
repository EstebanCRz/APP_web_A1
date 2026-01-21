# Documentation - Système de Recherche et Filtres

Ce dossier contient tous les composants du système de recherche et filtrage des événements.

## 📁 Structure des fichiers

```
search/
├── README.md                    # Documentation
├── search-filters.php          # HTML des filtres (sidebar + barre de recherche)
├── search-filters.css          # Styles des filtres
├── search-functions.php        # Fonctions PHP backend pour la recherche
└── search-filters.js           # JavaScript pour interactions (si nécessaire)
```

## 🔍 Fonctionnement

### 1. **Barre de recherche**
- **Fichier** : `search-filters.php` (lignes du formulaire)
- **Méthode** : GET
- **Champs recherchés** :
  - Titre de l'activité
  - Localisation
  - Ville
  - Description
  - Nom d'utilisateur de l'organisateur
  - Prénom/Nom de l'organisateur

**Exemple d'URL** : `events-list.php?search=running`

### 2. **Filtres disponibles**

#### Filtre par catégorie
- Sport
- Culture
- Loisirs
- etc.

#### Filtre par moment de la journée
- **Matin** : 06h00 - 11h59
- **Après-midi** : 12h00 - 17h59
- **Soir** : 18h00 - 23h59

#### Filtre par période
- **Cette semaine** : 7 prochains jours
- **Ce mois** : Mois en cours
- **À venir** : Tous les événements futurs
- **Passés** : Tous les événements passés

### 3. **Flux de données**

```
[Formulaire HTML] 
    ↓ (GET)
[events-list.php] 
    ↓ (récupère $_GET['search'], etc.)
[$filters array]
    ↓
[getAllActivities($filters)]
    ↓ (requête SQL avec WHERE et LIKE)
[Résultats filtrés]
    ↓
[Affichage sur la page]
```

## 💻 Utilisation

### Intégrer les filtres dans une page

```php
<?php
// 1. Récupérer les filtres depuis l'URL
$filters = [
    'search' => (string) ($_GET['search'] ?? ''),
    'category' => (string) ($_GET['category'] ?? ''),
    'time_filter' => (string) ($_GET['time'] ?? ''),
    'date_filter' => (string) ($_GET['date'] ?? '')
];

// 2. Récupérer les catégories pour les filtres
$categories = getAllCategories();

// 3. Obtenir les résultats filtrés
$activitiesFromDB = getAllActivities($filters);

// 4. Inclure le composant des filtres
include 'search/search-filters.php';
?>
```

### Ajouter les styles CSS

```html
<link rel="stylesheet" href="search/search-filters.css">
```

## 🎨 Personnalisation

### Modifier les critères de recherche
Éditer `search-functions.php`, fonction `getAllActivities()` :

```php
// Ajouter un nouveau champ de recherche
if (!empty($filters['search'])) {
    $searchTerm = '%' . $filters['search'] . '%';
    $sql .= " AND (
        a.title LIKE :search1 
        OR a.nouveau_champ LIKE :search8  // Nouveau champ
    )";
    $params[':search8'] = $searchTerm;
}
```

### Ajouter un nouveau filtre
1. Ajouter le HTML dans `search-filters.php`
2. Ajouter la logique dans `search-functions.php`
3. Ajouter les styles dans `search-filters.css`

## 🔧 Technologies utilisées

- **PHP 8+** : Backend et requêtes SQL
- **SQL** : Filtrage avec WHERE, LIKE, BETWEEN
- **HTML5** : Formulaires
- **CSS3** : Styles et responsive
- **JavaScript** : Interactions (optionnel)

## 📝 Notes importantes

- Les filtres sont **cumulables** : vous pouvez chercher "running" + catégorie "Sport" + période "Cette semaine"
- La recherche utilise **LIKE** avec des wildcards `%` pour une recherche flexible
- Les filtres de date utilisent des fonctions SQL natives (CURDATE(), LAST_DAY(), etc.)
- Tous les filtres passent par l'URL (méthode GET) pour permettre le partage des résultats

## 🐛 Débogage

Pour voir les requêtes SQL générées :
```php
// Dans search-functions.php
echo $sql;
print_r($params);
```
