<?php
require_once __DIR__ . '/../includes/config.php';
session_start();

header('Content-Type: application/json');

try {
    $pdo = getDB();
    
    $result = [
        'success' => true,
        'tables' => [],
        'user_points' => [],
        'current_user_id' => $_SESSION['user_id'] ?? null
    ];
    
    // Vérifier les tables
    $tables = ['user_points', 'points_history', 'badges', 'user_badges'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;
        $result['tables'][$table] = $exists;
        
        if ($exists) {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $result['tables'][$table . '_count'] = $stmt->fetchColumn();
        }
    }
    
    // Si l'utilisateur est connecté, vérifier ses points
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM user_points WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userPoints = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userPoints) {
            $result['user_points'] = $userPoints;
            
            // Calculer le rang manuellement
            $stmt = $pdo->prepare("SELECT COUNT(*) + 1 FROM user_points WHERE total_points > ?");
            $stmt->execute([$userPoints['total_points']]);
            $result['user_rank'] = $stmt->fetchColumn();
        } else {
            // Créer l'entrée
            $stmt = $pdo->prepare("INSERT INTO user_points (user_id, total_points, level) VALUES (?, 0, 1)");
            $stmt->execute([$_SESSION['user_id']]);
            $result['user_points_created'] = true;
        }
    }
    
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
