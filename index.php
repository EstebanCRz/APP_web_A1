<?php
// Mode développement - Commentez ces lignes en production
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Charger la configuration AVANT de démarrer la session
require_once 'includes/config.php';

// Configuration de session (AVANT session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

session_start();
header('Content-Type: text/html; charset=UTF-8');

// Charger le système de traduction
require_once 'includes/language.php';

require_once 'includes/activities_functions.php';

// Récupération des activités depuis la base de données (limitées à 8 pour la page d'accueil)
try {
    $activitiesFromDB = getAllActivities(['limit' => 8]);
    
    // Récupération de toutes les catégories pour les filtres
    $categories = getAllCategories();
    
    // Transformation des données pour l'affichage
    $activities = [];
    $userId = $_SESSION['user_id'] ?? null;
    
    foreach ($activitiesFromDB as $act) {
        $isRegistered = false;
        if ($userId) {
            $isRegistered = isUserRegistered((int)$act['id'], (int)$userId);
        }
        
        $activities[] = [
            'id' => $act['id'],
            'title' => $act['title'],
            'type' => t('categories.' . $act['category_name']),
            'loc' => $act['location'] . ', ' . $act['city'],
            'date' => formatEventDate($act['event_date']),
            'user' => $act['creator_first_name'] ?? $act['creator_username'],
            'color' => $act['category_color'],
            'inscrits' => $act['current_participants'] . '/' . $act['max_participants'],
            'img' => $act['image'] ?? 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=400',
            'is_registered' => $isRegistered
        ];
    }
} catch (Exception $e) {
    // En cas d'erreur, afficher un message et utiliser des données vides
    echo "<!-- Erreur de base de données: " . htmlspecialchars($e->getMessage()) . " -->";
    $activities = [];
    $categories = [];
}

$pageTitle = "AmiGo - " . t('home.title');
$assetsDepth = 0;
$customCSS = [
    "assets/css/style.css",
    "assets/css/index.css"
];

include 'includes/header.php';
?>

<div class="main-container">
    <section class="hero-section">
        <div class="hero-content">
            <h1><?php echo t('home.subtitle'); ?></h1>
            <p><?php echo t('home.description'); ?></p>
            
            <input type="text" id="searchBar" placeholder="<?php echo t('home.search_placeholder'); ?>">
            
            <div class="filter-tags">
                <button class="filter-btn active" data-filter="all"><?php echo t('home.filter_all'); ?></button>
                <?php foreach ($categories as $cat): ?>
                    <button class="filter-btn" data-filter="<?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            
            <div class="hero-badges">
                <span class="hero-badge badge-local"><?php echo t('home.badge_local'); ?></span>
                <span class="hero-badge badge-convivial"><?php echo t('home.badge_friendly'); ?></span>
                <span class="hero-badge badge-gratuit"><?php echo t('home.badge_free'); ?></span>
            </div>
        </div>
        
        <div class="hero-grid">
            <img src="https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?w=400" alt="Running">
            <img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=400" alt="Nature">
            <img src="https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=400" alt="Yoga">
            <img src="https://images.unsplash.com/photo-1511367461989-f85a21fda167?w=400" alt="Music">
            <img src="https://images.unsplash.com/photo-1511876484798-816e2c24f1c3?w=400" alt="Games">
        </div>
    </section>

    <div class="section-header">
        <div>
            <h2><?php echo t('events.title'); ?></h2>
            <p><?php echo t('home.latest_community'); ?></p>
        </div>
        <a href="events/events-list.php" class="voir-tout"><?php echo t('home.view_all'); ?></a>
    </div>
    
    <div class="activities-grid" id="activitiesContainer">
        <?php foreach ($activities as $act): ?>
            <a href="events/event-details.php?id=<?php echo $act['id']; ?>" class="activity-item" data-type="<?php echo htmlspecialchars($act['type'], ENT_QUOTES, 'UTF-8'); ?>">
                <div class="card-img" style="background-image: url('<?php echo htmlspecialchars($act['img'], ENT_QUOTES, 'UTF-8'); ?>');">
                    <span class="badge" style="background: <?php echo htmlspecialchars($act['color'], ENT_QUOTES, 'UTF-8'); ?>;"><?php echo htmlspecialchars($act['type'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="card-body">
                    <h4><?php echo htmlspecialchars($act['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                    <div class="card-meta">
                        <span class="info">📍 <?php echo htmlspecialchars($act['loc'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="info">📅 <?php echo htmlspecialchars($act['date'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="info">👤 <?php echo htmlspecialchars($act['user'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div class="card-footer">
                        <span class="participant-count"><?php echo htmlspecialchars($act['inscrits'], ENT_QUOTES, 'UTF-8'); ?> <?php echo t('home.registered'); ?></span>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php if ($act['is_registered']): ?>
                                <button class="btn-unsubscribe" data-activity-id="<?php echo htmlspecialchars($act['id'], ENT_QUOTES, 'UTF-8'); ?>">\u2713 <?php echo t('home.unregister'); ?></button>
                            <?php else: ?>
                                <button class="btn-subscribe" data-activity-id="<?php echo htmlspecialchars($act['id'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo t('events.register'); ?></button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="auth/login.php" class="btn-subscribe"><?php echo t('header.login'); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<script src="assets/css/script.js"></script>
<script src="assets/js/activity-registration.js"></script>
<?php include 'includes/footer.php'; ?>