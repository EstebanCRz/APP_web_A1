<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
require_once '../includes/config.php';
require_once '../includes/language.php';

$pageTitle = t('auth.register_title') . " - AmiGo";
$pageDescription = t('auth.register_title');
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/register.css"
];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $last_name = $_POST['nom'] ?? '';
    $first_name = $_POST['prenom'] ?? '';
    $username = $_POST['username'] ?? strtolower($first_name . $last_name);
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    
    $errors = [];
    
    // Validation
    if (empty($last_name)) $errors[] = t('auth.name_required');
    if (empty($first_name)) $errors[] = t('auth.firstname_required');
    if (empty($email)) $errors[] = t('auth.email_required');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = t('auth.invalid_email');
    if (empty($password)) $errors[] = t('auth.password_required');
    if (strlen($password) < 6) $errors[] = t('auth.password_min_length');
    if ($password !== $confirm_password) $errors[] = t('auth.passwords_not_match');
    
    if (empty($errors)) {
        try {
            $pdo = getDB();
            
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $errors[] = t('auth.email_already_used');
            } else {
                // Hasher le mot de passe
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Insérer le nouvel utilisateur
                $stmt = $pdo->prepare("INSERT INTO users (username, first_name, last_name, email, password) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$username, $first_name, $last_name, $email, $password_hash]);
                
                // Envoi du mail de bienvenue
                require_once '../includes/send_zoho_mail.php';
                $welcomeSubject = 'Bienvenue sur AmiGo !';
                $welcomeBody = '<div style="text-align:center; margin-bottom:1.5em;">'
                    . '<img src="https://amigo.nocsy.fr/assets/images/cap.png" alt="Logo AmiGo" style="max-width:120px; margin-bottom:1em;">'
                    . '</div>'
                    . '<h2>Bienvenue ' . htmlspecialchars($first_name) . ' !</h2>'
                    . '<p>Merci de t\'être inscrit sur AmiGo. Nous sommes ravis de t\'accueillir dans la communauté !</p>'
                    . '<p>Tu peux dès maintenant découvrir des activités, rejoindre des groupes et échanger avec d\'autres membres.</p>'
                    . '<br><div style="color:#888; font-size:0.95em; margin-top:2em;">© 2026 AmiGo - Tous droits réservés</div>';
                sendZohoMail($email, $welcomeSubject, $welcomeBody, 'AmiGo', 'amigocontact@zohomail.eu');
                
                // Récupérer l'ID de l'utilisateur
                $user_id = $pdo->lastInsertId();
                
                // Connecter automatiquement l'utilisateur
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_first_name'] = $first_name;
                $_SESSION['user_last_name'] = $last_name;
                
                
                
                // Rediriger vers la page de sélection des centres d'intérêt
                header('Location: ../profile/choose-interests.php');
                exit;
            }
        } catch(PDOException $e) {
            $errors[] = t('auth.registration_error') . ": " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h2><?php echo t('auth.register_title'); ?></h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="nom"><?php echo t('auth.last_name'); ?></label>
                <input type="text" id="nom" name="nom" required placeholder="Dupont" value="<?php echo htmlspecialchars($last_name ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="prenom"><?php echo t('auth.first_name'); ?></label>
                <input type="text" id="prenom" name="prenom" required placeholder="Jean" value="<?php echo htmlspecialchars($first_name ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="email"><?php echo t('auth.email'); ?></label>
                <input type="email" id="email" name="email" required placeholder="votre@email.com" value="<?php echo htmlspecialchars($email ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="password"><?php echo t('auth.password'); ?></label>
                <input type="password" id="password" name="password" required minlength="6" placeholder="<?php echo getCurrentLanguage() === 'fr' ? 'Minimum 6 caractères' : 'Minimum 6 characters'; ?>">
            </div>
            <div class="form-group">
                <label for="confirm_password"><?php echo t('auth.password_confirm'); ?></label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="form-group">
                <label><input type="checkbox" required> <?php echo getCurrentLanguage() === 'fr' ? 'J\'accepte les' : 'I accept the'; ?> <a href="../pages/cgu.php"><?php echo t('footer.cgu'); ?></a></label>
            </div>

            
            <button type="submit" class="btn btn-primary btn-block"><?php echo t('auth.sign_up'); ?></button>
        </form>
        <div class="form-links"><p><?php echo t('auth.already_account'); ?> <a href="login.php"><?php echo t('auth.sign_in'); ?></a></p></div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
