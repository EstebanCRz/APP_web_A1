# 📸 Support d'Images dans les Messages

## Installation

### 1. Mise à jour de la base de données

Exécutez le script SQL pour ajouter le support d'images :

```sql
-- Dans phpMyAdmin ou MySQL Workbench
USE amigo_db;

ALTER TABLE private_messages 
ADD COLUMN image_path VARCHAR(255) DEFAULT NULL AFTER message;
```

Ou exécutez le fichier :
```
database/add_images_to_messages.sql
```

### 2. Vérification du dossier uploads

Le dossier `uploads/messages/` a déjà été créé. Vérifiez les permissions :

```bash
chmod 755 uploads/messages/
```

Sur Windows (via PowerShell) :
```powershell
# Déjà créé automatiquement
Test-Path "uploads/messages"  # Devrait retourner True
```

## Fonctionnalités

### Messages Privés
- ✅ Upload d'images (JPEG, PNG, GIF, WebP)
- ✅ Taille max : 5MB
- ✅ Prévisualisation avant envoi
- ✅ Affichage des images dans les conversations
- ✅ Clic pour agrandir en plein écran
- ✅ Compatible avec le texte (texte + image possible)

### Messages de Groupe (Activités)
- ✅ Upload d'images dans les discussions d'activité
- ✅ Mêmes fonctionnalités que les messages privés
- ✅ Stockage dans le même dossier `uploads/messages/`

## Utilisation

### 1. Envoyer une image

**Messages Privés :**
1. Ouvrir une conversation
2. Cliquer sur le bouton 📎
3. Sélectionner une image
4. (Optionnel) Ajouter un message texte
5. Envoyer

**Chat d'Activité :**
1. Accéder aux détails d'une activité
2. Cliquer sur le bouton 📎 dans le chat
3. Sélectionner une image
4. (Optionnel) Ajouter un message texte
5. Envoyer

### 2. Voir une image en grand

Cliquez sur n'importe quelle image dans un message pour l'afficher en plein écran.
Cliquez en dehors de l'image ou sur le ✕ pour fermer.

## Structure des fichiers

```
uploads/messages/           # Dossier de stockage des images
├── msg_1_1735045678.jpg   # Messages privés
├── chat_2_1735045679.png  # Messages de groupe
└── ...

database/
├── add_images_to_messages.sql  # Script de migration

pages/api/
├── upload-message-image.php    # Upload pour messages privés
└── private-messages.php        # API modifiée avec support images

events/api/
├── upload-chat-image.php       # Upload pour chat de groupe
└── chat-messages.php           # API modifiée avec support images

assets/
├── css/message-images.css      # Styles pour les images
└── js/
    ├── messages.js             # JS messages privés (modifié)
    └── activity-chat.js        # JS chat activité (modifié)
```

## Sécurité

### Vérifications implémentées :
- ✅ Authentification requise (session)
- ✅ Vérification du type MIME réel du fichier
- ✅ Limite de taille (5MB)
- ✅ Types autorisés : JPEG, PNG, GIF, WebP uniquement
- ✅ Noms de fichiers uniques (uniqid + user_id + timestamp)
- ✅ Protection contre l'injection de code (htmlspecialchars)

### Recommandations supplémentaires :
- Ajouter un scan antivirus pour la production
- Limiter le nombre d'uploads par utilisateur/jour
- Compresser automatiquement les images volumineuses
- Nettoyer les anciennes images (cron job)

## Formats supportés

| Format | Extension | MIME Type      |
|--------|-----------|----------------|
| JPEG   | .jpg      | image/jpeg     |
| PNG    | .png      | image/png      |
| GIF    | .gif      | image/gif      |
| WebP   | .webp     | image/webp     |

## Limitation

- **Taille maximale** : 5 MB par image
- **Formats** : Images uniquement (pas de vidéos/documents)
- **Stockage** : Local (uploads/messages/)

## Dépannage

### L'image ne s'upload pas
1. Vérifier que le dossier `uploads/messages/` existe et a les bonnes permissions
2. Vérifier la taille de l'image (< 5MB)
3. Vérifier le format (JPEG, PNG, GIF, WebP uniquement)
4. Consulter les logs PHP pour les erreurs

### L'image ne s'affiche pas
1. Vérifier que le chemin dans la BDD est correct (`uploads/messages/...`)
2. Vérifier que le fichier existe physiquement
3. Vérifier les permissions de lecture du dossier
4. Vérifier la console du navigateur pour les erreurs JavaScript

### Erreur 500
1. Vérifier que la colonne `image_path` existe dans `private_messages`
2. Vérifier les logs Apache/PHP
3. Vérifier que les fichiers API ont les bonnes permissions

## Migration depuis l'ancien système

Le système est compatible avec les messages existants. Les anciens messages sans images continueront de fonctionner normalement.

```sql
-- Vérifier les messages avec images
SELECT COUNT(*) FROM private_messages WHERE image_path IS NOT NULL;

-- Vérifier les messages sans images
SELECT COUNT(*) FROM private_messages WHERE image_path IS NULL;
```

## Support

Pour toute question ou problème, consultez la documentation ou contactez l'équipe de développement.
