// JavaScript pour la page Messages et Groupes

document.addEventListener('DOMContentLoaded', function() {
    // Gestion des onglets
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;
            
            console.log('Changing to tab:', tab);
            
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            btn.classList.add('active');
            document.getElementById(tab + '-content').classList.add('active');
            
            // Arrêter le rafraîchissement des messages
            if (messageRefreshInterval) {
                console.log('Stopping message refresh');
                clearInterval(messageRefreshInterval);
                messageRefreshInterval = null;
            }
            
            // Fermer toutes les modales lors du changement d'onglet
            const modals = document.querySelectorAll('.modal');
            console.log('Closing modals:', modals.length);
            modals.forEach(modal => {
                console.log('Closing modal:', modal.id);
                modal.classList.remove('show');
            });
        });
    });
    
    // Charger les groupes
    loadGroups();
    
    // Charger les invitations
    loadInvitations();
    
    // Charger les conversations
    loadConversations();
    
    // Recherche d'utilisateurs
    let searchTimeout;
    const searchInput = document.getElementById('search-users');
    console.log('Search input element:', searchInput);
    
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            console.log('Search input event triggered:', e.target.value);
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => searchUsers(e.target.value), 300);
        });
    } else {
        console.error('Element search-users not found');
    }
    
    // Fermer les résultats de recherche en cliquant ailleurs
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.search-users')) {
            document.getElementById('search-results').classList.remove('show');
        }
    });
    
    // Bouton créer un groupe
    document.getElementById('btn-create-group')?.addEventListener('click', showCreateGroupModal);
    
    // Fermer les modales
    document.querySelectorAll('.modal .close').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.modal').classList.remove('show');
            // Arrêter le rafraîchissement des messages
            if (messageRefreshInterval) {
                clearInterval(messageRefreshInterval);
                messageRefreshInterval = null;
            }
        });
    });
    
    // Rafraîchir périodiquement
    setInterval(() => {
        loadGroups();
        loadInvitations();
        loadConversations();
    }, 30000);
});

