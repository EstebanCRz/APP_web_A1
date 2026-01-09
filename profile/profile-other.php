<?php
declare(strict_types=1);

// Configuration de session (AVANT session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once '../includes/language.php';
require_once '../includes/activities_functions.php';
require_once '../includes/config.php';

// Récupérer l'ID de l'utilisateur à afficher
$user_id = (int)($_GET['id'] ?? 0);

if ($user_id === 0) {
    header('Location: ../index.php');
    exit;
}

// Récupérer les informations de l'utilisateur
$user = null;
$userActivities = [];
$friendshipStatus = 'none';
$isFriend = false;

try {
    $pdo = getDB();
    
    // Récupérer les infos de l'utilisateur
    $stmt = $pdo->prepare("
        SELECT id, username, first_name, last_name, email, created_at
        FROM users
        WHERE id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header('Location: ../index.php');
        exit;
    }
    
    // Vérifier le statut d'amitié si l'utilisateur est connecté
    if (isset($_SESSION['user_id'])) {
        $current_user_id = $_SESSION['user_id'];
        
        // Ne pas afficher son propre profil ici
        if ($current_user_id === $user_id) {
            header('Location: profile.php');
            exit;
        }
        
        $stmt = $pdo->prepare("
            SELECT status, user_id, friend_id
            FROM friendships
            WHERE (user_id = ? AND friend_id = ?)
               OR (user_id = ? AND friend_id = ?)
        ");
        $stmt->execute([$current_user_id, $user_id, $user_id, $current_user_id]);
        $friendship = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($friendship) {
            if ($friendship['status'] === 'accepted') {
                $friendshipStatus = 'accepted';
                $isFriend = true;
            } elseif ($friendship['user_id'] === $current_user_id) {
                $friendshipStatus = 'pending_sent';
            } else {
                $friendshipStatus = 'pending_received';
            }
        }
    }
    
    // Récupérer les activités créées par cet utilisateur
    $userActivities = getUserCreatedActivities($user_id);
    
} catch (PDOException $e) {
    error_log("Profile other error: " . $e->getMessage());
    header('Location: ../index.php');
    exit;
}

$pageTitle = htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES, 'UTF-8') . " - AmiGo";
$pageDescription = "Profil de " . htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES, 'UTF-8');
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/profile.css",
    "css/profile-other.css"
];

include '../includes/header.php';
?>

<div class="container profile-other-page">
    <div class="profile-other-header">
        <div class="user-avatar-large">
            <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
        </div>
        <div class="user-main-info">
            <h1 class="user-display-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="user-username-display">@<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="user-member-since">Membre depuis <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
        </div>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="friendship-actions">
                <?php if ($friendshipStatus === 'none'): ?>
                    <button class="btn btn-primary" onclick="sendFriendRequestToUser(<?php echo $user_id; ?>)">
                        ➕ Ajouter en ami
                    </button>
                <?php elseif ($friendshipStatus === 'pending_sent'): ?>
                    <button class="btn btn-secondary" disabled>
                        ⏳ Demande envoyée
                    </button>
                <?php elseif ($friendshipStatus === 'pending_received'): ?>
                    <button class="btn btn-success" onclick="acceptFriendRequestFromUser(<?php echo $user_id; ?>)">
                        ✓ Accepter la demande
                    </button>
                    <button class="btn btn-danger" onclick="declineFriendRequestFromUser(<?php echo $user_id; ?>)">
                        ✗ Refuser
                    </button>
                <?php elseif ($friendshipStatus === 'accepted'): ?>
                    <div class="friend-badge">✓ Ami</div>
                    <button class="btn btn-danger btn-sm" onclick="removeFriendFromUser(<?php echo $user_id; ?>)">
                        Retirer
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="profile-other-content">
        <h2 class="section-title">📅 Activités créées (<?php echo count($userActivities); ?>)</h2>
        
        <?php if (empty($userActivities)): ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <p>Cet utilisateur n'a pas encore créé d'activité</p>
            </div>
        <?php else: ?>
            <div class="activities-grid">
                <?php foreach ($userActivities as $activity): ?>
                    <a href="../events/event-details.php?id=<?php echo $activity['id']; ?>" class="event-card">
                        <div class="event-banner" style="background-image: url('<?php echo htmlspecialchars($activity['image'] ?? '', ENT_QUOTES, 'UTF-8'); ?>'); background-size: cover; background-position: center; height: 150px;"></div>
                        <div class="event-info">
                            <span class="badge" style="background: <?php echo htmlspecialchars($activity['category_color'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($activity['category_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <h4 class="event-title"><?php echo htmlspecialchars($activity['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p class="event-details">📅 <?php echo formatEventDate($activity['event_date']); ?> - <?php echo formatEventTime($activity['event_time']); ?></p>
                            <p class="event-details">📍 <?php echo htmlspecialchars($activity['location'] . ', ' . $activity['city'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="event-details">👥 <?php echo $activity['current_participants']; ?>/<?php echo $activity['max_participants']; ?> participants</p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
async function sendFriendRequestToUser(userId) {
    try {
        const response = await fetch('../pages/api/friends-action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'send', friend_id: userId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Erreur', 'error');
    }
}

async function acceptFriendRequestFromUser(userId) {
    try {
        const response = await fetch('../pages/api/friends-action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'accept', friend_id: userId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function declineFriendRequestFromUser(userId) {
    try {
        const response = await fetch('../pages/api/friends-action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'decline', friend_id: userId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function removeFriendFromUser(userId) {
    if (!confirm('Voulez-vous vraiment retirer cet ami ?')) return;
    
    try {
        const response = await fetch('../pages/api/friends-action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'remove', friend_id: userId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
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
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 10px;
        color: white;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 10000;
        background: ${type === 'success' ? 'linear-gradient(135deg, #4caf50 0%, #2e7d32 100%)' : 'linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%)'};
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(400px)';
        notification.style.transition = 'all 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
</script>

<?php include '../includes/footer.php'; ?>
