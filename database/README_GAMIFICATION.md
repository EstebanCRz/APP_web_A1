# Système de Gamification - AmiGo

## 🎮 Description

Le système de gamification encourage l'engagement des utilisateurs en leur attribuant des points, niveaux et badges pour leurs actions sur la plateforme.

## 📊 Fonctionnalités

### Points
Les utilisateurs gagnent des points pour différentes actions :
- **Créer un événement** : 50 points
- **Participer à un événement** : 20 points
- **Compléter un événement** : 30 points
- **Laisser un avis** : 10 points
- **Ajouter un ami** : 5 points
- **Créer un groupe** : 15 points
- **Envoyer un message** : 1 point
- **Compléter son profil** : 25 points
- **Première connexion** : 10 points

### Niveaux
- Le niveau est calculé automatiquement en fonction des points totaux
- Formule : `Niveau = floor(sqrt(points / 100)) + 1`
- Exemples :
  - Niveau 1 : 0-99 points
  - Niveau 2 : 100-399 points
  - Niveau 3 : 400-899 points
  - Niveau 10 : 8100-9999 points

### Noms de niveaux
- **Niveau 1-4** : Débutant / Beginner
- **Niveau 5-9** : Intermédiaire / Intermediate
- **Niveau 10-19** : Expérimenté / Experienced
- **Niveau 20-29** : Professionnel / Professional
- **Niveau 30-39** : Expert / Expert
- **Niveau 40-49** : Maître / Master
- **Niveau 50+** : Légende / Legend

### Badges
17 badges disponibles dans 7 catégories :

#### Organisateur 📅
- **Premier Pas** : Créer 1 événement
- **Organisateur Pro** : Créer 10 événements
- **Super Organisateur** : Créer 50 événements

#### Participant 🎉
- **Première Sortie** : Participer à 1 événement
- **Papillon Social** : Participer à 10 événements
- **Fêtard** : Participer à 25 événements
- **Légende** : Participer à 100 événements

#### Social 🤝
- **Premier Ami** : Avoir 1 ami
- **Populaire** : Avoir 10 amis
- **Influenceur** : Avoir 50 amis

#### Critique 📝
- **Critique** : Laisser 5 avis
- **Expert Critique** : Laisser 25 avis

#### Autres 🌟
- **Lève-tôt** : S'inscrire à 5 événements à l'avance
- **Créateur de Groupe** : Créer 3 groupes
- **Bavard** : Envoyer 100 messages
- **Niveau 10/25/50** : Atteindre ces niveaux

## 🔧 Installation

1. **Exécuter le script SQL** :
   ```bash
   cd database
   install_gamification.bat
   ```

2. **Vérifier l'installation** :
   - 4 nouvelles tables créées : `user_points`, `points_history`, `badges`, `user_badges`
   - 17 badges insérés automatiquement
   - Points initialisés pour les utilisateurs existants

## 📁 Fichiers

### Backend
- `includes/gamification.php` : Fonctions de gamification
  - `addPoints()` : Ajouter des points
  - `getUserStats()` : Récupérer les stats d'un utilisateur
  - `checkBadges()` : Vérifier et attribuer les badges
  - `getLeaderboard()` : Obtenir le classement
  - `getLevelName()` : Nom du niveau
  - `getLevelColor()` : Couleur du niveau

### Pages
- `pages/leaderboard.php` : Classement des utilisateurs
- `pages/badges.php` : Liste de tous les badges
- `pages/css/leaderboard.css` : Styles du classement
- `pages/css/badges.css` : Styles des badges

### Base de données
- `database/gamification_tables.sql` : Script de création des tables
- `database/install_gamification.bat` : Installation automatique

### Intégrations
- `events/api/activity-registration.php` : +20 points lors d'une inscription
- `includes/activities_functions.php` : +50 points lors de la création d'événement
- `profile/profile.php` : Affichage des stats dans le profil

## 🎯 Utilisation

