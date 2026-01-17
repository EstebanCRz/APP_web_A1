<?php
// Affiche l'id reçu pour diagnostic
echo 'ID=' . htmlspecialchars($_GET['id'] ?? '') . ' '; die('TEST-ID');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

declare(strict_types=1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // À mettre sur 1 en HTTPS
session_start();

// 2. INCLUDES
require_once '../includes/language.php';
require_once '../includes/activities_functions.php';

// 3. TRAITEMENT DU FORMULAIRE D'AVIS (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'], $_POST['comment'])) {
    $activity_id = (int)$_POST['activity_id'];
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    $user_id = $_SESSION['user_id'] ?? null;
    $hasError = false;

    if (!$user_id) {
        $_SESSION['review_message'] = "Erreur : vous devez être connecté.";
        $hasError = true;
    }
    // Validation simple
    if (preg_match('/[^a-zA-Z0-9 .,;:!?()\[\]{}"\'\-\n\r]/u', $comment)) {
        $_SESSION['review_message'] = "Erreur : caractères spéciaux interdits.";
        $hasError = true;
    }
    if (strlen($comment) < 10) {
        $_SESSION['review_message'] = "Erreur : 10 caractères minimum.";
        $hasError = true;
    }
    if ($rating < 1 || $rating > 5) {
        $_SESSION['review_message'] = "Erreur : note invalide.";
        $hasError = true;
    }

    if (!$hasError) {
        $pdo = getDB();
        $sql = "INSERT INTO activity_reviews (rating, comment, activity_id, user_id) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE rating=?, comment=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$rating, $comment, $activity_id, $user_id, $rating, $comment]);
        $_SESSION['review_message'] = "Avis enregistré !";
    }
    header("Location: event-details.php?id=$activity_id");
    exit;
}

// 4. RÉCUPÉRATION DES DONNÉES
$event_id = (int)($_GET['id'] ?? 0);
    die('OK-AVANT');
die('OK0');
$event = getActivityById($event_id);
        die('OK1');

if (!$event) {
    header('Location: events-list.php');
    exit;
}

// Vérifications utilisateur
$isUserRegistered = false;
$isFavorite = false;
if (isset($_SESSION['user_id'])) {
    $isUserRegistered = isUserRegistered($event_id, $_SESSION['user_id']);
    // On suppose que cette fonction existe dans vos includes
    // if (function_exists('isActivityFavorite')) {
    //     $isFavorite = isActivityFavorite($event_id, $_SESSION['user_id']);
    // }
}

$participants = getActivityParticipants($event_id);
$otherActivities = getAllActivities(['category' => $event['category_name'], 'limit' => 3]);

// Gestion des avis (uniquement si l'événement est passé)
$reviews = [];
$averageRating = 0;
$isEventPassed = strtotime($event['event_date']) < time();

if ($isEventPassed) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT r.*, CONCAT(u.first_name, ' ', u.last_name) AS user_name 
                           FROM activity_reviews r 
                           JOIN users u ON r.user_id = u.id 
                           WHERE r.activity_id = ? ORDER BY r.created_at DESC");
    $stmt->execute([$event_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($reviews)) {
        $averageRating = round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1);
    }
}

// 5. CONFIGURATION HEADER
$pageTitle = htmlspecialchars($event['title']) . " - AmiGo";
$pageDescription = t('event_details.title');
$assetsDepth = 1;
$customCSS = ["../assets/css/style.css", "css/event-details.css", "../assets/css/message-images.css"];
include '../includes/header.php';
?>

