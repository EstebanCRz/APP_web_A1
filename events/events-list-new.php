<?php
declare(strict_types=1);
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Liste des événements - AmiGo";
$pageDescription = "Découvrez tous les événements disponibles";
$assetsDepth = 1;
$customCSS = "css/events-list.css";

$events = [
    ['id' => 1, 'category' => 'Sport', 'title' => 'Sortie Running au Parc', 'date' => '25/11/2025', 'time' => '09h00', 'location' => 'Parc Monceau, Paris', 'places' => 50, 'taken' => 7, 'organizer' => 'Camille', 'excerpt' => 'Rejoignez-nous pour un footing convivial de 5km, tous niveaux bienvenus !', 'image' => 'https://picsum.photos/id/1067/800/600', 'subscribed' => false],
    ['id' => 2, 'category' => 'Art', 'title' => 'Balade Photo au Bord de l\'Eau', 'date' => '28/11/2025', 'time' => '18h00', 'location' => 'Bordeaux', 'places' => 20, 'taken' => 12, 'organizer' => 'Zoé', 'excerpt' => 'Découvrons les meilleurs spots photo au coucher du soleil, débutants bienvenus.', 'image' => 'https://picsum.photos/id/1011/800/600', 'subscribed' => true],
    ['id' => 3, 'category' => 'Bien-être', 'title' => 'Initiation Yoga Vinyasa', 'date' => '30/11/2025', 'time' => '18h00', 'location' => 'Marseille', 'places' => 30, 'taken' => 4, 'organizer' => 'Nora', 'excerpt' => 'Séance détente et respiration, pensez à apporter votre tapis de yoga.', 'image' => 'https://picsum.photos/id/1043/800/600', 'subscribed' => false],
    ['id' => 4, 'category' => 'Jeux', 'title' => 'Soirée Jeux de Société', 'date' => '27/11/2025', 'time' => '20h00', 'location' => 'Toulouse', 'places' => 20, 'taken' => 16, 'organizer' => 'Mathis', 'excerpt' => 'Ambiance conviviale, venez avec vos jeux préférés, boissons partagées.', 'image' => 'https://picsum.photos/id/1045/800/600', 'subscribed' => false],
    ['id' => 5, 'category' => 'Nature', 'title' => 'Randonnée en Forêt', 'date' => '29/11/2025', 'time' => '18h00', 'location' => 'Chamonix', 'places' => 15, 'taken' => 6, 'organizer' => 'Romain', 'excerpt' => 'Parcours de 10km, prévoir chaussures de marche et eau. Belle vue garantie !', 'image' => 'https://picsum.photos/id/1022/800/600', 'subscribed' => false],
    ['id' => 6, 'category' => 'Musique', 'title' => 'Concert Jazz Improvisé', 'date' => '30/11/2025', 'time' => '18h00', 'location' => 'Nice', 'places' => 25, 'taken' => 18, 'organizer' => 'Sophie', 'excerpt' => 'Soirée musicale décontractée au son du jazz, apéro inclus.', 'image' => 'https://picsum.photos/id/1039/800/600', 'subscribed' => false],
    ['id' => 7, 'category' => 'Nature', 'title' => 'Picnic d\'Été en Montagne', 'date' => '31/11/2025', 'time' => '12h00', 'location' => 'Annecy', 'places' => 35, 'taken' => 22, 'organizer' => 'Luc', 'excerpt' => 'Partageons un moment convivial avec vue panoramique sur les Alpes.', 'image' => 'https://picsum.photos/id/1056/800/600', 'subscribed' => false],
    ['id' => 8, 'category' => 'Sport', 'title' => 'Cours de Badminton', 'date' => '26/11/2025', 'time' => '19h00', 'location' => 'Lyon', 'places' => 12, 'taken' => 9, 'organizer' => 'Alex', 'excerpt' => 'Entraînement récréatif tous niveaux avec moniteur bénévole.', 'image' => 'https://picsum.photos/id/1064/800/600', 'subscribed' => false],
];

// simple search and filters
$search = (string) ($_GET['search'] ?? '');
$filterCat = (string) ($_GET['category'] ?? '');
$filterTime = (string) ($_GET['time'] ?? '');
$filterDate = (string) ($_GET['date'] ?? '');

if ($search !== '') {
    $events = array_filter($events, function($event) use ($search) {
        return stripos($event['title'], $search) !== false || stripos($event['location'], $search) !== false || stripos($event['excerpt'], $search) !== false;
    });
}

if ($filterCat !== '') {
    $events = array_filter($events, function($event) use ($filterCat) {
        return strcasecmp($event['category'], $filterCat) === 0;
    });
}

// Helper function to parse time (e.g., "20h00" -> 20)
$getHour = function($timeStr) {
    preg_match('/(\d+)/', $timeStr, $matches);
    return (int) ($matches[1] ?? 0);
};

// Filter by time of day
if ($filterTime !== '') {
    $events = array_filter($events, function($event) use ($filterTime, $getHour) {
        $hour = $getHour($event['time']);
        switch ($filterTime) {
            case 'morning': return $hour >= 6 && $hour < 12;
            case 'afternoon': return $hour >= 12 && $hour < 18;
            case 'evening': return $hour >= 18 && $hour < 24;
            default: return true;
        }
    });
}

