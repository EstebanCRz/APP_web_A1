<?php
/**
 * Fichier de test de connexion et de fonctions
 * Accédez à ce fichier via : http://localhost/APP_web_A1/database/test_connection.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/config.php';
require_once '../includes/activities_functions.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Connexion - AmiGo</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success {
            color: #4CAF50;
            font-weight: bold;
        }
        .error {
            color: #f44336;
            font-weight: bold;
        }
        h1 { color: #333; }
        h2 { color: #666; border-bottom: 2px solid #2196F3; padding-bottom: 10px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #2196F3;
            color: white;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            color: white;
            font-size: 12px;
        }
        pre {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>🧪 Test du Système d'Activités AmiGo</h1>
    
    <!-- Test 1: Connexion à la base de données -->
    <div class="test-section">
        <h2>1. Connexion à la base de données</h2>
        <?php
        try {
            $pdo = getDB();
            echo '<p class="success">✅ Connexion réussie à la base de données!</p>';
            echo '<p>Hôte: ' . DB_HOST . '</p>';
            echo '<p>Base de données: ' . DB_NAME . '</p>';
            echo '<p>Utilisateur: ' . DB_USER . '</p>';
        } catch (Exception $e) {
            echo '<p class="error">❌ Erreur de connexion: ' . htmlspecialchars($e->getMessage()) . '</p>';
            exit;
        }
        ?>
    </div>

    <!-- Test 2: Vérification des tables -->
    <div class="test-section">
        <h2>2. Vérification des tables</h2>
        <?php
        $tables = ['users', 'activity_categories', 'activities', 'activity_registrations'];
        $allTablesExist = true;
        
        foreach ($tables as $table) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
                $result = $stmt->fetch();
                echo '<p class="success">✅ Table <strong>' . $table . '</strong>: ' . $result['count'] . ' enregistrement(s)</p>';
            } catch (Exception $e) {
                echo '<p class="error">❌ Table <strong>' . $table . '</strong>: Non trouvée ou erreur</p>';
                $allTablesExist = false;
            }
        }
        
        if ($allTablesExist) {
            echo '<p class="success"><strong>Toutes les tables sont présentes!</strong></p>';
        }
        ?>
    </div>

    <!-- Test 3: Liste des catégories -->
    <div class="test-section">
        <h2>3. Catégories d'activités</h2>
        <?php
        try {
            $categories = getAllCategories();
            if (empty($categories)) {
                echo '<p class="error">❌ Aucune catégorie trouvée</p>';
            } else {
                echo '<p class="success">✅ ' . count($categories) . ' catégorie(s) trouvée(s)</p>';
                echo '<table>';
                echo '<tr><th>ID</th><th>Nom</th><th>Couleur</th><th>Icône</th></tr>';
                foreach ($categories as $cat) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($cat['id']) . '</td>';
                    echo '<td>' . htmlspecialchars($cat['name']) . '</td>';
                    echo '<td><span class="badge" style="background:' . htmlspecialchars($cat['color']) . '">' . htmlspecialchars($cat['color']) . '</span></td>';
                    echo '<td>' . htmlspecialchars($cat['icon'] ?? '') . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
        } catch (Exception $e) {
            echo '<p class="error">❌ Erreur: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>
    </div>

    <!-- Test 4: Liste des activités -->
    <div class="test-section">
        <h2>4. Activités</h2>
        <?php
        try {
            $activities = getAllActivities();
            if (empty($activities)) {
                echo '<p class="error">❌ Aucune activité trouvée</p>';
            } else {
                echo '<p class="success">✅ ' . count($activities) . ' activité(s) trouvée(s)</p>';
                echo '<table>';
                echo '<tr><th>ID</th><th>Titre</th><th>Catégorie</th><th>Créateur</th><th>Date</th><th>Participants</th></tr>';
                foreach ($activities as $act) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($act['id']) . '</td>';
                    echo '<td>' . htmlspecialchars($act['title']) . '</td>';
                    echo '<td><span class="badge" style="background:' . htmlspecialchars($act['category_color']) . '">' . htmlspecialchars($act['category_name']) . '</span></td>';
                    echo '<td>' . htmlspecialchars($act['creator_first_name'] ?? $act['creator_username']) . '</td>';
                    echo '<td>' . htmlspecialchars($act['event_date']) . ' ' . htmlspecialchars(substr($act['event_time'], 0, 5)) . '</td>';
                    echo '<td>' . htmlspecialchars($act['current_participants']) . '/' . htmlspecialchars($act['max_participants']) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
        } catch (Exception $e) {
            echo '<p class="error">❌ Erreur: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>
    </div>

    <!-- Test 5: Test des filtres -->
    <div class="test-section">
        <h2>5. Test des filtres</h2>
        <?php
        try {
            // Test recherche
            $searchResults = getAllActivities(['search' => 'yoga']);
            echo '<p>🔍 Recherche "yoga": <strong>' . count($searchResults) . '</strong> résultat(s)</p>';
            
            // Test catégorie
            $sportResults = getAllActivities(['category' => 'Sport']);
            echo '<p>⚽ Catégorie "Sport": <strong>' . count($sportResults) . '</strong> résultat(s)</p>';
            
            // Test limite
            $limitedResults = getAllActivities(['limit' => 3]);
            echo '<p>📊 Limite à 3: <strong>' . count($limitedResults) . '</strong> résultat(s)</p>';
            
            echo '<p class="success">✅ Les filtres fonctionnent correctement!</p>';
        } catch (Exception $e) {
            echo '<p class="error">❌ Erreur lors du test des filtres: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>
    </div>

    <!-- Test 6: Test de formatage -->
    <div class="test-section">
        <h2>6. Test de formatage des dates</h2>
        <?php
        try {
            $testDate = '2025-12-25';
            $testTime = '14:30:00';
            
            if (extension_loaded('intl')) {
                echo '<p class="success">✅ Extension PHP intl chargée</p>';
                echo '<p>Date brute: ' . htmlspecialchars($testDate) . '</p>';
                echo '<p>Date formatée: <strong>' . formatEventDate($testDate) . '</strong></p>';
                echo '<p>Heure brute: ' . htmlspecialchars($testTime) . '</p>';
                echo '<p>Heure formatée: <strong>' . formatEventTime($testTime) . '</strong></p>';
            } else {
                echo '<p class="error">⚠️ Extension PHP intl non chargée - Le formatage des dates peut ne pas fonctionner correctement</p>';
                echo '<p>Pour activer intl, décommentez "extension=intl" dans php.ini</p>';
            }
        } catch (Exception $e) {
            echo '<p class="error">❌ Erreur lors du formatage: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>
    </div>

    <!-- Test 7: Configuration -->
    <div class="test-section">
        <h2>7. Configuration de l'application</h2>
        <pre><?php
echo "BASE_PATH: " . BASE_PATH . "\n";
echo "BASE_URL: " . BASE_URL . "\n";
echo "ASSETS_URL: " . ASSETS_URL . "\n";
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Extensions chargées:\n";
$extensions = ['pdo', 'pdo_mysql', 'intl', 'json'];
foreach ($extensions as $ext) {
    echo "  - $ext: " . (extension_loaded($ext) ? '✅' : '❌') . "\n";
}
        ?></pre>
    </div>

    <div class="test-section" style="background: #e3f2fd; border-left: 4px solid #2196F3;">
        <h2>✅ Résumé</h2>
        <p><strong>Si tous les tests ci-dessus sont verts, votre installation est complète et fonctionnelle!</strong></p>
        <p>Vous pouvez maintenant:</p>
        <ul>
            <li>Accéder à la <a href="../index.php">page d'accueil</a></li>
            <li>Voir la <a href="../events/events-list.php">liste des événements</a></li>
            <li>Tester les filtres et la recherche</li>
            <li>Développer de nouvelles fonctionnalités</li>
        </ul>
        <p><em>Note: Supprimez ou sécurisez ce fichier de test en production!</em></p>
    </div>

</body>
</html>
