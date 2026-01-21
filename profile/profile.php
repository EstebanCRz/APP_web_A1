<?php
// Configuration de session (AVANT session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once '../includes/language.php';
require_once '../includes/activities_functions.php';
require_once '../includes/gamification.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$pageTitle = t('profile.my_profile') . " - AmiGo";
$pageDescription = t('profile.my_profile');
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/profile.css"
];

// Récupérer les activités créées par l'utilisateur
$myActivities = getUserCreatedActivities($_SESSION['user_id']);

// Récupérer les activités auxquelles l'utilisateur est inscrit
$registeredActivities = getUserRegisteredActivities($_SESSION['user_id']);

// Récupérer les activités favorites de l'utilisateur
$favoriteActivities = [];
try {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT 
            a.id,
            a.title,
            a.description,
            a.location,
            a.city,
            a.event_date,
            a.event_time,
            a.max_participants,
            a.current_participants,
            a.image,
            c.name as category_name,
            c.color as category_color,
            u.first_name as creator_first_name,
            u.last_name as creator_last_name,
            u.username as creator_username
        FROM user_favorites f
        INNER JOIN activities a ON f.activity_id = a.id
        INNER JOIN activity_categories c ON a.category_id = c.id
        INNER JOIN users u ON a.creator_id = u.id
        WHERE f.user_id = ?
        ORDER BY f.created_at DESC
        LIMIT 6
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $favoriteActivities = $stmt->fetchAll();
} catch (PDOException $e) {
    $favoriteActivities = [];
}

// Récupérer les amis de l'utilisateur
$myFriends = [];
try {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT 
            u.id,
            u.username,
            u.first_name,
            u.last_name,
            u.email,
            f.accepted_at
        FROM users u
        INNER JOIN friendships f ON (
            (f.user_id = ? AND f.friend_id = u.id)
            OR (f.friend_id = ? AND f.user_id = u.id)
        )
        WHERE f.status = 'accepted'
        ORDER BY u.first_name, u.last_name
        LIMIT 6
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $myFriends = $stmt->fetchAll();
} catch (PDOException $e) {
    $myFriends = [];
}

// Récupérer les stats de gamification
$userStats = getUserStats($_SESSION['user_id']);
$levelName = getLevelName($userStats['level']);
$levelColor = getLevelColor($userStats['level']);

// Récupérer le classement pour l'onglet classement
$leaderboard = getLeaderboard(50, 0);
$recentBadges = getRecentBadges(10);

include '../includes/header.php';
?>

