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

$pageTitle = t('events.all_events') . " - AmiGo";
$pageDescription = t('events.all_events');
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/events-list.css",
    "css/search-bar.css"
];

// Récupération des filtres depuis l'URL
$filters = [
    'search' => (string) ($_GET['search'] ?? ''),
    'category' => (string) ($_GET['category'] ?? ''),
    'time_filter' => (string) ($_GET['time'] ?? ''),
    'date_filter' => (string) ($_GET['date'] ?? '')
];

// Fonction pour nettoyer les entrées utilisateur
function cleanInput($input) {
    // Supprimer les caractères null (byte 0x00)
    $input = str_replace(chr(0), '', $input);
    $input = str_replace("\0", '', $input);
    $input = str_replace("\\0", '', $input);
    
    // Supprimer les caractères de contrôle (0x00-0x1F sauf espaces/tabs/retours)
    $input = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F]/', '', $input);
    
    // Supprimer les backslashes et astérisques
    $input = str_replace('\\', '', $input);
    $input = str_replace('*', '', $input);
    
    return trim($input);
}

// Nettoyer et valider les filtres
$filters['search'] = cleanInput($filters['search']);
$filters['search'] = mb_substr($filters['search'], 0, 50); // Limiter à 50 caractères
$filters['category'] = cleanInput($filters['category']);
$filters['time_filter'] = cleanInput($filters['time_filter']);
$filters['date_filter'] = cleanInput($filters['date_filter']);

// Variables pour faciliter l'accès
$filterDate = $filters['date_filter'];

// Récupération des activités depuis la base de données avec filtres
$activitiesFromDB = getAllActivities($filters);

// Récupération de toutes les catégories pour les filtres
$categories = getAllCategories();

// Transformation des données pour l'affichage
$events = [];
$userFavorites = [];

// Récupérer les favoris de l'utilisateur si connecté
if (isset($_SESSION['user_id'])) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT activity_id FROM user_favorites WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userFavorites = array_column($stmt->fetchAll(), 'activity_id');
    } catch (PDOException $e) {
        $userFavorites = [];
    }
}

foreach ($activitiesFromDB as $act) {
    $eventDate = new DateTime($act['event_date']);
    $events[] = [
        'id' => $act['id'],
        'category' => $act['category_name'],
        'title' => $act['title'],
        'date' => $eventDate->format('d/m/Y'),
        'time' => formatEventTime($act['event_time']),
        'location' => $act['location'] . ', ' . $act['city'],
        'places' => (int)$act['max_participants'],
        'taken' => (int)$act['current_participants'],
        'organizer' => $act['creator_first_name'] ?? $act['creator_username'],
        'excerpt' => $act['excerpt'],
        'image' => $act['image'] ?? 'https://picsum.photos/800/600',
        'subscribed' => isset($_SESSION['user_id']) ? isUserRegistered($act['id'], $_SESSION['user_id']) : false,
        'is_favorite' => in_array($act['id'], $userFavorites)
    ];
}

