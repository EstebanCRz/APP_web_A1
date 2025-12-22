<?php
/**
 * API pour gérer les inscriptions aux activités
 */

// Configuration de session (AVANT session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

session_start();
require_once '../../includes/language.php';
header('Content-Type: application/json');

require_once '../../includes/activities_functions.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => t('common.must_login_register')]);
    exit;
}

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => t('common.invalid_method')]);
    exit;
}

// Récupérer les données
$data = json_decode(file_get_contents('php://input'), true);
$activityId = (int)($data['activity_id'] ?? 0);
$action = $data['action'] ?? '';
$userId = (int)$_SESSION['user_id'];

if ($activityId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => t('common.invalid_activity_id')]);
    exit;
}

try {
    if ($action === 'register') {
        // Inscription
        registerUserToActivity($activityId, $userId);
        
        // Créer ou récupérer le groupe de l'activité et créer une invitation
        require_once '../../includes/config.php';
        $pdo = getDB();
        
        // Récupérer les infos de l'activité (incluant le créateur)
        $activityInfo = getActivityById($activityId);
        $activityCreatorId = $activityInfo['creator_id']; // Le créateur de l'activité
        
        // Vérifier si un groupe existe déjà pour cette activité
        $stmt = $pdo->prepare("SELECT id FROM groups WHERE activity_id = ? LIMIT 1");
        $stmt->execute([$activityId]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$group) {
            // Créer le groupe avec le créateur de l'activité comme créateur du groupe
            $stmt = $pdo->prepare("
                INSERT INTO groups (name, description, activity_id, created_by)
                VALUES (?, ?, ?, ?)
            ");
            $groupName = "Groupe - " . $activityInfo['title'];
            $groupDesc = "Groupe pour l'activité: " . $activityInfo['title'];
            $stmt->execute([$groupName, $groupDesc, $activityId, $activityCreatorId]);
            $groupId = $pdo->lastInsertId();
            
            // Ajouter le créateur de l'activité comme admin du groupe
            $stmt = $pdo->prepare("
                INSERT INTO group_members (group_id, user_id, role)
                VALUES (?, ?, 'admin')
                ON DUPLICATE KEY UPDATE role = 'admin'
            ");
            $stmt->execute([$groupId, $activityCreatorId]);
            
            // Si c'est quelqu'un d'autre qui s'inscrit, créer une invitation
            if ($userId != $activityCreatorId) {
                $stmt = $pdo->prepare("
                    INSERT INTO group_invitations (group_id, user_id, invited_by, status)
                    VALUES (?, ?, ?, 'pending')
                    ON DUPLICATE KEY UPDATE status = 'pending', updated_at = CURRENT_TIMESTAMP
                ");
                $stmt->execute([$groupId, $userId, $activityCreatorId]);
            }
        } else {
            $groupId = $group['id'];
            
            // S'assurer que le créateur de l'activité est admin
            $stmt = $pdo->prepare("
                INSERT INTO group_members (group_id, user_id, role)
                VALUES (?, ?, 'admin')
                ON DUPLICATE KEY UPDATE role = 'admin'
            ");
            $stmt->execute([$groupId, $activityCreatorId]);
            
            // Vérifier si l'utilisateur est déjà membre
            $stmt = $pdo->prepare("
                SELECT id FROM group_members WHERE group_id = ? AND user_id = ?
            ");
            $stmt->execute([$groupId, $userId]);
            $isMember = $stmt->fetch();
            
            if (!$isMember) {
                // Créer une invitation
                $stmt = $pdo->prepare("
                    INSERT INTO group_invitations (group_id, user_id, invited_by, status)
                    VALUES (?, ?, 1, 'pending')
                    ON DUPLICATE KEY UPDATE status = 'pending', updated_at = CURRENT_TIMESTAMP
                ");
                $stmt->execute([$groupId, $userId]);
            }
        }
        
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
