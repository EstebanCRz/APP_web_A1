<?php
/**
 * API pour gérer les messages de chat des activités
 * Stockage temporaire dans des fichiers JSON
 */

// Configuration de session (AVANT session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

session_start();
require_once '../../includes/language.php';
header('Content-Type: application/json');

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => t('common.must_login')]);
    exit;
}

// Dossier pour stocker les messages (créer si n'existe pas)
$messagesDir = __DIR__ . '/../data/messages/';
if (!file_exists($messagesDir)) {
    mkdir($messagesDir, 0755, true);
}

$activityId = (int) ($_GET['activity_id'] ?? $_POST['activity_id'] ?? 0);
if ($activityId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => t('common.invalid_activity_id')]);
    exit;
}

$messagesFile = $messagesDir . 'activity_' . $activityId . '.json';

// GET - Récupérer les messages
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (file_exists($messagesFile)) {
        $messages = json_decode(file_get_contents($messagesFile), true) ?? [];
    } else {
        $messages = [];
    }
    
    echo json_encode(['success' => true, 'messages' => $messages]);
    exit;
}

// POST - Envoyer un message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $messageText = trim($data['message'] ?? '');
    
    if (empty($messageText)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Message vide']);
        exit;
    }
    
    // Charger les messages existants
    $messages = [];
    if (file_exists($messagesFile)) {
        $messages = json_decode(file_get_contents($messagesFile), true) ?? [];
    }
    
    // Créer le nouveau message
    $newMessage = [
        'id' => uniqid(),
        'user_id' => $_SESSION['user_id'],
        'user_name' => ($_SESSION['user_first_name'] ?? '') . ' ' . ($_SESSION['user_last_name'] ?? ''),
        'username' => $_SESSION['user_username'] ?? 'user' . $_SESSION['user_id'],
        'message' => htmlspecialchars($messageText, ENT_QUOTES, 'UTF-8'),
        'timestamp' => time(),
        'datetime' => date('Y-m-d H:i:s')
    ];
    
    // Ajouter le message
    $messages[] = $newMessage;
    
    // Limiter à 100 messages max
    if (count($messages) > 100) {
        $messages = array_slice($messages, -100);
    }
    
    // Sauvegarder
    file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    echo json_encode(['success' => true, 'message' => $newMessage]);
    exit;
}

// DELETE - Supprimer un message (optionnel)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $messageId = $data['message_id'] ?? '';
    
    if (file_exists($messagesFile)) {
        $messages = json_decode(file_get_contents($messagesFile), true) ?? [];
        
        // Filtrer le message à supprimer (seulement si c'est l'auteur)
        $messages = array_filter($messages, function($msg) use ($messageId) {
            return $msg['id'] !== $messageId || $msg['user_id'] !== $_SESSION['user_id'];
        });
        
        $messages = array_values($messages); // Réindexer
        file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
