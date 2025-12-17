<?php
declare(strict_types=1);
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Détails de l'activité - AmiGo";
$pageDescription = "Découvrez tous les détails de cette activité";
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/event-details.css"
];

// Sample event data (in real app, fetch from DB)
$events = [
    ['id' => 1, 'category' => 'Sport', 'title' => 'Sortie Running au Parc', 'date' => '25/11/2025', 'time' => '09h00', 'location' => 'Parc Monceau, Paris', 'places' => 50, 'taken' => 7, 'organizer' => 'Camille', 'excerpt' => 'Rejoignez-nous pour un footing convivial de 5km, tous niveaux bienvenus !', 'description' => 'Rejoignez-nous pour un footing convivial de 5km, tous niveaux bienvenus ! Rendez-vous à l\'entrée du parc. Pensez à apporter de l\'eau et des chaussures de sport confortables.', 'image' => 'https://picsum.photos/id/1067/800/600', 'subscribed' => false],
    ['id' => 2, 'category' => 'Art', 'title' => 'Balade Photo au Bord de l\'Eau', 'date' => '28/11/2025', 'time' => '18h00', 'location' => 'Bordeaux', 'places' => 20, 'taken' => 12, 'organizer' => 'Zoé', 'excerpt' => 'Découvrons les meilleurs spots photo au coucher du soleil, débutants bienvenus.', 'description' => 'Découvrons les meilleurs spots photo au coucher du soleil, débutants bienvenus. Nous explorerons les plus beaux endroits le long de la Garonne.', 'image' => 'https://picsum.photos/id/288/800/600', 'subscribed' => true],
    ['id' => 3, 'category' => 'Bien-être', 'title' => 'Initiation Yoga Vinyasa', 'date' => '30/11/2025', 'time' => '18h00', 'location' => 'Marseille', 'places' => 30, 'taken' => 4, 'organizer' => 'Nora', 'excerpt' => 'Séance détente et respiration, pensez à apporter votre tapis de yoga.', 'description' => 'Séance détente et respiration, pensez à apporter votre tapis de yoga. Parfait pour les débutants!', 'image' => 'https://picsum.photos/id/232/800/600', 'subscribed' => false],
    ['id' => 4, 'category' => 'Jeux', 'title' => 'Soirée Jeux de Société', 'date' => '27/11/2025', 'time' => '20h00', 'location' => 'Toulouse', 'places' => 20, 'taken' => 16, 'organizer' => 'Mathis', 'excerpt' => 'Ambiance conviviale, venez avec vos jeux préférés, boissons partagées.', 'description' => 'Ambiance conviviale, venez avec vos jeux préférés, boissons partagées. De nombreux jeux seront aussi fournis sur place.', 'image' => 'https://picsum.photos/id/500/800/600', 'subscribed' => false],
    ['id' => 5, 'category' => 'Nature', 'title' => 'Randonnée en Forêt', 'date' => '29/11/2025', 'time' => '18h00', 'location' => 'Chamonix', 'places' => 15, 'taken' => 6, 'organizer' => 'Romain', 'excerpt' => 'Parcours de 10km, prévoir chaussures de marche et eau. Belle vue garantie !', 'description' => 'Parcours de 10km, prévoir chaussures de marche et eau. Belle vue garantie ! Niveau modéré.', 'image' => 'https://picsum.photos/id/1022/800/600', 'subscribed' => false],
    ['id' => 6, 'category' => 'Musique', 'title' => 'Concert Jazz Improvisé', 'date' => '30/11/2025', 'time' => '18h00', 'location' => 'Nice', 'places' => 25, 'taken' => 18, 'organizer' => 'Sophie', 'excerpt' => 'Soirée musicale décontractée au son du jazz, apéro inclus.', 'description' => 'Soirée musicale décontractée au son du jazz, apéro inclus. Ambiance intimiste et chaleureuse.', 'image' => 'https://picsum.photos/id/441/800/600', 'subscribed' => false],
    ['id' => 7, 'category' => 'Nature', 'title' => 'Picnic d\'Été en Montagne', 'date' => '31/11/2025', 'time' => '12h00', 'location' => 'Annecy', 'places' => 35, 'taken' => 22, 'organizer' => 'Luc', 'excerpt' => 'Partageons un moment convivial avec vue panoramique sur les Alpes.', 'description' => 'Partageons un moment convivial avec vue panoramique sur les Alpes. Apportez votre pique-nique ou partagez le nôtre!', 'image' => 'https://picsum.photos/id/1056/800/600', 'subscribed' => false],
    ['id' => 8, 'category' => 'Sport', 'title' => 'Cours de Badminton', 'date' => '26/11/2025', 'time' => '19h00', 'location' => 'Lyon', 'places' => 12, 'taken' => 9, 'organizer' => 'Alex', 'excerpt' => 'Entraînement récréatif tous niveaux avec moniteur bénévole.', 'description' => 'Entraînement récréatif tous niveaux avec moniteur bénévole. Tous les équipements sont fournis.', 'image' => 'https://picsum.photos/id/463/800/600', 'subscribed' => false],
];

