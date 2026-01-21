<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/gamification.php';
require_once __DIR__ . '/../includes/language.php';

// Rediriger si pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$pageTitle = t('leaderboard.page_title');
require_once __DIR__ . '/../includes/header.php';

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

// Récupérer le classement
$leaderboard = getLeaderboard($limit, $offset);

// Récupérer les stats de l'utilisateur actuel
$userStats = getUserStats($_SESSION['user_id']);

// Récupérer les derniers badges obtenus
$recentBadges = getRecentBadges(10);
?>

<link rel="stylesheet" href="css/leaderboard.css">

<div class="container">
    <div class="leaderboard-header">
        <h1><?php echo t('leaderboard.page_title'); ?></h1>
        <p class="subtitle"><?php echo t('leaderboard.subtitle'); ?></p>
    </div>
    
    <!-- Stats personnelles -->
    <div class="user-stats-banner">
        <div class="stat-card highlight">
            <div class="stat-icon">🏆</div>
            <div class="stat-info">
                <div class="stat-value"><?php echo number_format($userStats['total_points']); ?></div>
                <div class="stat-label"><?php echo t('leaderboard.your_points'); ?></div>
            </div>
        </div>
        
        <div class="stat-card highlight">
            <div class="stat-icon" style="color: <?php echo getLevelColor($userStats['level']); ?>">⭐</div>
            <div class="stat-info">
                <div class="stat-value"><?php echo t('leaderboard.level'); ?> <?php echo $userStats['level']; ?></div>
                <div class="stat-label"><?php echo getLevelName($userStats['level'])[getCurrentLanguage()]; ?></div>
            </div>
        </div>
        
        <div class="stat-card highlight">
            <div class="stat-icon">📊</div>
            <div class="stat-info">
                <div class="stat-value">#<?php echo $userStats['rank']; ?></div>
                <div class="stat-label"><?php echo t('leaderboard.your_rank'); ?></div>
            </div>
        </div>
        
        <div class="stat-card highlight">
            <div class="stat-icon">🎖️</div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $userStats['badge_count']; ?></div>
                <div class="stat-label"><?php echo t('leaderboard.badges'); ?></div>
            </div>
        </div>
    </div>
    
    <!-- Barre de progression vers le prochain niveau -->
    <div class="level-progress-card">
        <div class="progress-header">
            <span><?php echo t('leaderboard.progress_to_next'); ?></span>
            <span class="progress-points">
                <?php echo number_format($userStats['points_in_current_level']); ?> / <?php echo number_format($userStats['points_needed_for_next']); ?>
            </span>
        </div>
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?php echo $userStats['progress_percent']; ?>%"></div>
        </div>
        <small><?php echo $userStats['progress_percent']; ?>% - <?php echo number_format($userStats['points_needed_for_next'] - $userStats['points_in_current_level']); ?> <?php echo t('leaderboard.points_remaining'); ?></small>
    </div>
    
    <div class="leaderboard-grid">
        <!-- Classement principal -->
        <div class="leaderboard-section">
            <div class="section-header">
                <h2><?php echo t('leaderboard.ranking'); ?></h2>
            </div>
            
            <?php if (empty($leaderboard)): ?>
                <div class="empty-state">
                    <p><?php echo t('leaderboard.no_users'); ?></p>
                </div>
            <?php else: ?>
                <div class="ranking-list">
                    <?php foreach ($leaderboard as $user): ?>
                        <div class="ranking-item <?php echo $user['id'] == $_SESSION['user_id'] ? 'current-user' : ''; ?>">
                            <div class="rank-badge rank-<?php echo $user['rank']; ?>">
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
                            
                            <div class="user-info">
                                <div class="user-name">
                                    <a href="../profile/profile-other.php?id=<?php echo $user['id']; ?>">
                                        <?php 
                                        $displayName = trim($user['first_name'] . ' ' . $user['last_name']);
                                        echo htmlspecialchars($displayName ?: $user['username']); 
                                        ?>
                                    </a>
                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                        <span class="you-badge"><?php echo t('leaderboard.you'); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="user-stats-mini">
                                    <span title="<?php echo t('leaderboard.events_created'); ?>">📅 <?php echo $user['events_created']; ?></span>
                                    <span title="<?php echo t('leaderboard.events_attended'); ?>">🎉 <?php echo $user['events_attended']; ?></span>
                                    <span title="<?php echo t('leaderboard.badges'); ?>">🎖️ <?php echo $user['badge_count']; ?></span>
                                </div>
                            </div>
                            
                            <div class="user-level">
                                <div class="level-badge" style="border-color: <?php echo getLevelColor($user['level']); ?>">
                                    <div class="level-number"><?php echo $user['level']; ?></div>
                                </div>
                            </div>
                            
                            <div class="user-points">
                                <div class="points-value"><?php echo number_format($user['total_points']); ?></div>
                                <div class="points-label"><?php echo t('leaderboard.points'); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if (count($leaderboard) >= $limit): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="btn-pagination">← <?php echo t('leaderboard.previous'); ?></a>
                        <?php endif; ?>
                        
                        <span class="page-info"><?php echo t('leaderboard.page'); ?> <?php echo $page; ?></span>
                        
                        <?php if (count($leaderboard) >= $limit): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="btn-pagination"><?php echo t('leaderboard.next'); ?> →</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar - Derniers badges -->
        <div class="sidebar-section">
            <div class="section-header">
                <h3><?php echo t('leaderboard.recent_badges'); ?></h3>
            </div>
            
            <?php if (empty($recentBadges)): ?>
                <div class="empty-state-small">
                    <p><?php echo t('leaderboard.no_recent_badges'); ?></p>
                </div>
            <?php else: ?>
                <div class="recent-badges-list">
                    <?php foreach ($recentBadges as $badge): ?>
                        <div class="badge-item">
                            <div class="badge-icon"><?php echo $badge['icon']; ?></div>
                            <div class="badge-info">
                                <div class="badge-name"><?php echo $badge['name_' . getCurrentLanguage()]; ?></div>
                                <div class="badge-user">
                                    <?php 
                                    $displayName = trim($badge['first_name'] . ' ' . $badge['last_name']);
                                    echo htmlspecialchars($displayName ?: $badge['username']); 
                                    ?>
                                </div>
                                <div class="badge-time"><?php echo formatRelativeTime($badge['earned_at']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Lien vers badges -->
            <a href="badges.php" class="btn-view-all"><?php echo t('leaderboard.view_all_badges'); ?></a>
        </div>
    </div>
</div>

<?php
// Fonction helper pour formater le temps relatif
function formatRelativeTime($datetime) {
    $now = new DateTime();
    $time = new DateTime($datetime);
    $diff = $now->diff($time);
    
    if ($diff->days == 0) {
        if ($diff->h == 0) {
            return $diff->i . ' min';
        }
        return $diff->h . 'h';
    } elseif ($diff->days < 7) {
        return $diff->days . 'j';
    } else {
        return $time->format('d/m');
    }
}
?>

<?php require_once __DIR__ . '/../includes/footer.php';
