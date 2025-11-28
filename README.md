# AmiGo - Plateforme d'événements et de rencontres

## Description
AmiGo est une plateforme web permettant aux utilisateurs de créer, découvrir et participer à des événements et activités collectives. Ce repository contient les maquettes HTML statiques de l'application.

## Structure du projet

```
APP_web/
├── index.html              # Page d'accueil
├── css/
│   └── style.css          # Styles CSS principaux
├── html/                  # Pages HTML
│   ├── login.html         # Connexion
│   ├── register.html      # Inscription
│   ├── forgot-password.html # Réinitialisation mot de passe
│   ├── events-list.html   # Liste des événements
│   ├── event-details.html # Détails d'un événement
│   ├── event-create.html  # Création/édition d'événement
│   ├── profile.html       # Profil personnel
│   ├── profile-other.html # Profil d'un autre utilisateur
│   ├── profile-edit.html  # Édition du profil
│   ├── profile-favorites.html   # Événements favoris
│   ├── profile-registered.html  # Événements inscrits
│   ├── profile-created.html     # Événements créés
│   ├── profile-waitlist.html    # Liste d'attente
│   ├── payment.html       # Page de paiement
│   ├── settings.html      # Paramètres du compte
│   ├── faq.html          # Foire aux questions
│   ├── cgu.html          # Conditions générales d'utilisation
│   ├── mentions-legales.html # Mentions légales
│   ├── contact.html      # Contact
│   ├── admin-dashboard.html  # Dashboard admin
│   ├── admin-users.html      # Gestion utilisateurs
│   ├── admin-events.html     # Gestion événements
│   ├── admin-forum.html      # Modération forum
│   ├── admin-messages.html   # Gestion messagerie
│   └── admin-content.html    # Gestion contenu
├── images/               # Images (à ajouter)
└── js/                   # Scripts JavaScript (à ajouter)
```

## Fonctionnalités implémentées (Maquettes statiques)

### Pour les utilisateurs
- ✅ Inscription et connexion
- ✅ Réinitialisation du mot de passe
- ✅ Navigation dans les événements
- ✅ Détails des événements avec forum
- ✅ Création d'événements
- ✅ Gestion du profil utilisateur
- ✅ Événements favoris, inscrits, créés, liste d'attente
- ✅ Page de paiement
- ✅ Paramètres du compte
- ✅ Pages légales (FAQ, CGU, Mentions légales, Contact)

### Pour les administrateurs
- ✅ Dashboard administrateur
- ✅ Gestion des utilisateurs
- ✅ Modération des événements
- ✅ Modération du forum
- ✅ Gestion de la messagerie
- ✅ Gestion du contenu (FAQ, CGU, Mentions légales, apparence)

## Navigation

Toutes les pages sont interconnectées avec des liens fonctionnels :
- Navigation principale dans le header
- Liens dans le footer vers les pages légales
- Boutons d'action contextuels sur chaque page

## Points d'intégration (TODO)

Les commentaires `<!-- TODO: ... -->` indiquent les endroits où du code PHP/MySQL devra être ajouté :

### Backend (PHP)
- Authentification et gestion de session
- CRUD pour les utilisateurs, événements, messages
- Traitement des paiements (intégration Stripe/PayPal)
- Envoi d'emails (confirmation, notifications)
- Upload et gestion de fichiers (photos de profil, bannières)
- Modération et signalements

### Base de données (MySQL)
Les tables principales à créer :
- `users` - Utilisateurs
- `events` - Événements
- `event_participants` - Inscriptions aux événements
- `event_favorites` - Événements favoris
- `event_waitlist` - Listes d'attente
- `forum_messages` - Messages du forum
- `private_messages` - Messagerie privée
- `reports` - Signalements
- `faq` - Questions FAQ
- `site_content` - Contenu modifiable (CGU, mentions légales)

### Frontend (JavaScript)
- Système de swipe pour mobile
- Chat en temps réel (WebSocket)
- Validation des formulaires
- Interactions dynamiques

## Accessibilité

Le site respecte les bonnes pratiques d'accessibilité :
- Labels ARIA appropriés
- Navigation au clavier
- Structure sémantique HTML5
- Contraste des couleurs
- Design responsive

## Responsive Design

Le CSS inclut des media queries pour l'adaptation aux différentes tailles d'écran :
- Desktop (> 768px)
- Tablette et mobile (≤ 768px)

## Technologies utilisées

### Actuellement
- HTML5
- CSS3
- JavaScript (minimal, pour les exemples)

### À intégrer
- PHP 7.4+
- MySQL 8.0+
- JavaScript (interactions avancées)

## Prochaines étapes

1. Créer la base de données MySQL avec le schéma complet
2. Implémenter le backend PHP pour chaque fonctionnalité
3. Intégrer un système de paiement (Stripe/PayPal)
4. Ajouter JavaScript pour les interactions dynamiques
5. Implémenter le système de chat en temps réel
6. Créer le système de swipe pour mobile
7. Tests et optimisations

## Licence

© 2025 AmiGo - Tous droits réservés

## Contact

Pour toute question : contact@amigo.fr