// Filter by date range
if ($filterDate !== '') {
    $today = new DateTime();
    $events = array_filter($events, function($event) use ($filterDate, $today) {
        $eventDate = DateTime::createFromFormat('d/m/Y', $event['date']);
        $thisWeekEnd = (clone $today)->modify('+6 days');
        $thisMonthEnd = (clone $today)->modify('last day of this month');
        
        switch ($filterDate) {
            case 'week': return $eventDate <= $thisWeekEnd;
            case 'month': return $eventDate <= $thisMonthEnd;
            case 'coming': return $eventDate >= $today;
            default: return true;
        }
    });
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
            <input type="search" name="search" placeholder="Chercher (mot-clé, ville, organisateur)" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </form>
    </div>

    <div class="filters-section">
        <div class="filter-group">
            <h3>Catégorie</h3>
            <div class="filter-chips">
                <a href="?<?php echo http_build_query(['search' => $search, 'time' => $filterTime, 'date' => $filterDate]); ?>" class="filter-chip <?php echo ($filterCat === '') ? 'active' : ''; ?>">Tous</a>
                <a href="?<?php echo http_build_query(['search' => $search, 'category' => 'Sport', 'time' => $filterTime, 'date' => $filterDate]); ?>" class="filter-chip <?php echo ($filterCat === 'Sport') ? 'active' : ''; ?>">Sport</a>
                <a href="?<?php echo http_build_query(['search' => $search, 'category' => 'Art', 'time' => $filterTime, 'date' => $filterDate]); ?>" class="filter-chip <?php echo ($filterCat === 'Art') ? 'active' : ''; ?>">Art</a>
                <a href="?<?php echo http_build_query(['search' => $search, 'category' => 'Bien-être', 'time' => $filterTime, 'date' => $filterDate]); ?>" class="filter-chip <?php echo ($filterCat === 'Bien-être') ? 'active' : ''; ?>">Bien-être</a>
                <a href="?<?php echo http_build_query(['search' => $search, 'category' => 'Jeux', 'time' => $filterTime, 'date' => $filterDate]); ?>" class="filter-chip <?php echo ($filterCat === 'Jeux') ? 'active' : ''; ?>">Jeux</a>
                <a href="?<?php echo http_build_query(['search' => $search, 'category' => 'Nature', 'time' => $filterTime, 'date' => $filterDate]); ?>" class="filter-chip <?php echo ($filterCat === 'Nature') ? 'active' : ''; ?>">Nature</a>
                <a href="?<?php echo http_build_query(['search' => $search, 'category' => 'Musique', 'time' => $filterTime, 'date' => $filterDate]); ?>" class="filter-chip <?php echo ($filterCat === 'Musique') ? 'active' : ''; ?>">Musique</a>
            </div>
        </div>

        <div class="filter-group">
            <h3>Moment</h3>
            <div class="filter-chips">
                <a href="?<?php echo http_build_query(['search' => $search, 'category' => $filterCat, 'date' => $filterDate]); ?>" class="filter-chip <?php echo ($filterTime === '') ? 'active' : ''; ?>">N'importe quand</a>
                <a href="?<?php echo http_build_query(['search' => $search, 'category' => $filterCat, 'time' => 'morning', 'date' => $filterDate]); ?>" class="filter-chip <?php echo ($filterTime === 'morning') ? 'active' : ''; ?>">Matin</a>
                <a href="?<?php echo http_build_query(['search' => $search, 'category' => $filterCat, 'time' => 'afternoon', 'date' => $filterDate]); ?>" class="filter-chip <?php echo ($filterTime === 'afternoon') ? 'active' : ''; ?>">Après-midi</a>
                <a href="?<?php echo http_build_query(['search' => $search, 'category' => $filterCat, 'time' => 'evening', 'date' => $filterDate]); ?>" class="filter-chip <?php echo ($filterTime === 'evening') ? 'active' : ''; ?>">Soirée</a>
            </div>
        </div>

        <div class="filter-group">
            <h3>Quand</h3>
            <div class="filter-chips">
                <a href="?<?php echo http_build_query(['search' => $search, 'category' => $filterCat, 'time' => $filterTime]); ?>" class="filter-chip <?php echo ($filterDate === '') ? 'active' : ''; ?>">Tous les événements</a>
                <a href="?<?php echo http_build_query(['search' => $search, 'category' => $filterCat, 'time' => $filterTime, 'date' => 'week']); ?>" class="filter-chip <?php echo ($filterDate === 'week') ? 'active' : ''; ?>">Cette semaine</a>
                <a href="?<?php echo http_build_query(['search' => $search, 'category' => $filterCat, 'time' => $filterTime, 'date' => 'month']); ?>" class="filter-chip <?php echo ($filterDate === 'month') ? 'active' : ''; ?>">Ce mois-ci</a>
                <a href="?<?php echo http_build_query(['search' => $search, 'category' => $filterCat, 'time' => $filterTime, 'date' => 'coming']); ?>" class="filter-chip <?php echo ($filterDate === 'coming') ? 'active' : ''; ?>">À venir</a>
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
                <article class="event-card">
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
                            <span class="places"><?php echo $taken; ?>/<?php echo $places; ?> inscrits</span>
                            <?php if ($subscribed): ?>
                                <a href="event-details.php?id=<?php echo $id; ?>" class="event-cta event-cta--subscribed">Se désinscrire</a>
                            <?php else: ?>
                                <a href="event-details.php?id=<?php echo $id; ?>" class="event-cta">S'inscrire</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
