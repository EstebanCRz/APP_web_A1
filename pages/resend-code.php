<?php
// Page pour renvoyer le code de confirmation d'inscription
require_once '../includes/config.php';
require_once '../pages/send-confirmation-mail.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    if (filter_var($email, FILTER_VALIDATE_EMAIL) && $prenom) {
        $code = random_int(100000, 999999);
        // Stocker le nouveau code en base
        $pdo = getDB();
        $stmt = $pdo->prepare('INSERT INTO email_confirmations (email, code, created_at, used) VALUES (?, ?, NOW(), 0)');
        $stmt->execute([$email, $code]);
        $mailSent = sendConfirmationMail($email, $prenom, $code);
        if ($mailSent) {
            $message = 'Un nouveau code a été envoyé à ' . htmlspecialchars($email) . '.<br><div style="background:#e0ffe0;padding:12px;margin:16px 0;border-radius:8px;color:#222;font-size:1.1em;text-align:center;">Code de confirmation pour debug : <b>' . $code . '</b></div>';
        } else {
            global $mail_error_debug;
            $message = 'Erreur lors de l\'envoi du mail.';
            if (!empty($mail_error_debug)) {
                $message .= '<br><pre style="color:red;">' . htmlspecialchars($mail_error_debug) . '</pre>';
            }
        }
    } else {
        $message = 'Email ou prénom invalide.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Renvoyer le code de confirmation - AmiGo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container" style="max-width:400px;margin:40px auto;">
    <h2>Renvoyer le code de confirmation</h2>
    <?php if ($message) { echo '<div style="margin-bottom:20px;">' . $message . '</div>'; } ?>
    <form method="post">
        <input type="email" name="email" placeholder="Ton email" required style="width:100%;margin-bottom:12px;">
        <input type="text" name="prenom" placeholder="Ton prénom" required style="width:100%;margin-bottom:12px;">
        <button type="submit" style="width:100%;background:#ff5a36;color:#fff;padding:10px 0;border:none;border-radius:6px;font-size:1.1em;">Renvoyer le code</button>
    </form>
</div>
</body>
</html>
