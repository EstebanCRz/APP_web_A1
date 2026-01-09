<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/gamification.php';
require_once __DIR__ . '/../includes/language.php';

// Rediriger si pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$pageTitle = t('badges.page_title');
require_once __DIR__ . '/../includes/header.php';

// Récupérer la connexion à la base de données
$pdo = getDB();

// Récupérer tous les badges
$stmt = $pdo->query("SELECT * FROM badges ORDER BY condition_value ASC, condition_type ASC");
$allBadges = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les badges de l'utilisateur
$stmt = $pdo->prepare("SELECT badge_id FROM user_badges WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userBadgeIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Récupérer les stats pour afficher la progression
$userStats = getUserStats($_SESSION['user_id']);
?>

<link rel="stylesheet" href="css/badges.css">

<div class="container">
    <div class="badges-header">
        <h1><?php echo t('badges.page_title'); ?></h1>
        <p class="subtitle"><?php echo t('badges.subtitle'); ?></p>
        
        <!-- Niveau et points -->
        <div class="level-info-section">
            <div class="level-badge">
                <span class="level-icon">⭐</span>
                <div class="level-details">
                    <div class="level-number">Niveau <?php echo $userStats['level']; ?></div>
                    <div class="level-points"><?php echo number_format($userStats['total_points']); ?> points</div>
                </div>
            </div>
            <div class="level-progress">
                <div class="progress-label">
                    <span>Progression vers le niveau <?php echo $userStats['level'] + 1; ?></span>
                    <span class="progress-fraction"><?php echo number_format($userStats['points_in_current_level']); ?> / <?php echo number_format($userStats['points_needed_for_next']); ?> pts</span>
                </div>
                <div class="progress-bar-level">
                    <div class="progress-fill-level" style="width: <?php echo $userStats['progress_percent']; ?>%">
                        <span class="progress-text-inner"><?php echo $userStats['progress_percent']; ?>%</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Badges débloqués -->
        <div class="badges-progress">
            <span class="progress-text">
                <?php echo count($userBadgeIds); ?> / <?php echo count($allBadges); ?> <?php echo t('badges.unlocked'); ?>
            </span>
            <div class="progress-bar-small">
                <div class="progress-fill-small" style="width: <?php echo round((count($userBadgeIds) / count($allBadges)) * 100); ?>%"></div>
            </div>
        </div>
    </div>
    
    <!-- Catégories de badges -->
    <?php
    $categories = [
        'events_created' => ['icon' => '📅', 'name_fr' => 'Organisateur', 'name_en' => 'Organizer'],
        'events_attended' => ['icon' => '🎉', 'name_fr' => 'Participant', 'name_en' => 'Participant'],
        'friends_count' => ['icon' => '🤝', 'name_fr' => 'Social', 'name_en' => 'Social'],
        'reviews_count' => ['icon' => '📝', 'name_fr' => 'Critique', 'name_en' => 'Reviewer'],
        'groups_created' => ['icon' => '💬', 'name_fr' => 'Communauté', 'name_en' => 'Community'],
        'messages_sent' => ['icon' => '💭', 'name_fr' => 'Communication', 'name_en' => 'Communication'],
        'level' => ['icon' => '⭐', 'name_fr' => 'Niveau', 'name_en' => 'Level'],
    ];
    
    foreach ($categories as $categoryType => $category):
        $categoryBadges = array_filter($allBadges, function($b) use ($categoryType) {
            return $b['condition_type'] === $categoryType;
        });
        
        if (empty($categoryBadges)) continue;
    ?>
        <div class="badge-category">
            <h2 class="category-title">
                <span class="category-icon"><?php echo $category['icon']; ?></span>
                <?php echo $category['name_' . getCurrentLanguage()]; ?>
            </h2>
            
            <div class="badges-grid">
                <?php foreach ($categoryBadges as $badge):
                    $isUnlocked = in_array($badge['id'], $userBadgeIds);
                    
                    // Calculer la progression
                    $currentValue = 0;
                    switch ($badge['condition_type']) {
                        case 'events_created':
                            $currentValue = $userStats['stats']['events_created'];
                            break;
                        case 'events_attended':
                            $currentValue = $userStats['stats']['events_attended'];
                            break;
                        case 'friends_count':
                            $currentValue = $userStats['stats']['friends_count'];
                            break;
                        case 'reviews_count':
                            $currentValue = $userStats['stats']['reviews_count'];
                            break;
                        case 'groups_created':
                            $currentValue = $userStats['stats']['groups_created'];
                            break;
                        case 'messages_sent':
                            $currentValue = $userStats['stats']['messages_sent'];
                            break;
                        case 'level':
                            $currentValue = $userStats['level'];
                            break;
                    }
                    
                    $progress = min(100, round(($currentValue / $badge['condition_value']) * 100));
                ?>
                    <div class="badge-card <?php echo $isUnlocked ? 'unlocked' : 'locked'; ?>">
                        <div class="badge-icon-large">
                            <?php echo $badge['icon']; ?>
                            <?php if ($isUnlocked): ?>
                                <div class="unlock-checkmark">✓</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="badge-details">
                            <h3 class="badge-name"><?php echo $badge['name_' . getCurrentLanguage()]; ?></h3>
                            <p class="badge-description"><?php echo $badge['description_' . getCurrentLanguage()]; ?></p>
                            
                            <?php if ($isUnlocked): ?>
                                <div class="badge-status unlocked-status">
                                    ✓ <?php echo t('badges.unlocked'); ?>
                                </div>
                            <?php else: ?>
                                <div class="badge-progress-info">
                                    <div class="progress-text-small">
                                        <?php echo $currentValue; ?> / <?php echo $badge['condition_value']; ?>
                                    </div>
                                    <div class="progress-bar-tiny">
                                        <div class="progress-fill-tiny" style="width: <?php echo $progress; ?>%"></div>
                                    </div>
                                    <div class="progress-percent"><?php echo $progress; ?>%</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
    
    <!-- Astuces pour gagner plus de badges -->
    <div class="tips-section">
        <h2><?php echo t('badges.tips_title'); ?></h2>
        <div class="tips-grid">
            <div class="tip-card">
                <div class="tip-icon">📅</div>
                <h3><?php echo t('badges.tip1_title'); ?></h3>
                <p><?php echo t('badges.tip1_desc'); ?></p>
            </div>
            <div class="tip-card">
                <div class="tip-icon">🎉</div>
                <h3><?php echo t('badges.tip2_title'); ?></h3>
                <p><?php echo t('badges.tip2_desc'); ?></p>
            </div>
            <div class="tip-card">
                <div class="tip-icon">🤝</div>
                <h3><?php echo t('badges.tip3_title'); ?></h3>
                <p><?php echo t('badges.tip3_desc'); ?></p>
            </div>
            <div class="tip-card">
                <div class="tip-icon">📝</div>
                <h3><?php echo t('badges.tip4_title'); ?></h3>
                <p><?php echo t('badges.tip4_desc'); ?></p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
