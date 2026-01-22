<?php
require_once '../includes/session.php';
require_once '../includes/config.php';
require_once '../includes/admin_functions.php';

// VÃ©rifier que l'utilisateur est admin
requireAdmin();

$pageTitle = "Gestion des Messages - Admin";
$assetsDepth = 1;
$customCSS = ["css/admin-dashboard.css"];

include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>âœ‰ï¸ Gestion des Messages</h1>
    </div>

    <div class="admin-nav">
        <a href="admin-dashboard.php">ðŸ“Š Dashboard</a>
        <a href="admin-users.php">ðŸ‘¥ Utilisateurs</a>
        <a href="admin-events.php">ðŸŽ‰ Ã‰vÃ©nements</a>
        <a href="admin-forum.php">ðŸ’¬ Forum</a>
        <a href="admin-messages.php" class="active">âœ‰ï¸ Messages</a>
        <a href="admin-content.php">ðŸ“ Contenu</a>
    </div>

    <div class="admin-section">
        <h2>Messages de contact</h2>
        <p>Cette section est en cours de dÃ©veloppement.</p>
    </div>
</div>

<?php include '../includes/footer.php';