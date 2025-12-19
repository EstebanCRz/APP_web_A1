<?php
/**
 * API pour gérer les inscriptions aux activités
 */

// Configuration de session (AVANT session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

session_start();
header('Content-Type: application/json');

require_once '../../includes/activities_functions.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour vous inscrire']);
    exit;
}

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Récupérer les données
$data = json_decode(file_get_contents('php://input'), true);
$activityId = (int)($data['activity_id'] ?? 0);
$action = $data['action'] ?? '';
$userId = (int)$_SESSION['user_id'];

if ($activityId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID d\'activité invalide']);
    exit;
}

try {
    if ($action === 'register') {
        // Inscription
        registerUserToActivity($activityId, $userId);
        
        // Récupérer les données mises à jour
        $activity = getActivityById($activityId);
        
        echo json_encode([
            'success' => true,
            'message' => 'Inscription réussie',
            'current_participants' => $activity['current_participants'],
            'max_participants' => $activity['max_participants']
        ]);
        
    } elseif ($action === 'unregister') {
        // Désinscription
        unregisterUserFromActivity($activityId, $userId);
        
        // Récupérer les données mises à jour
        $activity = getActivityById($activityId);
        
        echo json_encode([
            'success' => true,
            'message' => 'Désinscription réussie',
            'current_participants' => $activity['current_participants'],
            'max_participants' => $activity['max_participants']
        ]);
        
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Action invalide']);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