<div class="container">
    <!-- Bannière de gamification -->
    <section class="gamification-banner">
        <div class="gamification-card">
            <div class="level-display" style="border-color: <?php echo $levelColor; ?>">
                <div class="level-number"><?php echo $userStats['level']; ?></div>
                <div class="level-name"><?php echo $levelName[getCurrentLanguage()]; ?></div>
            </div>
            <div class="points-display">
                <div class="points-value">🏆 <?php echo number_format($userStats['total_points']); ?></div>
                <div class="points-label"><?php echo t('leaderboard.points'); ?></div>
            </div>
            <div class="rank-display">
                <div class="rank-value">#<?php echo $userStats['rank']; ?></div>
                <div class="rank-label"><?php echo t('leaderboard.ranking'); ?></div>
            </div>
            <div class="badges-display">
                <div class="badges-preview">
                    <?php 
                    $displayBadges = array_slice($userStats['badges'], 0, 5);
                    foreach ($displayBadges as $badge): ?>
                        <span class="badge-icon-small" title="<?php echo $badge['name_' . getCurrentLanguage()]; ?>"><?php echo $badge['icon']; ?></span>
                    <?php endforeach; ?>
                    <?php if ($userStats['badge_count'] > 5): ?>
                        <span class="more-badges">+<?php echo $userStats['badge_count'] - 5; ?></span>
                    <?php endif; ?>
                </div>
                <a href="../pages/badges.php" class="btn-view-badges"><?php echo t('leaderboard.view_all_badges'); ?></a>
            </div>
        </div>
        <div class="progress-section">
            <div class="progress-info">
                <span><?php echo t('leaderboard.progress_to_next'); ?></span>
                <span><?php echo number_format($userStats['points_in_current_level']); ?> / <?php echo number_format($userStats['points_needed_for_next']); ?></span>
            </div>
            <div class="progress-bar-profile">
                <div class="progress-fill-profile" style="width: <?php echo $userStats['progress_percent']; ?>%"></div>
            </div>
        </div>
    </section>
    
    <section class="profile-header">
        <h2><?php echo t('profile.my_profile'); ?></h2>
        <p><?php echo t('profile.welcome'); ?> <?php echo htmlspecialchars($_SESSION['user_first_name'] ?? 'Utilisateur', ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($_SESSION['user_last_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?> !</p>
        
        <div class="profile-tabs">
            <button class="tab-btn btn btn-primary active" data-tab="modifier"><?php echo t('profile.edit'); ?></button>
            <button class="tab-btn btn btn-secondary" data-tab="leaderboard"> <?php echo t('leaderboard.page_title'); ?></button>
            <button class="tab-btn btn btn-secondary" data-tab="friends"> <?php echo t('profile.my_friends'); ?> (<?php echo count($myFriends); ?>)</button>
            <button class="tab-btn btn btn-secondary" data-tab="favorites"> <?php echo t('profile.favorites'); ?> (<?php echo count($favoriteActivities); ?>)</button>
            <button class="tab-btn btn btn-secondary" data-tab="created"><?php echo t('profile.created_events'); ?> (<?php echo count($myActivities); ?>)</button>
            <button class="tab-btn btn btn-secondary" data-tab="registered"><?php echo t('profile.registered_events'); ?> (<?php echo count($registeredActivities); ?>)</button>
            <a href="../auth/login.php?logout=1" class="btn btn-secondary"><?php echo t('profile.logout'); ?></a>
        </div>
    </section>

    <!-- Onglet Modifier profil -->
    <section class="tab-content active" id="tab-modifier">
        <div style="padding: 2rem; background: #f9f9f9; border-radius: 8px; text-align: center;">
            <h3><?php echo t('profile.edit'); ?></h3>
            <p><?php echo t('profile.upcoming_feature'); ?></p>
            <a href="profile-edit.php" class="btn btn-primary"><?php echo t('profile.access_settings'); ?></a>
        </div>
    </section>

    <!-- Onglet Classement -->
    <section class="tab-content" id="tab-leaderboard">
        <div class="leaderboard-container">
            <h3 style="font-size: 1.8rem; color: var(--accent-structure); margin-bottom: 1.5rem;">
                 <?php echo t('leaderboard.ranking'); ?>
            </h3>
            
            <?php if (empty($leaderboard)): ?>
                <div class="empty-state">
                    <p><?php echo t('leaderboard.no_users'); ?></p>
                </div>
            <?php else: ?>
                <div class="ranking-list-profile">
                    <?php foreach ($leaderboard as $user): ?>
                        <div class="ranking-item-profile <?php echo $user['id'] == $_SESSION['user_id'] ? 'current-user' : ''; ?>">
                            <div class="rank-badge-profile rank-<?php echo $user['rank']; ?>">
                                <?php if ($user['rank'] == 1): ?>
                                    🥇
                                <?php elseif ($user['rank'] == 2): ?>
                                    🥈
                                <?php elseif ($user['rank'] == 3): ?>
                                    🥉
                                <?php else: ?>
                                    #<?php echo $user['rank']; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="user-info-profile">
                                <div class="user-name-profile">
                                    <a href="profile-other.php?id=<?php echo $user['id']; ?>">
                                        <?php 
                                        $displayName = trim($user['first_name'] . ' ' . $user['last_name']);
                                        echo htmlspecialchars($displayName ?: $user['username']); 
                                        ?>
                                    </a>
                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                        <span class="you-badge-profile"><?php echo t('leaderboard.you'); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="user-stats-mini-profile">
                                    <span title="<?php echo t('leaderboard.events_created'); ?>">📅 <?php echo $user['events_created']; ?></span>
                                    <span title="<?php echo t('leaderboard.events_attended'); ?>">🎉 <?php echo $user['events_attended']; ?></span>
                                    <span title="<?php echo t('leaderboard.badges'); ?>">🎖️ <?php echo $user['badge_count']; ?></span>
                                </div>
                            </div>
                            
                            <div class="user-level-profile">
                                <div class="level-badge-profile" style="border-color: <?php echo getLevelColor($user['level']); ?>">
                                    <div class="level-number-profile"><?php echo $user['level']; ?></div>
                                </div>
                            </div>
                            
                            <div class="user-points-profile">
                                <div class="points-value-profile"><?php echo number_format($user['total_points']); ?></div>
                                <div class="points-label-profile"><?php echo t('leaderboard.points'); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Derniers badges -->
            <div class="recent-badges-section" style="margin-top: 2rem;">
                <h3 style="font-size: 1.5rem; color: var(--accent-structure); margin-bottom: 1rem;">
                    🎖️ <?php echo t('leaderboard.recent_badges'); ?>
                </h3>
                
                <?php if (empty($recentBadges)): ?>
                    <div class="empty-state-small">
                        <p><?php echo t('leaderboard.no_recent_badges'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="recent-badges-grid">
                        <?php foreach ($recentBadges as $badge): ?>
                            <div class="badge-item-profile">
                                <div class="badge-icon-profile"><?php echo $badge['icon']; ?></div>
                                <div class="badge-info-profile">
                                    <div class="badge-name-profile"><?php echo $badge['name_' . getCurrentLanguage()]; ?></div>
                                    <div class="badge-user-profile">
                                        <?php 
                                        $displayName = trim($badge['first_name'] . ' ' . $badge['last_name']);
                                        echo htmlspecialchars($displayName ?: $badge['username']); 
                                        ?>
                                    </div>
                                    <div class="badge-time-profile">
                                        <?php 
                                        $now = new DateTime();
                                        $time = new DateTime($badge['earned_at']);
                                        $diff = $now->diff($time);
                                        
                                        if ($diff->days == 0) {
                                            if ($diff->h == 0) {
                                                echo $diff->i . ' min';
                                            } else {
                                                echo $diff->h . 'h';
                                            }
                                        } elseif ($diff->days < 7) {
                                            echo $diff->days . 'j';
                                        } else {
                                            echo $time->format('d/m');
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <a href="../pages/badges.php" class="btn btn-primary" style="margin-top: 1.5rem; display: inline-block;">
                    <?php echo t('leaderboard.view_all_badges'); ?>
                </a>
            </div>
        </div>
    </section>

    <!-- Onglet Mes Amis -->
    <section class="tab-content" id="tab-friends">
        <!-- Friends Stats -->
        <div class="friends-stats-mini">
            <div class="stat-card-mini">
                <div class="stat-number"><?php echo count($myFriends); ?></div>
                <div class="stat-label"><?php echo t('profile.friends_count'); ?></div>
            </div>
            <div class="stat-card-mini">
                <div class="stat-number" id="pending-count">
                    <?php 
                    try {
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM friendships WHERE friend_id = ? AND status = 'pending'");
                        $stmt->execute([$_SESSION['user_id']]);
                        echo $stmt->fetchColumn();
                    } catch (PDOException $e) {
                        echo '0';
                    }
                    ?>
                </div>
                <div class="stat-label"><?php echo t('profile.pending_requests'); ?></div>
            </div>
        </div>

        <!-- Sub-tabs for Friends -->
        <div class="friends-subtabs">
            <button class="subtab-btn active" data-subtab="search">🔍 <?php echo t('profile.search_friends'); ?></button>
            <button class="subtab-btn" data-subtab="my-friends">👥 <?php echo t('profile.friends_list'); ?></button>
            <button class="subtab-btn" data-subtab="requests">
                📬 <?php echo t('profile.friends_requests'); ?>
                <?php 
                try {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM friendships WHERE friend_id = ? AND status = 'pending'");
                    $stmt->execute([$_SESSION['user_id']]);
                    $pendingCount = $stmt->fetchColumn();
                    if ($pendingCount > 0): ?>
                        <span class="badge"><?php echo $pendingCount; ?></span>
                    <?php endif;
                } catch (PDOException $e) {}
                ?>
            </button>
        </div>

        <!-- Sub-tab: Rechercher -->
        <div class="friends-subtab-content active" id="search-subtab">
            <div class="search-box-friends">
                <input 
                    type="text" 
                    id="friends-search-input" 
                    placeholder="<?php echo t('profile.search_friends_placeholder'); ?>"
                    class="search-input"
                >
                <button class="search-btn" id="friends-search-btn">🔍 <?php echo t('profile.search_friends_button'); ?></button>
            </div>
            <div id="friends-search-results" class="users-grid">
                <p class="info-message"><?php echo t('profile.search_friends_info'); ?></p>
            </div>
        </div>

        <!-- Sub-tab: Mes Amis -->
        <div class="friends-subtab-content" id="my-friends-subtab">
            <div id="friends-list-full" class="users-grid">
                <p class="loading"><?php echo t('profile.search_friends_loading'); ?></p>
            </div>
        </div>

        <!-- Sub-tab: Demandes -->
        <div class="friends-subtab-content" id="requests-subtab">
            <div id="friends-requests-list" class="users-grid">
                <p class="loading"><?php echo t('profile.search_friends_loading'); ?></p>
            </div>
        </div>
    </section>

    <!-- Onglet Favoris -->
    <section class="tab-content" id="tab-favorites">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3> <?php echo t('profile.favorites'); ?></h3>
            <a href="profile-favorites.php" class="btn btn-primary"><?php echo t('profile.view_all_favorites'); ?></a>
        </div>
        <?php if (empty($favoriteActivities)): ?>
            <div style="padding: 3rem 2rem; background: #f9f9f9; border-radius: 12px; text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;">💔</div>
                <h4><?php echo t('pages.no_favorites'); ?></h4>
                <p style="color: #666; margin: 1rem 0 1.5rem;"><?php echo t('profile.add_favorites_hint'); ?></p>
                <a href="../events/events-list.php" class="btn btn-secondary"><?php echo t('pages.discover_events'); ?></a>
            </div>
        <?php else: ?>
            <div class="activities-grid">
                <?php foreach ($favoriteActivities as $activity): ?>
                    <div class="event-card-wrapper" data-activity-id="<?php echo $activity['id']; ?>">
                        <a href="../events/event-details.php?id=<?php echo $activity['id']; ?>" class="event-card">
                            <div class="event-banner" style="background-image: url('<?php echo htmlspecialchars($activity['image'] ?? '', ENT_QUOTES, 'UTF-8'); ?>'); background-size: cover; background-position: center; height: 150px; position: relative;">
                                <button 
                                    class="favorite-remove-btn" 
                                    data-activity-id="<?php echo $activity['id']; ?>"
                                    onclick="event.preventDefault(); event.stopPropagation(); removeFavoriteFromProfile(this);"
                                    title="Retirer des favoris"
                                >
                                    <span class="heart-icon">❤️</span>
                                    <span class="remove-icon">✕</span>
                                </button>
                            </div>
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
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($favoriteActivities) >= 6): ?>
                <div style="text-align: center; margin-top: 2rem;">
                    <a href="profile-favorites.php" class="btn btn-secondary">Voir tous mes favoris →</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <!-- Onglet Mes activités créées -->
    <section class="tab-content" id="tab-created">
        <h3><?php echo t('profile.my_events'); ?></h3>
        <?php if (empty($myActivities)): ?>
            <p>Vous n'avez pas encore créé d'activité. <a href="../events/event-create.php">Créer une activité</a></p>
        <?php else: ?>
            <div class="activities-grid">
                <?php foreach ($myActivities as $activity): ?>
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
    </section>

    <!-- Onglet Activités inscrites -->
    <section class="tab-content" id="tab-registered">
        <h3>Activités auxquelles je participe</h3>
        <?php if (empty($registeredActivities)): ?>
            <p>Vous n'êtes inscrit à aucune activité pour le moment. <a href="../events/events-list.php">Découvrir les activités</a></p>
        <?php else: ?>
            <div class="activities-grid">
                <?php foreach ($registeredActivities as $activity): ?>
                    <a href="../events/event-details.php?id=<?php echo $activity['id']; ?>" class="event-card">
                        <div class="event-banner" style="background-image: url('<?php echo htmlspecialchars($activity['image'] ?? '', ENT_QUOTES, 'UTF-8'); ?>'); background-size: cover; background-position: center; height: 150px;"></div>
                        <div class="event-info">
                            <span class="badge" style="background: <?php echo htmlspecialchars($activity['category_color'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($activity['category_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <h4 class="event-title"><?php echo htmlspecialchars($activity['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p class="event-details">📅 <?php echo formatEventDate($activity['event_date']); ?> - <?php echo formatEventTime($activity['event_time']); ?></p>
                            <p class="event-details">📍 <?php echo htmlspecialchars($activity['location'] . ', ' . $activity['city'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="event-details">👤 Organisé par <?php echo htmlspecialchars($activity['creator_first_name'] ?? $activity['creator_username'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<script>
// Translations for JavaScript
const translations = {
    search_min_chars: <?php echo json_encode(t('profile.search_friends_min')); ?>,
    searching: <?php echo json_encode(t('profile.search_friends_loading')); ?>,
    no_users_found: <?php echo json_encode(t('profile.search_friends_no_results')); ?>,
    search_error: <?php echo json_encode(t('profile.error_loading')); ?>,
    loading: <?php echo json_encode(t('profile.search_friends_loading')); ?>,
    no_friends_yet: <?php echo json_encode(t('profile.no_friends_yet')); ?>,
    no_pending_requests: <?php echo json_encode(t('profile.no_pending_requests')); ?>,
    view_profile_title: <?php echo json_encode(t('profile.view_profile')); ?>,
    add_friend: <?php echo json_encode(t('profile.add_friend')); ?>,
    pending: <?php echo json_encode(t('profile.friend_pending')); ?>,
    friend: <?php echo json_encode(t('profile.friend_accepted')); ?>,
    view_profile: <?php echo json_encode(t('profile.view_profile')); ?>,
    remove_friend: <?php echo json_encode(t('profile.remove_friend')); ?>,
    accept: <?php echo json_encode(t('profile.accept_request')); ?>,
    decline: <?php echo json_encode(t('profile.decline_request')); ?>,
    remove_friend_confirm: <?php echo json_encode(t('profile.remove_friend_confirm')); ?>
};

// Gestion des onglets
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            
            // Retirer la classe active de tous les boutons
            tabButtons.forEach(btn => {
                btn.classList.remove('active', 'btn-primary');
                btn.classList.add('btn-secondary');
            });
            
            // Ajouter la classe active au bouton cliqué
            this.classList.add('active', 'btn-primary');
            this.classList.remove('btn-secondary');
            
            // Cacher tous les contenus
            tabContents.forEach(content => {
                content.classList.remove('active');
            });
            
            // Afficher le contenu correspondant
            const targetContent = document.getElementById('tab-' + targetTab);
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });
});

// Fonction pour retirer un favori depuis le profil
function removeFavoriteFromProfile(button) {
    const activityId = button.dataset.activityId;
    const cardWrapper = button.closest('.event-card-wrapper');
    
    if (!confirm('Retirer cette activité de vos favoris ?')) {
        return;
    }
    
    fetch('../events/api/favorite-toggle.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `activity_id=${activityId}&action=remove`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Animation de suppression
            cardWrapper.style.opacity = '0';
            cardWrapper.style.transform = 'scale(0.8)';
            
            setTimeout(() => {
                cardWrapper.remove();
                
                // Vérifier s'il reste des favoris
                const remainingCards = document.querySelectorAll('#tab-favorites .event-card-wrapper').length;
                if (remainingCards === 0) {
                    location.reload();
                } else {
                    // Mettre à jour le compteur dans l'onglet
                    const favoritesTab = document.querySelector('[data-tab="favorites"]');
                    if (favoritesTab) {
                        const match = favoritesTab.textContent.match(/\((\d+)\)/);
                        if (match) {
                            const newCount = parseInt(match[1]) - 1;
                            favoritesTab.innerHTML = favoritesTab.innerHTML.replace(/\(\d+\)/, `(${newCount})`);
                        }
                    }
                }
            }, 300);
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur de connexion');
    });
}

// Fonction pour retirer un ami depuis le profil
function removeFriendFromProfile(friendId) {
    if (!confirm('Voulez-vous vraiment retirer cet ami ?')) {
        return;
    }
    
    fetch('../pages/api/friends-action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ action: 'remove', friend_id: friendId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue');
    });
}

// Fonction pour voir le profil d'un ami
function viewProfile(userId) {
    window.location.href = `profile-other.php?id=${userId}`;
}

// === GESTION DES AMIS - SOUS-ONGLETS ===
document.querySelectorAll('.subtab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const subtabName = btn.dataset.subtab;
        
        document.querySelectorAll('.subtab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        
        document.querySelectorAll('.friends-subtab-content').forEach(t => t.classList.remove('active'));
        document.getElementById(subtabName + '-subtab').classList.add('active');
        
        if (subtabName === 'my-friends') {
            loadFriendsFullList();
        } else if (subtabName === 'requests') {
            loadFriendsRequests();
        }
    });
});

