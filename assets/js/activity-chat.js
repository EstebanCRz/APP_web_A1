/**
 * Gestion du chat pour les activités avec API serveur et support d'images
 */

class ActivityChat {
    constructor(activityId) {
        this.activityId = activityId;
        this.messagesContainer = document.getElementById('chat-messages');
        this.messageForm = document.getElementById('chat-form');
        this.messageInput = document.getElementById('chat-input');
        this.uploadedImagePath = null;
        this.refreshInterval = null;
        
        this.init();
    }
    
    init() {
        console.log('Initialisation du chat pour l\'activité:', this.activityId);
        
        // Charger les messages
        this.loadMessages();
        
        // Rafraîchir automatiquement toutes les 5 secondes
        this.refreshInterval = setInterval(() => this.loadMessages(), 5000);
        
        // Écouter l'envoi de message
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
        
        this.messagesContainer.innerHTML = '';
        
        if (!messages || messages.length === 0) {
            this.messagesContainer.innerHTML = '<div class="no-messages">Aucun message pour le moment. Soyez le premier à écrire !</div>';
            return;
        }
        
        messages.forEach(msg => {
            const messageEl = this.createMessageElement(msg);
            this.messagesContainer.appendChild(messageEl);
        });
        
        this.scrollToBottom();
    }
    
    createMessageElement(msg) {
        const div = document.createElement('div');
        div.className = 'chat-message';
        
        const time = new Date(msg.timestamp * 1000);
        const timeStr = time.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        
        let messageContent = '';
        if (msg.image_path) {
            messageContent += `<img src="../${msg.image_path}" alt="Image" class="message-image" onclick="openImageModal('../${msg.image_path}')"/>`;
        }
        if (msg.message) {
            messageContent += `<div class="message-text">${this.escapeHtml(msg.message)}</div>`;
        }
        
        div.innerHTML = `
            <div class="message-header">
                <span class="message-author">${this.escapeHtml(msg.user_name)}</span>
                <span class="message-time">${timeStr}</span>
            </div>
            <div class="message-content">${messageContent}</div>
        `;
        
        return div;
    }
    
    async sendMessage() {
        const messageText = this.messageInput.value.trim();
        
        if (!messageText && !this.uploadedImagePath) {
            return;
        }
        
        try {
            const response = await fetch('api/chat-messages.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    activity_id: this.activityId,
                    message: messageText,
                    image_path: this.uploadedImagePath
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.messageInput.value = '';
                this.uploadedImagePath = null;
                this.removeImagePreview();
                this.loadMessages();
            }
        } catch (error) {
            console.error('Erreur lors de l\'envoi:', error);
        }
    }
    
    async handleImageSelect(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        if (!file.type.startsWith('image/')) {
            alert('Veuillez sélectionner une image');
            return;
        }
        
        if (file.size > 5 * 1024 * 1024) {
            alert('Image trop volumineuse (max 5MB)');
            return;
        }
        
        // Afficher la prévisualisation
        const reader = new FileReader();
        reader.onload = (e) => {
            const preview = document.getElementById('chat-image-preview');
            const previewImg = document.getElementById('chat-preview-img');
            if (preview && previewImg) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(file);
        
        // Upload l'image
        const formData = new FormData();
        formData.append('image', file);
        
        try {
            const response = await fetch('api/upload-chat-image.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.uploadedImagePath = data.image_path;
            } else {
                alert(data.message || 'Erreur lors de l\'upload');
                this.removeImagePreview();
            }
        } catch (error) {
            console.error('Erreur upload:', error);
            alert('Erreur lors de l\'upload');
            this.removeImagePreview();
        }
    }
    
    removeImagePreview() {
        const preview = document.getElementById('chat-image-preview');
        const previewImg = document.getElementById('chat-preview-img');
        const input = document.getElementById('chat-image-input');
        
        if (preview) preview.style.display = 'none';
        if (previewImg) previewImg.src = '';
        if (input) input.value = '';
        this.uploadedImagePath = null;
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

// Fonction globale pour ouvrir une image en plein écran
function openImageModal(imagePath) {
    const modal = document.createElement('div');
    modal.className = 'image-modal';
    modal.innerHTML = `
        <div class="image-modal-content">
            <span class="image-modal-close" onclick="this.parentElement.parentElement.remove()">&times;</span>
            <img src="${imagePath}" alt="Image">
        </div>
    `;
    modal.onclick = (e) => {
        if (e.target === modal) modal.remove();
    };
    document.body.appendChild(modal);
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
            console.log('Chat initialisé avec succès');
        } else {
            console.error('Activity ID manquant');
        }
    } else {
        console.log('Pas de chat sur cette page');
    }
});