include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h2><?php echo t('events.all_events'); ?></h2>
        <a href="event-create.php" class="btn btn-primary"><?php echo t('events.create'); ?></a>
    </div>

    <div class="content-wrapper">
        <!-- Sidebar avec filtres -->
        <aside class="filters-sidebar">
            <div class="filter-group">
                <h3><?php echo t('events.category_filter'); ?></h3>
                <div class="filter-chips">
                    <a href="?<?php echo http_build_query(['search' => $filters['search'], 'time' => $filters['time_filter'], 'date' => $filters['date_filter']]); ?>" class="filter-chip <?php echo ($filters['category'] === '') ? 'active' : ''; ?>"><?php echo t('events.all_categories'); ?></a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $cat['name'], 'time' => $filters['time_filter'], 'date' => $filters['date_filter']]); ?>" class="filter-chip <?php echo ($filters['category'] === $cat['name']) ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="filter-group">
                <h3><?php echo t('events.time_filter'); ?></h3>
                <div class="filter-chips">
                    <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'date' => $filters['date_filter']]); ?>" class="filter-chip <?php echo ($filters['time_filter'] === '') ? 'active' : ''; ?>"><?php echo t('events.all_times'); ?></a>
                    <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => 'morning', 'date' => $filters['date_filter']]); ?>" class="filter-chip <?php echo ($filters['time_filter'] === 'morning') ? 'active' : ''; ?>"><?php echo t('events.morning'); ?></a>
                    <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => 'afternoon', 'date' => $filters['date_filter']]); ?>" class="filter-chip <?php echo ($filters['time_filter'] === 'afternoon') ? 'active' : ''; ?>"><?php echo t('events.afternoon'); ?></a>
                    <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => 'evening', 'date' => $filters['date_filter']]); ?>" class="filter-chip <?php echo ($filters['time_filter'] === 'evening') ? 'active' : ''; ?>"><?php echo t('events.evening'); ?></a>
                </div>
            </div>

            <div class="filter-group">
                <h3><?php echo t('events.period_filter'); ?></h3>
                <div class="filter-chips">
                    <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => $filters['time_filter']]); ?>" class="filter-chip <?php echo ($filters['date_filter'] === '') ? 'active' : ''; ?>"><?php echo t('events.all_periods'); ?></a>
                    <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => $filters['time_filter'], 'date' => 'week']); ?>" class="filter-chip <?php echo ($filters['date_filter'] === 'week') ? 'active' : ''; ?>"><?php echo t('events.this_week'); ?></a>
                    <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => $filters['time_filter'], 'date' => 'month']); ?>" class="filter-chip <?php echo ($filters['date_filter'] === 'month') ? 'active' : ''; ?>"><?php echo t('events.this_month'); ?></a>
                    <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => $filters['time_filter'], 'date' => 'coming']); ?>" class="filter-chip <?php echo ($filters['date_filter'] === 'coming') ? 'active' : ''; ?>"><?php echo t('events.coming_soon'); ?></a>
                    <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => $filters['time_filter'], 'date' => 'past']); ?>" class="filter-chip <?php echo ($filters['date_filter'] === 'past') ? 'active' : ''; ?>"><?php echo t('events.past'); ?></a>
                </div>
            </div>
        </aside>

        <!-- Contenu principal -->
        <div class="main-content">
            <!-- Barre de recherche -->
            <div class="search-section">
                <form method="GET" class="search-form" role="search" aria-label="Rechercher des événements">
                    <input 
                        type="search" 
                        name="search" 
                        placeholder="<?php echo t('events.search_placeholder'); ?>" 
                        value="<?php echo htmlspecialchars($filters['search'], ENT_QUOTES, 'UTF-8'); ?>"
                        aria-label="Terme de recherche"
                        autocomplete="off"
                        maxlength="50"
                        pattern="[a-zA-Z0-9\s\-'àâäéèêëïîôùûüÿçÀÂÄÉÈÊËÏÎÔÙÛÜŸÇ]*"
                        title="Recherche limitée à 50 caractères (lettres, chiffres, espaces et tirets uniquement)"
                    >
                    <button type="submit" class="btn btn-primary">
                        <span>🔍</span> <?php echo t('events.search_button'); ?>
                    </button>
                </form>
                
                <?php if (!empty($filters['search'])): ?>
                <div class="search-results-meta">
                    <div class="search-results-count">
                        <strong><?php echo count($events); ?></strong> 
                        <?php echo count($events) > 1 ? 'événements trouvés' : 'événement trouvé'; ?>
                    </div>
                    <div class="search-results-term">
                        pour <strong>"<?php echo htmlspecialchars($filters['search'], ENT_QUOTES, 'UTF-8'); ?>"</strong>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Carte Maps -->
            <div class="map-section" id="mapSection">
                <button class="map-toggle-btn" id="mapToggleBtn" onclick="toggleMap()" aria-label="Toggle map visibility">
                    <span id="mapToggleIcon">🗺️</span> <span id="mapToggleText">Masquer la carte</span>
                </button>
                <div id="events-map" class="events-map"></div>
            </div>

    <?php if (empty($events)): ?>
        <div class="no-results-message">
            <h3>🔍 Aucun événement trouvé</h3>
            <p>Nous n'avons trouvé aucun événement correspondant à vos critères de recherche.</p>
            <?php if (!empty($filters['search'])): ?>
            <p>Essayez avec un terme différent ou <a href="events-list.php">réinitialisez la recherche</a>.</p>
            <?php endif; ?>
            <div class="search-suggestions">
                <strong>Suggestions :</strong>
                <ul>
                    <li>Vérifiez l'orthographe de vos mots-clés</li>
                    <li>Utilisez des termes plus généraux</li>
                    <li>Essayez d'autres filtres</li>
                </ul>
            </div>
        </div>
    <?php else: ?>
        <div class="events-grid">
            <?php foreach ($events as $event):
                $id = (int) $event['id'];
                $title = htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8');
                $excerpt = htmlspecialchars($event['excerpt'], ENT_QUOTES, 'UTF-8');
                $date = htmlspecialchars($event['date'], ENT_QUOTES, 'UTF-8');
                $time = htmlspecialchars($event['time'], ENT_QUOTES, 'UTF-8');
                $location = htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8');
                $category = htmlspecialchars($event['category'], ENT_QUOTES, 'UTF-8');
                $taken = (int) ($event['taken'] ?? 0);
                $places = (int) ($event['places'] ?? 0);
                $organizer = htmlspecialchars($event['organizer'] ?? '', ENT_QUOTES, 'UTF-8');
                $image = htmlspecialchars($event['image'] ?? '', ENT_QUOTES, 'UTF-8');
                $subscribed = !empty($event['subscribed']);
                $isFavorite = !empty($event['is_favorite']);
            ?>
                <a href="event-details.php?id=<?php echo $id; ?>" class="event-card">
                    <div class="card-media" style="background-image: url('<?php echo $image; ?>');">
                        <span class="badge"><?php echo $category; ?></span>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <button 
                                class="favorite-btn-card <?php echo $isFavorite ? 'active' : ''; ?>" 
                                data-activity-id="<?php echo $id; ?>"
                                onclick="event.preventDefault(); event.stopPropagation(); toggleFavorite(this);"
                                title="<?php echo $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?>"
                            >
                                ❤️
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><?php echo $title; ?></h3>
                        <p class="card-excerpt"><?php echo $excerpt; ?></p>

                        <div class="card-meta">
                            <span class="meta-item">📍 <?php echo $location; ?></span>
                            <span class="meta-item">📅 <?php echo $date; ?> <?php echo $time; ?></span>
                            <span class="meta-item">👤 <?php echo $organizer; ?></span>
                        </div>

                        <div class="card-footer">
                            <span class="places participant-count"><?php echo $taken; ?>/<?php echo $places; ?> inscrits</span>
                            <?php if ($subscribed): ?>
                                <button class="event-cta event-cta--subscribed btn-unsubscribe" data-activity-id="<?php echo $id; ?>">Se désinscrire</button>
                            <?php else: ?>
                                <button class="event-cta btn-subscribe" data-activity-id="<?php echo $id; ?>">S'inscrire</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
        </div><!-- fin main-content -->
    </div><!-- fin content-wrapper -->
