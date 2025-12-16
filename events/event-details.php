<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$event_id = $_GET['id'] ?? 1;
$event = [
    'id' => $event_id, 'title' => 'Concert Rock en plein air', 'date' => '25/11/2025', 'time' => '20h00',
    'location' => 'Paris, France', 'address' => '123 Rue de la Musique, 75001 Paris',
    'places' => 50, 'places_taken' => 12, 'price' => 'Gratuit',
    'description' => 'Rejoignez-nous pour une soirée musicale inoubliable !',
    'organizer' => 'Marie Dubois', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
];

$pageTitle = htmlspecialchars($event['title']) . " - AmiGo";
$pageDescription = "Détails de l'événement";
$assetsDepth = 1;
$customCSS = "css/event-details.css";

include '../includes/header.php';
?>

<div class="container">
    <div class="event-header">
        <div class="event-header-inner">
            <h2><?php echo htmlspecialchars($event['title']); ?></h2>
            <p class="event-sub"><?php echo htmlspecialchars($event['location']); ?> — <?php echo htmlspecialchars($event['date']); ?> à <?php echo htmlspecialchars($event['time']); ?></p>
        </div>
    </div>
    <div class="event-details-container">
        <div class="event-main-info card">
            <div class="info-section">
                <h3> Date et heure</h3>
                <p><?php echo $event['date']; ?> à <?php echo $event['time']; ?></p>
            </div>
            <div class="info-section">
                <h3> Lieu</h3>
                <p><?php echo htmlspecialchars($event['location']); ?></p>
                <p><?php echo htmlspecialchars($event['address']); ?></p>
            </div>
            <div class="info-section">
                <h3> Participants</h3>
                <p><?php echo $event['places_taken']; ?> / <?php echo $event['places']; ?> places</p>
            </div>
            <div class="info-section">
                <h3> Prix</h3>
                <p><?php echo $event['price']; ?></p>
            </div>
            <div class="info-section">
                <h3> Description</h3>
                <p><?php echo htmlspecialchars($event['description']); ?></p>
            </div>
            <div class="info-section">
                <h3> Organisateur</h3>
                <p><?php echo htmlspecialchars($event['organizer']); ?></p>
            </div>
        </div>
        <aside class="event-actions card">
            <?php if (isset($_SESSION['user_id'])): ?>
                <button class="btn btn-primary btn-block">S'inscrire</button>
                <button class="btn btn-secondary btn-block">Ajouter aux favoris</button>
            <?php else: ?>
                <p class="muted">Connectez-vous pour participer</p>
                <a href="../auth/login.php" class="btn btn-primary btn-block">Connexion</a>
            <?php endif; ?>
            <div class="action-meta">
                <p><strong>Places :</strong> <?php echo $event['places_taken']; ?> / <?php echo $event['places']; ?></p>
                <p><strong>Prix :</strong> <?php echo htmlspecialchars($event['price']); ?></p>
            </div>
        </aside>
    </div>
    <a href="events-list.php"> Retour</a>
</div>

<?php include '../includes/footer.php'; ?>
