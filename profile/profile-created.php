<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Mes Événements Créés - AmiGo";
$pageDescription = "Événements que vous avez créés";
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/profile.css"
];

include '../includes/header.php';
?>

<div class="container">
    <h2>Mes événements créés</h2>
    <p>Voici la liste des événements que vous avez créés.</p>
    
    <div class="events-grid">
        <!-- TODO: Afficher les événements créés depuis la base de données -->
        <p>Aucun événement créé pour le moment.</p>
        <a href="../events/event-create.php" class="btn btn-primary">Créer un événement</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
