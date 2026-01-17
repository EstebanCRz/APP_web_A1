<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'AmiGo'; ?></title>
    
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if (isset($customCSS)): ?>
        <?php foreach ($customCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <header class="main-header">
        <nav class="navbar">
            <a href="index.php" class="logo">AmiGo</a>
            
            <input type="checkbox" id="menu-toggle" class="menu-toggle">
            
            <label for="menu-toggle" class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </label>

            <ul class="nav-links">
                <li class="nav-item"><a href="index.php">Accueil</a></li>
                <li class="nav-item"><a href="events/events-list.php">Événements</a></li>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a href="profile.php">Mon Profil</a></li>
                    <li class="nav-item"><a href="auth/logout.php" class="mobile-btn-logout">Déconnexion</a></li>
                <?php else: ?>
                    <li class="nav-item"><a href="auth/login.php" class="mobile-btn-login">Connexion</a></li>
                    <li class="nav-item"><a href="auth/register.php" class="mobile-btn-signup">Inscription</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>