</div>

<script src="../assets/js/activity-registration.js"></script>

<!-- Fonction pour toggle la carte sur mobile -->
<script>
function toggleMap() {
    const mapSection = document.getElementById('mapSection');
    const toggleText = document.getElementById('mapToggleText');
    const toggleIcon = document.getElementById('mapToggleIcon');
    
    if (mapSection && toggleText && toggleIcon) {
        mapSection.classList.toggle('collapsed');
        
        if (mapSection.classList.contains('collapsed')) {
            toggleText.textContent = 'Afficher la carte';
            toggleIcon.textContent = '📍';
        } else {
            toggleText.textContent = 'Masquer la carte';
            toggleIcon.textContent = '🗺️';
        }
    }
}

// Par défaut, masquer la carte sur mobile au chargement
document.addEventListener('DOMContentLoaded', function() {
    if (window.innerWidth <= 768) {
        const mapSection = document.getElementById('mapSection');
        const toggleText = document.getElementById('mapToggleText');
        const toggleIcon = document.getElementById('mapToggleIcon');
        if (mapSection && toggleText && toggleIcon) {
            mapSection.classList.add('collapsed');
            toggleText.textContent = 'Afficher la carte';
            toggleIcon.textContent = '📍';
        }
    }
});
</script>

<!-- Leaflet CSS et JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Passer les données PHP au JavaScript -->
<script>
window.evenementsData = <?php echo json_encode($events, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
</script>

<!-- Fichiers JavaScript séparés -->
<script src="js/search.js"></script>
<script src="js/events-list.js"></script>

<?php include '../includes/footer.php';