// Charger les invitations
async function loadInvitations() {
    try {
        const response = await fetch('api/groups.php?action=invitations');
        const data = await response.json();
        
        if (data.success) {
            displayInvitations(data.invitations);
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

function displayInvitations(invitations) {
    const container = document.getElementById('invitations-section');
    const badge = document.getElementById('groups-badge');
    
    // Mettre à jour le badge sur l'onglet
    if (badge) {
        if (invitations.length > 0) {
            badge.textContent = invitations.length;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }
    
    if (invitations.length === 0) {
        container.innerHTML = '';
        return;
    }
    
    container.innerHTML = `
        <div class="invitations-container">
            <h3 style="margin-bottom: 15px; color: #333;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 8px;">
                    <path d="M22 12h-6l-2 3h-4l-2-3H2"></path>
                    <path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path>
                </svg>
                Invitations de groupe (${invitations.length})
            </h3>
            <div class="invitations-list">
                ${invitations.map(inv => `
                    <div class="invitation-card">
                        <div class="invitation-info">
                            <h4>${escapeHtml(inv.group_name)}</h4>
                            <p>${escapeHtml(inv.activity_title || inv.description || '')}</p>
                            <small>Invité par ${escapeHtml(inv.invited_by_name)}</small>
                        </div>
                        <div class="invitation-actions">
                            <button class="btn-accept" onclick="acceptInvitation(${inv.id})">Accepter</button>
                            <button class="btn-decline" onclick="declineInvitation(${inv.id})">Refuser</button>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
}

async function acceptInvitation(invitationId) {
    try {
        const response = await fetch('api/groups.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'accept_invitation', invitation_id: invitationId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadInvitations();
            loadGroups();
            // Mettre à jour le badge de notification
            if (typeof loadNotifications === 'function') {
                loadNotifications();
            }
        } else {
            alert(data.message || 'Erreur');
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

async function declineInvitation(invitationId) {
    try {
        const response = await fetch('api/groups.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'decline_invitation', invitation_id: invitationId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadInvitations();
            // Mettre à jour le badge de notification
            if (typeof loadNotifications === 'function') {
                loadNotifications();
            }
        } else {
            alert(data.message || 'Erreur');
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Charger les groupes
async function loadGroups() {
    try {
        const response = await fetch('api/groups.php');
        const data = await response.json();
        
        if (data.success) {
            displayGroups(data.groups);
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

function displayGroups(groups) {
    const container = document.getElementById('groups-list');
    
    if (groups.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                </svg>
                <h3>Aucun groupe</h3>
                <p>Créez votre premier groupe pour commencer</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = groups.map(group => `
        <div class="group-card">
            <div class="group-info" onclick="openGroup(${group.id})">
                <h3>${escapeHtml(group.name)}</h3>
                ${group.activity_title ? `<p class="activity-badge">📌 ${escapeHtml(group.activity_title)}</p>` : ''}
                <p>${escapeHtml(group.description || '')}</p>
                <div class="group-meta">
                    <span>${group.member_count} membres</span>
                    <span>${formatDate(group.created_at)}</span>
                </div>
            </div>
            <button class="btn-leave-group" onclick="event.stopPropagation(); leaveGroup(${group.id})" title="Quitter le groupe">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
            </button>
        </div>
    `).join('');
}

// Ouvrir un groupe
async function openGroup(groupId) {
    try {
        const response = await fetch(`api/groups.php?id=${groupId}`);
        const data = await response.json();
        
        if (data.success) {
            displayGroupDetails(data.group, data.messages, data.members, data.current_user_id);
            document.getElementById('group-modal').classList.add('show');
            
            // Rafraîchir les messages
            startMessageRefresh(groupId, 'group');
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

function displayGroupDetails(group, messages, members, currentUserId) {
    // Vérifier si l'utilisateur actuel est admin
    const currentUserMember = members.find(m => m.user_id == currentUserId);
    const isAdmin = currentUserMember && currentUserMember.role === 'admin';
    
    const content = `
        <div class="chat-header">
            <div style="flex: 1;">
                <h2>${escapeHtml(group.name)}</h2>
                <p>${escapeHtml(group.description || '')}</p>
            </div>
            ${isAdmin ? `
                <button class="btn-delete-group" onclick="deleteGroup(${group.id})" title="Supprimer le groupe">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                </button>
            ` : ''}
        </div>
        
        <div class="chat-messages" id="chat-messages">
            ${messages.map(msg => {
                const isOwnMessage = msg.user_id == currentUserId;
                return `
                    <div class="message ${isOwnMessage ? 'own-message' : 'other-message'}">
                        <div class="message-header">
                            <span class="message-author">${escapeHtml(msg.username)}</span>
                            <span class="message-time">${formatDate(msg.created_at)}</span>
                        </div>
                        <div class="message-content">${escapeHtml(msg.message)}</div>
                    </div>
                `;
            }).join('') || '<p class="empty-state">Aucun message</p>'}
        </div>
        
        <form class="chat-form" onsubmit="sendGroupMessage(event, ${group.id})">
            <textarea name="message" placeholder="Votre message..." required></textarea>
            <button type="submit">Envoyer</button>
        </form>
        
        <div class="members-list">
            <h3>Membres (${members.length})</h3>
            ${members.map(member => `
                <div class="member-item">
                    <span>${escapeHtml(member.username)}</span>
                    ${member.role === 'admin' ? '<span class="badge">Admin</span>' : ''}
                </div>
            `).join('')}
        </div>
    `;
    
    document.getElementById('group-details').innerHTML = content;
    scrollToBottom('chat-messages');
}

// Envoyer un message de groupe
async function sendGroupMessage(e, groupId) {
    e.preventDefault();
    
    const form = e.target;
    const message = form.message.value.trim();
    
    if (!message) return;
    
    try {
        const response = await fetch('api/groups.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'send_message', group_id: groupId, message })
        });
        
        const data = await response.json();
        
        if (data.success) {
            form.reset();
            openGroup(groupId);
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Charger les conversations
async function loadConversations() {
    try {
        const response = await fetch('api/private-messages.php');
        const data = await response.json();
        
        if (data.success) {
            displayConversations(data.conversations);
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

function displayConversations(conversations) {
    const container = document.getElementById('conversations-list');
    
    if (conversations.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <h3>Aucune conversation</h3>
                <p>Recherchez un utilisateur pour démarrer une conversation</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = conversations.map(conv => `
        <div class="conversation-card">
            <div class="conversation-info" onclick="openConversation(${conv.id})">
                <h3>${escapeHtml(conv.other_user_name)}</h3>
                <p>${escapeHtml(conv.last_message || 'Aucun message')}</p>
                ${conv.unread_count > 0 ? `<span class="unread-badge">${conv.unread_count}</span>` : ''}
            </div>
            <button class="btn-delete-conv" onclick="event.stopPropagation(); deleteConversation(${conv.id})" title="Supprimer la conversation">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
            </button>
        </div>
    `).join('');
}

// Rechercher des utilisateurs
async function searchUsers(query) {
    console.log('searchUsers called with query:', query);
    const resultsContainer = document.getElementById('search-results');
    
    if (query.length < 2) {
        console.log('Query too short, hiding results');
        resultsContainer.classList.remove('show');
        return;
    }
    
    try {
        console.log('Fetching users with query:', query);
        const response = await fetch(`api/private-messages.php?action=search&query=${encodeURIComponent(query)}`);
        const data = await response.json();
        console.log('Search response:', data);
        
        if (data.success) {
            displaySearchResults(data.users);
        }
    } catch (error) {
        console.error('Search error:', error);
    }
}

function displaySearchResults(users) {
    const container = document.getElementById('search-results');
    
    if (users.length === 0) {
        container.innerHTML = '<div class="search-result-item">Aucun utilisateur trouvé</div>';
        container.classList.add('show');
        return;
    }
    
    container.innerHTML = users.map(user => {
        const fullName = `${user.first_name || ''} ${user.last_name || ''}`.trim();
        const displayName = fullName || user.username;
        
        return `
            <div class="search-result-item" onclick="startConversation(${user.id})">
                <h4>${escapeHtml(displayName)}</h4>
                <p>@${escapeHtml(user.username)}</p>
            </div>
        `;
    }).join('');
    
    container.classList.add('show');
}

// Démarrer une conversation
async function startConversation(userId) {
    try {
        const response = await fetch('api/private-messages.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'start_conversation', user_id: userId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('search-users').value = '';
            document.getElementById('search-results').classList.remove('show');
            loadConversations();
            openConversation(data.conversation_id);
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Ouvrir une conversation
async function openConversation(conversationId) {
    try {
        const response = await fetch(`api/private-messages.php?id=${conversationId}`);
        const data = await response.json();
        
        if (data.success) {
            displayConversationDetails(data.conversation, data.messages, data.current_user_id);
            document.getElementById('conversation-modal').classList.add('show');
            
            // Rafraîchir les messages
            startMessageRefresh(conversationId, 'conversation');
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

function displayConversationDetails(conversation, messages, currentUserId) {
    const content = `
        <div class="chat-header">
            <h2>${escapeHtml(conversation.other_user_name)}</h2>
        </div>
        
        <div class="chat-messages" id="chat-messages">
            ${messages.map(msg => {
                const isOwnMessage = msg.sender_id == currentUserId;
                return `
                    <div class="message ${isOwnMessage ? 'own-message' : 'other-message'}">
                        <div class="message-header">
                            <span class="message-author">${escapeHtml(msg.sender_name)}</span>
                            <span class="message-time">${formatDate(msg.created_at)}</span>
                        </div>
                        <div class="message-content">${escapeHtml(msg.message)}</div>
                    </div>
                `;
            }).join('') || '<p class="empty-state">Aucun message</p>'}
        </div>
        
        <form class="chat-form" onsubmit="sendPrivateMessage(event, ${conversation.id})">
            <textarea name="message" placeholder="Votre message..." required></textarea>
            <button type="submit">Envoyer</button>
        </form>
    `;
    
    document.getElementById('conversation-details').innerHTML = content;
    scrollToBottom('chat-messages');
}

// Envoyer un message privé
async function sendPrivateMessage(e, conversationId) {
    e.preventDefault();
    
    const form = e.target;
    const message = form.message.value.trim();
    
    if (!message) return;
    
    try {
        const response = await fetch('api/private-messages.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'send_message', conversation_id: conversationId, message })
        });
        
        const data = await response.json();
        
        if (data.success) {
            form.reset();
            openConversation(conversationId);
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Supprimer une conversation
async function deleteConversation(conversationId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette conversation ? Tous les messages seront supprimés.')) {
        return;
    }
    
    try {
        const response = await fetch('api/private-messages.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete_conversation', conversation_id: conversationId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadConversations();
        } else {
            alert(data.message || 'Erreur lors de la suppression');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la suppression');
    }
}

// Créer un groupe
function showCreateGroupModal() {
    const content = `
        <div class="chat-header">
            <h2>Créer un groupe</h2>
        </div>
        
        <form class="chat-form" onsubmit="createGroup(event)" style="flex-direction: column;">
            <input type="text" name="name" placeholder="Nom du groupe" required style="padding: 10px; border: 2px solid #e0e0e0; border-radius: 5px; margin-bottom: 10px;">
            <textarea name="description" placeholder="Description (optionnel)" style="padding: 10px; border: 2px solid #e0e0e0; border-radius: 5px; min-height: 80px; margin-bottom: 10px;"></textarea>
            <button type="submit">Créer</button>
        </form>
    `;
    
    document.getElementById('group-details').innerHTML = content;
    document.getElementById('group-modal').classList.add('show');
}

async function createGroup(e) {
    e.preventDefault();
    
    const form = e.target;
    const name = form.name.value.trim();
    const description = form.description.value.trim();
    
    try {
        const response = await fetch('api/groups.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'create_group', name, description })
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('group-modal').classList.remove('show');
            loadGroups();
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Quitter un groupe
async function leaveGroup(groupId) {
    if (!confirm('Êtes-vous sûr de vouloir quitter ce groupe ?')) {
        return;
    }
    
    try {
        const response = await fetch('api/groups.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'leave_group', group_id: groupId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadGroups();
        } else {
            alert(data.message || 'Erreur lors de la sortie du groupe');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la sortie du groupe');
    }
}

// Supprimer un groupe (admin uniquement)
async function deleteGroup(groupId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce groupe ? Tous les messages seront supprimés et cette action est irréversible.')) {
        return;
    }
    
    try {
        const response = await fetch('api/groups.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete_group', group_id: groupId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('group-modal').classList.remove('show');
            loadGroups();
        } else {
            alert(data.message || 'Erreur lors de la suppression du groupe');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la suppression du groupe');
    }
}

// Utilitaires
let messageRefreshInterval;

function startMessageRefresh(id, type) {
    if (messageRefreshInterval) clearInterval(messageRefreshInterval);
    
    messageRefreshInterval = setInterval(() => {
        if (type === 'group') {
            openGroup(id);
        } else {
            openConversation(id);
        }
    }, 5000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    
    if (minutes < 1) return 'À l\'instant';
    if (minutes < 60) return `Il y a ${minutes} min`;
    if (hours < 24) return `Il y a ${hours}h`;
    if (days < 7) return `Il y a ${days}j`;
    
    return date.toLocaleDateString('fr-FR');
}

function scrollToBottom(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollTop = element.scrollHeight;
    }
}
