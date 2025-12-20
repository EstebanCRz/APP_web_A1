<?php
declare(strict_types=1);

// Configuration de session (AVANT session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once '../includes/language.php';
require_once '../includes/activities_functions.php';

$pageTitle = t('event_details.title') . " - AmiGo";
$pageDescription = t('event_details.title');
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/event-details.css"
];

// Récupérer l'ID de l'activité
$event_id = (int) ($_GET['id'] ?? 0);

if ($event_id === 0) {
    header('Location: events-list.php');
    exit;
}

// Récupérer les détails de l'activité depuis la DB
$event = getActivityById($event_id);

if (!$event) {
    header('Location: events-list.php');
    exit;
}

// Récupérer la liste des participants
$participants = getActivityParticipants($event_id);

// Vérifier si l'utilisateur est inscrit
$isUserRegistered = false;
$isFavorite = false;
$userReview = null;

if (isset($_SESSION['user_id'])) {
    $isUserRegistered = isUserRegistered($event_id, $_SESSION['user_id']);
    
    // Obtenir la connexion PDO une seule fois
    try {
        $pdo = getDB();
        
        // Vérifier si l'activité est dans les favoris
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_favorites WHERE user_id = ? AND activity_id = ?");
        $stmt->execute([$_SESSION['user_id'], $event_id]);
        $isFavorite = $stmt->fetchColumn() > 0;
        
        // Vérifier si l'utilisateur a déjà un avis
        $stmt = $pdo->prepare("SELECT * FROM activity_reviews WHERE activity_id = ? AND user_id = ?");
        $stmt->execute([$event_id, $_SESSION['user_id']]);
        $userReview = $stmt->fetch();
        
    } catch (PDOException $e) {
        // Silently fail
    }
}

// Récupérer d'autres activités de la même catégorie (pour la sidebar)
$otherActivities = getAllActivities(['category' => $event['category_name'], 'limit' => 3]);

// Traiter la soumission d'avis
$reviewMessage = '';
$reviewError = '';

if (isset($_SESSION['user_id'])) {
    // Traiter le formulaire de soumission d'avis
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
        if ($isUserRegistered && strtotime($event['event_date']) < time()) {
            $rating = (int)($_POST['rating'] ?? 0);
            $comment = trim($_POST['comment'] ?? '');
            
            if ($rating < 1 || $rating > 5) {
                $reviewError = 'Veuillez sélectionner une note entre 1 et 5 étoiles';
            } elseif (empty($comment) || strlen($comment) < 10) {
                $reviewError = 'Votre commentaire doit contenir au moins 10 caractères';
            } else {
                try {
                    $pdo = getDB();
                    if ($userReview) {
                        // Mise à jour
                        $stmt = $pdo->prepare("UPDATE activity_reviews SET rating = ?, comment = ?, updated_at = NOW() WHERE activity_id = ? AND user_id = ?");
                        $stmt->execute([$rating, $comment, $event_id, $_SESSION['user_id']]);
                        $reviewMessage = 'Votre avis a été mis à jour avec succès !';
                    } else {
                        // Création
                        $stmt = $pdo->prepare("INSERT INTO activity_reviews (activity_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$event_id, $_SESSION['user_id'], $rating, $comment]);
                        $reviewMessage = 'Merci pour votre retour d\'expérience !';
                    }
                    
                    // Recharger l'avis de l'utilisateur
                    $stmt = $pdo->prepare("SELECT * FROM activity_reviews WHERE activity_id = ? AND user_id = ?");
                    $stmt->execute([$event_id, $_SESSION['user_id']]);
                    $userReview = $stmt->fetch();
                    
                    // Rediriger pour éviter la resoumission
                    header('Location: event-details.php?id=' . $event_id . '&review=success');
                    exit;
                } catch (PDOException $e) {
                    $reviewError = 'Erreur lors de l\'enregistrement de votre avis';
                }
            }
        }
    }
    
    // Afficher le message de succès après redirection
    if (isset($_GET['review']) && $_GET['review'] === 'success') {
        $reviewMessage = 'Votre avis a été enregistré avec succès !';
    }
}

