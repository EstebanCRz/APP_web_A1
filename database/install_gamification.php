<?php
/**
 * Script d'installation des tables de gamification
 */

require_once __DIR__ . '/../includes/config.php';

echo "========================================\n";
echo "Installation des tables de Gamification\n";
echo "========================================\n\n";

try {
    $pdo = getDB();
    
    // Lire le fichier SQL
    $sql = file_get_contents(__DIR__ . '/gamification_tables.sql');
    
    // Diviser les requêtes
    $queries = array_filter(
        array_map('trim', explode(';', $sql)),
        function($query) {
            return !empty($query) && !preg_match('/^--/', $query);
        }
    );
    
    echo "Exécution de " . count($queries) . " requêtes SQL...\n\n";
    
    $success = 0;
    $errors = 0;
    
    foreach ($queries as $query) {
        try {
            $pdo->exec($query);
            $success++;
            
            // Afficher le nom de la table créée si c'est un CREATE TABLE
            if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $query, $matches)) {
                echo "✓ Table '{$matches[1]}' créée\n";
            } elseif (preg_match('/INSERT INTO.*?`?(\w+)`?/i', $query, $matches)) {
                echo "✓ Données insérées dans '{$matches[1]}'\n";
            }
        } catch (PDOException $e) {
            // Ignorer les erreurs "table already exists"
            if (strpos($e->getMessage(), 'already exists') === false) {
                $errors++;
                echo "✗ Erreur: " . $e->getMessage() . "\n";
            } else {
                echo "ℹ Table déjà existante (ignorée)\n";
            }
        }
    }
    
    echo "\n========================================\n";
    echo "Installation terminée!\n";
    echo "========================================\n\n";
    echo "Succès: $success requêtes exécutées\n";
    if ($errors > 0) {
        echo "Erreurs: $errors\n";
    }
    
    echo "\nLes tables suivantes ont été créées:\n";
    echo "  - user_points\n";
    echo "  - points_history\n";
    echo "  - badges\n";
    echo "  - user_badges\n\n";
    
    // Compter les badges
    $stmt = $pdo->query("SELECT COUNT(*) FROM badges");
    $badgeCount = $stmt->fetchColumn();
    echo "Badges insérés: $badgeCount\n\n";
    
    echo "Système de gamification installé avec succès!\n";
    
} catch (Exception $e) {
    echo "\n[ERREUR] Installation échouée!\n";
    echo "Message: " . $e->getMessage() . "\n";
    exit(1);
}
