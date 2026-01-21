<?php
session_start();
require_once '../includes/language.php';
require_once '../includes/config.php';
header('Content-Type: text/html; charset=UTF-8');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$pageTitle = t('pages.settings') . " - AmiGo";
$pageDescription = t('pages.settings');
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "../assets/css/index.css",
    "../profile/css/profile.css"
];

$pdo = getDB();
$userId = (int)$_SESSION['user_id'];
$categories = [];
$selectedInterests = [];
$interestsMessage = '';

try {
    // Ensure mapping table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_interest_categories (
        user_id INT NOT NULL,
        category_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, category_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES activity_categories(id) ON DELETE RESTRICT,
        INDEX idx_user (user_id),
        INDEX idx_category (category_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Load categories
    $stmtCats = $pdo->query("SELECT id, name FROM activity_categories ORDER BY name");
    $categories = $stmtCats->fetchAll();

    // Load current selections
    $stmtSel = $pdo->prepare("SELECT category_id FROM user_interest_categories WHERE user_id = ?");
    $stmtSel->execute([$userId]);
    $selectedInterests = array_map(function($r){ return (int)$r['category_id']; }, $stmtSel->fetchAll());

    // Handle interests update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['interests'])) {
        $newInterests = is_array($_POST['interests']) ? array_map('intval', $_POST['interests']) : [];
        $pdo->prepare("DELETE FROM user_interest_categories WHERE user_id = ?")->execute([$userId]);
        if (!empty($newInterests)) {
            $ins = $pdo->prepare("INSERT INTO user_interest_categories (user_id, category_id) VALUES (?, ?)");
            foreach ($newInterests as $cid) {
                $ins->execute([$userId, $cid]);
            }
        }
        $selectedInterests = $newInterests;
        $interestsMessage = t('profile.choose_interests_saved');
    }
} catch (PDOException $e) {
    $categories = [];
}

include '../includes/header.php';
?>

<div class="container">
    <h2><?php echo t('pages.settings'); ?></h2>
    
    <section>
        <h3><?php echo t('pages.notifications'); ?></h3>
        <form method="POST" action="">
            <div class="form-group">
                <label for="language"><?php echo t('pages.language_preference'); ?></label>
                <select id="language" name="language" class="language-selector">
                    <option value="fr">Français</option>
                    <option value="en">English</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="notifications"><?php echo t('pages.receive_email_notifications'); ?></label>
                <input type="checkbox" id="notifications" name="notifications" checked>
            </div>
            
            <button type="submit" class="btn btn-primary"><?php echo t('pages.save_settings'); ?></button>
        </form>
    </section>

    <section class="choose-interests">
        <h3><?php echo t('profile.choose_interests_title'); ?></h3>
        <p class="subtitle"><?php echo t('profile.choose_interests_subtitle'); ?></p>
        <?php if ($interestsMessage): ?>
            <p class="chip" style="border-color:#c7d2ff;background:#f3f5ff;">
                <?php echo htmlspecialchars($interestsMessage, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="interests-grid">
                <?php foreach ($categories as $cat): $cid = (int)$cat['id']; ?>
                    <label class="interest-item">
                        <input type="checkbox" name="interests[]" value="<?php echo $cid; ?>" <?php echo in_array($cid, $selectedInterests) ? 'checked' : ''; ?>>
                        <span><?php echo t('categories.' . $cat['name']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="actions">
                <button type="submit" class="btn btn-primary"><?php echo t('profile.choose_interests_save'); ?></button>
                <a href="../profile/recommendations.php" class="btn btn-secondary"><?php echo t('profile.update_interests'); ?></a>
            </div>
        </form>
    </section>
</div>

<?php include '../includes/footer.php';
