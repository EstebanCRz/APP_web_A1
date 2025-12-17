<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Éditer le profil - AmiGo";
$pageDescription = "Modifiez vos informations de profil";
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/profile.css"
];

include '../includes/header.php';
?>

<div class="container">
    <h2>Éditer mon profil</h2>
    <form method="POST" class="profile-form">
        <div class="form-group">
            <label for="name">Nom complet</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="bio">Bio</label>
            <textarea id="bio" name="bio" rows="4"></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="profile.php" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
