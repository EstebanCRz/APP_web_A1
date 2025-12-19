# Guide d'Installation et Utilisation - Système d'Activités

## 🚀 Installation Rapide

### Étape 1 : Préparer la base de données

**Option A - Avec le script automatique (Windows)** :
1. Ouvrez PowerShell ou l'invite de commandes
2. Naviguez vers le dossier `database` :
   ```bash
   cd c:\Users\natha\Documents\GitHub\APP_web_A1\database
   ```
3. Exécutez le script d'installation :
   ```bash
   .\install.bat
   ```
4. Suivez les instructions à l'écran

**Option B - Manuellement** :
1. Ouvrez MAMP/WAMP/XAMPP
2. Accédez à phpMyAdmin (http://localhost/phpmyadmin)
3. Créez la base de données `amigo_db` si elle n'existe pas
4. Importez le fichier `database/activities_table.sql`

### Étape 2 : Vérifier la configuration

Ouvrez le fichier [includes/config.php](../includes/config.php) et vérifiez :

```php
define('DB_HOST', 'localhost:3306');
define('DB_NAME', 'amigo_db');
define('DB_USER', 'root');
define('DB_PASS', 'root');
```

Ajustez si nécessaire selon votre configuration.

### Étape 3 : Démarrer l'application

1. Démarrez votre serveur local (MAMP/WAMP/XAMPP)
2. Accédez à : http://localhost/APP_web_A1

---

## 📊 Ce qui a été créé

### Base de données

✅ **4 tables créées** :
- `users` - Utilisateurs de la plateforme
- `activity_categories` - Catégories d'activités
- `activities` - Les activités
- `activity_registrations` - Inscriptions

✅ **Données de test** :
- 8 utilisateurs
- 8 catégories (Sport, Cuisine, Art, Musique, Jeux, Nature, Bien-être, Culture)
- 8 activités d'exemple

### Fichiers créés/modifiés

✅ **Backend** :
- [includes/activities_functions.php](../includes/activities_functions.php) - Toutes les fonctions PHP
- [events/api/activity-registration.php](../events/api/activity-registration.php) - API d'inscription

✅ **Frontend** :
- [index.php](../index.php) - Affiche les activités depuis la DB
- [events/events-list.php](../events/events-list.php) - Liste complète avec filtres
- [assets/js/activity-registration.js](../assets/js/activity-registration.js) - Gestion des inscriptions

✅ **SQL** :
- [database/activities_table.sql](activities_table.sql) - Structure et données
- [database/install.bat](install.bat) - Script d'installation automatique

---

## 🎯 Fonctionnalités disponibles

### 1. Affichage des activités

**Page d'accueil** (`index.php`) :
- Affiche les 8 dernières activités
- Filtres par catégorie (dynamiques depuis la DB)
- Barre de recherche

**Liste complète** (`events/events-list.php`) :
- Toutes les activités
- Filtres avancés :
  - Recherche (titre, ville, organisateur)
  - Catégorie
  - Période (semaine, mois, à venir)
  - Moment (matin, après-midi, soirée)

### 2. Informations affichées

Pour chaque activité :
- ✅ Titre
- ✅ Description et extrait
- ✅ Catégorie (avec couleur)
- ✅ Créateur (nom, prénom)
- ✅ Localisation (lieu + ville)
- ✅ Date et heure formatées en français
- ✅ Nombre de participants (actuel/maximum)
- ✅ Image

### 3. Système d'inscription

- Boutons "S'inscrire" / "Se désinscrire"
- Mise à jour en temps réel (AJAX)
- Notifications visuelles
- Gestion du nombre de places

---

## 💡 Utilisation des fonctions

### Récupérer les activités

```php
// Dans n'importe quel fichier PHP
require_once 'includes/activities_functions.php';

// Toutes les activités
$activities = getAllActivities();

// Avec filtres
$activities = getAllActivities([
    'search' => 'yoga',
    'category' => 'Bien-être',
    'date_filter' => 'week',
    'time_filter' => 'evening',
    'limit' => 10
]);

// Une activité spécifique
$activity = getActivityById(1);
```

### Récupérer les catégories

```php
$categories = getAllCategories();

foreach ($categories as $cat) {
    echo $cat['name'] . ' - ' . $cat['color'];
}
```

### Créer une activité

```php
$activityId = createActivity([
    'title' => 'Sortie vélo',
    'description' => 'Balade à vélo de 20km',
    'excerpt' => 'Balade conviviale',
    'category_id' => 1, // ID de la catégorie
    'creator_id' => $_SESSION['user_id'],
    'location' => 'Parc du centre',
    'city' => 'Paris',
    'event_date' => '2025-12-25',
    'event_time' => '14:00:00',
    'max_participants' => 15,
    'image' => 'https://example.com/velo.jpg'
]);
```

### Gérer les inscriptions

```php
// Vérifier si inscrit
$isRegistered = isUserRegistered($activityId, $userId);

// Inscrire
registerUserToActivity($activityId, $userId);

// Désinscrire
unregisterUserFromActivity($activityId, $userId);

// Activités d'un utilisateur
$myActivities = getUserCreatedActivities($userId);
$registeredActivities = getUserRegisteredActivities($userId);
```

---

## 🔧 Personnalisation

### Ajouter une catégorie

```sql
INSERT INTO activity_categories (name, color, icon) 
VALUES ('Lecture', '#607D8B', '📚');
```

### Modifier les couleurs des catégories

```sql
UPDATE activity_categories 
SET color = '#FF5722' 
WHERE name = 'Sport';
```

### Changer le nombre d'activités sur l'accueil

Dans [index.php](../index.php), ligne 8 :
```php
$activitiesFromDB = getAllActivities(['limit' => 8]); // Changez 8 par le nombre souhaité
```

---

## 🐛 Dépannage

### "Erreur de connexion à la base de données"
- Vérifiez que MySQL est démarré
- Vérifiez [includes/config.php](../includes/config.php)
- Vérifiez que la base `amigo_db` existe

### "Les activités ne s'affichent pas"
- Vérifiez que le SQL a bien été importé
- Vérifiez les erreurs PHP (activer `display_errors`)
- Consultez les logs d'erreurs de votre serveur

### "Les inscriptions ne fonctionnent pas"
- Vérifiez que vous êtes connecté (système de session requis)
- Ouvrez la console du navigateur (F12) pour voir les erreurs JavaScript
- Vérifiez que le chemin vers l'API est correct

### "Extension intl non trouvée"
L'extension PHP `intl` est requise pour le formatage des dates.

**Activer dans MAMP/WAMP** :
1. Ouvrez `php.ini`
2. Décommentez : `extension=intl`
3. Redémarrez le serveur

---

## 📝 Prochaines étapes suggérées

1. **Système d'authentification complet**
   - Inscription utilisateur
   - Connexion/déconnexion
   - Gestion de profil

2. **Upload d'images**
   - Formulaire d'upload
   - Stockage local des images
   - Redimensionnement automatique

3. **Page de détails d'activité**
   - Vue complète de l'activité
   - Liste des participants
   - Commentaires

4. **Gestion des activités**
   - Modifier ses propres activités
   - Supprimer une activité
   - Annuler une activité

5. **Notifications**
   - Email de confirmation d'inscription
   - Rappels avant l'événement
   - Notifications en temps réel

6. **Améliorations UX**
   - Pagination
   - Carte interactive
   - Filtres sauvegardés
   - Mode sombre

---

## 📞 Support

Pour toute question ou problème :
1. Consultez la [documentation des fonctions](README_ACTIVITIES.md)
2. Vérifiez les fichiers de log
3. Activez le mode debug dans PHP

**Bon développement ! 🚀**
