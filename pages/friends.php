<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/language.php';
require_once '../includes/config.php';

$pageTitle = "Recherche d'amis - AmiGo";
$pageDescription = "Trouvez et connectez-vous avec d'autres utilisateurs";
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/friends.css"
];

$user_id = $_SESSION['user_id'];

// Récupérer les statistiques de l'utilisateur
try {
    $pdo = getDB();
    
    // Compter les amis
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_friends 
        FROM friendships 
        WHERE (user_id = ? OR friend_id = ?) AND status = 'accepted'
    ");
    $stmt->execute([$user_id, $user_id]);
    $total_friends = $stmt->fetch()['total_friends'];
    
    // Compter les demandes en attente
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as pending_requests 
        FROM friendships 
        WHERE friend_id = ? AND status = 'pending'
    ");
    $stmt->execute([$user_id]);
    $pending_requests = $stmt->fetch()['pending_requests'];
    
} catch (PDOException $e) {
    $total_friends = 0;
    $pending_requests = 0;
}

require_once '../includes/header.php';
?>

<div class="container friends-page">
    <div class="friends-header">
        <h1>👥 <?php echo getCurrentLanguage() === 'fr' ? 'Mes Amis' : 'My Friends'; ?></h1>
        <p class="subtitle"><?php echo getCurrentLanguage() === 'fr' ? 'Recherchez et connectez-vous avec d\'autres membres' : 'Search and connect with other members'; ?></p>
    </div>

    <div class="friends-stats">
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_friends; ?></div>
            <div class="stat-label"><?php echo getCurrentLanguage() === 'fr' ? 'Amis' : 'Friends'; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $pending_requests; ?></div>
            <div class="stat-label"><?php echo getCurrentLanguage() === 'fr' ? 'Demandes' : 'Requests'; ?></div>
        </div>
    </div>

    <div class="friends-tabs">
        <button class="tab-btn active" data-tab="search">
            🔍 <?php echo getCurrentLanguage() === 'fr' ? 'Rechercher' : 'Search'; ?>
        </button>
        <button class="tab-btn" data-tab="my-friends">
            👥 <?php echo getCurrentLanguage() === 'fr' ? 'Mes Amis' : 'My Friends'; ?>
        </button>
        <button class="tab-btn" data-tab="requests">
            📬 <?php echo getCurrentLanguage() === 'fr' ? 'Demandes' : 'Requests'; ?>
            <?php if ($pending_requests > 0): ?>
                <span class="badge"><?php echo $pending_requests; ?></span>
            <?php endif; ?>
        </button>
    </div>

    <!-- Tab: Rechercher -->
    <div class="tab-content active" id="search-tab">
        <div class="search-section">
            <div class="search-box">
                <input 
                    type="text" 
                    id="search-input" 
                    placeholder="<?php echo getCurrentLanguage() === 'fr' ? 'Rechercher par nom, prénom ou nom d\'utilisateur...' : 'Search by name or username...'; ?>"
                    class="search-input"
                >
                <button class="search-btn" id="search-btn">
                    🔍 <?php echo getCurrentLanguage() === 'fr' ? 'Rechercher' : 'Search'; ?>
                </button>
            </div>
            <div id="search-results" class="users-grid">
                <p class="info-message"><?php echo getCurrentLanguage() === 'fr' ? 'Utilisez la barre de recherche pour trouver des amis' : 'Use the search bar to find friends'; ?></p>
            </div>
        </div>
    </div>

    <!-- Tab: Mes Amis -->
    <div class="tab-content" id="my-friends-tab">
        <div id="friends-list" class="users-grid">
            <p class="loading"><?php echo getCurrentLanguage() === 'fr' ? 'Chargement...' : 'Loading...'; ?></p>
        </div>
    </div>

    <!-- Tab: Demandes -->
    <div class="tab-content" id="requests-tab">
        <div id="requests-list" class="users-grid">
            <p class="loading"><?php echo getCurrentLanguage() === 'fr' ? 'Chargement...' : 'Loading...'; ?></p>
        </div>
    </div>
</div>

<script>
const currentLang = '<?php echo getCurrentLanguage(); ?>';
const userId = <?php echo $user_id; ?>;

// Gestion des onglets
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const tabName = btn.dataset.tab;
        
        // Activer l'onglet
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        
        // Afficher le contenu
        document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
        document.getElementById(tabName + '-tab').classList.add('active');
        
        // Charger les données
        if (tabName === 'my-friends') {
            loadFriends();
        } else if (tabName === 'requests') {
            loadRequests();
        }
    });
});

// Recherche d'utilisateurs
document.getElementById('search-btn').addEventListener('click', searchUsers);
document.getElementById('search-input').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') searchUsers();
});

