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
$name = $email = $subject = $message = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && $email && $subject && $message) {
        require_once '../vendor/autoload.php';
        
        $gmxEmail = 'amigo.c@gmx.fr';
        // ⚠️ Utilisez le mot de passe généré pour "MAILAMIGO2" (votre capture indique qu'il n'est pas encore utilisé)
        $gmxAppPassword = 'VOTRE_MOT_DE_PASSE_MAILAMIGO2'; 
        
        $mail = new PHPMailer(true);
        
        try {
            // --- Configuration SMTP (Basée sur vos captures d'écran) ---
            $mail->isSMTP();
            $mail->Host       = 'mail.gmx.com'; // Confirmé par votre capture
            $mail->SMTPAuth   = true;
            $mail->Username   = $gmxEmail;
            $mail->Password   = $gmxAppPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';
            
            // Options pour éviter les échecs SSL sur serveur local
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            // --- Configuration de l'identité ---
            // L'expéditeur DOIT être l'adresse GMX pour passer l'authentification
            $mail->setFrom($gmxEmail, 'Formulaire de Contact AmiGo');
            $mail->addAddress($gmxEmail, 'AmiGo Admin'); 
            
            // L'adresse de l'utilisateur est mise en "Reply-To" pour pouvoir lui répondre
            $mail->addReplyTo($email, $name);

            // --- Contenu ---
            $mail->isHTML(true);
            $mail->Subject = "Contact AmiGo : " . $subject;
            $mail->Body    = "<h3>Nouveau message de contact</h3>
                              <b>Nom :</b> {$name}<br>
                              <b>Email :</b> {$email}<br>
                              <b>Message :</b><br>" . nl2br(htmlspecialchars($message));
            
            $mail->AltBody = "Nom: $name\nEmail: $email\nMessage: $message";

            $mail->send();
            $success = true;
            $name = $email = $subject = $message = ''; // Reset
            
        } catch (Exception $e) {
            $error = "Erreur technique : " . $mail->ErrorInfo;
        }
    } else {
        $error = "Tous les champs sont obligatoires.";
    }
}

include '../includes/header.php';
?>

<div class="container" style="max-width:600px; margin:40px auto; padding:20px;">
    <h2><?php echo t('pages.contact_us'); ?></h2>
    <p><?php echo t('pages.contact_subtitle'); ?></p>

    <?php if ($success): ?>
        <div style="background:#d4edda; color:#155724; padding:15px; border-radius:8px; border:1px solid #c3e6cb; margin-bottom:20px;">
            ✅ Votre message a bien été envoyé !
        </div>
    <?php elseif ($error): ?>
        <div style="background:#f8d7da; color:#721c24; padding:15px; border-radius:8px; border:1px solid #f5c6cb; margin-bottom:20px;">
            ❌ <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div style="margin-bottom:15px;">
            <label style="display:block; font-weight:bold;"><?php echo t('pages.contact_form_name'); ?></label>
            <input type="text" name="name" required value="<?php echo htmlspecialchars($name); ?>" style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
        </div>
        <div style="margin-bottom:15px;">
            <label style="display:block; font-weight:bold;"><?php echo t('pages.contact_form_email'); ?></label>
            <input type="email" name="email" required value="<?php echo htmlspecialchars($email); ?>" style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
        </div>
        <div style="margin-bottom:15px;">
            <label style="display:block; font-weight:bold;"><?php echo t('pages.contact_form_subject'); ?></label>
            <input type="text" name="subject" required value="<?php echo htmlspecialchars($subject); ?>" style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
        </div>
        <div style="margin-bottom:15px;">
            <label style="display:block; font-weight:bold;"><?php echo t('pages.contact_form_message'); ?></label>
            <textarea name="message" rows="6" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;"><?php echo htmlspecialchars($message); ?></textarea>
        </div>
        <button type="submit" style="background:#ff5a36; color:white; padding:12px; border:none; border-radius:6px; font-weight:bold; cursor:pointer; width:100%;">
            <?php echo t('pages.contact_form_send'); ?>
        </button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>