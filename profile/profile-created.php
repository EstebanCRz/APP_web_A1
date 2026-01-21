<?php
session_start();
require_once '../includes/language.php';
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
    <h2><?php echo t('pages.my_created_events'); ?></h2>
    <p><?php echo t('pages.created_events_desc'); ?></p>
    
    <div class="events-grid">
        <!-- TODO: Afficher les événements créés depuis la base de données -->
        <p><?php echo t('pages.no_created_events'); ?></p>
        <a href="../events/event-create.php" class="btn btn-primary"><?php echo t('pages.create_event'); ?></a>
    </div>
</div>

<?php include '../includes/footer.php';