async function searchUsers() {
    const query = document.getElementById('search-input').value.trim();
    const resultsContainer = document.getElementById('search-results');
    
    if (query.length < 2) {
        resultsContainer.innerHTML = `<p class="info-message">${currentLang === 'fr' ? 'Entrez au moins 2 caractères' : 'Enter at least 2 characters'}</p>`;
        return;
    }
    
    resultsContainer.innerHTML = `<p class="loading">${currentLang === 'fr' ? 'Recherche...' : 'Searching...'}</p>`;
    
    try {
        const response = await fetch('../pages/api/friends-search.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ query })
        });
        
        const data = await response.json();
        
        if (data.success && data.users.length > 0) {
            displayUsers(data.users, resultsContainer, true);
        } else {
            resultsContainer.innerHTML = `<p class="info-message">${currentLang === 'fr' ? 'Aucun utilisateur trouvé' : 'No users found'}</p>`;
        }
    } catch (error) {
        console.error('Error:', error);
        resultsContainer.innerHTML = `<p class="error-message">${currentLang === 'fr' ? 'Erreur de recherche' : 'Search error'}</p>`;
    }
}

async function loadFriends() {
    const container = document.getElementById('friends-list');
    container.innerHTML = `<p class="loading">${currentLang === 'fr' ? 'Chargement...' : 'Loading...'}</p>`;
    
    try {
        const response = await fetch('../pages/api/friends-list.php');
        const data = await response.json();
        
        if (data.success && data.friends.length > 0) {
            displayUsers(data.friends, container, false);
        } else {
            container.innerHTML = `<p class="info-message">${currentLang === 'fr' ? 'Vous n\'avez pas encore d\'amis' : 'You don\'t have friends yet'}</p>`;
        }
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = `<p class="error-message">${currentLang === 'fr' ? 'Erreur de chargement' : 'Loading error'}</p>`;
    }
}

async function loadRequests() {
    const container = document.getElementById('requests-list');
    container.innerHTML = `<p class="loading">${currentLang === 'fr' ? 'Chargement...' : 'Loading...'}</p>`;
    
    try {
        const response = await fetch('../pages/api/friends-requests.php');
        const data = await response.json();
        
        if (data.success && data.requests.length > 0) {
            displayRequests(data.requests, container);
        } else {
            container.innerHTML = `<p class="info-message">${currentLang === 'fr' ? 'Aucune demande en attente' : 'No pending requests'}</p>`;
        }
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = `<p class="error-message">${currentLang === 'fr' ? 'Erreur de chargement' : 'Loading error'}</p>`;
    }
}

function displayUsers(users, container, showAddButton) {
    container.innerHTML = users.map(user => `
        <div class="user-card">
            <div class="user-avatar">${user.first_name.charAt(0)}${user.last_name.charAt(0)}</div>
            <div class="user-info">
                <h3 class="user-name">${user.first_name} ${user.last_name}</h3>
                <p class="user-username">@${user.username}</p>
            </div>
            <div class="user-actions">
                ${showAddButton ? (
                    user.friendship_status === 'none' 
                        ? `<button class="btn btn-primary btn-sm" onclick="sendFriendRequest(${user.id})">
                            ➕ ${currentLang === 'fr' ? 'Ajouter' : 'Add'}
                           </button>`
                        : user.friendship_status === 'pending'
                        ? `<button class="btn btn-secondary btn-sm" disabled>
                            ⏳ ${currentLang === 'fr' ? 'En attente' : 'Pending'}
                           </button>`
                        : `<button class="btn btn-success btn-sm" disabled>
                            ✓ ${currentLang === 'fr' ? 'Ami' : 'Friend'}
                           </button>`
                ) : `
                    <button class="btn btn-danger btn-sm" onclick="removeFriend(${user.id})">
                        ✗ ${currentLang === 'fr' ? 'Retirer' : 'Remove'}
                    </button>
                `}
            </div>
        </div>
    `).join('');
}

function displayRequests(requests, container) {
    container.innerHTML = requests.map(user => `
        <div class="user-card">
            <div class="user-avatar">${user.first_name.charAt(0)}${user.last_name.charAt(0)}</div>
            <div class="user-info">
                <h3 class="user-name">${user.first_name} ${user.last_name}</h3>
                <p class="user-username">@${user.username}</p>
            </div>
            <div class="user-actions">
                <button class="btn btn-success btn-sm" onclick="acceptRequest(${user.id})">
                    ✓ ${currentLang === 'fr' ? 'Accepter' : 'Accept'}
                </button>
                <button class="btn btn-danger btn-sm" onclick="declineRequest(${user.id})">
                    ✗ ${currentLang === 'fr' ? 'Refuser' : 'Decline'}
                </button>
            </div>
        </div>
    `).join('');
}

async function sendFriendRequest(friendId) {
    try {
        const response = await fetch('../pages/api/friends-action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'send', friend_id: friendId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            searchUsers();
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification(currentLang === 'fr' ? 'Erreur' : 'Error', 'error');
    }
}

async function acceptRequest(friendId) {
    try {
        const response = await fetch('../pages/api/friends-action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'accept', friend_id: friendId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            loadRequests();
            document.querySelector('[data-tab="requests"] .badge')?.remove();
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function declineRequest(friendId) {
    try {
        const response = await fetch('../pages/api/friends-action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'decline', friend_id: friendId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            loadRequests();
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function removeFriend(friendId) {
    if (!confirm(currentLang === 'fr' ? 'Retirer cet ami ?' : 'Remove this friend?')) return;
    
    try {
        const response = await fetch('../pages/api/friends-action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'remove', friend_id: friendId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            loadFriends();
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => notification.classList.add('show'), 10);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
</script>

<?php require_once '../includes/footer.php';