// Recherche d'amis
document.getElementById('friends-search-btn')?.addEventListener('click', searchFriendsInProfile);
document.getElementById('friends-search-input')?.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') searchFriendsInProfile();
});

async function searchFriendsInProfile() {
    const query = document.getElementById('friends-search-input').value.trim();
    const resultsContainer = document.getElementById('friends-search-results');
    
    if (query.length < 2) {
        resultsContainer.innerHTML = `<p class="info-message">${translations.search_min_chars}</p>`;
        return;
    }
    
    resultsContainer.innerHTML = `<p class="loading">${translations.searching}</p>`;
    
    try {
        const response = await fetch('../pages/api/friends-search.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ query })
        });
        
        const data = await response.json();
        
        if (data.success && data.users.length > 0) {
            displayFriendsUsers(data.users, resultsContainer, true);
        } else {
            resultsContainer.innerHTML = `<p class="info-message">${translations.no_users_found}</p>`;
        }
    } catch (error) {
        console.error('Error:', error);
        resultsContainer.innerHTML = `<p class="error-message">${translations.search_error}</p>`;
    }
}

async function loadFriendsFullList() {
    const container = document.getElementById('friends-list-full');
    container.innerHTML = `<p class="loading">${translations.loading}</p>`;
    
    try {
        const response = await fetch('../pages/api/friends-list.php');
        const data = await response.json();
        
        if (data.success && data.friends.length > 0) {
            displayFriendsUsers(data.friends, container, false);
        } else {
            container.innerHTML = `<p class="info-message">${translations.no_friends_yet}</p>`;
        }
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = `<p class="error-message">${translations.search_error}</p>`;
    }
}

