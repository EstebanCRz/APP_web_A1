<?php
// Page de test pour l'envoi du mail de confirmation d'inscription
require_once '../pages/send-confirmation-mail.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    if (filter_var($email, FILTER_VALIDATE_EMAIL) && $prenom) {
        $code = random_int(100000, 999999);
        if (sendConfirmationMail($email, $prenom, $code)) {
            $message = 'Mail de confirmation envoyé à ' . htmlspecialchars($email) . ' avec le code : <b>' . $code . '</b>';
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
    <title>Test mail de confirmation - AmiGo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container" style="max-width:400px;margin:40px auto;">
    <h2>Test mail de confirmation</h2>
    <?php if ($message) { echo '<div style="margin-bottom:20px;">' . $message . '</div>'; } ?>
    <form method="post">
        <input type="email" name="email" placeholder="Ton email" required style="width:100%;margin-bottom:12px;">
        <input type="text" name="prenom" placeholder="Ton prénom" required style="width:100%;margin-bottom:12px;">
        <button type="submit" style="width:100%;background:#ff5a36;color:#fff;padding:10px 0;border:none;border-radius:6px;font-size:1.1em;">Tester l'envoi</button>
    </form>
</div>
</body>
</html>
