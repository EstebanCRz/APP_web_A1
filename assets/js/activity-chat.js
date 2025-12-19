/**
 * Gestion du chat en temps réel pour les activités
 */

class ActivityChat {
    constructor(activityId) {
        this.activityId = activityId;
        this.messagesContainer = document.getElementById('chat-messages');
        this.messageForm = document.getElementById('chat-form');
        this.messageInput = document.getElementById('chat-input');
        this.lastMessageId = null;
        
        this.init();
    }
    
    init() {
        console.log('Initialisation du chat...');
        console.log('Form:', this.messageForm);
        console.log('Input:', this.messageInput);
        console.log('Container:', this.messagesContainer);
        
        // Charger les messages initiaux
        this.loadMessages();
        
        // Écouter l'envoi de message via le formulaire
        if (this.messageForm) {
            this.messageForm.addEventListener('submit', (e) => {
                console.log('Submit event déclenché');
                e.preventDefault();
                e.stopPropagation();
                this.sendMessage();
                return false;
            });
            console.log('Event listener ajouté au formulaire');
        } else {
            console.error('Formulaire de chat non trouvé!');
        }
        
        // Écouteur supplémentaire sur le bouton d'envoi
        const submitBtn = this.messageForm?.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.addEventListener('click', (e) => {
                console.log('Bouton cliqué');
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
                    console.log('Touche Entrée pressée');
                    e.preventDefault();
                    this.sendMessage();
                }
            });
        }
        
        // Rafraîchir les messages toutes les 3 secondes
        setInterval(() => this.loadMessages(), 3000);
    }
    
    async loadMessages() {
        try {
            const response = await fetch(`api/chat-messages.php?activity_id=${this.activityId}`);
            const data = await response.json();
            
            if (data.success) {
                this.displayMessages(data.messages);
            }
        } catch (error) {
            console.error('Erreur lors du chargement des messages:', error);
        }
    }
    
    displayMessages(messages) {
        if (!this.messagesContainer) return;
        
        // Sauvegarder la position du scroll
        const wasAtBottom = this.isScrolledToBottom();
        
        this.messagesContainer.innerHTML = '';
        
        if (messages.length === 0) {
            this.messagesContainer.innerHTML = '<div class="no-messages">Aucun message pour le moment. Soyez le premier à écrire !</div>';
            return;
        }
        
        messages.forEach(msg => {
            const messageEl = this.createMessageElement(msg);
            this.messagesContainer.appendChild(messageEl);
        });
        
        // Scroller en bas si on était déjà en bas
        if (wasAtBottom || messages.length === 1) {
            this.scrollToBottom();
        }
    }
    
    createMessageElement(msg) {
        const div = document.createElement('div');
        div.className = 'chat-message';
        div.dataset.messageId = msg.id;
        
        // Vérifier si c'est notre message
        const isOwnMessage = msg.user_id == (window.currentUserId || 0);
        if (isOwnMessage) {
            div.classList.add('own-message');
        }
        
        const time = new Date(msg.timestamp * 1000);
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
    
    async sendMessage() {
        const messageText = this.messageInput.value.trim();
        
        console.log('Envoi du message:', messageText);
        
        if (!messageText) {
            console.log('Message vide, annulation');
            return;
        }
        
        try {
            console.log('Envoi vers API...');
            const response = await fetch('api/chat-messages.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    activity_id: this.activityId,
                    message: messageText
                })
            });
            
            const data = await response.json();
            console.log('Réponse API:', data);
            
            if (data.success) {
                this.messageInput.value = '';
                this.loadMessages();
                console.log('Message envoyé avec succès');
            } else {
                alert(data.message || 'Erreur lors de l\'envoi du message');
            }
        } catch (error) {
            console.error('Erreur lors de l\'envoi:', error);
            alert('Erreur lors de l\'envoi du message');
        }
    }
    
    isScrolledToBottom() {
        if (!this.messagesContainer) return true;
        const threshold = 50;
        return this.messagesContainer.scrollHeight - this.messagesContainer.scrollTop - this.messagesContainer.clientHeight < threshold;
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
    console.log('Chat container:', chatContainer);
    
    if (chatContainer) {
        const activityId = chatContainer.dataset.activityId;
        const userId = chatContainer.dataset.userId;
        
        console.log('Initialisation du chat - Activity ID:', activityId, 'User ID:', userId);
        
        if (activityId) {
            window.currentUserId = userId;
            window.activityChat = new ActivityChat(activityId);
            console.log('Chat initialisé avec succès');
        }
    } else {
        console.warn('Container de chat non trouvé');
    }
});
