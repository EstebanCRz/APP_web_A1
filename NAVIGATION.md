# Plan de Navigation - AmiGo

## Pages Publiques (Non connecté)

### Page d'Accueil - `index.html`
- → Inscription (`html/register.html`)
- → Connexion (`html/login.html`)
- → Événements (`html/events-list.html`)
- → FAQ (`html/faq.html`)
- → Contact (`html/contact.html`)
- → CGU (`html/cgu.html`)
- → Mentions légales (`html/mentions-legales.html`)

### Inscription - `html/register.html`
- → Connexion (`html/login.html`)
- → CGU (`html/cgu.html`)
- → Mentions légales (`html/mentions-legales.html`)

### Connexion - `html/login.html`
- → Inscription (`html/register.html`)
- → Mot de passe oublié (`html/forgot-password.html`)

### Mot de passe oublié - `html/forgot-password.html`
- → Connexion (`html/login.html`)

## Pages Utilisateur (Connecté)

### Liste des événements - `html/events-list.html`
- → Détails événement (`html/event-details.html`)
- → Profil (`html/profile.html`)
- → Accueil (`../index.html`)

### Détails d'un événement - `html/event-details.html`
- → Paiement (`html/payment.html`) - si événement payant
- → Profil créateur (`html/profile-other.html`)
- → Événements similaires (`html/event-details.html`)

### Création/Édition d'événement - `html/event-create.html`
- → Profil (`html/profile.html`)

### Mon Profil - `html/profile.html`
- → Édition profil (`html/profile-edit.html`)
- → Paramètres (`html/settings.html`)
- → Événements favoris (`html/profile-favorites.html`)
- → Événements inscrits (`html/profile-registered.html`)
- → Événements créés (`html/profile-created.html`)
- → Liste d'attente (`html/profile-waitlist.html`)

### Profil d'un autre utilisateur - `html/profile-other.html`
- → Détails événement (`html/event-details.html`)

### Événements Favoris - `html/profile-favorites.html`
- → Profil (`html/profile.html`)
- → Détails événement (`html/event-details.html`)

### Événements Inscrits - `html/profile-registered.html`
- → Profil (`html/profile.html`)
- → Détails événement (`html/event-details.html`)

### Événements Créés - `html/profile-created.html`
- → Profil (`html/profile.html`)
- → Détails événement (`html/event-details.html`)
- → Création événement (`html/event-create.html`)

### Liste d'attente - `html/profile-waitlist.html`
- → Profil (`html/profile.html`)
- → Détails événement (`html/event-details.html`)

### Édition du profil - `html/profile-edit.html`
- → Profil (`html/profile.html`)

### Paramètres - `html/settings.html`
- → Profil (`html/profile.html`)

### Paiement - `html/payment.html`
- → Détails événement (`html/event-details.html`)
- → CGU (`html/cgu.html`)

## Pages Légales et Informations

### FAQ - `html/faq.html`
- → Contact (`html/contact.html`)
- → Accueil (`../index.html`)

### CGU - `html/cgu.html`
- → Contact (`html/contact.html`)
- → Mentions légales (`html/mentions-legales.html`)
- → Accueil (`../index.html`)

### Mentions légales - `html/mentions-legales.html`
- → Contact (`html/contact.html`)
- → CGU (`html/cgu.html`)
- → Accueil (`../index.html`)

### Contact - `html/contact.html`
- → Accueil (`../index.html`)

## Backoffice Administrateur

### Dashboard Admin - `html/admin-dashboard.html`
- → Gestion utilisateurs (`html/admin-users.html`)
- → Gestion événements (`html/admin-events.html`)
- → Modération forum (`html/admin-forum.html`)
- → Gestion messagerie (`html/admin-messages.html`)
- → Gestion contenu (`html/admin-content.html`)
- → Retour au site (`../index.html`)

### Gestion des utilisateurs - `html/admin-users.html`
- → Dashboard (`html/admin-dashboard.html`)
- → Profil utilisateur (`html/profile-other.html`)

### Gestion des événements - `html/admin-events.html`
- → Dashboard (`html/admin-dashboard.html`)
- → Détails événement (`html/event-details.html`)
- → Profil créateur (`html/profile-other.html`)

### Modération du forum - `html/admin-forum.html`
- → Dashboard (`html/admin-dashboard.html`)
- → Détails événement (`html/event-details.html`)
- → Profil utilisateur (`html/profile-other.html`)

### Gestion de la messagerie - `html/admin-messages.html`
- → Dashboard (`html/admin-dashboard.html`)
- → Profil utilisateur (`html/profile-other.html`)

### Gestion du contenu - `html/admin-content.html`
- → Dashboard (`html/admin-dashboard.html`)
- → Aperçu CGU (`html/cgu.html`)
- → Aperçu Mentions légales (`html/mentions-legales.html`)

## Navigation Transversale

Toutes les pages incluent :
- **Header** : Logo AmiGo, sélecteur de langue
- **Navigation** : Liens vers pages principales selon l'état de connexion
- **Footer** : Contact, FAQ, CGU, Mentions légales
