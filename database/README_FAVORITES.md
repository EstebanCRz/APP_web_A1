# Installation de la table user_favorites

## Méthode 1 : Via le script PHP (Recommandé)

1. Ouvrez votre navigateur
2. Accédez à : `http://localhost/APP_web_A1/database/install_favorites.php`
3. La table sera créée automatiquement

## Méthode 2 : Via phpMyAdmin

1. Ouvrez phpMyAdmin (http://localhost/phpMyAdmin)
2. Sélectionnez la base de données `amigo_db`
3. Cliquez sur l'onglet "SQL"
4. Copiez-collez le contenu du fichier `favorites_table.sql`
5. Cliquez sur "Exécuter"

## Méthode 3 : Via MySQL en ligne de commande

```bash
mysql -h localhost -P 3306 -u root -proot amigo_db < favorites_table.sql
```

## Vérification

Pour vérifier que la table a bien été créée :

```sql
SHOW TABLES LIKE 'user_favorites';
DESCRIBE user_favorites;
```

La table devrait contenir les colonnes :
- `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `user_id` (INT, NOT NULL)
- `activity_id` (INT, NOT NULL)
- `created_at` (TIMESTAMP)

## Fonctionnalités activées après installation

✅ Bouton ❤️ sur chaque activité (events-list.php et event-details.php)
✅ Onglet "Favoris" dans le profil utilisateur (profile.php)
✅ Page dédiée aux favoris (profile-favorites.php)
✅ Sauvegarde persistante des favoris en base de données
