<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Mes Inscriptions - AmiGo";
$pageDescription = "Événements auxquels vous êtes inscrits";
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/profile.css"
];

include '../includes/header.php';
?>

<div class="container">
    <h2>Mes inscriptions</h2>
    <p>Événements auxquels vous êtes inscrit.</p>
    
    <div class="events-grid">
        <!-- TODO: Afficher les événements inscrits depuis la base de données -->
        <p>Aucune inscription pour le moment.</p>
        <a href="../events/events-list.php" class="btn btn-primary">Découvrir des événements</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