### Ajouter des points manuellement
```php
require_once 'includes/gamification.php';

// Ajouter 10 points
addPoints(
    $userId,                    // ID de l'utilisateur
    10,                         // Nombre de points
    'custom_action',            // Type d'action
    'Description de l\'action', // Description
    $referenceId                // ID de référence (optionnel)
);
```

### Récupérer les stats d'un utilisateur
```php
$stats = getUserStats($userId);

echo "Points: " . $stats['total_points'];
echo "Niveau: " . $stats['level'];
echo "Rang: " . $stats['rank'];
echo "Badges: " . $stats['badge_count'];
```

### Afficher le classement
```php
$leaderboard = getLeaderboard(50, 0); // 50 premiers

foreach ($leaderboard as $user) {
    echo $user['rank'] . ". " . $user['username'];
    echo " - " . $user['total_points'] . " points";
    echo " - Niveau " . $user['level'];
}
```

## 🌐 Pages utilisateur

### Classement (Leaderboard)
- **URL** : `/pages/leaderboard.php`
- **Lien dans le header** :  Classement
- **Contenu** :
  - Stats personnelles (points, niveau, rang, badges)
  - Barre de progression vers le prochain niveau
  - Top 50 des utilisateurs
  - Derniers badges obtenus
  - Pagination

### Badges
- **URL** : `/pages/badges.php`
- **Accès** : Depuis le classement ou le profil
- **Contenu** :
  - Tous les badges disponibles
  - Progression pour chaque badge
  - Badges débloqués marqués ✓
  - Conseils pour gagner plus de badges

### Profil
- **Bannière de gamification** en haut du profil :
  - Niveau avec couleur
  - Points totaux
  - Rang dans le classement
  - Aperçu des 5 derniers badges
  - Barre de progression

## 🔄 Automatisation

Les points sont attribués automatiquement lors de :
- ✅ Création d'événement (`createActivity()`)
- ✅ Inscription à un événement (`registerUserToActivity()`)
- ⏳ Ajout d'ami (à implémenter)
- ⏳ Création de groupe (à implémenter)
- ⏳ Envoi de message (à implémenter)
- ⏳ Avis laissé (à implémenter)

Les badges sont vérifiés automatiquement après chaque ajout de points.

## 🎨 Personnalisation

### Modifier les points
Éditer les constantes dans `includes/gamification.php` :
```php
const POINTS = [
    'event_create' => 50,
    'event_attend' => 20,
    // ...
];
```

### Ajouter un nouveau badge
Insérer dans la table `badges` :
```sql
INSERT INTO badges (code, name_fr, name_en, description_fr, description_en, icon, condition_type, condition_value) 
VALUES ('mon_badge', 'Mon Badge', 'My Badge', 'Description FR', 'Description EN', '🎯', 'events_created', 100);
```

Types de conditions :
- `events_created` : Nombre d'événements créés
- `events_attended` : Nombre d'événements participés
- `friends_count` : Nombre d'amis
- `reviews_count` : Nombre d'avis
- `groups_created` : Nombre de groupes créés
- `messages_sent` : Nombre de messages envoyés
- `level` : Niveau atteint

## 📱 Responsive

Toutes les pages sont responsive et s'adaptent aux mobiles, tablettes et ordinateurs.

## 🔮 Améliorations futures

- [ ] Notifications pour nouveaux badges
- [ ] Récompenses pour les séries (streak)
- [ ] Badges secrets
- [ ] Système de parrainage avec bonus
- [ ] Classements par catégorie
- [ ] Classements mensuels/hebdomadaires
- [ ] Défis communautaires
- [ ] Avatars basés sur le niveau

## 📄 Traductions

Le système est entièrement traduit en français et anglais :
- `includes/translations/fr.php` : Section `leaderboard` et `badges`
- `includes/translations/en.php` : Section `leaderboard` et `badges`

## ✅ Tests

Après l'installation, vérifier :
1. ✓ Tables créées dans la base de données
2. ✓ Badges insérés (17 badges)
3. ✓ Création d'événement donne 50 points
4. ✓ Inscription à événement donne 20 points
5. ✓ Badges débloqués automatiquement
6. ✓ Classement fonctionnel
7. ✓ Profil affiche les stats
