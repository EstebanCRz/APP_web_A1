<?php
// contact.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once '../includes/language.php';
require_once '../includes/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$pageTitle = t('pages.contact_us') . " - AmiGo";
$pageDescription = t('pages.contact_us');
$assetsDepth = 1;
$customCSS = ["../assets/css/style.css", "css/contact.css"];

// Traitement de l'envoi
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && $email && $subject && $message) {
        require_once '../includes/send_zoho_mail.php';
        $adminEmail = 'amigo.contact@zohomail.com'; // À personnaliser
        $mailSubject = "Contact AmiGo : " . $subject;
        $mailBody = "<h3>Nouveau message de contact</h3>"
                  . "<b>Nom :</b> " . htmlspecialchars($name) . "<br>"
                  . "<b>Email :</b> " . htmlspecialchars($email) . "<br>"
                  . "<b>Message :</b><br>" . nl2br(htmlspecialchars($message));

        // Envoi via ZohoMail
        try {
            $sent = @sendZohoMail($adminEmail, $mailSubject, $mailBody, $name, $email);
            if ($sent) {
                $success = true;
            } else {
                $error = "Erreur lors de l'envoi du message. Veuillez réessayer plus tard.";
            }
        } catch (Throwable $e) {
            error_log("Erreur envoi email contact: " . $e->getMessage());
            $error = "Erreur lors de l'envoi du message. Veuillez réessayer plus tard.";
        }
    } else {
        $error = "Tous les champs sont obligatoires.";
    }
}

include '../includes/header.php';
?>

<div class="container">
    <h2><?php echo t('pages.contact_us'); ?></h2>
    <p><?php echo t('pages.contact_subtitle'); ?></p>

    <?php if ($success): ?>
        <div class="alert alert-success" style="color:green; background:#eaffea; padding:15px; border-radius:8px;">
            Votre message a bien été envoyé !
        </div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger" style="color:red; background:#ffeaea; padding:15px; border-radius:8px;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="name"><?php echo t('pages.contact_form_name'); ?></label>
            <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($name ?? ''); ?>" style="width:100%; margin-bottom:10px;">
        </div>
        <div class="form-group">
            <label for="email"><?php echo t('pages.contact_form_email'); ?></label>
            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>" style="width:100%; margin-bottom:10px;">
        </div>
        <div class="form-group">
            <label for="subject"><?php echo t('pages.contact_form_subject'); ?></label>
            <input type="text" id="subject" name="subject" required value="<?php echo htmlspecialchars($subject ?? ''); ?>" style="width:100%; margin-bottom:10px;">
        </div>
        <div class="form-group">
            <label for="message"><?php echo t('pages.contact_form_message'); ?></label>
            <textarea id="message" name="message" rows="6" required style="width:100%; margin-bottom:10px;"><?php echo htmlspecialchars($message ?? ''); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary" style="background:#ff5a36; color:white; padding:10px 20px; border:none; border-radius:6px; cursor:pointer;">
            <?php echo t('pages.contact_form_send'); ?>
        </button>
    </form>
    
    <div class="contact-info" style="margin-top:30px; border-top:1px solid #eee; padding-top:20px;">
        <h3><?php echo t('pages.contact_info_title'); ?></h3>
        <p>Email : amigo.contact@zohomail.com</p>
    </div>
</div>

<?php include '../includes/footer.php';
