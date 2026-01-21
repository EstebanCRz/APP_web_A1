<?php
require_once '../includes/session.php';
require_once '../includes/config.php';
require_once '../includes/language.php';
require_once '../includes/security.php';

// Définir les headers de sécurité
Security::setSecurityHeaders();

// Valider la session
Security::validateSession();

// Gérer la déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ../index.php');
    exit;
}

$pageTitle = t('auth.login_title') . " - AmiGo";
$pageDescription = t('auth.login_title');
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/login.css"
];

// Traiter le formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
        $error = "Token de sécurité invalide.";
    } else {
        $email = Security::sanitizeSQL($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Vérifier le rate limiting
        if (!Security::checkRateLimit('login', 5, 300)) {
            $error = "Trop de tentatives. Veuillez réessayer dans 5 minutes.";
        } else if (!empty($email) && !empty($password)) {
            // Vérifier la protection brute force
            $identifier = $email . '|' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
            
            if (!Security::checkBruteForce($identifier, 5, 900)) {
                $error = "Trop de tentatives de connexion. Compte temporairement bloqué pour 15 minutes.";
            } else {
                try {
                    $pdo = getDB();
                    
                    // Chercher l'utilisateur par email
                    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, password FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch();
                    
                    // Vérifier le mot de passe
                    if ($user && password_verify($password, $user['password'])) {
                        // Connexion réussie - Réinitialiser les tentatives
                        Security::resetLoginAttempts($identifier);
                        
                        // Regénérer l'ID de session pour éviter session fixation
                        session_regenerate_id(true);

                        // Générer et envoyer le code de vérification
                        require_once '../includes/verification_mail.php';
                        $code = generateVerificationCode();
                        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                        $stmt = $pdo->prepare('INSERT INTO login_verifications (user_id, code, expires_at) VALUES (?, ?, ?)');
                        $stmt->execute([$user['id'], $code, $expires]);
                        sendVerificationMail($user['email'], $code);
                        $_SESSION['pending_verif'] = true;
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_first_name'] = $user['first_name'];
                        $_SESSION['user_last_name'] = $user['last_name'];
                        $_SESSION['fingerprint'] = md5($_SERVER['HTTP_USER_AGENT'] ?? '' . ($_SERVER['REMOTE_ADDR'] ?? ''));
                        $_SESSION['created'] = time();
                        header('Location: verify-login.php');
                        exit;
                    } else {
                        // Enregistrer la tentative échouée
                        Security::recordLoginAttempt($identifier);
                        $error = t('auth.invalid_credentials');
                    }
                } catch(PDOException $e) {
                    $error = t('auth.connection_error') . ": " . $e->getMessage();
                }
            }
        } else {
            $error = t('auth.fill_all_fields');
        }
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h2><?php echo t('auth.login_title'); ?></h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="email"><?php echo t('auth.email'); ?></label>
                <input type="email" id="email" name="email" required placeholder="votre@email.com">
            </div>
            
            <div class="form-group">
                <label for="password"><?php echo t('auth.password'); ?></label>
                <input type="password" id="password" name="password" required placeholder="">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="remember"> <?php echo t('auth.remember_me'); ?>
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block"><?php echo t('auth.sign_in'); ?></button>
        </form>
        
        <div class="form-links">
            <a href="forgot-password.php"><?php echo t('auth.forgot_password'); ?></a>
            <p><?php echo t('auth.no_account'); ?> <a href="register.php"><?php echo t('auth.sign_up'); ?></a></p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
