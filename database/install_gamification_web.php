<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Gamification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #28a745;
            margin: 20px 0;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #dc3545;
            margin: 20px 0;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #17a2b8;
            margin: 20px 0;
        }
        .log {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            margin: 20px 0;
            max-height: 400px;
            overflow-y: auto;
        }
        .log-line {
            margin: 5px 0;
        }
        .log-success { color: #28a745; }
        .log-error { color: #dc3545; }
        .log-info { color: #17a2b8; }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎮 Installation du Système de Gamification</h1>
        
        <?php
        require_once __DIR__ . '/../includes/config.php';
        
        $logs = [];
        $hasErrors = false;
        
        try {
            $pdo = getDB();
            $logs[] = ['type' => 'success', 'msg' => 'Connexion à la base de données réussie'];
            
            // Lire le fichier SQL
            $sqlFile = __DIR__ . '/gamification_tables.sql';
            if (!file_exists($sqlFile)) {
                throw new Exception("Fichier gamification_tables.sql introuvable");
            }
            
            $sql = file_get_contents($sqlFile);
            $logs[] = ['type' => 'info', 'msg' => 'Fichier SQL chargé'];
            
            // Diviser les requêtes
            $queries = array_filter(
                array_map('trim', explode(';', $sql)),
                function($query) {
                    return !empty($query) && !preg_match('/^--/', $query);
                }
            );
            
            $logs[] = ['type' => 'info', 'msg' => count($queries) . ' requêtes à exécuter'];
            
            $success = 0;
            $errors = 0;
            
            foreach ($queries as $query) {
                try {
                    $pdo->exec($query);
                    $success++;
                    
                    // Afficher le nom de la table créée
                    if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $query, $matches)) {
                        $logs[] = ['type' => 'success', 'msg' => "✓ Table '{$matches[1]}' créée"];
                    } elseif (preg_match('/INSERT INTO.*?`?(\w+)`?/i', $query, $matches)) {
                        $logs[] = ['type' => 'success', 'msg' => "✓ Données insérées dans '{$matches[1]}'"];
                    }
                } catch (PDOException $e) {
                    // Ignorer les erreurs "table already exists"
                    if (strpos($e->getMessage(), 'already exists') !== false) {
                        $logs[] = ['type' => 'info', 'msg' => 'ℹ Table déjà existante (ignorée)'];
                    } else {
                        $errors++;
                        $hasErrors = true;
                        $logs[] = ['type' => 'error', 'msg' => '✗ Erreur: ' . $e->getMessage()];
                    }
                }
            }
            
            // Vérifier les tables créées
            $tables = ['user_points', 'points_history', 'badges', 'user_badges'];
            $logs[] = ['type' => 'info', 'msg' => ''];
            $logs[] = ['type' => 'info', 'msg' => 'Vérification des tables...'];
            
            foreach ($tables as $table) {
                $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                if ($stmt->rowCount() > 0) {
                    $logs[] = ['type' => 'success', 'msg' => "✓ Table '$table' existe"];
                } else {
                    $logs[] = ['type' => 'error', 'msg' => "✗ Table '$table' n'existe pas"];
                    $hasErrors = true;
                }
            }
            
            // Compter les badges
            $stmt = $pdo->query("SELECT COUNT(*) FROM badges");
            $badgeCount = $stmt->fetchColumn();
            $logs[] = ['type' => 'success', 'msg' => "✓ $badgeCount badges insérés"];
            
            // Initialiser les points pour les utilisateurs existants
            $stmt = $pdo->query("SELECT COUNT(*) FROM users");
            $userCount = $stmt->fetchColumn();
            
            if ($userCount > 0) {
                $stmt = $pdo->exec("
                    INSERT IGNORE INTO user_points (user_id, total_points, level)
                    SELECT id, 0, 1 FROM users
                ");
                $logs[] = ['type' => 'success', 'msg' => "✓ Points initialisés pour $userCount utilisateurs"];
            }
            
        } catch (Exception $e) {
            $hasErrors = true;
            $logs[] = ['type' => 'error', 'msg' => '[ERREUR CRITIQUE] ' . $e->getMessage()];
        }
        ?>
        
        <?php if (!$hasErrors): ?>
            <div class="success">
                <strong>✓ Installation réussie!</strong><br>
                Le système de gamification a été installé avec succès.
            </div>
        <?php else: ?>
            <div class="error">
                <strong>✗ Installation avec erreurs</strong><br>
                Certaines erreurs sont survenues. Consultez le log ci-dessous.
            </div>
        <?php endif; ?>
        
        <div class="log">
            <?php foreach ($logs as $log): ?>
                <div class="log-line log-<?php echo $log['type']; ?>">
                    <?php echo htmlspecialchars($log['msg']); ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (!$hasErrors): ?>
            <div class="info">
                <strong>📋 Tables créées:</strong>
                <ul>
                    <li>user_points - Points et niveaux des utilisateurs</li>
                    <li>points_history - Historique des points gagnés</li>
                    <li>badges - Définition des badges</li>
                    <li>user_badges - Badges obtenus par les utilisateurs</li>
                </ul>
                
                <strong>🎯 Prochaines étapes:</strong>
                <ul>
                    <li>Visitez le <a href="../pages/leaderboard.php">classement</a> pour voir les meilleurs joueurs</li>
                    <li>Consultez tous les <a href="../pages/badges.php">badges disponibles</a></li>
                    <li>Les points seront attribués automatiquement pour vos actions!</li>
                </ul>
            </div>
            
            <a href="../pages/leaderboard.php" class="btn">🏆 Voir le Classement</a>
            <a href="../pages/badges.php" class="btn">🎖️ Voir les Badges</a>
            <a href="../profile/profile.php" class="btn">👤 Mon Profil</a>
        <?php else: ?>
            <div class="info">
                <strong>ℹ️ Que faire en cas d'erreur?</strong>
                <ul>
                    <li>Vérifiez que votre base de données est accessible</li>
                    <li>Vérifiez que le fichier gamification_tables.sql existe</li>
                    <li>Consultez les logs ci-dessus pour plus de détails</li>
                    <li>Vous pouvez réexécuter cette page pour réessayer</li>
                </ul>
            </div>
            
            <a href="?retry=1" class="btn">🔄 Réessayer</a>
        <?php endif; ?>
        
        <br><br>
        <a href="../index.php" class="btn" style="background: #6c757d;">← Retour à l'accueil</a>
    </div>
</body>
</html>
