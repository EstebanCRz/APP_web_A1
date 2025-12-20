<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once '../includes/config.php';
require_once '../includes/language.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$pageTitle = t('profile.choose_interests_title') . " - AmiGo";
$pageDescription = t('profile.choose_interests_subtitle');
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/profile.css"
];

$pdo = getDB();
$userId = (int)$_SESSION['user_id'];
$categories = [];
$selected = [];
$message = '';

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
    $selected = array_map(function($r){ return (int)$r['category_id']; }, $stmtSel->fetchAll());

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newInterests = isset($_POST['interests']) && is_array($_POST['interests']) ? array_map('intval', $_POST['interests']) : [];

        // Replace selections
        $pdo->prepare("DELETE FROM user_interest_categories WHERE user_id = ?")->execute([$userId]);
        if (!empty($newInterests)) {
            $ins = $pdo->prepare("INSERT INTO user_interest_categories (user_id, category_id) VALUES (?, ?)");
            foreach ($newInterests as $cid) {
                $ins->execute([$userId, $cid]);
            }
        }
        $message = t('profile.choose_interests_saved');
        // Redirect to recommendations
        header('Location: recommendations.php');
        exit;
    }
} catch (PDOException $e) {
    $categories = [];
}

include '../includes/header.php';
?>

<div class="container choose-interests">
    <h2><?php echo t('profile.choose_interests_title'); ?></h2>
    <p class="subtitle"><?php echo t('profile.choose_interests_subtitle'); ?></p>

    <form method="POST">
        <div class="interests-grid">
            <?php foreach ($categories as $cat): $cid = (int)$cat['id']; ?>
                <label class="interest-item">
                    <input type="checkbox" name="interests[]" value="<?php echo $cid; ?>" <?php echo in_array($cid, $selected) ? 'checked' : ''; ?>>
                    <span><?php echo t('categories.' . $cat['name']); ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        <div class="actions">
            <button type="submit" class="btn btn-primary">
                <?php echo t('profile.choose_interests_save'); ?>
            </button>
            <a href="recommendations.php" class="btn btn-secondary">
                <?php echo t('profile.choose_interests_skip'); ?>
            </a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
