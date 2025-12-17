<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Mes Favoris - AmiGo";
$pageDescription = "Événements favoris";
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/profile.css"
];

include '../includes/header.php';
?>

<div class="container">
    <h2>Mes favoris</h2>
    <p>Événements que vous avez ajoutés à vos favoris.</p>
    
    <div class="events-grid">
        <!-- TODO: Afficher les événements favoris depuis la base de données -->
        <p>Aucun favori pour le moment.</p>
        <a href="../events/events-list.php" class="btn btn-primary">Découvrir des événements</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
