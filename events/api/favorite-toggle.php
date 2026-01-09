<?php
session_start();
header('Content-Type: application/json');
require_once '../../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$activityId = isset($_POST['activity_id']) ? (int)$_POST['activity_id'] : 0;
$action = $_POST['action'] ?? '';

if ($activityId <= 0 || !in_array($action, ['add', 'remove'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
    exit;
}

try {
    $pdo = getDB();
    
    if ($action === 'add') {
        // Ajouter aux favoris
        $stmt = $pdo->prepare("INSERT IGNORE INTO user_favorites (user_id, activity_id) VALUES (?, ?)");
        $stmt->execute([$userId, $activityId]);
        echo json_encode(['success' => true, 'action' => 'added', 'message' => 'Ajouté aux favoris']);
    } else {
        // Retirer des favoris
        $stmt = $pdo->prepare("DELETE FROM user_favorites WHERE user_id = ? AND activity_id = ?");
        $stmt->execute([$userId, $activityId]);
        echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Retiré des favoris']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    // En développement, renvoyer le message d'erreur détaillé
    $errorMsg = $e->getMessage();
    if (strpos($errorMsg, "Table") !== false && strpos($errorMsg, "doesn't exist") !== false) {
        echo json_encode(['success' => false, 'message' => 'Table user_favorites non créée. Exécutez database/install_favorites.php']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $errorMsg]);
    }
}
