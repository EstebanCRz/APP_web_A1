<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once '../includes/config.php';
require_once '../includes/language.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$pageTitle = t('profile.recommendations_title') . " - AmiGo";
$pageDescription = t('profile.recommendations_desc');
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/profile.css"
];

$userId = (int)$_SESSION['user_id'];
$interests = [];
$activities = [];

try {
    $pdo = getDB();
    // Fetch user's interests
    $stmt = $pdo->prepare("SELECT c.id, c.name FROM user_interest_categories uic JOIN activity_categories c ON c.id = uic.category_id WHERE uic.user_id = ? ORDER BY c.name");
    $stmt->execute([$userId]);
    $interests = $stmt->fetchAll();

    if (!empty($interests)) {
        $catIds = array_map(function($c){ return (int)$c['id']; }, $interests);
        $placeholders = implode(',', array_fill(0, count($catIds), '?'));
        // Upcoming activities in interested categories
        $sql = "SELECT a.id, a.title, a.excerpt, a.event_date, a.event_time, a.city, c.name AS category_name
                FROM activities a
                JOIN activity_categories c ON c.id = a.category_id
                WHERE a.category_id IN ($placeholders) AND a.status = 'active' AND a.event_date >= CURDATE()
                ORDER BY a.event_date ASC, a.event_time ASC
                LIMIT 20";
        $stmt2 = $pdo->prepare($sql);
        $stmt2->execute($catIds);
        $activities = $stmt2->fetchAll();
    }
} catch (PDOException $e) {
    $interests = [];
    $activities = [];
}

include '../includes/header.php';
?>

<div class="container">
    <h2><?php echo t('profile.recommendations_title'); ?></h2>
    <p><?php echo t('profile.recommendations_desc'); ?></p>

    <?php if (empty($interests)): ?>
        <div class="alert alert-error" style="margin-top:15px;">
            <p><?php echo t('profile.no_recommendations'); ?></p>
            <p><a href="../pages/settings.php"><?php echo t('profile.update_interests'); ?></a></p>
        </div>
    <?php else: ?>
        <div class="interests-list" style="margin: 10px 0 16px 0;">
            <?php foreach ($interests as $i): ?>
                <span class="chip">
                    <?php echo t('categories.' . $i['name']); ?>
                </span>
            <?php endforeach; ?>
        </div>

        <?php if (empty($activities)): ?>
            <p><?php echo t('profile.no_recommendations'); ?></p>
        <?php else: ?>
            <div class="activities-grid">
                <?php foreach ($activities as $act): ?>
                    <a href="../events/event-details.php?id=<?php echo (int)$act['id']; ?>" class="event-card">
                        <div class="event-banner" style="background-image:url('https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?q=80&w=800');"></div>
                        <div class="event-info">
                            <div style="font-size:14px;color:#7f8c8d;margin-bottom:6px;">
                            <?php echo t('categories.' . $act['category_name']); ?> • <?php echo htmlspecialchars($act['city']); ?>
                            </div>
                            <h4 class="event-title"><?php echo htmlspecialchars($act['title']); ?></h4>
                            <p class="event-details"><?php echo htmlspecialchars($act['excerpt']); ?></p>
                            <p class="event-details"><?php echo htmlspecialchars($act['event_date']); ?> • <?php echo htmlspecialchars(substr($act['event_time'],0,5)); ?></p>
                            <span class="btn btn-primary"><?php echo t('events.view_details'); ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php';
