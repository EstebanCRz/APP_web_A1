// Fonction pour afficher des notifications
function afficherNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: ${type === 'success' ? '#4caf50' : '#2196F3'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 9999;
        animation: slideInRight 0.3s ease, slideOutRight 0.3s ease 2.7s;
        font-weight: 500;
    `;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

// Ajouter les animations CSS si elles n'existent pas
if (!document.querySelector('#notification-styles')) {
    const style = document.createElement('style');
    style.id = 'notification-styles';
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOutRight {
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
}

// Gestion du bouton favoris
document.addEventListener('DOMContentLoaded', function() {
    const boutonFavori = document.querySelector('.favorite-btn-large');
    
    console.log('Bouton favori trouvé:', boutonFavori);
    
    if (boutonFavori) {
        console.log('État initial du bouton:', boutonFavori.classList.contains('active') ? 'actif' : 'inactif');
        
        boutonFavori.addEventListener('click', function() {
            const idActivite = this.dataset.activityId;
            const estActif = this.classList.contains('active');
            const action = estActif ? 'remove' : 'add';
            
            console.log('Clic sur le bouton favori - Activity ID:', idActivite, 'Action:', action);
            
            // Demander confirmation pour la suppression
            if (estActif && !confirm('Retirer cette activité de vos favoris ?')) {
                console.log('Suppression annulée par l\'utilisateur');
                return;
            }
            
            const urlApi = 'api/favorite-toggle.php';
            console.log('Appel API:', urlApi);
            
            fetch(urlApi, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `activity_id=${idActivite}&action=${action}`
            })
            .then(reponse => {
                console.log('Réponse HTTP:', reponse.status);
                return reponse.json();
            })
            .then(donnees => {
                console.log('Données reçues:', donnees);
                
                if (donnees.success) {
                    this.classList.toggle('active');
                    this.title = this.classList.contains('active') ? 'Retirer des favoris' : 'Ajouter aux favoris';
                    
                    console.log('Nouveau statut:', this.classList.contains('active') ? 'actif' : 'inactif');
                    
                    // Animation
                    const iconeCoeur = this.querySelector('.heart-icon');
                    if (this.classList.contains('active')) {
                        iconeCoeur.style.animation = 'heartBeat 0.3s ease';
                        setTimeout(() => {
                            iconeCoeur.style.animation = '';
                        }, 300);
                        // Message de succès pour l'ajout
                        afficherNotification('❤️ Ajouté aux favoris', 'success');
                    } else {
                        // Message pour la suppression
                        afficherNotification('Retiré des favoris', 'info');
                    }
                } else {
                    console.error('Erreur API:', donnees.message);
                    alert('Erreur: ' + donnees.message);
                }
            })
            .catch(erreur => {
                console.error('Erreur complète:', erreur);
                alert('Erreur de connexion. Vérifiez la console pour plus de détails.');
            });
        });
    }
});
