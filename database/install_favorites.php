<?php
/**
 * Script d'installation de la table user_favorites
 */

require_once '../includes/config.php';

echo "<h2>Installation de la table user_favorites</h2>";

try {
    $pdo = getDB();
    
    // Lire le fichier SQL
    $sql = file_get_contents(__DIR__ . '/favorites_table.sql');
    
    // Supprimer les commentaires
    $sql = preg_replace('/--.*$/m', '', $sql);
    
    // Séparer les requêtes
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($queries as $query) {
        if (!empty($query)) {
            $pdo->exec($query);
            echo "<p style='color: green;'>✓ Requête exécutée : " . substr($query, 0, 50) . "...</p>";
        }
    }
    
    echo "<h3 style='color: green;'>✅ Table user_favorites créée avec succès !</h3>";
    echo "<p><a href='../events/events-list.php'>Retour aux événements</a></p>";
    
} catch (PDOException $e) {
    echo "<h3 style='color: red;'>❌ Erreur : " . htmlspecialchars($e->getMessage()) . "</h3>";
}