<div class="container">
    <div class="event-details-wrapper">
        
        <main class="event-main">
            
            <section class="event-info card">
                <div class="event-title-row" style="display:flex; justify-content: space-between; align-items: center;">
                    <h1 class="event-title"><?= htmlspecialchars($event['title']) ?></h1>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button class="favorite-btn-large <?= $isFavorite ? 'active' : '' ?>" 
                                data-activity-id="<?= $event_id ?>"
                                title="<?= $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>">
                            <span class="heart-icon">❤️</span>
                        </button>
                    <?php endif; ?>
                </div>
                
                <div class="event-meta">
                    <span class="badge" style="background-color: <?= htmlspecialchars($event['category_color'] ?? '#3498db') ?>;">
                        <?= htmlspecialchars($event['category_icon'] ?? '📌') ?> <?= htmlspecialchars($event['category_name']) ?>
                    </span>
                    <span class="meta-text">📍 <?= htmlspecialchars($event['location']) ?></span>
                    <span class="meta-text">📅 <?= formatEventDate($event['event_date']) ?> à <?= formatEventTime($event['event_date']) ?></span>
                    <span class="meta-text">👤 Hôte: <?= htmlspecialchars($event['creator_first_name'] . ' ' . $event['creator_last_name']) ?></span>
                </div>
                
                <div class="event-description">
                    <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                </div>
            </section>

            <section class="event-discussion card" id="activity-chat" data-activity-id="<?= $event_id ?>" data-user-id="<?= $_SESSION['user_id'] ?? 0 ?>">
                <h2 class="discussion-title">💬 <?= t('event_details.discussion') ?></h2>
                
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="chat-login-notice">
                        <p>Vous devez être connecté pour participer à la discussion.</p>
                        <a href="../auth/login.php" class="btn btn-primary"><?= t('header.login') ?></a>
                    </div>
                <?php else: ?>
                    <div class="discussion-messages" id="chat-messages" style="height: 300px; overflow-y: auto;">
                        <div class="loading-messages"><?= t('event_details.loading_messages') ?></div>
                    </div>
                    
                    <form class="discussion-form" id="chat-form">
                        <input type="file" id="chat-image-input" accept="image/*" style="display:none">
                        <div id="chat-image-preview" style="display:none;">
                            <img id="chat-preview-img" src="" style="max-width:80px; border-radius:8px;">
                            <button type="button" class="btn-remove-img">×</button>
                        </div>
                        <div class="input-group" style="display:flex; gap:10px; margin-top:10px;">
                            <button type="button" class="btn-attach" onclick="document.getElementById('chat-image-input').click()">📎</button>
                            <input type="text" id="chat-input" placeholder="<?= t('event_details.type_message') ?>" class="message-input" style="flex:1;">
                            <button type="submit" class="btn btn-primary"><?= t('event_details.send_button') ?></button>
                        </div>
                    </form>
                <?php endif; ?>
            </section>

            <?php if ($isEventPassed): ?>
                <section class="event-reviews card">
                    <h2>⭐ Avis des membres</h2>
                    
                    <?php if (isset($_SESSION['review_message'])): ?>
                        <div class="alert <?= strpos($_SESSION['review_message'], 'Erreur') === false ? 'alert-success' : 'alert-danger' ?>">
                            <?= htmlspecialchars($_SESSION['review_message']); unset($_SESSION['review_message']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="review-layout" style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-top: 20px;">
                        <div class="review-form-side">
                            <?php if (isset($_SESSION['user_id']) && $isUserRegistered): ?>
                                <form method="POST" class="review-form">
                                    <input type="hidden" name="activity_id" value="<?= $event_id ?>">
                                    <label>Votre note :</label>
                                    <div class="star-rating">
                                        <?php for($i=5; $i>=1; $i--): ?>
                                            <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" required>
                                            <label for="star<?= $i ?>">★</label>
                                        <?php endfor; ?>
                                    </div>
                                    <textarea name="comment" rows="3" placeholder="Votre commentaire (min 10 car.)..." required></textarea>
                                    <button type="submit" class="btn btn-primary" style="width:100%; margin-top:10px;">Publier</button>
                                </form>
                            <?php else: ?>
                                <p><small>Inscrivez-vous et participez pour laisser un avis !</small></p>
                            <?php endif; ?>
                        </div>

                        <div class="review-list-side">
                            <?php if (!empty($reviews)): ?>
                                <div class="reviews-list">
                                    <?php foreach ($reviews as $r): ?>
                                        <div class="review-item" style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px;">
                                            <strong><?= htmlspecialchars($r['user_name']) ?></strong> 
                                            <span class="stars" style="color: #f1c40f;"><?= str_repeat('★', $r['rating']) ?></span>
                                            <p style="margin: 5px 0;"><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
                                            <small class="text-muted"><?= date('d/m/Y', strtotime($r['created_at'])) ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="no-reviews">Aucun avis pour le moment.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
        </main>

        <aside class="event-sidebar">
            <div class="participate-card card">
                <h3>Participer</h3>
                <p>Capacité: <strong><?= count($participants) ?> / <?= $event['max_participants'] ?></strong></p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <button class="btn <?= $isUserRegistered ? 'btn-danger' : 'btn-primary' ?> btn-block" 
                            data-activity-id="<?= $event_id ?>" 
                            data-action="<?= $isUserRegistered ? 'unsubscribe' : 'subscribe' ?>">
                        <?= $isUserRegistered ? '✓ Annuler l\'inscription' : 'S\'inscrire maintenant' ?>
                    </button>
                <?php else: ?>
                    <a href="../auth/login.php" class="btn btn-primary btn-block"><?= t('event_details.login_to_register') ?></a>
                <?php endif; ?>
            </div>

            <div class="participants-list card">
                <h3>Participants (<?= count($participants) ?>)</h3>
                <div class="participants-grid" style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <?php foreach ($participants as $p): ?>
                        <div class="participant-avatar" title="<?= htmlspecialchars($p['first_name']) ?>">
                            <?php if (!empty($p['avatar'])): ?>
                                <img src="<?= htmlspecialchars($p['avatar']) ?>" alt="Avatar" style="width:40px; height:40px; border-radius:50%; object-fit: cover;">
                            <?php else: ?>
                                <div style="width:40px; height:40px; border-radius:50%; background:#eee; display:flex; align-items:center; justify-content:center; font-size:12px;">
                                    <?= strtoupper(substr($p['first_name'], 0, 1) . substr($p['last_name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>

    </div>
</div>

<script src="../assets/js/activity-registration.js"></script>
<script src="../assets/js/activity-chat.js"></script>
<script src="../assets/js/reviews.js"></script>

<script>
// Notification UI
function showNotification(m,t='success'){
    const n=document.createElement('div');
    n.style.cssText=`position:fixed;top:80px;right:20px;background:${t==='success'?'#4caf50':'#2196F3'};color:white;padding:1rem;border-radius:8px;z-index:9999;`;
    n.textContent=m;
    document.body.appendChild(n);
    setTimeout(()=>n.remove(),3000);
}

// Gestion des favoris
document.addEventListener('DOMContentLoaded', function() {
    const favBtn = document.querySelector('.favorite-btn-large');
    if(favBtn) {
        favBtn.addEventListener('click', function() {
            const id = this.dataset.activityId;
            const action = this.classList.contains('active') ? 'remove' : 'add';
            
            fetch('api/favorite-toggle.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `activity_id=${id}&action=${action}`
            })
            .then(r => r.json())
            .then(d => {
                if(d.success) {
                    this.classList.toggle('active');
                    showNotification(action === 'add' ? '❤️ Ajouté' : 'Retiré', 'success');
                }
            });
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>