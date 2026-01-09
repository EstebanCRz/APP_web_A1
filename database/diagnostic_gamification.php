<?php
/**
 * Script de diagnostic et réparation du système de gamification
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/gamification.php';

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic Gamification</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { color: #17a2b8; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: 600; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .section { margin: 30px 0; padding: 20px; background: #f8f9fa; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnostic du Système de Gamification</h1>
        
        <?php
        $pdo = getDB();
        $issues = [];
        $fixes = [];
        
        // 1. Vérifier les tables
        echo '<div class="section">';
        echo '<h2>1. Vérification des tables</h2>';
        echo '<table>';
        echo '<tr><th>Table</th><th>Statut</th><th>Lignes</th></tr>';
        
        $tables = ['user_points', 'points_history', 'badges', 'user_badges'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                $stmt2 = $pdo->query("SELECT COUNT(*) FROM $table");
                $count = $stmt2->fetchColumn();
                echo "<tr><td>$table</td><td class='success'>✓ Existe</td><td>$count</td></tr>";
            } else {
                echo "<tr><td>$table</td><td class='error'>✗ Manquante</td><td>-</td></tr>";
                $issues[] = "Table $table manquante";
            }
        }
        echo '</table>';
        echo '</div>';
        
        // 2. Vérifier les badges
        echo '<div class="section">';
        echo '<h2>2. Badges configurés</h2>';
        $stmt = $pdo->query("SELECT COUNT(*) FROM badges");
        $badgeCount = $stmt->fetchColumn();
        
        if ($badgeCount >= 17) {
            echo "<p class='success'>✓ $badgeCount badges disponibles</p>";
        } else {
            echo "<p class='warning'>⚠ Seulement $badgeCount badges (17 attendus)</p>";
            $issues[] = "Nombre de badges insuffisant";
        }
        
        $stmt = $pdo->query("SELECT code, name_fr, icon, condition_type, condition_value FROM badges ORDER BY condition_type, condition_value");
        $badges = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo '<table>';
        echo '<tr><th>Code</th><th>Nom</th><th>Icône</th><th>Condition</th></tr>';
        foreach ($badges as $badge) {
            echo "<tr>";
            echo "<td>{$badge['code']}</td>";
            echo "<td>{$badge['name_fr']}</td>";
            echo "<td>{$badge['icon']}</td>";
            echo "<td>{$badge['condition_type']} >= {$badge['condition_value']}</td>";
            echo "</tr>";
        }
        echo '</table>';
        echo '</div>';
        
        // 3. Vérifier les utilisateurs
        echo '<div class="section">';
        echo '<h2>3. Utilisateurs et points</h2>';
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $userCount = $stmt->fetchColumn();
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM user_points");
        $userPointsCount = $stmt->fetchColumn();
        
        echo "<p><strong>Utilisateurs dans la base:</strong> $userCount</p>";
        echo "<p><strong>Utilisateurs avec points:</strong> $userPointsCount</p>";
        
        if ($userCount > $userPointsCount) {
            $missing = $userCount - $userPointsCount;
            echo "<p class='warning'>⚠ $missing utilisateur(s) sans points initialisés</p>";
            $issues[] = "$missing utilisateurs sans points";
            
            // Bouton pour réparer
            if (isset($_GET['fix_users'])) {
                $stmt = $pdo->exec("
                    INSERT IGNORE INTO user_points (user_id, total_points, level)
                    SELECT id, 0, 1 FROM users
                ");
                echo "<p class='success'>✓ Points initialisés pour tous les utilisateurs</p>";
                $fixes[] = "Points initialisés";
            } else {
                echo '<p><a href="?fix_users=1" class="btn btn-success">Réparer: Initialiser les points</a></p>';
            }
        } else {
            echo "<p class='success'>✓ Tous les utilisateurs ont des points</p>";
        }
        
        // Top 10 utilisateurs
        $stmt = $pdo->query("
            SELECT u.username, u.first_name, u.last_name, up.total_points, up.level
            FROM user_points up
            JOIN users u ON up.user_id = u.id
            ORDER BY up.total_points DESC
            LIMIT 10
        ");
        $topUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($topUsers)) {
            echo '<h3>Top 10 utilisateurs</h3>';
            echo '<table>';
            echo '<tr><th>Rang</th><th>Utilisateur</th><th>Points</th><th>Niveau</th></tr>';
            $rank = 1;
            foreach ($topUsers as $user) {
                $displayName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: $user['username'];
                echo "<tr>";
                echo "<td>#$rank</td>";
                echo "<td>" . htmlspecialchars($displayName) . "</td>";
                echo "<td>{$user['total_points']}</td>";
                echo "<td>{$user['level']}</td>";
                echo "</tr>";
                $rank++;
            }
            echo '</table>';
        }
        echo '</div>';
        
        // 4. Statistiques des badges
        echo '<div class="section">';
        echo '<h2>4. Badges obtenus</h2>';
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM user_badges");
        $earnedBadges = $stmt->fetchColumn();
        
        echo "<p><strong>Badges débloqués au total:</strong> $earnedBadges</p>";
        
        if ($earnedBadges > 0) {
            $stmt = $pdo->query("
                SELECT b.name_fr, b.icon, COUNT(*) as count
                FROM user_badges ub
                JOIN badges b ON ub.badge_id = b.id
                GROUP BY b.id
                ORDER BY count DESC
                LIMIT 10
            ");
            $badgeStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo '<h3>Badges les plus obtenus</h3>';
            echo '<table>';
            echo '<tr><th>Badge</th><th>Utilisateurs</th></tr>';
            foreach ($badgeStats as $stat) {
                echo "<tr>";
                echo "<td>{$stat['icon']} {$stat['name_fr']}</td>";
                echo "<td>{$stat['count']}</td>";
                echo "</tr>";
            }
            echo '</table>';
        } else {
            echo "<p class='info'>ℹ Aucun badge débloqué pour le moment</p>";
        }
        echo '</div>';
        
        // 5. Historique des points
        echo '<div class="section">';
        echo '<h2>5. Activité récente</h2>';
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM points_history");
        $historyCount = $stmt->fetchColumn();
        
        echo "<p><strong>Actions enregistrées:</strong> $historyCount</p>";
        
        if ($historyCount > 0) {
            $stmt = $pdo->query("
                SELECT u.username, ph.points, ph.action_type, ph.description, ph.created_at
                FROM points_history ph
                JOIN users u ON ph.user_id = u.id
                ORDER BY ph.created_at DESC
                LIMIT 20
            ");
            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo '<h3>20 dernières actions</h3>';
            echo '<table>';
            echo '<tr><th>Utilisateur</th><th>Action</th><th>Points</th><th>Date</th></tr>';
            foreach ($history as $entry) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($entry['username']) . "</td>";
                echo "<td>{$entry['description']}</td>";
                echo "<td>+{$entry['points']}</td>";
                echo "<td>" . date('d/m/Y H:i', strtotime($entry['created_at'])) . "</td>";
                echo "</tr>";
            }
            echo '</table>';
        } else {
            echo "<p class='info'>ℹ Aucune activité enregistrée pour le moment</p>";
        }
        echo '</div>';
        
        // Résumé
        echo '<div class="section">';
        echo '<h2>📋 Résumé</h2>';
        
        if (empty($issues)) {
            echo '<p class="success" style="font-size: 1.2em;">✓ Tout fonctionne correctement!</p>';
            echo '<p>Le système de gamification est opérationnel.</p>';
        } else {
            echo '<p class="warning" style="font-size: 1.2em;">⚠ ' . count($issues) . ' problème(s) détecté(s)</p>';
            echo '<ul>';
            foreach ($issues as $issue) {
                echo "<li>$issue</li>";
            }
            echo '</ul>';
        }
        
        if (!empty($fixes)) {
            echo '<p class="success">Réparations effectuées:</p>';
            echo '<ul>';
            foreach ($fixes as $fix) {
                echo "<li>$fix</li>";
            }
            echo '</ul>';
        }
        echo '</div>';
        
        // Actions
        echo '<div class="section">';
        echo '<h2>🎯 Actions disponibles</h2>';
        echo '<a href="../pages/leaderboard.php" class="btn">🏆 Voir le Classement</a>';
        echo '<a href="../pages/badges.php" class="btn">🎖️ Voir les Badges</a>';
        echo '<a href="../profile/profile.php" class="btn">👤 Mon Profil</a>';
        echo '<a href="install_gamification_web.php" class="btn">🔄 Réinstaller</a>';
        echo '<a href="?refresh=1" class="btn">🔄 Rafraîchir</a>';
        echo '</div>';
        ?>
        
        <p style="text-align: center; margin-top: 30px;">
            <a href="../index.php">← Retour à l'accueil</a>
        </p>
    </div>
</body>
</html>
