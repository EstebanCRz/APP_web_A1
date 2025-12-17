<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Liste d'attente - AmiGo";
$pageDescription = "Événements en liste d'attente";
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/profile.css"
];

include '../includes/header.php';
?>

<div class="container">
    <h2>Liste d'attente</h2>
    <p>Événements pour lesquels vous êtes en liste d'attente.</p>
    
    <div class="events-grid">
        <!-- TODO: Afficher les événements en liste d'attente depuis la base de données -->
        <p>Aucune liste d'attente pour le moment.</p>
        <a href="../events/events-list.php" class="btn btn-primary">Découvrir des événements</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
