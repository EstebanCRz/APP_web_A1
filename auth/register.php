<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Inscription - AmiGo";
$pageDescription = "Créez votre compte AmiGo";
$assetsDepth = 1;
$customCSS = "../assets/css/index.css";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    if (empty($name)) $errors[] = "Le nom est requis";
    if (empty($email)) $errors[] = "L'email est requis";
    if (empty($password)) $errors[] = "Le mot de passe est requis";
    if ($password !== $confirm_password) $errors[] = "Les mots de passe ne correspondent pas";
    
    if (empty($errors)) {
        $_SESSION['user_id'] = 1;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $name;
        header('Location: ../profile/profile.php');
        exit;
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h2>Inscription</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="name">Nom complet</label>
                <input type="text" id="name" name="name" required placeholder="Jean Dupont">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="votre@email.com">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="form-group">
                <label><input type="checkbox" required> J'accepte les <a href="../pages/cgu.php">CGU</a></label>
            </div>
            <button type="submit" class="btn btn-primary btn-block">S'inscrire</button>
        </form>
        <div class="form-links"><p>Déjà un compte ? <a href="login.php">Se connecter</a></p></div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
