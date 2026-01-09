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
    
    // Récupérer les demandes en attente (reçues)
    $stmt = $pdo->prepare("
        SELECT 
            u.id,
            u.username,
            u.first_name,
            u.last_name,
            f.requested_at
        FROM users u
        INNER JOIN friendships f ON f.user_id = u.id
        WHERE f.friend_id = ? AND f.status = 'pending'
        ORDER BY f.requested_at DESC
    ");
    
    $stmt->execute([$user_id]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'requests' => $requests
    ]);
    
} catch (PDOException $e) {
    error_log("Friend requests error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de chargement'
    ]);
}