async function loadFriendsRequests() {
    const container = document.getElementById('friends-requests-list');
    container.innerHTML = `<p class="loading">${translations.loading}</p>`;
    
    try {
        const response = await fetch('../pages/api/friends-requests.php');
        const data = await response.json();
        
        if (data.success && data.requests.length > 0) {
            displayFriendsRequests(data.requests, container);
        } else {
            container.innerHTML = `<p class="info-message">${translations.no_pending_requests}</p>`;
        }
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = `<p class="error-message">${translations.search_error}</p>`;
    }
}

function displayFriendsUsers(users, container, showAddButton) {
    container.innerHTML = users.map(user => `
        <div class="user-card">
            <div class="user-avatar-friends" onclick="viewProfile(${user.id})" style="cursor: pointer;" title="${translations.view_profile_title}">${user.first_name.charAt(0)}${user.last_name.charAt(0)}</div>
            <div class="user-info" onclick="viewProfile(${user.id})" style="cursor: pointer;" title="${translations.view_profile_title}">
                <h3 class="user-name">${user.first_name} ${user.last_name}</h3>
                <p class="user-username">@${user.username}</p>
            </div>
            <div class="user-actions">
                ${showAddButton ? (
                    user.friendship_status === 'none' 
                        ? `<button class="btn btn-primary btn-sm" onclick="sendFriendRequestProfile(${user.id})">➕ ${translations.add_friend}</button>`
                        : user.friendship_status === 'pending'
                        ? `<button class="btn btn-secondary btn-sm" disabled>⏳ ${translations.pending}</button>`
                        : `<button class="btn btn-success btn-sm" disabled>✓ ${translations.friend}</button>`
                ) : `
                    <button class="btn btn-info btn-sm" onclick="viewProfile(${user.id})">${translations.view_profile}</button>
                    <button class="btn btn-danger btn-sm" onclick="removeFriendProfile(${user.id})">✗ ${translations.remove_friend}</button>
                `}
            </div>
        </div>
    `).join('');
}

