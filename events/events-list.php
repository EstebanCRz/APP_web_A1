<?php
declare(strict_types=1);

// Configuration de session (AVANT session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once '../includes/activities_functions.php';

$pageTitle = "Liste des événements - AmiGo";
$pageDescription = "Découvrez tous les événements disponibles";
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/events-list.css"
];

// Récupération des filtres depuis l'URL
$filters = [
    'search' => (string) ($_GET['search'] ?? ''),
    'category' => (string) ($_GET['category'] ?? ''),
    'time_filter' => (string) ($_GET['time'] ?? ''),
    'date_filter' => (string) ($_GET['date'] ?? '')
];

// Variables pour faciliter l'accès
$filterDate = $filters['date_filter'];

// Récupération des activités depuis la base de données avec filtres
$activitiesFromDB = getAllActivities($filters);

// Récupération de toutes les catégories pour les filtres
$categories = getAllCategories();

// Transformation des données pour l'affichage
$events = [];
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
        'subscribed' => isset($_SESSION['user_id']) ? isUserRegistered($act['id'], $_SESSION['user_id']) : false
    ];
}

include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h2>Tous les événements</h2>
        <a href="event-create.php" class="btn btn-primary">Créer une activité</a>
    </div>

    <div class="search-section">
        <form method="GET" class="search-form">
            <input type="search" name="search" placeholder="Chercher (mot-clé, ville, organisateur)" value="<?php echo htmlspecialchars($filters['search'], ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </form>
    </div>

    <div class="filters-section">
        <div class="filter-group">
            <h3>Catégorie</h3>
            <div class="filter-chips">
                <a href="?<?php echo http_build_query(['search' => $filters['search'], 'time' => $filters['time_filter'], 'date' => $filters['date_filter']]); ?>" class="filter-chip <?php echo ($filters['category'] === '') ? 'active' : ''; ?>">Tous</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $cat['name'], 'time' => $filters['time_filter'], 'date' => $filters['date_filter']]); ?>" class="filter-chip <?php echo ($filters['category'] === $cat['name']) ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="filter-group">
            <h3>Moment de la journée</h3>
            <div class="filter-chips">
                <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'date' => $filters['date_filter']]); ?>" class="filter-chip <?php echo ($filters['time_filter'] === '') ? 'active' : ''; ?>">Tous</a>
                <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => 'morning', 'date' => $filters['date_filter']]); ?>" class="filter-chip <?php echo ($filters['time_filter'] === 'morning') ? 'active' : ''; ?>">Matin</a>
                <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => 'afternoon', 'date' => $filters['date_filter']]); ?>" class="filter-chip <?php echo ($filters['time_filter'] === 'afternoon') ? 'active' : ''; ?>">Après-midi</a>
                <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => 'evening', 'date' => $filters['date_filter']]); ?>" class="filter-chip <?php echo ($filters['time_filter'] === 'evening') ? 'active' : ''; ?>">Soirée</a>
            </div>
        </div>

        <div class="filter-group">
            <h3>Période</h3>
            <div class="filter-chips">
                <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => $filters['time_filter']]); ?>" class="filter-chip <?php echo ($filters['date_filter'] === '') ? 'active' : ''; ?>">Toutes</a>
                <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => $filters['time_filter'], 'date' => 'week']); ?>" class="filter-chip <?php echo ($filters['date_filter'] === 'week') ? 'active' : ''; ?>">Cette semaine</a>
                <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => $filters['time_filter'], 'date' => 'month']); ?>" class="filter-chip <?php echo ($filters['date_filter'] === 'month') ? 'active' : ''; ?>">Ce mois-ci</a>
                <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => $filters['time_filter'], 'date' => 'coming']); ?>" class="filter-chip <?php echo ($filters['date_filter'] === 'coming') ? 'active' : ''; ?>">À venir</a>
                <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => $filters['time_filter'], 'date' => 'past']); ?>" class="filter-chip <?php echo ($filters['date_filter'] === 'past') ? 'active' : ''; ?>">Passés</a>
            </div>
        </div>
    </div>

    <?php if (empty($events)): ?>
        <p>Aucun événement trouvé.</p>
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
            ?>
                <a href="event-details.php?id=<?php echo $id; ?>" class="event-card">
                    <div class="card-media" style="background-image: url('<?php echo $image; ?>');">
                        <span class="badge"><?php echo $category; ?></span>
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
</div>

<script src="../assets/js/activity-registration.js"></script>
<?php include '../includes/footer.php'; ?>