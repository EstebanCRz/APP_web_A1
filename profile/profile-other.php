<?php
session_start();
require_once '../includes/language.php';
header('Content-Type: text/html; charset=UTF-8');

$other_user_id = $_GET['id'] ?? 1;
$pageTitle = "Profil Utilisateur - AmiGo";
$pageDescription = "Voir le profil d'un autre utilisateur";
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/profile.css"
];

include '../includes/header.php';
?>

<div class="container">
    <h2><?php echo t('pages.user_profile'); ?></h2>
    <p><?php echo t('pages.user_info'); ?><?php echo htmlspecialchars((string)$other_user_id, ENT_QUOTES, 'UTF-8'); ?></p>
    
    <div class="user-info">
        <!-- TODO: Afficher les informations de l'utilisateur depuis la base de données -->
        <p><?php echo t('pages.user_name'); ?> Utilisateur</p>
        <p><?php echo t('pages.user_bio'); ?> ...</p>
    </div>
    
    <h3><?php echo t('pages.user_created_events'); ?></h3>
    <div class="events-grid">
        <!-- TODO: Afficher les événements créés par cet utilisateur -->
        <p><?php echo t('pages.no_user_events'); ?></p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
