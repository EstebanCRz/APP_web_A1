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
    
    <?php if (isset($customCSS)): ?>
        <link rel="stylesheet" href="<?php echo $customCSS; ?>">
    <?php endif; ?>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <a href="<?php echo $prefix; ?>index.php">
                    <span class="logo-icon">A</span>
                    <span class="logo-text">AmiGo</span>
                </a>
            </div>
            <nav>
                <ul>
                    <li><a href="<?php echo $prefix; ?>index.php" class="nav-link active">Accueil</a></li>
                    <li><a href="<?php echo $prefix; ?>events/events-list.php" class="nav-link">Activités</a></li>
                    <li><a href="<?php echo $prefix; ?>pages/faq.php" class="nav-link">FAQ</a></li>
                </ul>
            </nav>
            <div class="user-profile">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="profile-dropdown">
                        <button class="profile-btn">
                            <img src="<?php echo $prefix; ?>assets/images/default-avatar.png" alt="Profil" class="profile-avatar">
                            <span class="profile-name"><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Utilisateur'; ?></span>
                        </button>
                        <div class="dropdown-content">
                            <a href="<?php echo $prefix; ?>profile/profile.php">Mon Profil</a>
                            <a href="<?php echo $prefix; ?>events/event-create.php">Créer un événement</a>
                            <a href="<?php echo $prefix; ?>auth/login.php?logout=1">Déconnexion</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo $prefix; ?>auth/login.php" class="btn-login">Connexion</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main>
