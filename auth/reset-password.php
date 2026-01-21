<?php
require_once '../includes/session.php';
require_once '../includes/config.php';
require_once '../includes/language.php';

date_default_timezone_set('Europe/Paris');

$token = $_GET['token'] ?? '';
$valid = false;
$email = '';
$error = '';
$success = false;

if ($token) {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT email, expires_at FROM password_resets WHERE token = ?');
    $stmt->execute([$token]);
    $row = $stmt->fetch();
    if (!$row) {
        echo '<div style="color:red;">Aucun token trouvé en base pour ce lien.</div>';
    } elseif (strtotime($row['expires_at']) <= time()) {
        echo '<div style="color:red;">Token trouvé mais expiré (expires_at=' . htmlspecialchars($row['expires_at']) . ')</div>';
    }
    if ($row && strtotime($row['expires_at']) > time()) {
        $valid = true;
        $email = $row['email'];
    } else {
        $error = "Lien invalide ou expiré.";
    }
}

if ($valid && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($password !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
        $stmt->execute([$hash, $email]);
        $stmt = $pdo->prepare('DELETE FROM password_resets WHERE email = ?');
        $stmt->execute([$email]);
        // Envoi du mail de confirmation
        require_once '../includes/send_zoho_mail.php';
        $confirmSubject = 'Votre mot de passe AmiGo a été modifié';
        $confirmBody = '<h2>Votre mot de passe a bien été changé</h2>'
            . '<p>Bonjour,</p>'
            . '<p>Nous vous confirmons que votre mot de passe AmiGo a été modifié avec succès.</p>'
            . '<p>Si vous n’êtes pas à l’origine de cette demande, contactez immédiatement le support.</p>'
            . '<br><div style="color:#888; font-size:0.95em; margin-top:2em;">© 2026 AmiGo - Tous droits réservés</div>';
        sendZohoMail($email, $confirmSubject, $confirmBody, 'AmiGo', 'amigocontact@zohomail.eu');
        $success = true;
    }
}

$pageTitle = 'Réinitialisation du mot de passe - AmiGo';
$pageDescription = $pageTitle;
$assetsDepth = 1;
$customCSS = ["../assets/css/style.css", "../assets/css/reset-password.css"];
include '../includes/header.php';
?>
<div class="container">
    <div class="form-container">
        <h2>Réinitialisation du mot de passe</h2>
        <?php if ($success): ?>
            <div class="alert alert-success">Votre mot de passe a été réinitialisé avec succès.<br><a href="login.php">Se connecter</a></div>
        <?php elseif ($valid): ?>
            <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="password">Nouveau mot de passe</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary">Réinitialiser</button>
            </form>
        <?php else: ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
