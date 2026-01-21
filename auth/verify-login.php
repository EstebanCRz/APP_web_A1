<?php
require_once '../includes/session.php';
require_once '../includes/config.php';
require_once '../includes/language.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['pending_verif'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM login_verifications WHERE user_id = ? AND code = ? AND expires_at > NOW() AND used = 0');
    $stmt->execute([$userId, $code]);
    $row = $stmt->fetch();
    if ($row) {
        $stmt = $pdo->prepare('UPDATE login_verifications SET used = 1 WHERE id = ?');
        $stmt->execute([$row['id']]);
        unset($_SESSION['pending_verif']);
        $success = true;
        // Rediriger vers le choix des centres d'intérêt après vérification
        header('Location: ../profile/choose-interests.php');
        exit;
    } else {
        $error = "Code invalide ou expiré.";
    }
}

$pageTitle = 'Vérification de connexion - AmiGo';
$pageDescription = $pageTitle;
$assetsDepth = 1;
$customCSS = ["../assets/css/style.css", "../assets/css/reset-password.css"];
include '../includes/header.php';
?>
<div class="container reset-container">
    <h2>Vérification de connexion</h2>
    <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label for="code">Code reçu par e-mail</label>
            <input type="text" id="code" name="code" required maxlength="6" pattern="[0-9]{6}">
        </div>
        <button type="submit" class="btn btn-primary">Valider</button>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
