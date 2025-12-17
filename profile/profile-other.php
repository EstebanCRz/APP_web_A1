<?php
session_start();
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
    <h2>Profil de l'utilisateur</h2>
    <p>Informations sur l'utilisateur #<?php echo htmlspecialchars((string)$other_user_id, ENT_QUOTES, 'UTF-8'); ?></p>
    
    <div class="user-info">
        <!-- TODO: Afficher les informations de l'utilisateur depuis la base de données -->
        <p>Nom: Utilisateur</p>
        <p>Bio: ...</p>
    </div>
    
    <h3>Événements créés</h3>
    <div class="events-grid">
        <!-- TODO: Afficher les événements créés par cet utilisateur -->
        <p>Aucun événement créé.</p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
