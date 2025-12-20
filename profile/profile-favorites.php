<?php
session_start();
require_once '../includes/language.php';
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
    <h2><?php echo t('pages.my_favorites'); ?></h2>
    <p><?php echo t('pages.no_favorites'); ?></p>
    
    <div class="events-grid">
        <!-- TODO: Afficher les événements favoris depuis la base de données -->
        <a href="../events/events-list.php" class="btn btn-primary"><?php echo t('pages.discover_events'); ?></a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

