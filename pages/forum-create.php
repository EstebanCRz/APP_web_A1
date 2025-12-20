<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once '../includes/config.php';
require_once '../includes/language.php';
require_once '../includes/security.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$pageTitle = t('forum.create_topic') . " - AmiGo";
$pageDescription = "Créer un nouveau sujet sur le forum";
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/forum.css"
];

$pdo = getDB();
$security = new Security($pdo);
$userId = (int)$_SESSION['user_id'];
$message = '';
$error = '';

$categories = [
    'general' => 'Général',
    'events' => 'Événements',
    'help' => 'Aide & Support',
    'suggestions' => 'Suggestions'
];

// Generate CSRF token
$csrfToken = $security->generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!$security->verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        $title = $security->cleanInput($_POST['title'] ?? '');
        $category = $_POST['category'] ?? '';
        $content = $security->cleanInput($_POST['content'] ?? '');
        
        // Validation
        if (empty($title) || empty($category) || empty($content)) {
            $error = 'Tous les champs sont obligatoires';
        } elseif (strlen($title) < 5) {
            $error = 'Le titre doit contenir au moins 5 caractères';
        } elseif (strlen($content) < 10) {
            $error = 'Le contenu doit contenir au moins 10 caractères';
        } elseif (!array_key_exists($category, $categories)) {
            $error = 'Catégorie invalide';
        } else {
            try {
                // Create the topic
                $stmt = $pdo->prepare("INSERT INTO forum_topics (title, category, author_id) VALUES (?, ?, ?)");
                $stmt->execute([$title, $category, $userId]);
                
                $topicId = $pdo->lastInsertId();
                
                // Create the first post (content of the topic)
                $stmt2 = $pdo->prepare("INSERT INTO forum_posts (topic_id, author_id, content) VALUES (?, ?, ?)");
                $stmt2->execute([$topicId, $userId, $content]);
                
                // Redirect to the new topic
                header("Location: forum-topic.php?id=$topicId");
                exit;
                
            } catch (PDOException $e) {
                $error = 'Erreur lors de la création du sujet';
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="container forum-create-container">
    <div class="forum-create-header">
        <h1>✏️ <?php echo t('forum.create_topic'); ?></h1>
        <p>Partagez vos questions, idées ou discussions avec la communauté</p>
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
    
    <form method="POST" class="forum-create-form">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        
        <div class="form-group">
            <label for="title">Titre du sujet *</label>
            <input 
                type="text" 
                id="title" 
                name="title" 
                required 
                maxlength="255"
                placeholder="Ex: Comment organiser une randonnée en groupe ?"
                value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
            >
            <small>Minimum 5 caractères</small>
        </div>
        
        <div class="form-group">
            <label for="category">Catégorie *</label>
            <select id="category" name="category" required>
                <option value="">-- Choisir une catégorie --</option>
                <?php foreach ($categories as $key => $label): ?>
                    <option value="<?php echo $key; ?>" <?php echo (isset($_POST['category']) && $_POST['category'] === $key) ? 'selected' : ''; ?>>
                        <?php echo $label; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="content">Contenu *</label>
            <textarea 
                id="content" 
                name="content" 
                rows="10" 
                required
                placeholder="Décrivez votre question ou discussion en détail..."
            ><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
            <small>Minimum 10 caractères</small>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                ✅ Publier le sujet
            </button>
            <a href="forum.php" class="btn btn-secondary">
                ❌ Annuler
            </a>
        </div>
    </form>
</div>

<style>
.forum-create-container {
    max-width: 800px;
    margin: 0 auto;
}

.forum-create-header {
    text-align: center;
    margin-bottom: 30px;
    padding: 30px 20px;
    background: linear-gradient(135deg, #55D5E0 0%, #335F8A 100%);
    color: white;
    border-radius: 12px;
}

.forum-create-header h1 {
    margin: 0 0 10px 0;
    font-size: 2em;
}

.forum-create-header p {
    margin: 0;
    opacity: 0.9;
}

.forum-create-form {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e1e8ed;
    border-radius: 8px;
    font-size: 1em;
    font-family: inherit;
    transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #55D5E0;
}

.form-group small {
    display: block;
    margin-top: 5px;
    color: #999;
    font-size: 0.85em;
}

.form-group textarea {
    resize: vertical;
    min-height: 200px;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
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

<?php include '../includes/footer.php'; ?>
