<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

require_once '../../includes/config.php';

$input = json_decode(file_get_contents('php://input'), true);
$query = $input['query'] ?? '';

if (strlen($query) < 2) {
    echo json_encode(['success' => false, 'message' => 'Requête trop courte']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $pdo = getDB();
    
    // Rechercher les utilisateurs (excluant l'utilisateur actuel)
    $stmt = $pdo->prepare("
        SELECT 
            u.id,
            u.username,
            u.first_name,
            u.last_name,
            CASE
                WHEN f1.status = 'accepted' OR f2.status = 'accepted' THEN 'accepted'
                WHEN f1.status = 'pending' THEN 'pending'
                WHEN f2.status = 'pending' THEN 'received'
                ELSE 'none'
            END as friendship_status
        FROM users u
        LEFT JOIN friendships f1 ON (f1.user_id = ? AND f1.friend_id = u.id)
        LEFT JOIN friendships f2 ON (f2.user_id = u.id AND f2.friend_id = ?)
        WHERE u.id != ?
        AND (
            u.username LIKE ?
            OR u.first_name LIKE ?
            OR u.last_name LIKE ?
            OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?
        )
        ORDER BY u.first_name, u.last_name
        LIMIT 20
    ");
    
    $searchTerm = "%{$query}%";
    $stmt->execute([
        $user_id, $user_id, $user_id,
        $searchTerm, $searchTerm, $searchTerm, $searchTerm
    ]);
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'users' => $users
    ]);
    
} catch (PDOException $e) {
    error_log("Friend search error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de recherche'
    ]);
}
