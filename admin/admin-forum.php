<?php
require_once '../includes/session.php';
require_once '../includes/config.php';
require_once '../includes/admin_functions.php';

// Vérifier que l'utilisateur est admin
requireAdmin();

$pageTitle = "Gestion du Forum - Admin";
$assetsDepth = 1;
$customCSS = ["css/admin-dashboard.css"];

include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1> Gestion du Forum</h1>
    </div>

    <div class="admin-nav">
        <a href="admin-dashboard.php"> Dashboard</a>
        <a href="admin-users.php"> Utilisateurs</a>
        <a href="admin-events.php"> Événements</a>
        <a href="admin-forum.php" class="active"> Forum</a>
        <a href="admin-messages.php"> Messages</a>
        <a href="admin-content.php"> Contenu</a>
    </div>

    <div class="admin-section">
        <h2>Gestion du forum</h2>
        <p>Cette section est en cours de développement.</p>
    </div>
</div>

<?php include '../includes/footer.php';