function displayFriendsRequests(requests, container) {
    container.innerHTML = requests.map(user => `
        <div class="user-card">
            <div class="user-avatar-friends" onclick="viewProfile(${user.id})" style="cursor: pointer;" title="${translations.view_profile_title}">${user.first_name.charAt(0)}${user.last_name.charAt(0)}</div>
            <div class="user-info" onclick="viewProfile(${user.id})" style="cursor: pointer;" title="${translations.view_profile_title}">
                <h3 class="user-name">${user.first_name} ${user.last_name}</h3>
                <p class="user-username">@${user.username}</p>
            </div>
            <div class="user-actions">
                <button class="btn btn-success btn-sm" onclick="acceptFriendRequest(${user.id})">✓ ${translations.accept}</button>
                <button class="btn btn-danger btn-sm" onclick="declineFriendRequest(${user.id})">✗ ${translations.decline}</button>
            </div>
        </div>
    `).join('');
}

async function sendFriendRequestProfile(friendId) {
    try {
        const response = await fetch('../pages/api/friends-action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'send', friend_id: friendId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotificationProfile(data.message, 'success');
            searchFriendsInProfile();
        } else {
            showNotificationProfile(data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotificationProfile('Erreur', 'error');
    }
}

async function acceptFriendRequest(friendId) {
    try {
        const response = await fetch('../pages/api/friends-action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'accept', friend_id: friendId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotificationProfile(data.message, 'success');
            loadFriendsRequests();
            document.querySelector('[data-subtab="requests"] .badge')?.remove();
            const pendingCount = document.getElementById('pending-count');
            if (pendingCount) {
                const current = parseInt(pendingCount.textContent);
                if (current > 0) pendingCount.textContent = current - 1;
            }
        } else {
            showNotificationProfile(data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function declineFriendRequest(friendId) {
    try {
        const response = await fetch('../pages/api/friends-action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'decline', friend_id: friendId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotificationProfile(data.message, 'success');
            loadFriendsRequests();
        } else {
            showNotificationProfile(data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function removeFriendProfile(friendId) {
    if (!confirm(translations.remove_friend_confirm)) return;
    
    try {
        const response = await fetch('../pages/api/friends-action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'remove', friend_id: friendId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotificationProfile(data.message, 'success');
            loadFriendsFullList();
        } else {
            showNotificationProfile(data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function showNotificationProfile(message, type) {
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

<style>
.event-card-wrapper {
    transition: all 0.3s ease;
}

.favorite-remove-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
    z-index: 10;
}

.favorite-remove-btn .heart-icon {
    font-size: 1.3rem;
    position: absolute;
    transition: all 0.3s ease;
}

.favorite-remove-btn .remove-icon {
    font-size: 1.5rem;
    color: #e74c3c;
    opacity: 0;
    transform: scale(0);
    transition: all 0.3s ease;
    font-weight: bold;
}

.favorite-remove-btn:hover {
    background: #fee;
    transform: scale(1.1);
}

.favorite-remove-btn:hover .heart-icon {
    opacity: 0;
    transform: scale(0);
}

.favorite-remove-btn:hover .remove-icon {
    opacity: 1;
    transform: scale(1);
}

/* Friends Grid */
.friends-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.friend-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    transition: all 0.3s ease;
}

.friend-card:hover {
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    transform: translateY(-3px);
}

.friend-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6ee0eb 0%, #4a7fad 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0 auto;
}

.friend-info {
    text-align: center;
}

.friend-name {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--accent-structure);
    margin: 0 0 0.25rem 0;
}

.friend-username {
    font-size: 0.95rem;
    color: #666;
    margin: 0 0 0.5rem 0;
}

.friend-since {
    font-size: 0.85rem;
    color: #999;
    margin: 0;
    font-style: italic;
}

.friend-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

/* Friends Section - Stats and Sub-tabs */
.friends-stats-mini {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card-mini {
    background: linear-gradient(135deg, #6ee0eb 0%, #4a7fad 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-card-mini .stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.stat-card-mini .stat-label {
    font-size: 0.95rem;
    opacity: 0.9;
}

.friends-subtabs {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid #e0e0e0;
}

.subtab-btn {
    background: transparent;
    border: none;
    padding: 0.75rem 1.25rem;
    font-size: 1rem;
    font-weight: 600;
    color: #666;
    cursor: pointer;
    transition: all 0.3s ease;
    border-bottom: 3px solid transparent;
    position: relative;
}

.subtab-btn:hover {
    color: var(--accent-structure);
}

.subtab-btn.active {
    color: var(--accent-structure);
    border-bottom-color: #6ee0eb;
}

.subtab-btn .badge {
    background: #ff4444;
    color: white;
    padding: 0.15rem 0.4rem;
    border-radius: 999px;
    font-size: 0.75rem;
    margin-left: 0.35rem;
}

.friends-subtab-content {
    display: none;
}

.friends-subtab-content.active {
    display: block;
}

.search-box-friends {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.search-box-friends .search-input {
    flex: 1;
    padding: 0.85rem 1.25rem;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.search-box-friends .search-input:focus {
    outline: none;
    border-color: #6ee0eb;
    box-shadow: 0 0 0 3px rgba(110, 224, 235, 0.1);
}

.search-box-friends .search-btn {
    padding: 0.85rem 1.75rem;
    background: linear-gradient(135deg, #6ee0eb 0%, #4a7fad 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.search-box-friends .search-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(110, 224, 235, 0.4);
}

.users-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
}

.user-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
}

.user-card:hover {
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    transform: translateY(-3px);
}

.user-avatar-friends {
    width: 55px;
    height: 55px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6ee0eb 0%, #4a7fad 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    font-weight: 700;
    flex-shrink: 0;
}

.user-info {
    flex: 1;
}

.user-name {
    font-size: 1rem;
    font-weight: 700;
    color: var(--accent-structure);
    margin: 0 0 0.2rem 0;
}

.user-username {
    font-size: 0.85rem;
    color: #666;
    margin: 0;
}

.user-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.info-message,
.loading,
.error-message {
    text-align: center;
    padding: 2rem 1rem;
    font-size: 1rem;
    color: #666;
    grid-column: 1 / -1;
}

.loading {
    font-style: italic;
}

.error-message {
    color: #ff4444;
}

@media (max-width: 768px) {
    .friends-subtabs {
        overflow-x: auto;
    }
    
    .subtab-btn {
        white-space: nowrap;
        font-size: 0.9rem;
        padding: 0.6rem 1rem;
    }
    
    .search-box-friends {
        flex-direction: column;
    }
    
    .users-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../includes/footer.php';
