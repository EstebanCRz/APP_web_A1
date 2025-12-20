<?php
session_start();
require_once '../includes/language.php';
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
    <h2><?php echo t('pages.my_registrations'); ?></h2>
    <p><?php echo t('pages.registered_events_desc'); ?></p>
    
    <div class="events-grid">
        <!-- TODO: Afficher les événements inscrits depuis la base de données -->
        <p><?php echo t('pages.no_registrations'); ?></p>
        <a href="../events/events-list.php" class="btn btn-primary"><?php echo t('pages.discover_events'); ?></a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

