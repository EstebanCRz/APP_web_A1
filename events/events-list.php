<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Liste des événements - AmiGo";
$pageDescription = "Découvrez tous les événements disponibles";
$assetsDepth = 1;
$customCSS = "../assets/css/index.css";

$events = [
    ['id' => 1, 'title' => 'Concert Rock en plein air', 'date' => '25/11/2025', 'time' => '20h00', 'location' => 'Paris, France', 'places' => 50, 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
    ['id' => 2, 'title' => 'Match de Football', 'date' => '28/11/2025', 'time' => '15h00', 'location' => 'Lyon, France', 'places' => 20, 'gradient' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)'],
    ['id' => 3, 'title' => 'Soirée Cinéma', 'date' => '30/11/2025', 'time' => '19h30', 'location' => 'Marseille, France', 'places' => 30, 'gradient' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)']
];

$search = $_GET['search'] ?? '';
if ($search) {
    $events = array_filter($events, function($event) use ($search) {
        return stripos($event['title'], $search) !== false || stripos($event['location'], $search) !== false;
    });
}

include '../includes/header.php';
?>

<div class="container">
    <h2>Tous les événements</h2>
    <div class="search-section">
        <form method="GET">
            <input type="text" name="search" placeholder="Rechercher..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </form>
    </div>
    <?php if (empty($events)): ?>
        <p>Aucun événement trouvé.</p>
    <?php else: ?>
        <div class="events-grid">
            <?php foreach ($events as $event): ?>
                <div class="event-card">
                    <div class="event-banner" style="background: <?php echo $event['gradient']; ?>;"></div>
                    <div class="event-info">
                        <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                        <p> <?php echo $event['date']; ?> - <?php echo $event['time']; ?></p>
                        <p> <?php echo $event['location']; ?></p>
                        <p> <?php echo $event['places']; ?> places</p>
                        <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-primary">Voir plus</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