// Récupérer les avis de l'activité
$reviews = [];
$averageRating = 0;
try {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT r.*, u.username, u.first_name, u.last_name 
        FROM activity_reviews r
        JOIN users u ON u.id = r.user_id
        WHERE r.activity_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$event_id]);
    $reviews = $stmt->fetchAll();
    
    // Calculer la moyenne des notes
    if (!empty($reviews)) {
        $totalRating = array_sum(array_column($reviews, 'rating'));
        $averageRating = round($totalRating / count($reviews), 1);
    }
} catch (PDOException $e) {
    // Silently fail
}

include '../includes/header.php';
?>

<div class="container">
    <div class="event-details-wrapper">
        <!-- Main Content -->
        <div class="event-main">
            <!-- Event Info -->
            <div class="event-info">
                <div class="event-title-row">
                    <h1 class="event-title"><?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button 
                            class="favorite-btn-large <?php echo $isFavorite ? 'active' : ''; ?>" 
                            data-activity-id="<?php echo $event_id; ?>"
                            title="<?php echo $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?>"
                        >
                            <span class="heart-icon">❤️</span>
                        </button>
                    <?php endif; ?>
                </div>
                <p class="event-meta">
                    <span class="badge" style="background-color: <?php echo htmlspecialchars($event['category_color'] ?? '#3498db', ENT_QUOTES, 'UTF-8'); ?>;">
                        <?php echo htmlspecialchars($event['category_icon'] ?? '📌', ENT_NOQUOTES, 'UTF-8'); ?>
                        <?php echo htmlspecialchars($event['category_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <span class="meta-text">📍 <?php echo htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="meta-text">📅 <?php echo formatEventDate($event['event_date']); ?> <?php echo formatEventTime($event['event_date']); ?></span>
                    <span class="meta-text">👤 Hôte: <?php echo htmlspecialchars($event['creator_first_name'] . ' ' . $event['creator_last_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                </p>
                <p class="event-description"><?php echo nl2br(htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8')); ?></p>
            </div>

            <!-- Discussion Section -->
            <div class="event-discussion card" id="activity-chat" data-activity-id="<?php echo $event_id; ?>" data-user-id="<?php echo $_SESSION['user_id'] ?? 0; ?>">
                <h2 class="discussion-title">💬 <?php echo t('event_details.discussion'); ?></h2>
                
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="chat-login-notice">
                        <p><?php echo getCurrentLanguage() === 'fr' ? 'Vous devez être connecté pour participer à la discussion.' : 'You must be logged in to participate in the discussion.'; ?></p>
                        <a href="../auth/login.php" class="btn btn-primary"><?php echo t('header.login'); ?></a>
                    </div>
                <?php else: ?>
                    <div class="discussion-messages" id="chat-messages">
                        <div class="loading-messages"><?php echo t('event_details.loading_messages'); ?></div>
                    </div>
                    
                    <form class="discussion-form" id="chat-form" onsubmit="return false;">
                        <input type="text" id="chat-input" placeholder="<?php echo t('event_details.type_message'); ?>" class="message-input" required autocomplete="off">
                        <button type="submit" class="btn btn-primary"><?php echo t('event_details.send_button'); ?></button>
                    </form>
                <?php endif; ?>
            </div>
            <!-- Reviews Section -->
            <?php if (!empty($reviews) || (isset($_SESSION['user_id']) && $isUserRegistered && strtotime($event['event_date']) < time())): ?>
            <div class="event-reviews card">
                <h2 class="reviews-title">
                    ⭐ Avis des participants
                    <?php if (!empty($reviews)): ?>
                        <span class="average-rating">
                            <?php echo $averageRating; ?>/5 
                            <span class="rating-stars">
                                <?php 
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= round($averageRating) ? '★' : '☆';
                                }
                                ?>
                            </span>
                            <span class="review-count">(<?php echo count($reviews); ?> avis)</span>
                        </span>
                    <?php endif; ?>
                </h2>

                <?php if ($reviewMessage): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($reviewMessage); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($reviewError): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($reviewError); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id']) && $isUserRegistered && strtotime($event['event_date']) < time()): ?>
                    <div class="add-review-form-container">
                        <button id="toggleReviewForm" class="btn btn-primary" onclick="document.getElementById('reviewFormContent').classList.toggle('hidden')">
                            <?php echo $userReview ? '✏️ Modifier mon avis' : '✍️ Laisser un avis'; ?>
                        </button>
                        
                        <div id="reviewFormContent" class="review-form-inline hidden">
                            <form method="POST" class="review-form-compact">
                                <div class="form-group">
                                    <label>Note *</label>
                                    <div class="star-rating-inline">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input 
                                                type="radio" 
                                                id="star<?php echo $i; ?>" 
                                                name="rating" 
                                                value="<?php echo $i; ?>"
                                                <?php echo ($userReview && $userReview['rating'] == $i) ? 'checked' : ''; ?>
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
                                        rows="4" 
                                        required
                                        placeholder="Partagez votre expérience..."
                                    ><?php echo $userReview ? htmlspecialchars($userReview['comment']) : ''; ?></textarea>
                                    <small>Minimum 10 caractères</small>
                                </div>
                                
                                <div class="form-actions-inline">
                                    <button type="submit" name="submit_review" class="btn btn-primary">
                                        <?php echo $userReview ? '✅ Mettre à jour' : '✅ Publier'; ?>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('reviewFormContent').classList.add('hidden')">
                                        Annuler
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (empty($reviews)): ?>
                    <p class="no-reviews">Aucun avis pour le moment. Soyez le premier à partager votre expérience !</p>
                <?php else: ?>
                    <div class="reviews-list">
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="review-author">
                                        <div class="author-avatar">
                                            <?php echo strtoupper(substr($review['first_name'], 0, 1) . substr($review['last_name'], 0, 1)); ?>
                                        </div>
                                        <div class="author-info">
                                            <div class="author-name">
                                                <?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?>
                                            </div>
                                            <div class="review-date">
                                                <?php echo date('d/m/Y', strtotime($review['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="review-rating">
                                        <?php 
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo $i <= $review['rating'] ? '<span class="star filled">★</span>' : '<span class="star">☆</span>';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="review-comment">
                                    <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <!-- Participants List -->
            <div class="event-participants card">
                <h2 class="participants-title">
                    👥 <?php echo t('event_details.registered_participants'); ?> (<?php echo count($participants); ?>/<?php echo htmlspecialchars($event['max_participants'], ENT_QUOTES, 'UTF-8'); ?>)
                </h2>
                <?php if (empty($participants)): ?>
                    <p class="no-participants"><?php echo getCurrentLanguage() === 'fr' ? 'Aucun participant inscrit pour le moment. Soyez le premier !' : 'No participants registered yet. Be the first!'; ?></p>
                <?php else: ?>
                    <div class="participants-list">
                        <?php foreach ($participants as $participant): ?>
                            <div class="participant-item">
                                <div class="participant-avatar">
                                    <?php if (!empty($participant['avatar'])): ?>
                                        <img src="<?php echo htmlspecialchars($participant['avatar'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($participant['first_name'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php else: ?>
                                        <div class="avatar-placeholder">
                                            <?php echo strtoupper(substr($participant['first_name'], 0, 1) . substr($participant['last_name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="participant-info">
                                    <div class="participant-name"><?php echo htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="participant-username">@<?php echo htmlspecialchars($participant['username'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                                <div class="participant-date">
                                    Inscrit le <?php echo date('d/m/Y', strtotime($participant['registered_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <aside class="event-sidebar">
            <!-- Participate Card -->
            <div class="participate-card card">
                <h3 class="sidebar-title"><?php echo getCurrentLanguage() === 'fr' ? 'Participer' : 'Participate'; ?></h3>
                <div class="capacity-info">
                    <p class="capacity-text">
                        <?php echo getCurrentLanguage() === 'fr' ? 'Capacité' : 'Capacity'; ?>: <strong><span id="participant-count"><?php echo count($participants); ?></span>/<?php echo htmlspecialchars($event['max_participants'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    </p>
                </div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($isUserRegistered): ?>
                        <button class="btn btn-danger btn-block btn-unsubscribe" data-activity-id="<?php echo $event_id; ?>">
                            ✓ <?php echo t('event_details.unregister_button'); ?>
                        </button>
                        <?php
                        // Vérifier si l'activité est passée pour afficher le bouton d'avis
                        $eventDate = strtotime($event['event_date']);
                        $now = time();
                        if ($eventDate < $now): ?>
                            <a href="activity-review.php?id=<?php echo $event_id; ?>" class="btn btn-secondary btn-block" style="margin-top: 10px;">
                                ⭐ Laisser un avis
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="btn btn-primary btn-block btn-subscribe" data-activity-id="<?php echo $event_id; ?>">
                            <?php echo t('event_details.register_button'); ?>
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="../auth/login.php" class="btn btn-primary btn-block"><?php echo t('event_details.login_to_register'); ?></a>
                <?php endif; ?>
            </div>

            <!-- Other Activities Card -->
            <div class="other-activities card">
                <h3 class="sidebar-title"><?php echo t('event_details.other_activities'); ?></h3>
                <div class="activities-list">
                    <?php foreach ($otherActivities as $other): ?>
                        <div class="activity-mini">
                            <span class="activity-mini-badge" style="background-color: <?php echo htmlspecialchars($other['category_color'] ?? '#3498db', ENT_QUOTES, 'UTF-8'); ?>;">
                                <?php echo htmlspecialchars($other['category_icon'] ?? '📌', ENT_NOQUOTES, 'UTF-8'); ?>
                                <?php echo htmlspecialchars($other['category_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <h4 class="activity-mini-title"><?php echo htmlspecialchars($other['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p class="activity-mini-excerpt"><?php echo htmlspecialchars(substr($other['description'], 0, 60) . '...', ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="activity-mini-meta">
                                📍 <?php echo htmlspecialchars($other['location'], ENT_QUOTES, 'UTF-8'); ?><br>
                                📅 <?php echo formatEventDate($other['date']); ?><br>
                                👤 <?php echo htmlspecialchars($other['creator_first_name'] . ' ' . $other['creator_last_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                            <div class="activity-mini-footer">
                                <span class="places"><?php echo htmlspecialchars($other['current_participants'], ENT_QUOTES, 'UTF-8'); ?>/<?php echo htmlspecialchars($other['max_participants'], ENT_QUOTES, 'UTF-8'); ?> inscrits</span>
                                <a href="event-details.php?id=<?php echo htmlspecialchars($other['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn-mini">Voir</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>
    </div>
</div>

<script src="../assets/js/activity-registration.js"></script>
<script src="../assets/js/activity-chat.js"></script>

<script>
// Fonction pour afficher des notifications
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: ${type === 'success' ? '#4caf50' : '#2196F3'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 9999;
        animation: slideInRight 0.3s ease, slideOutRight 0.3s ease 2.7s;
        font-weight: 500;
    `;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

// Ajouter les animations CSS si elles n'existent pas
if (!document.querySelector('#notification-styles')) {
    const style = document.createElement('style');
    style.id = 'notification-styles';
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}

// Gestion du bouton favoris
document.addEventListener('DOMContentLoaded', function() {
    const favoriteBtn = document.querySelector('.favorite-btn-large');
    
    console.log('Bouton favori trouvé:', favoriteBtn);
    
    if (favoriteBtn) {
        console.log('État initial du bouton:', favoriteBtn.classList.contains('active') ? 'actif' : 'inactif');
        
        favoriteBtn.addEventListener('click', function() {
            const activityId = this.dataset.activityId;
            const isActive = this.classList.contains('active');
            const action = isActive ? 'remove' : 'add';
            
            console.log('Clic sur le bouton favori - Activity ID:', activityId, 'Action:', action);
            
            // Demander confirmation pour la suppression
            if (isActive && !confirm('Retirer cette activité de vos favoris ?')) {
                console.log('Suppression annulée par l\'utilisateur');
                return;
            }
            
            const apiUrl = 'api/favorite-toggle.php';
            console.log('Appel API:', apiUrl);
            
            fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `activity_id=${activityId}&action=${action}`
            })
            .then(response => {
                console.log('Réponse HTTP:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Données reçues:', data);
                
                if (data.success) {
                    this.classList.toggle('active');
                    this.title = this.classList.contains('active') ? 'Retirer des favoris' : 'Ajouter aux favoris';
                    
                    console.log('Nouveau statut:', this.classList.contains('active') ? 'actif' : 'inactif');
                    
                    // Animation
                    const heartIcon = this.querySelector('.heart-icon');
                    if (this.classList.contains('active')) {
                        heartIcon.style.animation = 'heartBeat 0.3s ease';
                        setTimeout(() => {
                            heartIcon.style.animation = '';
                        }, 300);
                        // Message de succès pour l'ajout
                        showNotification('❤️ Ajouté aux favoris', 'success');
                    } else {
                        // Message pour la suppression
                        showNotification('Retiré des favoris', 'info');
                    }
                } else {
                    console.error('Erreur API:', data.message);
                    alert('Erreur: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur complète:', error);
                alert('Erreur de connexion. Vérifiez la console pour plus de détails.');
            });
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>
