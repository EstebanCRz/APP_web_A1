<?php
require_once '../includes/session.php';
require_once '../includes/config.php';
require_once '../includes/language.php';

// Vérifier que l'utilisateur est connecté et attend la vérification
if (!isset($_SESSION['user_id']) || !isset($_SESSION['pending_email_verification'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    
    if (empty($code)) {
        $error = 'Veuillez entrer le code de vérification.';
    } else {
        try {
            $pdo = getDB();
            
            // Vérifier le code
            $stmt = $pdo->prepare("
                SELECT id FROM login_verifications 
                WHERE user_id = ? AND code = ? AND expires_at > NOW() AND used = 0
                ORDER BY created_at DESC LIMIT 1
            ");
            $stmt->execute([$_SESSION['user_id'], $code]);
            $verification = $stmt->fetch();
            
            if ($verification) {
                // Marquer le code comme utilisé
                $stmt = $pdo->prepare("UPDATE login_verifications SET used = 1 WHERE id = ?");
                $stmt->execute([$verification['id']]);
                
                // Retirer le flag de vérification en attente
                unset($_SESSION['pending_email_verification']);
                
                // Rediriger vers le choix des intérêts
                header('Location: ../profile/choose-interests.php');
                exit;
            } else {
                $error = 'Code invalide ou expiré. Veuillez vérifier votre email.';
            }
        } catch (PDOException $e) {
            error_log("Erreur vérification email: " . $e->getMessage());
            $error = 'Erreur lors de la vérification. Veuillez réessayer.';
        }
    }
}

// Fonction pour renvoyer le code
if (isset($_GET['resend']) && $_GET['resend'] === '1') {
    try {
        require_once '../includes/verification_mail.php';
        $pdo = getDB();
        
        // Générer un nouveau code
        $code = generateVerificationCode();
        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Insérer le nouveau code
        $stmt = $pdo->prepare('INSERT INTO login_verifications (user_id, code, expires_at) VALUES (?, ?, ?)');
        $stmt->execute([$_SESSION['user_id'], $code, $expires]);
        
        // Envoyer l'email
        sendVerificationMail($_SESSION['user_email'], $code);
        
        $success = true;
    } catch (Exception $e) {
        error_log("Erreur renvoi code: " . $e->getMessage());
        $error = 'Erreur lors du renvoi du code.';
    }
}

$pageTitle = "Vérification email - AmiGo";
$pageDescription = "Vérifiez votre adresse email";
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/verify-email.css"
];

include '../includes/header.php';
?>

<div class="verify-container">
    <div class="verify-card">
        <div class="verify-icon">📧</div>
        <h1>Vérifiez votre email</h1>
        <p class="verify-subtitle">
            Nous avons envoyé un code de vérification à<br>
            <strong><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></strong>
        </p>

        <?php if ($success): ?>
            <div class="alert alert-success">
                ✅ Un nouveau code a été envoyé à votre adresse email !
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="verify-form">
            <div class="form-group">
                <label for="code">Code de vérification</label>
                <input 
                    type="text" 
                    id="code" 
                    name="code" 
                    maxlength="6" 
                    placeholder="000000"
                    pattern="[0-9]{6}"
                    required
                    autocomplete="off"
                    class="code-input"
                >
                <p class="help-text">Entrez le code à 6 chiffres reçu par email</p>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                Vérifier le code
            </button>
        </form>

        <div class="verify-footer">
            <p>Vous n'avez pas reçu le code ?</p>
            <a href="?resend=1" class="link-resend">Renvoyer le code</a>
        </div>

        <div class="verify-help">
            <p class="small-text">
                💡 Le code est valable pendant 10 minutes.<br>
                Vérifiez également vos spams si vous ne trouvez pas l'email.
            </p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
