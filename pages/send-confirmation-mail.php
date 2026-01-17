<?php
require_once '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fonction d'envoi du mail de confirmation d'inscription
function sendConfirmationMail($email, $prenom, $code) {
    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = 2; // Affiche les logs SMTP dans l'output (pour debug local)
        $mail->isSMTP();
        $mail->Host       = 'mail.gmx.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'amigo.c@gmx.fr';
        $mail->Password   = 'AFDKHFLLWUE7MD4TZN7J';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->setFrom('amigo.c@gmx.fr', 'AmiGo');
        $mail->addAddress($email);
        $mail->addReplyTo('amigo.c@gmx.fr', 'Support AmiGo');
        $mail->isHTML(true);
        $mail->Subject = 'Confirme ton inscription à AmiGo';
        $mail->Body = '<div style="max-width:480px;margin:40px auto;background:#fff;border-radius:16px;box-shadow:0 4px 24px #0001;padding:32px 24px 24px 24px;text-align:center;font-family:Segoe UI,Arial,sans-serif;">'
            . '<img src="https://raw.githubusercontent.com/nocart/amigo-assets/main/logo-amigo.png" alt="AmiGo Logo" style="width:90px;margin-bottom:16px;border-radius:50%;box-shadow:0 2px 8px #0002;">'
            . '<h2 style="color:#1a1a1a;font-size:2em;margin:0 0 12px 0;">Bienvenue, ' . htmlspecialchars($prenom) . ' !</h2>'
            . '<p style="color:#444;font-size:1.1em;margin:0 0 24px 0;">Pour activer ton compte AmiGo, saisis ce code de confirmation :</p>'
            . '<div style="font-size:2.2em;letter-spacing:0.3em;font-weight:bold;background:#f4f6fb;padding:16px 0;margin:0 0 24px 0;border-radius:8px;color:#ff5a36;">' . $code . '</div>'
            . '<p style="color:#888;font-size:1em;">Ce code est valable 15 minutes.</p>'
            . '<div style="margin-top:32px;color:#aaa;font-size:0.95em;">© ' . date('Y') . ' AmiGo. Tous droits réservés.</div>'
            . '</div>';
        $mail->AltBody = "Bienvenue, $prenom ! Pour activer ton compte AmiGo, saisis ce code : $code";
        $mail->send();
        return true;
    } catch (Exception $e) {
        global $mail_error_debug;
        $mail_error_debug = $mail->ErrorInfo . ' | Exception: ' . $e->getMessage();
        echo '<div style="background:#fee2e2;color:#991b1b;padding:16px;margin:24px 0;border-radius:8px;font-size:1.1em;">Erreur PHPMailer : ' . htmlspecialchars($mail_error_debug) . '</div>';
        return false;
    }
}