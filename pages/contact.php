<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once '../includes/language.php';
require_once '../includes/config.php';

$pageTitle = t('pages.contact_us') . " - AmiGo";
$pageDescription = t('pages.contact_us');
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/contact.css"
];

// Suppression de l'envoi d'email et du traitement serveur
// Le formulaire ci-dessous est conservé pour l'affichage uniquement.

include '../includes/header.php';
?>

<div class="container">
    <h2><?php echo t('pages.contact_us'); ?></h2>
    <p><?php echo t('pages.contact_subtitle'); ?></p>
    
    
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="name"><?php echo t('pages.contact_form_name'); ?></label>
            <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($name ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="email"><?php echo t('pages.contact_form_email'); ?></label>
            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="subject"><?php echo t('pages.contact_form_subject'); ?></label>
            <input type="text" id="subject" name="subject" required value="<?php echo htmlspecialchars($subject ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="message"><?php echo t('pages.contact_form_message'); ?></label>
            <textarea id="message" name="message" rows="6" required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><?php echo t('pages.contact_form_send'); ?></button>
    </form>
    
    <div class="contact-info">
        <h3><?php echo t('pages.contact_info_title'); ?></h3>
        <p><?php echo t('pages.contact_email'); ?> : contact@amigo.fr</p>
        <p><?php echo t('pages.contact_phone'); ?> : +33 1 23 45 67 89</p>
        <p><?php echo t('pages.contact_address'); ?> : 123 Rue de Paris, 75001 Paris</p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
