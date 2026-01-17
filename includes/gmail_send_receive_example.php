<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
<?php
// Active le typage strict (optionnel mais propre)
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Chemin vers l'autoload de Composer
require __DIR__ . '/../vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // -------- DEBUG (à désactiver en prod) --------
    $mail->SMTPDebug  = 0;          // 0 en prod
    $mail->Debugoutput = 'html';

    // -------- CONFIG SMTP OUTLOOK --------
    $mail->isSMTP();
    // Serveur SMTP pour outlook.com / outlook.fr / hotmail / live
    $mail->Host       = 'smtp-mail.outlook.com';
    $mail->SMTPAuth   = true;

    // ⚠️ Mets ici TON adresse Outlook complète
    $mail->Username   = 'Amigo-Sup-Team@outlook.fr';

    // ⚠️ Mets ici TON mot de passe Outlook (change-le si tu l’as déjà exposé)
    $mail->Password   = 'fdsheykvoqjyuunx';

    // Sécurité et port
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS
    $mail->Port       = 587;

    // -------- EXPÉDITEUR / DESTINATAIRE --------
    $mail->setFrom('Amigo-Sup-Team@outlook.fr', 'Amigo Sup Team');

    // Mets une vraie adresse à toi pour tester
    $mail->addAddress('nocartewen@gmail.com', 'Moi');

    // -------- CONTENU DU MAIL --------
    $mail->isHTML(true);
    $mail->Subject = 'Test PHPMailer via Outlook';
    $mail->Body    = 'Ceci est un <b>test</b> d\'envoi réel avec PHPMailer et Outlook.';
    $mail->AltBody = 'Ceci est un test d\'envoi réel avec PHPMailer et Outlook.';

    // -------- ENVOI --------
    $mail->send();
    echo 'Message envoyé avec succès via Outlook !';

} catch (Exception $e) {
    echo "Erreur d'envoi : {$mail->ErrorInfo}";
}
// Fichier supprimé
