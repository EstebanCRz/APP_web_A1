<?php
/**
 * Page de diagnostic pour identifier les problèmes
 * Accédez à cette page via : http://localhost/APP_web_A1/debug.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Diagnostic - AmiGo</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 20px auto; padding: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .section { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
        pre { background: white; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔍 Diagnostic de l'application AmiGo</h1>

    <div class="section">
        <h2>1. Test de connexion à la base de données</h2>
        <?php
        try {
            require_once 'includes/config.php';
            $pdo = getDB();
            echo '<p class="success">✅ Connexion réussie</p>';
        } catch (Exception $e) {
            echo '<p class="error">❌ Erreur de connexion: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p>Vérifiez les paramètres dans includes/config.php</p>';
            exit;
        }
        ?>
    </div>

    <div class="section">
        <h2>2. Vérification des tables</h2>
        <?php
        $tables = ['users', 'activity_categories', 'activities', 'activity_registrations'];
        foreach ($tables as $table) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
                $result = $stmt->fetch();
                echo '<p class="success">✅ Table <strong>' . $table . '</strong>: ' . $result['count'] . ' enregistrements</p>';
            } catch (Exception $e) {
                echo '<p class="error">❌ Table <strong>' . $table . '</strong> manquante ou erreur</p>';
                echo '<p>→ Importez le fichier database/activities_table_no_emoji.sql</p>';
            }
        }
        ?>
    </div>

    <div class="section">
        <h2>3. Test des fonctions</h2>
        <?php
        try {
            require_once 'includes/activities_functions.php';
            echo '<p class="success">✅ Fichier activities_functions.php chargé</p>';
            
            // Test getAllActivities
            $activities = getAllActivities(['limit' => 1]);
            echo '<p class="success">✅ getAllActivities() fonctionne (' . count($activities) . ' activité(s))</p>';
            
            // Test getAllCategories
            $categories = getAllCategories();
            echo '<p class="success">✅ getAllCategories() fonctionne (' . count($categories) . ' catégories)</p>';
            
            // Test formatEventDate
            $date = formatEventDate('2025-12-25');
            echo '<p class="success">✅ formatEventDate() fonctionne: ' . htmlspecialchars($date) . '</p>';
            
        } catch (Exception $e) {
            echo '<p class="error">❌ Erreur: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        }
        ?>
    </div>

    <div class="section">
        <h2>4. Extensions PHP requises</h2>
        <?php
        $extensions = [
            'pdo' => 'PDO (Base de données)',
            'pdo_mysql' => 'PDO MySQL',
            'intl' => 'Intl (Formatage des dates)',
            'json' => 'JSON',
            'mbstring' => 'Mbstring (Chaînes multi-octets)'
        ];
        
        foreach ($extensions as $ext => $description) {
            if (extension_loaded($ext)) {
                echo '<p class="success">✅ ' . $description . '</p>';
            } else {
                echo '<p class="error">⚠️ ' . $description . ' - Non chargée (peut causer des problèmes)</p>';
            }
        }
        ?>
    </div>

    <div class="section">
        <h2>5. Test de la page index.php</h2>
        <?php
        try {
            ob_start();
            include 'index.php';
            $output = ob_get_clean();
            
            if (strlen($output) > 100) {
                echo '<p class="success">✅ index.php génère du contenu (' . strlen($output) . ' caractères)</p>';
                echo '<p><a href="index.php" target="_blank">→ Voir la page index.php</a></p>';
            } else {
                echo '<p class="error">❌ index.php ne génère pas assez de contenu</p>';
                echo '<pre>' . htmlspecialchars(substr($output, 0, 500)) . '</pre>';
            }
        } catch (Exception $e) {
            echo '<p class="error">❌ Erreur dans index.php: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        }
        ?>
    </div>

    <div class="section">
        <h2>6. Configuration PHP</h2>
        <pre><?php
        echo "Version PHP: " . phpversion() . "\n";
        echo "display_errors: " . ini_get('display_errors') . "\n";
        echo "error_reporting: " . error_reporting() . "\n";
        echo "Fuseau horaire: " . date_default_timezone_get() . "\n";
        ?></pre>
    </div>

    <div class="section" style="background: #e3f2fd; border-left: 4px solid #2196F3;">
        <h2>✅ Résumé</h2>
        <p>Si tous les tests ci-dessus sont au vert, votre application devrait fonctionner.</p>
        <p>Si vous voyez des erreurs :</p>
        <ul>
            <li>❌ Erreur de connexion → Vérifiez includes/config.php</li>
            <li>❌ Tables manquantes → Importez database/activities_table_no_emoji.sql</li>
            <li>⚠️ Extension intl manquante → Normal, la version de secours est utilisée</li>
        </ul>
        <p><strong>Pages à tester :</strong></p>
        <ul>
            <li><a href="index.php">Page d'accueil</a></li>
            <li><a href="events/events-list.php">Liste des événements</a></li>
            <li><a href="database/test_connection.php">Test de connexion DB</a></li>
        </ul>
    </div>

</body>
</html>
