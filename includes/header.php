<?php
// Charger le système de traduction
require_once dirname(__FILE__) . '/language.php';

// Déterminer le chemin relatif vers assets selon la profondeur du fichier
$depth = isset($assetsDepth) ? $assetsDepth : 1; // Par défaut 1 niveau (pour les sous-dossiers)
$prefix = str_repeat('../', $depth);

// Déterminer la page active
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>

<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo isset($pageDescription) ? htmlspecialchars($pageDescription) : 'AmiGo - Plateforme de rencontre et d\'événements'; ?>">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'AmiGo'; ?></title>
    
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
                    <li><a href="<?php echo $prefix; ?>index.php" class="<?php echo ($current_page === 'index.php' && $current_dir !== 'events' && $current_dir !== 'profile' && $current_dir !== 'pages' && $current_dir !== 'auth') ? 'active' : ''; ?>"><?php echo t('header.home'); ?></a></li>
                    <li><a href="<?php echo $prefix; ?>events/events-list.php" class="<?php echo ($current_dir === 'events' || strpos($_SERVER['REQUEST_URI'], 'events') !== false) ? 'active' : ''; ?>"><?php echo t('header.events'); ?></a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="<?php echo $prefix; ?>pages/mes-groupes.php" class="<?php echo (strpos($_SERVER['REQUEST_URI'], 'mes-groupes.php') !== false || $current_page === 'mes-groupes.php') ? 'active' : ''; ?>"><?php echo t('header.my_groups'); ?></a></li>
                        <li><a href="<?php echo $prefix; ?>profile/profile.php" class="<?php echo ($current_dir === 'profile' || strpos($_SERVER['REQUEST_URI'], 'profile') !== false) ? 'active' : ''; ?>"><?php echo t('header.profile'); ?></a></li>
                        <li><a href="<?php echo $prefix; ?>events/event-create.php" class="<?php echo ($current_page === 'event-create.php' || strpos($_SERVER['REQUEST_URI'], 'event-create.php') !== false) ? 'active' : ''; ?>"><?php echo t('header.create_event'); ?></a></li>
                        <li><a href="<?php echo $prefix; ?>auth/login.php?logout=1"><?php echo t('header.logout'); ?></a></li>
                    <?php else: ?>
                        <li><a href="<?php echo $prefix; ?>auth/login.php" class="<?php echo ($current_page === 'login.php') ? 'active' : ''; ?>"><?php echo t('header.login'); ?></a></li>
                        <li><a href="<?php echo $prefix; ?>auth/register.php" class="<?php echo ($current_page === 'register.php') ? 'active' : ''; ?>"><?php echo t('header.register'); ?></a></li>
                    <?php endif; ?>
                    <li class="language-selector">
                        <span class="language-label"><?php echo t('header.language'); ?></span>
                        <div class="language-options">
                            <a href="<?php echo getLanguageUrl('fr'); ?>" class="lang-option <?php echo getCurrentLanguage() === 'fr' ? 'active' : ''; ?>">
                                <span class="flag-fr">🇫🇷</span> Français
                            </a>
                            <a href="<?php echo getLanguageUrl('en'); ?>" class="lang-option <?php echo getCurrentLanguage() === 'en' ? 'active' : ''; ?>">
                                <span class="flag-en">🇬🇧</span> English
                            </a>
                        </div>
                    </li>
                    
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
