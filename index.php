<?php
// Mode développement - Commentez ces lignes en production
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Charger la session et configuration
require_once 'includes/session.php';
require_once 'includes/config.php';
require_once 'includes/activities_functions.php';

// Récupération des activités depuis la base de données
try {
    $activitiesFromDB = getAllActivities(['limit' => 8]);
    $categories = getAllCategories();
    
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
            'type' => $act['category_name'],
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
    echo "";
    $activities = [];
    $categories = [];
}

$pageTitle = "AmiGo - Partage d'activités";
$assetsDepth = 0;
$customCSS = ["assets/css/style.css", "assets/css/index.css"];

include 'includes/header.php';
?>

<div class="main-container">
    <section class="hero-section">
        <div class="hero-content">
            <h1>Partage d'activités entre particuliers</h1>
            <p>Découvrez, créez et rejoignez des activités près de chez vous.</p>
            
            <input type="text" id="searchBar" placeholder="Chercher une activité (mot-clé, ville, organisateur)">
            
            <div class="filter-tags">
                <button class="filter-btn active" data-filter="all">Tous</button>
                <?php foreach ($categories as $cat): ?>
                    <button class="filter-btn" data-filter="<?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="hero-grid">
            <img src="https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?w=400" alt="Running">
            <img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=400" alt="Nature">
            <img src="https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=400" alt="Yoga">
            <img src="https://images.unsplash.com/photo-1511367461989-f85a21fda167?w=400" alt="Music">
        </div>
    </section>

    <div class="section-header">
        <div>
            <h2>Activités récentes</h2>
        </div>
        <a href="events/events-list.php" class="voir-tout">Voir tout</a>
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
                        <span class="info">👤 <?php echo htmlspecialchars($act['user'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<script src="assets/css/script.js"></script>

<script src="assets/js/search-filter.js"></script>
<script src="assets/js/activity-registration.js"></script>
<?php include 'includes/footer.php'; ?>