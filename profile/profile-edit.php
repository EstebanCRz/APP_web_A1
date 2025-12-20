<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once '../includes/language.php';

$pageTitle = t('pages.edit_profile') . " - AmiGo";
$pageDescription = t('pages.edit_profile');
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/profile.css"
];

include '../includes/header.php';
?>

<div class="container">
    <h2><?php echo t('pages.edit_profile'); ?></h2>
    <form method="POST" class="profile-form">
        <div class="form-group">
            <label for="name"><?php echo t('pages.full_name'); ?></label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="email"><?php echo t('pages.contact_form_email'); ?></label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="bio"><?php echo t('pages.bio'); ?></label>
            <textarea id="bio" name="bio" rows="4"></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="profile.php" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
