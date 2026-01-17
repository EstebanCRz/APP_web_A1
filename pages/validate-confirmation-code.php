<?php
// Page pour valider le code reçu par mail et activer l'inscription
require_once '../includes/config.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $code = $_POST['code'] ?? '';
    if (filter_var($email, FILTER_VALIDATE_EMAIL) && preg_match('/^[0-9]{6}$/', $code)) {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT * FROM email_confirmations WHERE email = ? AND code = ? AND used = 0 AND created_at > (NOW() - INTERVAL 15 MINUTE)');
        $stmt->execute([$email, $code]);
        $row = $stmt->fetch();
        if ($row) {
            // Marquer le code comme utilisé
            $pdo->prepare('UPDATE email_confirmations SET used = 1 WHERE id = ?')->execute([$row['id']]);
            // Activer le compte utilisateur
            $pdo->prepare('UPDATE users SET is_active = 1 WHERE email = ?')->execute([$email]);
            // Connexion automatique
            $stmt = $pdo->prepare('SELECT id, first_name, last_name FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $email;
                $_SESSION['user_first_name'] = $user['first_name'];
                $_SESSION['user_last_name'] = $user['last_name'];
            }
            $message = 'Ton email est confirmé ! Inscription validée.';
            // Rediriger vers la page de sélection des centres d'intérêt
            header('Refresh:2; url=../profile/choose-interests.php');
        } else {
            $message = 'Code invalide, expiré ou déjà utilisé.';
        }
    } else {
        $message = 'Email ou code invalide.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Valider le code - AmiGo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container" style="max-width:400px;margin:40px auto;">
    <h2>Valider le code reçu</h2>
    <?php if ($message) { echo '<div style="margin-bottom:20px;">' . $message . '</div>'; } ?>
    <form method="post">
        <input type="email" name="email" placeholder="Ton email" required style="width:100%;margin-bottom:12px;">
        <input type="text" name="code" placeholder="Code reçu (6 chiffres)" required style="width:100%;margin-bottom:12px;">
        <button type="submit" style="width:100%;background:#ff5a36;color:#fff;padding:10px 0;border:none;border-radius:6px;font-size:1.1em;">Valider le code</button>
    </form>
</div>
</body>
</html>
