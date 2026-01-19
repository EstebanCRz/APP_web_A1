<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

require_once '../../includes/config.php';

$user_id = $_SESSION['user_id'];

try {
    $pdo = getDB();
    
    // Récupérer tous les amis acceptés
    $stmt = $pdo->prepare("
        SELECT 
            u.id,
            u.username,
            u.first_name,
            u.last_name
        FROM users u
        INNER JOIN friendships f ON (
            (f.user_id = ? AND f.friend_id = u.id)
            OR (f.friend_id = ? AND f.user_id = u.id)
        )
        WHERE f.status = 'accepted'
        ORDER BY u.first_name, u.last_name
    ");
    
    $stmt->execute([$user_id, $user_id]);
    $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'friends' => $friends
    ]);
    
} catch (PDOException $e) {
    error_log("Friends list error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de chargement'
    ]);
}
