<?php
session_start();
require_once '../../includes/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

$user_id = $_SESSION['user_id'];
$pdo = getDB();

try {
    // Compter les invitations en attente
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM group_invitations
        WHERE user_id = ? AND status = 'pending'
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Compter les messages privés non lus
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM private_messages pm
        JOIN private_conversations pc ON pm.conversation_id = pc.id
        WHERE (pc.user1_id = ? OR pc.user2_id = ?)
        AND pm.sender_id != ?
        AND pm.is_read = FALSE
    ");
    $stmt->execute([$user_id, $user_id, $user_id]);
    $unreadMessages = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'group_invitations' => (int)$result['count'],
        'unread_messages' => (int)$unreadMessages['count'],
        'total' => (int)$result['count'] + (int)$unreadMessages['count']
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
