<?php
// Page de vérification du code et réinitialisation du mot de passe (avec base de données)
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../includes/config.php';

$message = '';
$showForm = true;

$token = $_GET['token'] ?? '';
if (!$token) {
    $message = "Lien de réinitialisation invalide.";
    $showForm = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if (!$code || !$password || !$confirm) {
        $message = "Tous les champs sont obligatoires.";
    } elseif ($password !== $confirm) {
        $message = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifier code/token en base
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND code = ? AND used = 0 AND expires_at > NOW()");
        $stmt->execute([$token, $code]);
        $reset = $stmt->fetch();
        if (!$reset) {
            $message = "Code ou lien invalide ou expiré.";
        } else {
            // Mettre à jour le mot de passe de l'utilisateur (table users)
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([
                password_hash($password, PASSWORD_DEFAULT),
                $reset['email']
            ]);

            // Marquer le reset comme utilisé
            $pdo->prepare("UPDATE password_resets SET used = 1 WHERE id = ?")->execute([$reset['id']]);
            $message = "Votre mot de passe a été réinitialisé avec succès.";
            $showForm = false;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Définir un nouveau mot de passe</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Définir un nouveau mot de passe</h2>
        <?php if ($message): ?>
            <div class="alert"> <?php echo htmlspecialchars($message); ?> </div>
        <?php endif; ?>
        <?php if ($showForm): ?>
        <form method="POST" action="?token=<?php echo htmlspecialchars($token); ?>">
            <label for="code">Code de vérification reçu par mail</label>
            <input type="text" id="code" name="code" required placeholder="ex: 123456">
            <label for="password">Nouveau mot de passe</label>
            <input type="password" id="password" name="password" required>
            <label for="confirm">Confirmer le mot de passe</label>
            <input type="password" id="confirm" name="confirm" required>
            <button type="submit">Réinitialiser</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
