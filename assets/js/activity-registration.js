/**
 * Gestion des inscriptions/désinscriptions aux activités
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Gérer les boutons d'inscription
    const subscribeButtons = document.querySelectorAll('.btn-subscribe, .btn-unsubscribe');
    
    subscribeButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation(); // Empêcher la propagation vers le lien parent
            
            const activityId = this.dataset.activityId;
            const action = this.classList.contains('btn-subscribe') ? 'register' : 'unregister';
            
            // Calculer le chemin relatif vers l'API selon la page actuelle
            let basePath;
            const currentPath = window.location.pathname;
            
            if (currentPath.includes('/events/')) {
                // On est dans le dossier events (events-list.php, event-details.php, etc.)
                basePath = 'api/activity-registration.php';
            } else if (currentPath.includes('/auth/') || currentPath.includes('/profile/') || currentPath.includes('/pages/')) {
                // On est dans un autre sous-dossier
                basePath = '../events/api/activity-registration.php';
            } else {
                // On est à la racine (index.php)
                basePath = 'events/api/activity-registration.php';
            }
            
            try {
                const response = await fetch(basePath, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        activity_id: parseInt(activityId),
                        action: action
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Mettre à jour le bouton
                    if (action === 'register') {
                        this.textContent = 'Se désinscrire';
                        this.classList.remove('btn-subscribe');
                        this.classList.add('btn-unsubscribe');
                    } else {
                        this.textContent = 'S\'inscrire';
                        this.classList.remove('btn-unsubscribe');
                        this.classList.add('btn-subscribe');
                    }
                    
                    // Mettre à jour le compteur de participants
                    const participantCounter = this.closest('.activity-item, .event-card, .participate-card')
                        ?.querySelector('.participant-count, .card-footer span, #participant-count');
                    
                    if (participantCounter && data.current_participants !== undefined) {
                        // Sur event-details.php, rafraîchir la page pour afficher la liste mise à jour
                        if (window.location.pathname.includes('event-details.php')) {
                            participantCounter.textContent = data.current_participants;
                            // Afficher le message et rafraîchir après 1 seconde
                            showNotification(data.message, 'success');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                            return; // Ne pas continuer l'exécution
                        } else {
                            // Sur les autres pages, juste mettre à jour le compteur
                            participantCounter.textContent = `${data.current_participants}/${data.max_participants} inscrits`;
                        }
                    }
                    
                    // Afficher un message de succès
                    showNotification(data.message, 'success');
                    
                } else {
                    // Afficher l'erreur
                    showNotification(data.message, 'error');
                }
                
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Une erreur est survenue. Veuillez réessayer.', 'error');
            }
        });
    });
    
});

/**
 * Afficher une notification
 */
function showNotification(message, type = 'info') {
    // Créer l'élément de notification
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Styles inline pour la notification
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        background: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#2196F3'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
        max-width: 300px;
    `;
    
    // Ajouter au body
    document.body.appendChild(notification);
    
    // Supprimer après 3 secondes
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Ajouter les animations CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
