<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo isset($pageDescription) ? htmlspecialchars($pageDescription) : 'AmiGo - Plateforme de rencontre et d\'événements'; ?>">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'AmiGo'; ?></title>
    
    <?php
    // Déterminer le chemin relatif vers assets selon la profondeur du fichier
    $depth = isset($assetsDepth) ? $assetsDepth : 1; // Par défaut 1 niveau (pour les sous-dossiers)
    $prefix = str_repeat('../', $depth);
    ?>
    
    <link rel="stylesheet" href="<?php echo $prefix; ?>assets/css/header.css">
    <link rel="stylesheet" href="<?php echo $prefix; ?>assets/css/footer.css">
    
    <?php if (isset($customCSS)): ?>
        <?php if (is_array($customCSS)): ?>
            <?php foreach ($customCSS as $css): ?>
                <link rel="stylesheet" href="<?php echo $css; ?>">
            <?php endforeach; ?>
        <?php else: ?>
            <link rel="stylesheet" href="<?php echo $customCSS; ?>">
        <?php endif; ?>
    <?php endif; ?>
</head>
<body>
    <header>
        <div class="header-inner">
            <div class="logo">
                <a href="<?php echo $prefix; ?>index.php">
                    <img src="<?php echo $prefix; ?>assets/images/cap.png" alt="AmiGo" style="height: 100px;">
                </a>

            </div>

            <button class="burger-menu" id="burgerMenu" aria-label="Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <nav id="mainNav">
                <ul>
                    <li><a href="<?php echo $prefix; ?>index.php">Accueil</a></li>
                    <li><a href="<?php echo $prefix; ?>events/events-list.php">Événements</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="<?php echo $prefix; ?>pages/mes-groupes.php">Mes groupes</a></li>
                        <li><a href="<?php echo $prefix; ?>profile/profile.php">Profil</a></li>
                        <li><a href="<?php echo $prefix; ?>events/event-create.php">Créer un événement</a></li>
                        <li><a href="<?php echo $prefix; ?>auth/login.php?logout=1">Déconnexion</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo $prefix; ?>auth/login.php">Connexion</a></li>
                        <li><a href="<?php echo $prefix; ?>auth/register.php">Inscription</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <div class="menu-overlay" id="menuOverlay"></div>
    </header>
    <script>
        // Menu burger toggle
        const burgerMenu = document.getElementById('burgerMenu');
        const mainNav = document.getElementById('mainNav');
        const menuOverlay = document.getElementById('menuOverlay');
        
        if (burgerMenu && mainNav) {
            burgerMenu.addEventListener('click', function() {
                this.classList.toggle('active');
                mainNav.classList.toggle('active');
                menuOverlay.classList.toggle('active');
                document.body.classList.toggle('menu-open');
            });
            
            // Fermer le menu en cliquant sur l'overlay
            if (menuOverlay) {
                menuOverlay.addEventListener('click', () => {
                    burgerMenu.classList.remove('active');
                    mainNav.classList.remove('active');
                    menuOverlay.classList.remove('active');
                    document.body.classList.remove('menu-open');
                });
            }
            
            // Fermer le menu en cliquant sur un lien
            const navLinks = mainNav.querySelectorAll('a');
            navLinks.forEach(link => {
                link.addEventListener('click', () => {
                    burgerMenu.classList.remove('active');
                    mainNav.classList.remove('active');
                    menuOverlay.classList.remove('active');
                    document.body.classList.remove('menu-open');
                });
            });
        }
    </script>
    <main>
