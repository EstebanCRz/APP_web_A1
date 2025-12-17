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
            <h1><a href="<?php echo $prefix; ?>index.php">AmiGo</a></h1>

            <nav>
                <ul>
                    <li><a href="<?php echo $prefix; ?>index.php">Accueil</a></li>
                    <li><a href="<?php echo $prefix; ?>events/events-list.php">Événements</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="<?php echo $prefix; ?>profile/profile.php">Profil</a></li>
                        <li><a href="<?php echo $prefix; ?>events/event-create.php">Créer un événement</a></li>
                        <li><a href="<?php echo $prefix; ?>auth/login.php?logout=1">Déconnexion</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo $prefix; ?>auth/login.php">Connexion</a></li>
                        <li><a href="<?php echo $prefix; ?>auth/register.php">Inscription</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

            <!-- Global header search removed (moved to events page) -->
        </div>
    </header>
    <main>
