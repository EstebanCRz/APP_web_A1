// Système de notifications pour les invitations de groupe et messages

// Déterminer le chemin de base
function obtenirCheminBase() {
    const chemin = window.location.pathname;
    const profondeur = (chemin.match(/\//g) || []).length - 1;
    return profondeur > 1 ? '../'.repeat(profondeur - 1) : './';
}

// Charger les notifications
async function chargerNotifications() {
    try {
        const cheminBase = obtenirCheminBase();
        const reponse = await fetch(`${cheminBase}pages/api/notifications.php`);
        const donnees = await reponse.json();
        
        if (donnees.success) {
            mettreAJourBadgeNotification(donnees.total);
        }
    } catch (erreur) {
        console.error('Erreur chargement notifications:', erreur);
    }
}

// Mettre à jour le badge de notification
function mettreAJourBadgeNotification(compteur) {
    let badge = document.getElementById('badge-notification');
    const lienMessages = document.getElementById('lien-messages');
    
    if (!lienMessages) return;
    
    if (compteur > 0) {
        if (!badge) {
            // Créer le badge s'il n'existe pas
            badge = document.createElement('span');
            badge.id = 'badge-notification';
            badge.className = 'badge-notification';
            lienMessages.appendChild(badge);
        }
        badge.textContent = compteur > 99 ? '99+' : compteur;
        badge.style.display = 'flex';
    } else {
        if (badge) {
            badge.style.display = 'none';
        }
    }
}

// Charger au démarrage
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', chargerNotifications);
} else {
    chargerNotifications();
}

// Rafraîchir toutes les 30 secondes
setInterval(chargerNotifications, 30000);
