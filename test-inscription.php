<?php
declare(strict_types=1);

// Configuration de session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once 'includes/activities_functions.php';

// Si pas connecté, simuler une connexion pour le test
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_email'] = 'test@example.com';
    $_SESSION['user_first_name'] = 'Test';
    $_SESSION['user_last_name'] = 'User';
}

$activityId = 1;
$activity = getActivityById($activityId);
$participants = getActivityParticipants($activityId);
$isRegistered = isUserRegistered($activityId, $_SESSION['user_id']);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Inscription</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .info-box {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .participant {
            background: #fff;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            border: none;
        }
        .btn-subscribe {
            background: #4CAF50;
            color: white;
        }
        .btn-unsubscribe {
            background: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <h1>Test du système d'inscription</h1>
    
    <div class="info-box">
        <h2>Activité: <?php echo htmlspecialchars($activity['title']); ?></h2>
        <p><strong>Catégorie:</strong> <?php echo htmlspecialchars($activity['category_name']); ?></p>
        <p><strong>Lieu:</strong> <?php echo htmlspecialchars($activity['location']); ?></p>
        <p><strong>Date:</strong> <?php echo formatEventDate($activity['date']); ?> à <?php echo formatEventTime($activity['date']); ?></p>
        <p><strong>Participants:</strong> <span id="participant-count"><?php echo $activity['current_participants']; ?></span>/<?php echo $activity['max_participants']; ?></p>
        <p><strong>Vous êtes inscrit:</strong> <?php echo $isRegistered ? 'Oui ✓' : 'Non ✗'; ?></p>
    </div>

    <div class="info-box">
        <h3>Action:</h3>
        <?php if ($isRegistered): ?>
            <button class="btn-unsubscribe" data-activity-id="<?php echo $activityId; ?>">Se désinscrire</button>
        <?php else: ?>
            <button class="btn-subscribe" data-activity-id="<?php echo $activityId; ?>">S'inscrire</button>
        <?php endif; ?>
    </div>

    <div class="info-box">
        <h3>Liste des participants (<?php echo count($participants); ?>):</h3>
        <?php if (empty($participants)): ?>
            <p>Aucun participant inscrit.</p>
        <?php else: ?>
            <?php foreach ($participants as $p): ?>
                <div class="participant">
                    <strong><?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?></strong>
                    (@<?php echo htmlspecialchars($p['username']); ?>)<br>
                    <small>Inscrit le <?php echo date('d/m/Y à H:i', strtotime($p['registered_at'])); ?></small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="assets/js/activity-registration.js"></script>
</body>
</html>
