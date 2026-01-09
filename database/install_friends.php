<?php
require_once '../includes/config.php';

try {
    $pdo = getDB();
    
    // Lire le fichier SQL
    $sql = file_get_contents(__DIR__ . '/friends_table.sql');
    
    // Exécuter les requêtes
    $pdo->exec($sql);
    
    echo "✓ Table 'friendships' créée avec succès!<br>";
    echo "Vous pouvez maintenant utiliser le système d'amis.<br>";
    echo "<a href='../pages/friends.php'>Aller à la recherche d'amis</a>";
    
} catch (PDOException $e) {
    echo "Erreur lors de la création de la table : " . $e->getMessage();
}
