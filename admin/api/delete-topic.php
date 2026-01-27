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

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$topicId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$topicId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID invalide']);
    exit;
}

try {
    $pdo = getDB();
    $pdo->beginTransaction();
    
    // Supprimer les posts du topic
    $stmt = $pdo->prepare("DELETE FROM forum_posts WHERE topic_id = ?");
    $stmt->execute([$topicId]);
    
    // Supprimer le topic
    $stmt = $pdo->prepare("DELETE FROM forum_topics WHERE id = ?");
    $stmt->execute([$topicId]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Topic supprimé avec succès'
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}
