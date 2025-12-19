<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
require_once '../includes/config.php';

$pageTitle = "Inscription - AmiGo";
$pageDescription = "Créez votre compte AmiGo";
$assetsDepth = 1;
$customCSS = "../assets/css/index.css";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $last_name = $_POST['nom'] ?? '';
    $first_name = $_POST['prenom'] ?? '';
    $username = $_POST['username'] ?? strtolower($first_name . $last_name);
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Validation
    if (empty($last_name)) $errors[] = "Le nom est requis";
    if (empty($first_name)) $errors[] = "Le prénom est requis";
    if (empty($email)) $errors[] = "L'email est requis";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide";
    if (empty($password)) $errors[] = "Le mot de passe est requis";
    if (strlen($password) < 6) $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
    if ($password !== $confirm_password) $errors[] = "Les mots de passe ne correspondent pas";
    
    if (empty($errors)) {
        try {
            $pdo = getDB();
            
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $errors[] = "Cet email est déjà utilisé";
            } else {
                // Hasher le mot de passe
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Insérer le nouvel utilisateur
                $stmt = $pdo->prepare("INSERT INTO users (username, first_name, last_name, email, password) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$username, $first_name, $last_name, $email, $password_hash]);
                
                // Récupérer l'ID de l'utilisateur
                $user_id = $pdo->lastInsertId();
                
                // Connecter automatiquement l'utilisateur
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_first_name'] = $first_name;
                $_SESSION['user_last_name'] = $last_name;
                
                header('Location: ../profile/profile.php');
                exit;
            }
        } catch(PDOException $e) {
            $errors[] = "Erreur lors de l'inscription: " . $e->getMessage();
        }
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
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" required placeholder="Dupont" value="<?php echo htmlspecialchars($nom ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" required placeholder="Jean" value="<?php echo htmlspecialchars($prenom ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="votre@email.com" value="<?php echo htmlspecialchars($email ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required minlength="6" placeholder="Minimum 6 caractères">
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
