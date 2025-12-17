<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Connexion - AmiGo";
$pageDescription = "Connectez-vous à votre compte AmiGo";
$assetsDepth = 1;
$customCSS = "../assets/css/index.css";

// Traiter le formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        // TODO: Vérifier dans la base de données
        $_SESSION['user_id'] = 1;
        $_SESSION['user_email'] = $email;
        header('Location: ../profile/profile.php');
        exit;
    } else {
        $error = "Veuillez remplir tous les champs";
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h2>Connexion</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="votre@email.com">
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required placeholder="">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="remember"> Se souvenir de moi
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
        </form>
        
        <div class="form-links">
            <a href="forgot-password.php">Mot de passe oublié ?</a>
            <p>Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
