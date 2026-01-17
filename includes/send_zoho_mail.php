<?php
// PHPMailer + ZohoMail SMTP configuration example
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

function sendZohoMail($to, $subject, $body, $fromName = 'AmiGo', $fromEmail = 'amigocontact@zohomail.eu') {
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.zoho.eu';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'amigocontact@zohomail.eu'; // Adresse ZohoMail
        $mail->Password   = '74KV4H9wuzup'; // Mot de passe d'application ZohoMail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Expéditeur = TOUJOURS amigocontact@zohomail.eu (obligatoire Zoho)
        $mail->setFrom('amigocontact@zohomail.eu', 'AmiGo');
        // Destinataire = amigocontact@zohomail.eu (réception admin)
        $mail->addAddress('amigocontact@zohomail.eu');
        // Répondre à = email de l'utilisateur (pour répondre facilement)
        if ($fromEmail !== 'amigocontact@zohomail.eu') {
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
