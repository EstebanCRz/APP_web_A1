<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once '../includes/config.php';
require_once '../includes/language.php';
require_once '../includes/security.php';
require_once '../includes/gamification.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$activityId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userId = (int)$_SESSION['user_id'];
$pdo = getDB();
$security = new Security($pdo);
$activity = null;
$message = '';
$error = '';
$existingReview = null;

if ($activityId <= 0) {
    header('Location: ../events/events-list.php');
    exit;
}

try {
    // Vérifier que l'activité existe et est passée
    $stmt = $pdo->prepare("
        SELECT a.*, 
               (SELECT COUNT(*) FROM activity_registrations WHERE activity_id = a.id AND user_id = ?) as user_registered
        FROM activities a 
        WHERE a.id = ?
    ");
    $stmt->execute([$userId, $activityId]);
    $activity = $stmt->fetch();
    
    if (!$activity) {
        header('Location: ../events/events-list.php');
        exit;
    }
    
    // Vérifier si l'utilisateur était inscrit
    if ($activity['user_registered'] == 0) {
        $error = "Vous devez avoir participé à cette activité pour laisser un avis.";
    }
    
    // Vérifier si l'utilisateur a déjà laissé un avis
    $stmt2 = $pdo->prepare("SELECT * FROM activity_reviews WHERE activity_id = ? AND user_id = ?");
    $stmt2->execute([$activityId, $userId]);
    $existingReview = $stmt2->fetch();
    
    // Traiter le formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
        if (!$security->verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $error = 'Token de sécurité invalide';
        } else {
            $rating = (int)($_POST['rating'] ?? 0);
            $comment = $security->cleanInput($_POST['comment'] ?? '');
            
            if ($rating < 1 || $rating > 5) {
                $error = 'Veuillez sélectionner une note entre 1 et 5 étoiles';
            } elseif (empty($comment) || strlen($comment) < 10) {
                $error = 'Votre commentaire doit contenir au moins 10 caractères';
            } else {
                try {
                    if ($existingReview) {
                        // Mise à jour
                        $stmt = $pdo->prepare("UPDATE activity_reviews SET rating = ?, comment = ?, updated_at = NOW() WHERE activity_id = ? AND user_id = ?");
                        $stmt->execute([$rating, $comment, $activityId, $userId]);
                        $message = 'Votre avis a été mis à jour avec succès !';
                    } else {
                        // Création
                        $stmt = $pdo->prepare("INSERT INTO activity_reviews (activity_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$activityId, $userId, $rating, $comment]);
                        $message = 'Merci pour votre retour d\'expérience !';
                        
                        // Attribuer des points pour l'avis
                        addPoints($userId, 10, 'review_leave');
                        checkBadges($userId);
                    }
                    
                    // Recharger l'avis
                    $stmt2 = $pdo->prepare("SELECT * FROM activity_reviews WHERE activity_id = ? AND user_id = ?");
                    $stmt2->execute([$activityId, $userId]);
                    $existingReview = $stmt2->fetch();
                    
                } catch (PDOException $e) {
                    $error = 'Erreur lors de l\'enregistrement de votre avis';
                }
            }
        }
    }
    
} catch (PDOException $e) {
    $error = 'Erreur lors du chargement de l\'activité';
}

$csrfToken = $security->generateCSRFToken();

$pageTitle = "Retour d'expérience - " . ($activity ? htmlspecialchars($activity['title']) : 'Activité');
$pageDescription = "Partagez votre retour d'expérience sur cette activité";
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/profile.css"
];

include '../includes/header.php';
?>

<div class="container review-container">
    <div class="review-header">
        <a href="../events/event-details.php?id=<?php echo $activityId; ?>" class="back-link">← Retour à l'activité</a>
        <h1>⭐ Votre retour d'expérience</h1>
        <?php if ($activity): ?>
            <div class="activity-info">
                <h2><?php echo htmlspecialchars($activity['title']); ?></h2>
                <p><?php echo date('d/m/Y', strtotime($activity['event_date'])); ?> à <?php echo substr($activity['event_time'], 0, 5); ?></p>
            </div>
        <?php endif; ?>
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
    
    <?php if ($activity && $activity['user_registered'] > 0): ?>
        <form method="POST" class="review-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="form-group">
                <label>Note globale *</label>
                <div class="star-rating">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input 
                            type="radio" 
                            id="star<?php echo $i; ?>" 
                            name="rating" 
                            value="<?php echo $i; ?>"
                            <?php echo ($existingReview && $existingReview['rating'] == $i) ? 'checked' : ''; ?>
                            required
                        >
                        <label for="star<?php echo $i; ?>">★</label>
                    <?php endfor; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="comment">Votre commentaire *</label>
                <textarea 
                    id="comment" 
                    name="comment" 
                    rows="6" 
                    required
                    placeholder="Partagez votre expérience : ce que vous avez aimé, ce qui pourrait être amélioré..."
                ><?php echo $existingReview ? htmlspecialchars($existingReview['comment']) : ''; ?></textarea>
                <small>Minimum 10 caractères</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?php echo $existingReview ? '✏️ Mettre à jour mon avis' : '✅ Publier mon avis'; ?>
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<style>
.review-container {
    max-width: 700px;
    margin: 0 auto;
    padding: 20px;
}

.review-header {
    text-align: center;
    margin-bottom: 30px;
}

.back-link {
    display: inline-block;
    color: #55D5E0;
    text-decoration: none;
    margin-bottom: 20px;
    font-weight: 500;
}

.back-link:hover {
    text-decoration: underline;
}

.review-header h1 {
    color: #335F8A;
    margin-bottom: 15px;
}

.activity-info {
    background: #f7f9fc;
    padding: 20px;
    border-radius: 12px;
    margin-top: 20px;
}

.activity-info h2 {
    margin: 0 0 10px 0;
    color: #333;
    font-size: 1.3em;
}

.activity-info p {
    margin: 0;
    color: #666;
}

.review-form {
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
    margin-bottom: 10px;
    font-weight: 600;
    color: #333;
}

.star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: center;
    gap: 5px;
    font-size: 3em;
}

.star-rating input {
    display: none;
}

.star-rating label {
    cursor: pointer;
    color: #ddd;
    transition: color 0.2s ease;
    margin: 0;
    font-weight: normal;
}

.star-rating input:checked ~ label,
.star-rating label:hover,
.star-rating label:hover ~ label {
    color: #F6B12D;
}

textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e1e8ed;
    border-radius: 8px;
    font-size: 1em;
    font-family: inherit;
    resize: vertical;
}

textarea:focus {
    outline: none;
    border-color: #55D5E0;
}

.form-group small {
    display: block;
    margin-top: 5px;
    color: #999;
    font-size: 0.9em;
}

.form-actions {
    text-align: center;
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