$event_id = (int) ($_GET['id'] ?? 1);
$event = null;

foreach ($events as $e) {
    if ($e['id'] === $event_id) {
        $event = $e;
        break;
    }
}

if ($event === null) {
    $event = $events[0];
}

// Other events for sidebar (exclude current)
$otherEvents = array_filter($events, function($e) use ($event_id) {
    return $e['id'] !== $event_id;
});

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
                    <span class="badge"><?php echo htmlspecialchars($event['category'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="meta-text">📍 <?php echo htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="meta-text">📅 <?php echo htmlspecialchars($event['date'], ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($event['time'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="meta-text">👤 Hôte: <?php echo htmlspecialchars($event['organizer'], ENT_QUOTES, 'UTF-8'); ?></span>
                </p>
                <p class="event-description"><?php echo htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>

            <!-- Discussion Section -->
            <div class="event-discussion card">
                <h2 class="discussion-title">💬 Discussion avec les participants</h2>
                <div class="discussion-messages">
                    <div class="discussion-message">
                        <div class="message-author">Vous - 13:16</div>
                        <div class="message-text">Salut tout le monde</div>
                    </div>
                </div>
                <form class="discussion-form" method="POST">
                    <input type="text" name="message" placeholder="Tapez votre message..." class="message-input" required>
                    <button type="submit" class="btn btn-primary">Envoyer</button>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <aside class="event-sidebar">
            <!-- Participate Card -->
            <div class="participate-card card">
                <h3 class="sidebar-title">Participer</h3>
                <div class="capacity-info">
                    <p class="capacity-text">Capacité: <strong><?php echo htmlspecialchars($event['places'], ENT_QUOTES, 'UTF-8'); ?> • inscrits: <?php echo htmlspecialchars($event['taken'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                </div>
                <?php if (!empty($event['subscribed'])): ?>
                    <button class="btn btn-danger btn-block">✓ Inscrit</button>
                <?php else: ?>
                    <button class="btn btn-primary btn-block">S'inscrire</button>
                <?php endif; ?>
            </div>

            <!-- Other Activities Card -->
            <div class="other-activities card">
                <h3 class="sidebar-title">Autres activités</h3>
                <div class="activities-list">
                    <?php foreach (array_slice($otherEvents, 0, 2) as $other): ?>
                        <div class="activity-mini">
                            <span class="activity-mini-badge"><?php echo htmlspecialchars($other['category'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <h4 class="activity-mini-title"><?php echo htmlspecialchars($other['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p class="activity-mini-excerpt"><?php echo htmlspecialchars(substr($other['excerpt'], 0, 60) . '...', ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="activity-mini-meta">
                                📍 <?php echo htmlspecialchars($other['location'], ENT_QUOTES, 'UTF-8'); ?><br>
                                📅 <?php echo htmlspecialchars($other['date'], ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($other['time'], ENT_QUOTES, 'UTF-8'); ?><br>
                                👤 <?php echo htmlspecialchars($other['organizer'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                            <div class="activity-mini-footer">
                                <span class="places"><?php echo htmlspecialchars($other['taken'], ENT_QUOTES, 'UTF-8'); ?>/<?php echo htmlspecialchars($other['places'], ENT_QUOTES, 'UTF-8'); ?> inscrits</span>
                                <a href="event-details.php?id=<?php echo htmlspecialchars($other['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn-mini">S'inscrire</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
