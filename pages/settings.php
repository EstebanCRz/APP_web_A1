<?php
session_start();
require_once '../includes/language.php';
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Paramètres - AmiGo";
$pageDescription = "Gérez vos paramètres AmiGo";
$assetsDepth = 1;
$customCSS = "../assets/css/index.css";

include '../includes/header.php';
?>

<div class="container">
    <h2><?php echo t('pages.settings'); ?></h2>
    
    <section>
        <h3><?php echo t('pages.notifications'); ?></h3>
        <form method="POST" action="">
            <div class="form-group">
                <label for="language"><?php echo t('pages.language_preference'); ?></label>
                <select id="language" name="language" class="language-selector">
                    <option value="fr">Français</option>
                    <option value="en">English</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="notifications"><?php echo t('pages.receive_email_notifications'); ?></label>
                <input type="checkbox" id="notifications" name="notifications" checked>
            </div>
            
            <button type="submit" class="btn btn-primary"><?php echo t('pages.save_settings'); ?></button>
        </form>
    </section>
</div>

<?php include '../includes/footer.php'; ?>
