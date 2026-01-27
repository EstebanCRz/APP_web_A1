<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: text/html; charset=UTF-8');
require_once '../includes/config.php';

$error = '';
$success = false;
$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('Location: forgot-password.php');
    exit;
}

// Vérifier que le token est valide
$pdo = getDB();
$stmt = $pdo->prepare('SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()');
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    $error = "Ce lien de réinitialisation est invalide ou expiré.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reset) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($password !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        try {
            // Mettre à jour le mot de passe
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
            $stmt->execute([$hash, $reset['email']]);
            
            // Supprimer le token utilisé
            $stmt = $pdo->prepare('DELETE FROM password_resets WHERE token = ?');
            $stmt->execute([$token]);
            
            // Envoi du mail de confirmation
            require_once '../includes/send_zoho_mail.php';
            $confirmSubject = 'Votre mot de passe AmiGo a été modifié';
            $confirmBody = '<h2>Votre mot de passe a bien été changé</h2>'
                . '<p>Bonjour,</p>'
                . '<p>Nous vous confirmons que votre mot de passe AmiGo a été modifié avec succès.</p>'
                . '<p>Si vous n\'êtes pas à l\'origine de cette demande, contactez immédiatement le support.</p>'
                . '<br><div style="color:#888; font-size:0.95em; margin-top:2em;">© 2026 AmiGo - Tous droits réservés</div>';
            
            try {
                sendZohoMail($reset['email'], $confirmSubject, $confirmBody, 'AmiGo', 'amigocontact@zohomail.eu');
            } catch (Throwable $e) {
                error_log("Erreur envoi email confirmation: " . $e->getMessage());
            }
            
            $success = true;
        } catch (PDOException $e) {
            $error = "Une erreur s'est produite. Veuillez réessayer.";
            error_log("Erreur reset password: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du mot de passe - AmiGo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/reset-password.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #55D5E0 0%, #335F8A 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .container {
            width: 100%;
            max-width: 450px;
        }
        .form-container {
            background: white;
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h2 {
            color: #333;
            margin: 0 0 1.5rem 0;
            text-align: center;
            font-size: 1.75rem;
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
        }
        input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }
        input[type="password"]:focus {
            outline: none;
            border-color: #55D5E0;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
            font-size: 1rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #55D5E0 0%, #335F8A 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-block {
            width: 100%;
            display: block;
            text-align: center;
        }
        .form-links {
            text-align: center;
            margin-top: 1.5rem;
        }
        .form-links a {
            color: #55D5E0;
            text-decoration: none;
        }
        .form-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form-container">
        <h2>Réinitialisation du mot de passe</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <p><strong>✅ Mot de passe modifié !</strong></p>
                <p>Votre mot de passe a été changé avec succès.</p>
                <p style="margin-top: 1rem;"><a href="login.php" class="btn btn-primary">Se connecter</a></p>
            </div>
        <?php elseif (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <p style="text-align: center;"><a href="forgot-password.php">Demander un nouveau lien</a></p>
        <?php else: ?>
            <p style="text-align: center; color: #666; margin-bottom: 1.5rem;">Entrez votre nouveau mot de passe ci-dessous :</p>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="password">Nouveau mot de passe</label>
                    <input type="password" id="password" name="password" required minlength="6" placeholder="Minimum 6 caractères">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6" placeholder="Confirmez votre mot de passe">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Réinitialiser le mot de passe</button>
            </form>
        <?php endif; ?>
        
        <div class="form-links">
            <p><a href="login.php">← Retour à la connexion</a></p>
        </div>
    </div>
</div>

</body>
</html>
