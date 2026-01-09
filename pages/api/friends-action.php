<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

require_once '../../includes/config.php';
require_once '../../includes/gamification.php';

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$friend_id = (int)($input['friend_id'] ?? 0);
$user_id = $_SESSION['user_id'];

if (!$friend_id || $friend_id === $user_id) {
    echo json_encode(['success' => false, 'message' => 'ID invalide']);
    exit;
}

try {
    $pdo = getDB();
    
    switch ($action) {
        case 'send':
            // Envoyer une demande d'ami
            $stmt = $pdo->prepare("
                INSERT INTO friendships (user_id, friend_id, status) 
                VALUES (?, ?, 'pending')
                ON DUPLICATE KEY UPDATE status = 'pending', requested_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$user_id, $friend_id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Demande envoyée'
            ]);
            break;
            
        case 'accept':
            // Accepter une demande (mise à jour du statut)
            $stmt = $pdo->prepare("
                UPDATE friendships 
                SET status = 'accepted', accepted_at = CURRENT_TIMESTAMP
                WHERE user_id = ? AND friend_id = ? AND status = 'pending'
            ");
            $stmt->execute([$friend_id, $user_id]);
            
            if ($stmt->rowCount() > 0) {
                // Attribuer des points aux deux utilisateurs pour l'amitié
                addPoints($user_id, 5, 'friend_add');
                checkBadges($user_id);
                addPoints($friend_id, 5, 'friend_add');
                checkBadges($friend_id);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Demande acceptée'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Demande introuvable'
                ]);
            }
            break;
            
        case 'decline':
            // Refuser une demande
            $stmt = $pdo->prepare("
                DELETE FROM friendships 
                WHERE user_id = ? AND friend_id = ? AND status = 'pending'
            ");
            $stmt->execute([$friend_id, $user_id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Demande refusée'
            ]);
            break;
            
        case 'remove':
            // Retirer un ami (supprimer la relation dans les deux sens)
            $stmt = $pdo->prepare("
                DELETE FROM friendships 
                WHERE (user_id = ? AND friend_id = ?) 
                   OR (user_id = ? AND friend_id = ?)
            ");
            $stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Ami retiré'
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Action invalide'
            ]);
    }
    
} catch (PDOException $e) {
    error_log("Friend action error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de traitement'
    ]);
}
