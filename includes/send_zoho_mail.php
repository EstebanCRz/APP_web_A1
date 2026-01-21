<?php
// PHPMailer + ZohoMail SMTP configuration
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require_once __DIR__ . '/env.php';

function sendZohoMail($to, $subject, $body, $fromName = null, $fromEmail = null) {
    $fromName = $fromName ?? env('APP_NAME', 'AmiGo');
    $fromEmail = $fromEmail ?? env('MAIL_FROM', 'amigocontact@zohomail.eu');
    $mailFromAddress = env('MAIL_FROM', 'amigocontact@zohomail.eu');
    
    $mail = new PHPMailer(true);
    try {
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
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        echo '<div style="color:red; font-weight:bold;">Mailer Error: ' . $mail->ErrorInfo . '</div>';
        return false;
    }
}
