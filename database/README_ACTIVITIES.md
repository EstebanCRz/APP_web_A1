# Système de Gestion des Activités

## Installation de la base de données

1. Ouvrez votre interface de gestion MySQL (phpMyAdmin, Adminer, ou ligne de commande)
2. Assurez-vous que la base de données `amigo_db` existe
3. Exécutez le fichier SQL : `database/activities_table.sql`

```bash
# Via ligne de commande MySQL
mysql -u root -p amigo_db < database/activities_table.sql
```

## Structure de la base de données

### Tables créées :

1. **users** - Utilisateurs de la plateforme
2. **activity_categories** - Catégories d'activités (Sport, Art, Musique, etc.)
3. **activities** - Les activités elles-mêmes
4. **activity_registrations** - Inscriptions des utilisateurs aux activités

## Fonctionnalités

### 1. Affichage des activités

Les activités sont maintenant récupérées depuis la base de données :

- **Page d'accueil** (`index.php`) : Affiche les 8 dernières activités
- **Liste des événements** (`events/events-list.php`) : Affiche toutes les activités avec filtres

### 2. Filtres disponibles

- **Recherche** : Par titre, ville, organisateur, description
- **Catégorie** : Sport, Cuisine, Art, Musique, Jeux, Nature, Bien-être, Culture
- **Période** : Cette semaine, Ce mois-ci, À venir
- **Moment** : Matin, Après-midi, Soirée

### 3. Données affichées pour chaque activité

- Titre
- Description et extrait
- Catégorie (avec couleur)
- Créateur (nom, prénom, avatar)
- Localisation (lieu et ville)
- Date et heure
- Nombre de participants (actuel/maximum)
- Image

## Fonctions disponibles

Le fichier `includes/activities_functions.php` contient toutes les fonctions :

### Récupération des activités

```php
// Récupérer toutes les activités
$activities = getAllActivities();

// Avec filtres
$activities = getAllActivities([
    'search' => 'yoga',
    'category' => 'Bien-être',
    'date_filter' => 'week',
    'time_filter' => 'evening',
    'limit' => 10
]);

// Récupérer une activité par ID
$activity = getActivityById(1);
```

### Gestion des catégories

```php
// Récupérer toutes les catégories
$categories = getAllCategories();
```

### Gestion des inscriptions

```php
// Vérifier si un utilisateur est inscrit
$isRegistered = isUserRegistered($activityId, $userId);

// Inscrire un utilisateur
registerUserToActivity($activityId, $userId);

// Désinscrire un utilisateur
unregisterUserFromActivity($activityId, $userId);
```

### Activités d'un utilisateur

```php
// Activités créées par l'utilisateur
$myActivities = getUserCreatedActivities($userId);

// Activités où l'utilisateur est inscrit
$registeredActivities = getUserRegisteredActivities($userId);
```

### Création d'activité

```php
$activityId = createActivity([
    'title' => 'Mon activité',
    'description' => 'Description complète',
    'excerpt' => 'Court résumé',
    'category_id' => 1,
    'creator_id' => $_SESSION['user_id'],
    'location' => 'Parc de la ville',
    'city' => 'Paris',
    'event_date' => '2025-12-25',
    'event_time' => '14:00:00',
    'max_participants' => 20,
    'image' => 'https://example.com/image.jpg'
]);
```

### Formatage des dates

```php
// Formater une date (ex: "mer. 25 déc")
$formattedDate = formatEventDate('2025-12-25');

// Formater une heure (ex: "14h30")
$formattedTime = formatEventTime('14:30:00');
```

## API d'inscription

### Endpoint : `events/api/activity-registration.php`

**Inscription à une activité :**
```javascript
fetch('/events/api/activity-registration.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        activity_id: 1,
        action: 'register'
    })
})
.then(res => res.json())
.then(data => console.log(data));
```

**Désinscription :**
```javascript
fetch('/events/api/activity-registration.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        activity_id: 1,
        action: 'unregister'
    })
})
.then(res => res.json())
.then(data => console.log(data));
```

## Gestion des images

Les images sont stockées comme URLs dans la base de données. Vous pouvez :

1. **Utiliser des URLs externes** (Unsplash, Pexels, etc.)
2. **Upload local** : Créer un système d'upload dans `/assets/images/activities/`

### Exemple d'upload (à implémenter)

```php
// Dans event-create.php
if (isset($_FILES['activity_image'])) {
    $uploadDir = '../assets/images/activities/';
    $fileName = uniqid() . '_' . $_FILES['activity_image']['name'];
    $uploadPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($_FILES['activity_image']['tmp_name'], $uploadPath)) {
        $imageUrl = IMAGES_URL . '/activities/' . $fileName;
    }
}
```

## Données de test

Le fichier SQL inclut :
- 8 utilisateurs de test (mot de passe : `password` - hash bcrypt)
- 8 catégories d'activités
- 8 activités d'exemple

### Connexion test

Email: `camille@amigo.com`  
Mot de passe: `password` (à hasher avec `password_hash()`)

## Prochaines étapes

1. Implémenter le système d'upload d'images
2. Ajouter la modification/suppression d'activités
3. Créer les pages de profil utilisateur
4. Ajouter des notifications
5. Implémenter le système d'authentification complet
6. Ajouter la pagination pour la liste des événements

## Notes importantes

- Les images utilisent actuellement des URLs Unsplash pour les exemples
- Le système de session doit être configuré pour les inscriptions
- L'extension PHP `intl` est requise pour le formatage des dates en français
