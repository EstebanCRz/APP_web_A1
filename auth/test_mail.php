<?php
// Test d'envoi d'email avec PHPMailer/ZohoMail
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/send_zoho_mail.php';

$to = isset($_POST['email']) ? $_POST['email'] : 'votre@email.com'; // Prend l'adresse de la personne qui demande le reset
$subject = 'Test PHPMailer ZohoMail';
$body = '<h2>Ceci est un test</h2><p>Si tu reçois ce mail, la configuration SMTP fonctionne !</p>';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
    $result = sendZohoMail($to, $subject, $body, 'AmiGo', 'amigocontact@zohomail.eu');
    echo $result ? '<b>Mail envoyé avec succès !</b>' : '<b>Échec de l\'envoi du mail.</b>';
}

// Formulaire pour saisir l'adresse
?>
<form method="POST">
    <input type="email" name="email" placeholder="Votre email" required>
    <button type="submit">Tester l'envoi</button>
</form>
<?php
// ...existing code...
?>
