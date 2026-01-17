<?php
// Page de demande de réinitialisation de mot de passe avec stockage en base
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $code = random_int(100000, 999999);
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1h de validité
        $resetLink = BASE_URL . "/pages/reset-password.php?token=$token";

        // Stocker en base
        try {
            $pdo = getDB();
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, code, token, expires_at, used) VALUES (?, ?, ?, ?, 0)");
            $stmt->execute([$email, $code, $token, $expires]);
        } catch (Exception $e) {
            $message = "Erreur base de données : " . $e->getMessage();
        }

        // Envoi du mail de vérification avec code
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'mail.gmx.net';
            $mail->SMTPAuth = true;
            $mail->Username = 'amigo.c@gmx.fr';
            $mail->Password = 'AFDKHFLLWUE7MD4TZN7J';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            $mail->setFrom('amigo.c@gmx.fr', 'AmiGo');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Votre code de vérification AmiGo';
            $mail->Body = '<div style="text-align:center;"><img src="https://i.imgur.com/0yKXQbA.png" alt="AmiGo Logo" style="width:120px;"><h2>Votre code de vérification</h2><p style="font-size:2em;letter-spacing:6px;"><b>' . $code . '</b></p><p>Entrez ce code pour vérifier votre adresse e-mail.</p><p>Ou cliquez sur ce <a href="' . $resetLink . '">lien de réinitialisation</a>.</p></div>';
            $mail->AltBody = 'Votre code de vérification : ' . $code . "\nLien de réinitialisation : $resetLink";
            $mail->send();
            $message = "Un code de vérification a été envoyé à $email.";
        } catch (Exception $e) {
            $message = "Erreur lors de l'envoi du mail : " . $mail->ErrorInfo;
        }
    } else {
        $message = "Adresse e-mail invalide.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialiser le mot de passe</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Réinitialiser le mot de passe</h2>
        <?php if ($message): ?>
            <div class="alert"> <?php echo htmlspecialchars($message); ?> </div>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="email">Adresse e-mail</label>
            <input type="email" id="email" name="email" required>
            <button type="submit">Recevoir le code</button>
        </form>
    </div>
</body>
</html>
