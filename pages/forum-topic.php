<?php
require_once '../includes/session.php';
require_once '../includes/config.php';
require_once '../includes/language.php';
require_once '../includes/security.php';

$pageTitle = "Sujet du forum - AmiGo";
$pageDescription = "Discussion du forum";
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/forum.css"
];

$pdo = getDB();
$security = new Security($pdo);
$topicId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$topic = null;
$posts = [];
$error = '';
$message = '';

if ($topicId <= 0) {
    header('Location: forum.php');
    exit;
}

try {
    // Increment view count
    $pdo->prepare("UPDATE forum_topics SET views = views + 1 WHERE id = ?")->execute([$topicId]);
    
    // Get topic details
    $stmt = $pdo->prepare("
        SELECT t.*, u.username, u.first_name, u.last_name
        FROM forum_topics t
        JOIN users u ON u.id = t.author_id
        WHERE t.id = ?
    ");
    $stmt->execute([$topicId]);
    $topic = $stmt->fetch();
    
    if (!$topic) {
        header('Location: forum.php');
        exit;
    }
    
    $pageTitle = htmlspecialchars($topic['title']) . " - Forum AmiGo";
    
    // Get all posts
    $stmt2 = $pdo->prepare("
        SELECT p.*, u.username, u.first_name, u.last_name
        FROM forum_posts p
        JOIN users u ON u.id = p.author_id
        WHERE p.topic_id = ?
        ORDER BY p.created_at ASC
    ");
    $stmt2->execute([$topicId]);
    $posts = $stmt2->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Erreur lors du chargement du sujet';
}

// Handle new post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    if (!$security->verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        $content = $security->cleanInput($_POST['content'] ?? '');
        $userId = (int)$_SESSION['user_id'];
        
        if (empty($content)) {
            $error = 'Le message ne peut pas être vide';
        } elseif (strlen($content) < 5) {
            $error = 'Le message doit contenir au moins 5 caractères';
        } elseif ($topic['is_locked']) {
            $error = 'Ce sujet est verrouillé';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO forum_posts (topic_id, author_id, content) VALUES (?, ?, ?)");
                $stmt->execute([$topicId, $userId, $content]);
                
                $message = 'Réponse publiée avec succès';
                
                // Reload posts
                $stmt2 = $pdo->prepare("
                    SELECT p.*, u.username, u.first_name, u.last_name
                    FROM forum_posts p
                    JOIN users u ON u.id = p.author_id
                    WHERE p.topic_id = ?
                    ORDER BY p.created_at ASC
                ");
                $stmt2->execute([$topicId]);
                $posts = $stmt2->fetchAll();
                
            } catch (PDOException $e) {
                $error = 'Erreur lors de la publication';
            }
        }
    }
}

$csrfToken = $security->generateCSRFToken();

include '../includes/header.php';
?>

<div class="container forum-topic-container">
    <div class="breadcrumb">
        <a href="forum.php">← Retour au forum</a>
    </div>
    
    <?php if ($topic): ?>
        <div class="topic-header">
            <div class="topic-info">
                <?php if ($topic['is_pinned']): ?>
                    <span class="topic-badge pinned">📌 Épinglé</span>
                <?php endif; ?>
                <?php if ($topic['is_locked']): ?>
                    <span class="topic-badge locked">🔒 Verrouillé</span>
                <?php endif; ?>
                <h1><?php echo htmlspecialchars($topic['title']); ?></h1>
                <div class="topic-meta">
                    <span>👤 Par <strong><?php echo htmlspecialchars($topic['username']); ?></strong></span>
                    <span>📅 <?php echo date('d/m/Y à H:i', strtotime($topic['created_at'])); ?></span>
                    <span>👁️ <?php echo $topic['views']; ?> vues</span>
                </div>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="posts-list">
            <?php foreach ($posts as $index => $post): ?>
                <div class="post-item <?php echo $index === 0 ? 'first-post' : ''; ?>">
                    <div class="post-author">
                        <div class="author-avatar">
                            <?php echo strtoupper(substr($post['username'], 0, 2)); ?>
                        </div>
                        <div class="author-name">
                            <?php echo htmlspecialchars($post['username']); ?>
                        </div>
                        <?php if ($index === 0): ?>
                            <div class="author-badge">Auteur</div>
                        <?php endif; ?>
                    </div>
                    <div class="post-content">
                        <div class="post-meta">
                            <?php echo date('d/m/Y à H:i', strtotime($post['created_at'])); ?>
                        </div>
                        <div class="post-text">
                            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (isset($_SESSION['user_id']) && !$topic['is_locked']): ?>
            <div class="reply-form">
                <h3>💬 Répondre au sujet</h3>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <textarea name="content" rows="6" placeholder="Écrivez votre réponse..." required></textarea>
                    <button type="submit" class="btn btn-primary">
                        ✅ Publier la réponse
                    </button>
                </form>
            </div>
        <?php elseif (!isset($_SESSION['user_id'])): ?>
            <div class="login-prompt">
                <p>Vous devez être <a href="../auth/login.php">connecté</a> pour répondre</p>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="alert alert-error">
            Sujet introuvable
        </div>
    <?php endif; ?>
</div>

<style>
.forum-topic-container {
    max-width: 1000px;
    margin: 0 auto;
}

.breadcrumb {
    margin-bottom: 20px;
}

.breadcrumb a {
    color: #55D5E0;
    text-decoration: none;
    font-size: 1.1em;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.topic-header {
    background: linear-gradient(135deg, #55D5E0 0%, #335F8A 100%);
    color: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
}

.topic-header h1 {
    margin: 10px 0;
    font-size: 2em;
}

.topic-meta {
    display: flex;
    gap: 20px;
    margin-top: 15px;
    opacity: 0.9;
}

.topic-badge {
    display: inline-block;
    padding: 4px 12px;
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    font-size: 0.85em;
    margin-right: 10px;
}

.posts-list {
    margin-bottom: 30px;
}

.post-item {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    display: flex;
    gap: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.post-item.first-post {
    border-left: 4px solid #55D5E0;
}

.post-author {
    flex-shrink: 0;
    text-align: center;
    width: 120px;
}

.author-avatar {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #55D5E0 0%, #335F8A 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5em;
    font-weight: bold;
    margin: 0 auto 10px;
}

.author-name {
    font-weight: 600;
    margin-bottom: 5px;
}

.author-badge {
    display: inline-block;
    padding: 3px 8px;
    background: #55D5E0;
    color: white;
    border-radius: 10px;
    font-size: 0.75em;
    margin-top: 5px;
}

.post-content {
    flex: 1;
}

.post-meta {
    color: #999;
    font-size: 0.85em;
    margin-bottom: 10px;
}

.post-text {
    line-height: 1.6;
    color: #333;
}

.reply-form {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.reply-form h3 {
    margin-top: 0;
    margin-bottom: 15px;
}

.reply-form textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e1e8ed;
    border-radius: 8px;
    font-size: 1em;
    font-family: inherit;
    margin-bottom: 15px;
    resize: vertical;
}

.reply-form textarea:focus {
    outline: none;
    border-color: #55D5E0;
}

.login-prompt {
    text-align: center;
    padding: 30px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.login-prompt a {
    color: #55D5E0;
    font-weight: 600;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-error {
    background: #fee;
    color: #c33;
    border: 1px solid #fcc;
}

.alert-success {
    background: #efe;
    color: #3c3;
    border: 1px solid #cfc;
}
</style>

<?php include '../includes/footer.php';
