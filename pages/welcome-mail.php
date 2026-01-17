<?php
// Fonction d'envoi du mail de bienvenue (à appeler lors de l'inscription)
function sendWelcomeMail($email, $prenom) {
    require __DIR__ . '/../vendor/autoload.php';
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'mail.gmx.net';
        $mail->SMTPAuth = true;
        $mail->Username = 'amigo.c@gmx.fr';
        $mail->Password = 'AFDKHFLLWUE7MD4TZN7J';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
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
                $mail->Subject = 'Bienvenue sur AmiGo !';
                $mail->Body = '
<body style="margin:0;padding:0;background:#f4f6fb;font-family:Segoe UI,Arial,sans-serif;">
    <div style="max-width:480px;margin:40px auto;background:#fff;border-radius:16px;box-shadow:0 4px 24px #0001;padding:32px 24px 24px 24px;text-align:center;">
        <img src="https://i.imgur.com/1Q9Z1ZB.png" alt="AmiGo Logo" style="width:120px;margin-bottom:10px;border-radius:16px;box-shadow:0 2px 8px #0002;">
        <div style="font-size:1.2em;color:#444;margin-bottom:8px;">' . htmlspecialchars($prenom) . '</div>
        <h2 style="color:#1a1a1a;font-size:2em;margin:0 0 12px 0;letter-spacing:0.5px;">Bienvenue sur AmiGo !</h2>
        <p style="color:#444;font-size:1.1em;margin:0 0 24px 0;">Merci de rejoindre <b>AmiGo</b>.<br>Nous sommes ravis de t\'accueillir dans la communauté !</p>
        <a href="https://amigo.cleverapps.io/" style="display:inline-block;padding:12px 32px;background:#ff5a36;color:#fff;text-decoration:none;font-weight:bold;border-radius:8px;font-size:1.1em;box-shadow:0 2px 8px #ff5a3622;transition:background 0.2s;">Découvrir AmiGo</a>
        <div style="margin-top:32px;color:#aaa;font-size:0.95em;">© ' . date('Y') . ' AmiGo. Tous droits réservés.</div>
    </div>
</body>';
                $mail->AltBody = 'Bienvenue, ' . $prenom . ' ! Merci de rejoindre AmiGo. Découvrir AmiGo : https://amigo.cleverapps.io/';
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Exemple d'utilisation (formulaire de test)
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    if (filter_var($email, FILTER_VALIDATE_EMAIL) && $prenom) {
        if (sendWelcomeMail($email, $prenom)) {
            $message = "Mail de bienvenue envoyé à $email.";
        } else {
            $message = "Erreur lors de l'envoi du mail de bienvenue.";
        }
    } else {
        $message = "Adresse e-mail ou prénom invalide.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mail de bienvenue AmiGo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Envoyer un mail de bienvenue</h2>
        <?php if ($message): ?>
            <div class="alert"> <?php echo htmlspecialchars($message); ?> </div>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="prenom">Prénom</label>
            <input type="text" id="prenom" name="prenom" required>
            <label for="email">Adresse e-mail</label>
            <input type="email" id="email" name="email" required>
            <button type="submit">Envoyer le mail de bienvenue</button>
        </form>
    </div>
</body>
</html>
