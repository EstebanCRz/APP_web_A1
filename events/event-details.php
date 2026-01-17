<?php
declare(strict_types=1);

// 1. CONFIGURATION SESSION & ERREURS
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_secure', '0'); 
session_start();

header('Content-Type: text/html; charset=UTF-8');

require_once '../includes/language.php';
require_once '../includes/activities_functions.php';

// 2. RÉCUPÉRATION DE L'ID
$event_id = (int) ($_GET['id'] ?? 0);
if ($event_id === 0) {
    header('Location: events-list.php');
    exit;
}

$event = getActivityById($event_id);
if (!$event) {
    header('Location: events-list.php');
    exit;
}

// 3. TRAITEMENT DU FORMULAIRE D'AVIS (POST)
$reviewMessage = '';
$reviewError = '';
$userReview = null;
$isUserRegistered = false;
$isFavorite = false;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $isUserRegistered = isUserRegistered($event_id, $user_id);
    
    $pdo = getDB();
    // Vérifier favoris
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_favorites WHERE user_id = ? AND activity_id = ?");
    $stmt->execute([$user_id, $event_id]);
    $isFavorite = $stmt->fetchColumn() > 0;

    // Vérifier si avis existant
    $stmt = $pdo->prepare("SELECT * FROM activity_reviews WHERE activity_id = ? AND user_id = ?");
    $stmt->execute([$event_id, $user_id]);
    $userReview = $stmt->fetch();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
        if ($isUserRegistered && strtotime($event['event_date']) < time()) {
            $rating = (int)($_POST['rating'] ?? 0);
            $comment = trim($_POST['comment'] ?? '');

            if ($rating < 1 || $rating > 5) {
                $reviewError = 'Veuillez sélectionner une note.';
            } elseif (strlen($comment) < 10) {
                $reviewError = '10 caractères minimum requis.';
            } elseif (preg_match('/[^\p{L}0-9 .,;:!?()\[\]{}"\'\-\n\r]/u', $comment)) {
                $reviewError = 'Caractères spéciaux interdits.';
            } else {
                if ($userReview) {
                    $stmt = $pdo->prepare("UPDATE activity_reviews SET rating = ?, comment = ?, updated_at = NOW() WHERE activity_id = ? AND user_id = ?");
                    $stmt->execute([$rating, $comment, $event_id, $user_id]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO activity_reviews (activity_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$event_id, $user_id, $rating, $comment]);
                }
                header('Location: event-details.php?id=' . $event_id . '&review=success');
                exit;
            }
        }
    }
}

if (isset($_GET['review']) && $_GET['review'] === 'success') {
    $reviewMessage = 'Votre avis a été enregistré !';
}

// 4. RÉCUPÉRATION DES LISTES (Participants, Avis, Autres)
$participants = getActivityParticipants($event_id);
$otherActivities = getAllActivities(['category' => $event['category_name'], 'limit' => 3]);

$reviews = [];
$averageRating = 0;
$pdo = getDB();
$stmt = $pdo->prepare("SELECT r.*, u.first_name, u.last_name FROM activity_reviews r JOIN users u ON r.user_id = u.id WHERE r.activity_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$event_id]);
$reviews = $stmt->fetchAll();

if (count($reviews) > 0) {
    $averageRating = round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1);
}

// 5. CONFIGURATION PAGE & HEADER
$pageTitle = htmlspecialchars($event['title']) . " - AmiGo";
$pageDescription = t('event_details.title');
$assetsDepth = 1;
$customCSS = ["../assets/css/style.css", 'css/event-details.css', '../assets/css/message-images.css'];
include '../includes/header.php';
?>

