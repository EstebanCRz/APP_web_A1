<?php
session_start();
require_once '../../includes/config.php';

header('Content-Type: application/json');

// Vérifier admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

$messageId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$messageId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID invalide']);
    exit;
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare("UPDATE contact_messages SET read_status = 1 WHERE id = ?");
    $stmt->execute([$messageId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Message marqué comme lu'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}
