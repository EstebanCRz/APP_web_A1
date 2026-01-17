<?php
// Page de réinitialisation du mot de passe avec envoi de mail local (MailHog)
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Générer un token unique (à stocker en base pour un vrai site)
        $token = bin2hex(random_bytes(32));
        $resetLink = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset-password.php?token=$token";

        // Envoi du mail local via MailHog
        require_once '../vendor/autoload.php';
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'localhost';
            $mail->Port = 1025;
            $mail->SMTPAuth = false;
            $mail->SMTPSecure = false;

            $mail->setFrom('no-reply@local.dev', 'AmiGo');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Réinitialisation de votre mot de passe';
            $mail->Body = "Bonjour,<br>Pour réinitialiser votre mot de passe, cliquez sur ce lien : <a href='$resetLink'>$resetLink</a>";

            $mail->send();
            $message = "Si l'adresse existe, un lien de réinitialisation a été envoyé à $email.";
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
            <button type="submit">Envoyer le lien</button>
        </form>
    </div>
</body>
</html>