<div class="container">
    <div class="event-details-wrapper">
        <main class="event-main">
            <section class="event-info card">
                <div class="event-title-row">
                    <h1><?= htmlspecialchars($event['title']) ?></h1>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button class="favorite-btn-large <?= $isFavorite ? 'active' : '' ?>" data-activity-id="<?= $event_id ?>">
                            <span class="heart-icon">❤️</span>
                        </button>
                    <?php endif; ?>
                </div>
                <p>📍 <?= htmlspecialchars($event['location']) ?> | 📅 <?= formatEventDate($event['event_date']) ?></p>
                <div class="event-description"><?= nl2br(htmlspecialchars($event['description'])) ?></div>
            </section>

            <section class="event-discussion card" id="activity-chat" data-activity-id="<?= $event_id ?>">
                <h2 class="discussion-title">💬 Discussion</h2>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <p><a href="../auth/login.php">Connectez-vous</a> pour discuter.</p>
                <?php else: ?>
                    <div class="discussion-messages" id="chat-messages">
                        <div class="loading-messages"><?= t('event_details.loading_messages') ?></div>
                    </div>
                    <form class="discussion-form" id="chat-form" onsubmit="return false;">
                        <input type="text" id="chat-input" class="message-input" placeholder="Votre message...">
                        <button type="submit" class="btn btn-primary"><?= t('event_details.send_button') ?></button>
                    </form>
                <?php endif; ?>
            </section>

            <?php if (strtotime($event['event_date']) < time()): ?>
            <section class="event-reviews card">
                <h2>⭐ Avis (<?= $averageRating ?>/5)</h2>
                <?php if ($reviewMessage): ?><div class="alert alert-success"><?= $reviewMessage ?></div><?php endif; ?>
                <?php if ($reviewError): ?><div class="alert alert-error"><?= $reviewError ?></div><?php endif; ?>

                <?php if ($isUserRegistered): ?>
                    <form method="POST" class="review-form">
                        <div class="star-rating-inline">
                            <?php for($i=5; $i>=1; $i--): ?>
                                <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" <?= ($userReview && $userReview['rating'] == $i) ? 'checked' : '' ?> required>
                                <label for="star<?= $i ?>">★</label>
                            <?php endfor; ?>
                        </div>
                        <textarea id="comment" name="comment" required minlength="10"><?= $userReview ? htmlspecialchars($userReview['comment']) : '' ?></textarea>
                        <small class="review-counter" style="display:block;margin-top:0.5rem;"></small>
                        <div class="review-error" style="display:none;"></div>
                        <button type="submit" name="submit_review" class="btn btn-primary" disabled>Publier l'avis</button>
                    </form>
                <?php endif; ?>

                <div class="reviews-list">
                    <?php foreach ($reviews as $rev): ?>
                        <div class="review-item" style="border-left:4px solid #f5f5f5; background:#fff; border-radius:10px; margin-bottom:1.2rem; padding:1.2rem 1.5rem; box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                            <div style="display:flex; align-items:center; gap:1rem; margin-bottom:0.5rem;">
                                <!-- Avatar supprimé -->
                                <div>
                                    <span style="font-weight:700; font-size:1.08rem; color:#222;">
                                        <?= htmlspecialchars($rev['first_name'] . ' ' . $rev['last_name']) ?>
                                    </span>
                                    <span style="color:#888; font-size:0.98rem; margin-left:0.5rem;">(<?= $rev['rating'] ?>/5)
                                        <?php for($i=1;$i<=5;$i++): ?>
                                            <span style="color:<?= $i<=$rev['rating'] ? '#deb514':'#ccc' ?>; font-size:1.1em;">★</span>
                                        <?php endfor; ?>
                                    </span>
                                    <span style="color:#aaa; font-size:0.92rem; margin-left:0.7rem;">
                                        <?= date('d/m/Y', strtotime($rev['created_at'] ?? 'now')) ?>
                                    </span>
                                </div>
                            </div>
                            <div style="font-size:1.05rem; color:#222; margin-left:2.5rem; white-space:pre-line;">
                                <?= nl2br(htmlspecialchars($rev['comment'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
        </main>

        <aside class="event-sidebar">
            <div class="card">
                <h3>Participants (<?= count($participants) ?>/<?= $event['max_participants'] ?>)</h3>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <button class="btn <?= $isUserRegistered ? 'btn-danger' : 'btn-primary' ?> btn-block">
                        <?= $isUserRegistered ? 'Se désinscrire' : 'S\'inscrire' ?>
                    </button>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</div>

<script src="../assets/js/activity-registration.js"></script>
<script src="../assets/js/activity-chat.js"></script>
<script src="../assets/js/review-form-validation.js"></script>
<script>
// Gestion du bouton favoris avec message inline
(function() {
  const favoriteBtn = document.querySelector('.favorite-btn-large');
  if (favoriteBtn) {
    let favMsg = document.querySelector('.favorite-message');
    if (!favMsg) {
      favMsg = document.createElement('div');
      favMsg.className = 'favorite-message';
      favMsg.style.display = 'none';
      document.body.appendChild(favMsg);
    }
    function showFavMsg(txt) {
      favMsg.textContent = txt;
      favMsg.style.display = 'block';
      setTimeout(() => { favMsg.style.display = 'none'; }, 2200);
    }
    favoriteBtn.addEventListener('click', function() {
      const activityId = this.dataset.activityId;
      const isActive = this.classList.contains('active');
      const action = isActive ? 'remove' : 'add';
      fetch('api/favorite-toggle.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `activity_id=${activityId}&action=${action}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          this.classList.toggle('active');
          this.title = this.classList.contains('active') ? 'Retirer des favoris' : 'Ajouter aux favoris';
          const heartIcon = this.querySelector('.heart-icon');
          if (this.classList.contains('active')) {
            if (heartIcon) {
              heartIcon.style.animation = 'heartBeat 0.3s ease';
              setTimeout(() => { heartIcon.style.animation = ''; }, 300);
            }
            showFavMsg('❤️ Ajouté aux favoris');
          } else {
            showFavMsg('Retiré des favoris');
          }
        } else {
          showFavMsg('Erreur: ' + data.message);
        }
      })
      .catch(() => showFavMsg('Erreur de connexion.'));
    });
  }
})();
</script>
<?php include '../includes/footer.php'; ?>