<?php
declare(strict_types=1);

// Configuration de session (AVANT session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once '../includes/activities_functions.php';

$pageTitle = "Détails de l'activité - AmiGo";
$pageDescription = "Découvrez tous les détails de cette activité";
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
if (isset($_SESSION['user_id'])) {
    $isUserRegistered = isUserRegistered($event_id, $_SESSION['user_id']);
}

// Récupérer d'autres activités de la même catégorie (pour la sidebar)
$otherActivities = getAllActivities(['category' => $event['category_name'], 'limit' => 3]);

include '../includes/header.php';
?>

<div class="container">
    <div class="event-details-wrapper">
        <!-- Main Content -->
        <div class="event-main">
            <!-- Event Info -->
            <div class="event-info">
                <h1 class="event-title"><?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
                <p class="event-meta">
                    <span class="badge" style="background-color: <?php echo htmlspecialchars($event['category_color'] ?? '#3498db', ENT_QUOTES, 'UTF-8'); ?>;">
                        <?php echo htmlspecialchars($event['category_icon'] ?? '📌', ENT_NOQUOTES, 'UTF-8'); ?>
                        <?php echo htmlspecialchars($event['category_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <span class="meta-text">📍 <?php echo htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="meta-text">📅 <?php echo formatEventDate($event['date']); ?> <?php echo formatEventTime($event['date']); ?></span>
                    <span class="meta-text">👤 Hôte: <?php echo htmlspecialchars($event['creator_first_name'] . ' ' . $event['creator_last_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                </p>
                <p class="event-description"><?php echo nl2br(htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8')); ?></p>
            </div>

            <!-- Discussion Section -->
            <div class="event-discussion card" id="activity-chat" data-activity-id="<?php echo $event_id; ?>" data-user-id="<?php echo $_SESSION['user_id'] ?? 0; ?>">
                <h2 class="discussion-title">💬 Discussion avec les participants</h2>
                
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="chat-login-notice">
                        <p>Vous devez être connecté pour participer à la discussion.</p>
                        <a href="../auth/login.php" class="btn btn-primary">Se connecter</a>
                    </div>
                <?php else: ?>
                    <div class="discussion-messages" id="chat-messages">
                        <div class="loading-messages">Chargement des messages...</div>
                    </div>
                    
                    <form class="discussion-form" id="chat-form" onsubmit="return false;">
                        <input type="text" id="chat-input" placeholder="Tapez votre message..." class="message-input" required autocomplete="off">
                        <button type="submit" class="btn btn-primary">Envoyer</button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Participants List -->
            <div class="event-participants card">
                <h2 class="participants-title">
                    👥 Participants inscrits (<?php echo count($participants); ?>/<?php echo htmlspecialchars($event['max_participants'], ENT_QUOTES, 'UTF-8'); ?>)
                </h2>
                <?php if (empty($participants)): ?>
                    <p class="no-participants">Aucun participant inscrit pour le moment. Soyez le premier !</p>
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
                <h3 class="sidebar-title">Participer</h3>
                <div class="capacity-info">
                    <p class="capacity-text">
                        Capacité: <strong><span id="participant-count"><?php echo count($participants); ?></span>/<?php echo htmlspecialchars($event['max_participants'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    </p>
                </div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($isUserRegistered): ?>
                        <button class="btn btn-danger btn-block btn-unsubscribe" data-activity-id="<?php echo $event_id; ?>">
                            ✓ Se désinscrire
                        </button>
                    <?php else: ?>
                        <button class="btn btn-primary btn-block btn-subscribe" data-activity-id="<?php echo $event_id; ?>">
                            S'inscrire
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="../auth/login.php" class="btn btn-primary btn-block">Connexion pour s'inscrire</a>
                <?php endif; ?>
            </div>

            <!-- Other Activities Card -->
            <div class="other-activities card">
                <h3 class="sidebar-title">Autres activités</h3>
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
<?php include '../includes/footer.php'; ?>
