// Système de notifications pour les invitations de groupe et messages

// Déterminer le chemin de base
function getBasePath() {
    const path = window.location.pathname;
    const depth = (path.match(/\//g) || []).length - 1;
    return depth > 1 ? '../'.repeat(depth - 1) : './';
}

// Charger les notifications
async function loadNotifications() {
    try {
        const basePath = getBasePath();
        const response = await fetch(`${basePath}pages/api/notifications.php`);
        const data = await response.json();
        
        if (data.success) {
            updateNotificationBadge(data.total);
        }
    } catch (error) {
        console.error('Erreur chargement notifications:', error);
    }
}

// Mettre à jour le badge de notification
function updateNotificationBadge(count) {
    let badge = document.getElementById('notification-badge');
    const messagesLink = document.getElementById('messages-link');
    
    if (!messagesLink) return;
    
    if (count > 0) {
        if (!badge) {
            // Créer le badge s'il n'existe pas
            badge = document.createElement('span');
            badge.id = 'notification-badge';
            badge.className = 'notification-badge';
            messagesLink.appendChild(badge);
        }
        badge.textContent = count > 99 ? '99+' : count;
        badge.style.display = 'flex';
    } else {
        if (badge) {
            badge.style.display = 'none';
        }
    }
}

// Charger au démarrage
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadNotifications);
} else {
    loadNotifications();
}

// Rafraîchir toutes les 30 secondes
setInterval(loadNotifications, 30000);
