<?php
// Exemple d'envoi d'e-mail avec PHPMailer et Gmail SMTP
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Chemin vers autoload PHPMailer

$mail = new PHPMailer(true);

try {
    // Paramètres SMTP Gmail
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'amigoocontact@gmail.com'; // Adresse Gmail
    $mail->Password = 'Azerty123,'; // Mot de passe principal (non recommandé, préférez un mot de passe d'application)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Expéditeur et destinataire
    $mail->setFrom('amigoocontact@gmail.com', 'AmiGoo Contact');
    // À remplacer par les données du formulaire de contact
    $userEmail = 'utilisateur@example.com'; // Récupérez cette valeur depuis le formulaire
    $userName = 'Nom Utilisateur'; // Récupérez cette valeur depuis le formulaire
    $mail->addAddress($userEmail, $userName);

    // Contenu du mail
    $mail->isHTML(true);
    $mail->Subject = 'Test SMTP Gmail';
    $mail->Body    = 'Ceci est un <b>test</b> d\'envoi via Gmail SMTP.';
    $mail->AltBody = 'Ceci est un test d\'envoi via Gmail SMTP.';

    $mail->send();
    echo 'Message envoyé avec succès';
} catch (Exception $e) {
    echo "Erreur lors de l'envoi du message : {$mail->ErrorInfo}";
}
