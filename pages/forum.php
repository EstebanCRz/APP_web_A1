<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once '../includes/config.php';
require_once '../includes/language.php';

$pageTitle = t('forum.title') . " - AmiGo";
$pageDescription = t('forum.description');
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/forum.css"
];

$pdo = getDB();
$topics = [];
$categories = [
    'general' => 'Général',
    'events' => 'Événements',
    'help' => 'Aide & Support',
    'suggestions' => 'Suggestions'
];

try {
    // Create forum tables if they don't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS forum_topics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        category VARCHAR(50) NOT NULL,
        author_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        views INT DEFAULT 0,
        is_pinned BOOLEAN DEFAULT FALSE,
        is_locked BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_category (category),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS forum_posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        topic_id INT NOT NULL,
        author_id INT NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (topic_id) REFERENCES forum_topics(id) ON DELETE CASCADE,
        FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_topic (topic_id),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Get filter
    $filterCategory = isset($_GET['category']) ? $_GET['category'] : 'all';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    // Build query
    $sql = "SELECT t.*, u.username, u.first_name, u.last_name,
            (SELECT COUNT(*) FROM forum_posts p WHERE p.topic_id = t.id) as post_count,
            (SELECT MAX(p.created_at) FROM forum_posts p WHERE p.topic_id = t.id) as last_post_at
            FROM forum_topics t
            JOIN users u ON u.id = t.author_id
            WHERE 1=1";
    
    $params = [];
    
    if ($filterCategory !== 'all') {
        $sql .= " AND t.category = ?";
        $params[] = $filterCategory;
    }
    
    if (!empty($search)) {
        $sql .= " AND t.title LIKE ?";
        $params[] = "%$search%";
    }
    
    $sql .= " ORDER BY t.is_pinned DESC, t.updated_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $topics = $stmt->fetchAll();

} catch (PDOException $e) {
    $topics = [];
}

include '../includes/header.php';
?>

<div class="container forum-container">
    <div class="forum-header">
        <h1>💬 <?php echo t('forum.title'); ?></h1>
        <p><?php echo t('forum.description'); ?></p>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="forum-create.php" class="btn btn-primary">
                ➕ <?php echo t('forum.create_topic'); ?>
            </a>
        <?php endif; ?>
    </div>

    <div class="forum-filters">
        <form method="GET" class="forum-search">
            <input type="text" name="search" placeholder="<?php echo t('forum.search_placeholder'); ?>" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-secondary"><?php echo t('forum.search'); ?></button>
        </form>
        
        <div class="category-filters">
            <a href="?category=all" class="filter-btn <?php echo $filterCategory === 'all' ? 'active' : ''; ?>">
                <?php echo t('forum.all_categories'); ?>
            </a>
            <?php foreach ($categories as $key => $label): ?>
                <a href="?category=<?php echo $key; ?>" class="filter-btn <?php echo $filterCategory === $key ? 'active' : ''; ?>">
                    <?php echo $label; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="forum-topics">
        <?php if (empty($topics)): ?>
            <div class="no-topics">
                <p><?php echo t('forum.no_topics'); ?></p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="forum-create.php" class="btn btn-primary">
                        <?php echo t('forum.create_first_topic'); ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <table class="topics-table">
                <thead>
                    <tr>
                        <th><?php echo t('forum.topic'); ?></th>
                        <th><?php echo t('forum.category'); ?></th>
                        <th><?php echo t('forum.author'); ?></th>
                        <th><?php echo t('forum.replies'); ?></th>
                        <th><?php echo t('forum.views'); ?></th>
                        <th><?php echo t('forum.last_post'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topics as $topic): ?>
                        <tr class="topic-row <?php echo $topic['is_pinned'] ? 'pinned' : ''; ?> <?php echo $topic['is_locked'] ? 'locked' : ''; ?>">
                            <td class="topic-title">
                                <?php if ($topic['is_pinned']): ?>
                                    <span class="topic-badge pinned">📌</span>
                                <?php endif; ?>
                                <?php if ($topic['is_locked']): ?>
                                    <span class="topic-badge locked">🔒</span>
                                <?php endif; ?>
                                <a href="forum-topic.php?id=<?php echo $topic['id']; ?>">
                                    <?php echo htmlspecialchars($topic['title']); ?>
                                </a>
                            </td>
                            <td>
                                <span class="category-badge"><?php echo $categories[$topic['category']] ?? $topic['category']; ?></span>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($topic['username']); ?>
                            </td>
                            <td class="topic-stat"><?php echo $topic['post_count']; ?></td>
                            <td class="topic-stat"><?php echo $topic['views']; ?></td>
                            <td class="topic-date">
                                <?php 
                                $lastPost = $topic['last_post_at'] ?? $topic['created_at'];
                                echo date('d/m/Y H:i', strtotime($lastPost)); 
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php';
