<?php
// PHPMailer + ZohoMail SMTP configuration
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Vérifier si le fichier autoload existe
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    error_log("Composer autoload not found. Please run: composer install");
    return false;
}

require $autoloadPath;
require_once __DIR__ . '/env.php';

function sendZohoMail($to, $subject, $body, $fromName = null, $fromEmail = null) {
    // Vérifier si PHPMailer est disponible
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $error = "PHPMailer not installed. Please run: composer install";
        error_log($error);
        throw new \Exception($error);
    }
    
    $fromName = $fromName ?? env('APP_NAME', 'AmiGo');
    $fromEmail = $fromEmail ?? env('MAIL_FROM', 'amigocontact@zohomail.eu');
    $mailFromAddress = env('MAIL_FROM', 'amigocontact@zohomail.eu');
    
    $mail = new PHPMailer(true);
    
    //Server settings
    $mail->isSMTP();
    $mail->Host       = env('MAIL_HOST', 'smtp.zoho.eu');
    $mail->SMTPAuth   = true;
    $mail->Username   = env('MAIL_USERNAME', 'amigocontact@zohomail.eu');
    $mail->Password   = env('MAIL_PASSWORD', '');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = env('MAIL_PORT', 587);
    $mail->CharSet    = 'UTF-8';

    // Expéditeur = TOUJOURS l'adresse configurée dans .env (obligatoire Zoho)
    $mail->setFrom($mailFromAddress, $fromName);
    // Destinataire = $to (utilisateur ou admin selon usage)
    $mail->addAddress($to);
    // Répondre à = email de l'utilisateur (pour répondre facilement)
    if ($fromEmail !== $mailFromAddress) {
        $mail->addReplyTo($fromEmail, $fromName);
    }

    //Content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $body;

    $mail->send();
    return true;
}
