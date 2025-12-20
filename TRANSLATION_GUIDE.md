# Système de Traduction - AmiGo

## Vue d'ensemble
Le site supporte maintenant la traduction en **français** et **anglais**. Un système flexible permet de gérer facilement les traductions dans tout le site.

## Comment ça marche

### 1. **Fichiers de traduction**
Les traductions sont stockées dans `/includes/translations/`:
- `fr.php` - Traductions françaises
- `en.php` - Traductions anglaises

Chaque fichier contient un tableau associatif `$translations` organisé par sections.

### 2. **Utilisation des traductions**

#### Charger le système de traduction
Dans chaque fichier PHP, incluez le gestionnaire de langue au début :
```php
require_once 'includes/language.php';
```

#### Récupérer une traduction
Utilisez la fonction `t()` avec la clé de traduction :
```php
echo t('header.home'); // Affiche "Accueil" ou "Home"
echo t('auth.login_title'); // Affiche "Connexion" ou "Login"
```

#### Avec des paramètres
```php
echo t('message.welcome', ['name' => 'Jean']); 
// Clé : 'welcome' => 'Bienvenue :name'
// Affiche : 'Bienvenue Jean'
```

### 3. **Sélecteur de langue**

Un sélecteur de langue est intégré dans le header avec deux options :
- 🇫🇷 Français
- 🇬🇧 English

Les utilisateurs peuvent cliquer pour changer la langue. La préférence est sauvegardée en session.

### 4. **Fonctions disponibles**

#### `t($key, $params = [])`
Obtient une traduction. Retourne la clé si non trouvée.
```php
t('auth.email'); // Retourne la traduction
```

#### `getCurrentLanguage()`
Récupère le code de la langue actuelle ('fr' ou 'en').
```php
if (getCurrentLanguage() === 'fr') {
    // Code pour le français
}
```

#### `getLanguageUrl($language)`
Génère une URL pour changer de langue.
```php
<a href="<?php echo getLanguageUrl('en'); ?>">English</a>
```

### 5. **Organisation des clés de traduction**

Les clés sont organisées par sections :
- `header.*` - Navigation et header
- `home.*` - Page d'accueil
- `auth.*` - Authentification (login, register)
- `events.*` - Événements
- `profile.*` - Profil utilisateur
- `footer.*` - Pied de page
- `common.*` - Termes communs

### 6. **Ajouter une nouvelle traduction**

1. **Ouvrir `/includes/translations/fr.php`**
2. **Ajouter la clé et la valeur** dans la section appropriée :
   ```php
   'my_section' => [
       'my_key' => 'Ma traduction française',
   ],
   ```

3. **Faire la même chose pour `/includes/translations/en.php`** :
   ```php
   'my_section' => [
       'my_key' => 'My English translation',
   ],
   ```

4. **Utiliser dans le code** :
   ```php
   echo t('my_section.my_key');
   ```

### 7. **Sélection de langue - Ordre de priorité**

La langue est déterminée selon cet ordre :
1. **Paramètre URL** : `?lang=en` (priorité maximale)
2. **Session** : Si l'utilisateur a changé la langue
3. **Préférence du navigateur** : Basée sur l'en-tête Accept-Language
4. **Par défaut** : Français (défini dans `language.php`)

### 8. **HTML multilingue**

Pour l'attribut `lang` du HTML :
```html
<html lang="<?php echo getCurrentLanguage(); ?>">
```

## Points importants

- ✅ Toutes les pages du site supportent désormais les deux langues
- ✅ La préférence de langue est conservée en session
- ✅ Le sélecteur de langue apparaît dans le menu
- ✅ Les traductions sont faciles à ajouter et modifier
- ⚠️ Les noms de catégories d'activités ne sont pas encore traduits (ils proviennent de la BD)
- ⚠️ Les messages d'erreur de la BD doivent être traduits

## Prochaines améliorations possibles

1. **Traduire les catégories** stockées en base de données
2. **Implémenter un système de traduction pour les réponses d'API**
3. **Ajouter plus de langues** (espagnol, allemand, etc.)
4. **Créer une interface d'administration** pour gérer les traductions sans éditer le code

---

**Mise à jour** : Décembre 2025
