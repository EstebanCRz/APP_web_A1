/**
 * Gestion du chat LOCAL pour les activités (sans base de données)
 */

class ActivityChat {
    constructor(activityId) {
        this.activityId = activityId;
        this.messagesContainer = document.getElementById('chat-messages');
        this.messageForm = document.getElementById('chat-form');
        this.messageInput = document.getElementById('chat-input');
        this.messages = []; // Stockage local des messages
        
        this.init();
    }
    
    init() {
        console.log('Initialisation du chat LOCAL...');
        
        // Affichage initial
        this.displayMessages();
        
        // Écouter l'envoi de message via le formulaire
        if (this.messageForm) {
            this.messageForm.addEventListener('submit', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.sendMessage();
                return false;
            });
        }
        
        // Écouteur sur la touche Entrée
        if (this.messageInput) {
            this.messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });
        }
    }
    
    displayMessages() {
        if (!this.messagesContainer) return;
        
        this.messagesContainer.innerHTML = '';
        
        if (this.messages.length === 0) {
            this.messagesContainer.innerHTML = '<div class="no-messages">Aucun message pour le moment. Soyez le premier à écrire !</div>';
            return;
        }
        
        this.messages.forEach(msg => {
            const messageEl = this.createMessageElement(msg);
            this.messagesContainer.appendChild(messageEl);
        });
        
        this.scrollToBottom();
    }
    
    createMessageElement(msg) {
        const div = document.createElement('div');
        div.className = 'chat-message own-message';
        
        const time = new Date(msg.timestamp);
        const timeStr = time.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        
        div.innerHTML = `
            <div class="message-header">
                <span class="message-author">${this.escapeHtml(msg.user_name)}</span>
                <span class="message-time">${timeStr}</span>
            </div>
            <div class="message-content">${this.escapeHtml(msg.message)}</div>
        `;
        
        return div;
    }
    
    sendMessage() {
        const messageText = this.messageInput.value.trim();
        
        if (!messageText) {
            return;
        }
        
        // Créer un nouveau message local
        const newMessage = {
            id: Date.now(),
            user_name: 'Vous',
            message: messageText,
            timestamp: new Date()
        };
        
        // Ajouter le message au tableau
        this.messages.push(newMessage);
        
        // Vider le champ
        this.messageInput.value = '';
        
        // Rafraîchir l'affichage
        this.displayMessages();
    }
    
    scrollToBottom() {
        if (this.messagesContainer) {
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        }
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialiser le chat quand la page est chargée
document.addEventListener('DOMContentLoaded', function() {
    const chatContainer = document.getElementById('activity-chat');
    
    if (chatContainer) {
        const activityId = chatContainer.dataset.activityId;
        const userId = chatContainer.dataset.userId;
        
        console.log('Initialisation - Activity ID:', activityId, 'User ID:', userId);
        
        if (activityId) {
            window.activityChat = new ActivityChat(activityId);
            console.log('Chat LOCAL initialisé avec succès');
        } else {
            console.error('Activity ID manquant');
        }
    } else {
        console.log('Pas de chat sur cette page');
    }
});

