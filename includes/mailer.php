<?php
/**
 * Configuration de l'envoi d'emails avec domaine personnalisé
 * Domaine: @amigo.fr
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Charger PHPMailer
require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';

/**
 * Envoyer un email
 * 
 * @param string $to Email du destinataire
 * @param string $subject Sujet de l'email
 * @param string $body Corps de l'email (HTML ou texte)
 * @param bool $isHTML true pour HTML, false pour texte brut
 * @return bool true si envoyé, false sinon
 */
function sendEmail($to, $subject, $body, $isHTML = true) {
    $mail = new PHPMailer(true);
    
    try {
        // Configuration du serveur SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.amigo.fr'; // Remplacer par votre serveur SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'noreply@amigo.fr'; // Votre adresse email complète
        $mail->Password = 'VOTRE_MOT_DE_PASSE_EMAIL'; // À définir dans config.php
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // ou ENCRYPTION_SMTPS pour port 465
        $mail->Port = 587; // 587 pour TLS, 465 pour SSL
        
        // Options supplémentaires
        $mail->SMTPDebug = 0; // 0 = pas de debug, 2 = debug complet
        $mail->Debugoutput = function($str, $level) {
            error_log("SMTP Debug level $level: $str");
        };
        
        // Expéditeur
        $mail->setFrom('noreply@amigo.fr', 'AmiGo - Plateforme d\'activités');
        $mail->addReplyTo('contact@amigo.fr', 'Support AmiGo');
        
        // Destinataire
        $mail->addAddress($to);
        
        // Contenu de l'email
        $mail->isHTML($isHTML);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body = $body;
        
        // Version texte brut (optionnel, pour les clients email qui ne supportent pas HTML)
        if ($isHTML) {
            $mail->AltBody = strip_tags($body);
        }
        
        // Envoyer
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Erreur lors de l'envoi d'email à $to: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Envoyer un email de bienvenue
 */
function sendWelcomeEmail($userEmail, $userName) {
    $subject = "Bienvenue sur AmiGo ! 🎉";
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #55D5E0 0%, #335F8A 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #fff; padding: 30px; border: 1px solid #e0e0e0; }
            .footer { background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; background: #F6B12D; color: #2F4558; padding: 12px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Bienvenue sur AmiGo !</h1>
            </div>
            <div class='content'>
                <p>Bonjour <strong>$userName</strong> ! 👋</p>
                <p>Nous sommes ravis de vous accueillir sur AmiGo, votre nouvelle plateforme pour découvrir et participer à des activités passionnantes !</p>
                <p>Avec AmiGo, vous pouvez :</p>
                <ul>
                    <li>🎉 Découvrir des activités près de chez vous</li>
                    <li>🤝 Rencontrer de nouvelles personnes</li>
                    <li>📅 Créer vos propres événements</li>
                    <li>🏆 Gagner des points et des badges</li>
                </ul>
                <center>
                    <a href='https://amigo.fr/events/events-list.php' class='button'>Découvrir les activités</a>
                </center>
            </div>
            <div class='footer'>
                <p>AmiGo - Connectez-vous, amusez-vous !</p>
                <p><a href='https://amigo.fr'>amigo.fr</a> | <a href='mailto:contact@amigo.fr'>contact@amigo.fr</a></p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($userEmail, $subject, $body, true);
}

/**
 * Envoyer un email de notification d'événement
 */
function sendEventNotification($userEmail, $userName, $eventTitle, $eventDate) {
    $subject = "Nouvel événement : $eventTitle";
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #55D5E0 0%, #335F8A 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #fff; padding: 30px; border: 1px solid #e0e0e0; }
            .button { display: inline-block; background: #F6B12D; color: #2F4558; padding: 12px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>📅 Nouvel événement disponible !</h1>
            </div>
            <div class='content'>
                <p>Bonjour $userName,</p>
                <p>Un nouvel événement qui pourrait vous intéresser vient d'être créé :</p>
                <h2>$eventTitle</h2>
                <p><strong>Date :</strong> $eventDate</p>
                <center>
                    <a href='https://amigo.fr/events/events-list.php' class='button'>Voir l'événement</a>
                </center>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($userEmail, $subject, $body, true);
}

/**
 * Envoyer un email de réinitialisation de mot de passe
 */
function sendPasswordResetEmail($userEmail, $userName, $resetToken) {
    $resetLink = "https://amigo.fr/auth/reset-password.php?token=$resetToken";
    $subject = "Réinitialisation de votre mot de passe AmiGo";
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #55D5E0 0%, #335F8A 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #fff; padding: 30px; border: 1px solid #e0e0e0; }
            .button { display: inline-block; background: #F6B12D; color: #2F4558; padding: 12px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; margin: 20px 0; }
            .warning { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🔐 Réinitialisation de mot de passe</h1>
            </div>
            <div class='content'>
                <p>Bonjour $userName,</p>
                <p>Vous avez demandé à réinitialiser votre mot de passe AmiGo.</p>
                <p>Cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe :</p>
                <center>
                    <a href='$resetLink' class='button'>Réinitialiser mon mot de passe</a>
                </center>
                <div class='warning'>
                    ⚠️ Ce lien est valide pendant 1 heure seulement.
                </div>
                <p>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($userEmail, $subject, $body, true);
}
