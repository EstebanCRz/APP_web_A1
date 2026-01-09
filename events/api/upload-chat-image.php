<?php
/**
 * API pour uploader une image dans un chat de groupe
 */

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Erreur lors de l\'upload');
    }
    
    $file = $_FILES['image'];
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    // Vérifier l'extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        throw new Exception('Type de fichier non autorisé');
    }
    
    // Vérifier le type MIME déclaré
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Type de fichier non autorisé');
    }
    
    // Vérifier la taille
    if ($file['size'] > $maxSize) {
        throw new Exception('Fichier trop volumineux (max 5MB)');
    }
    
    // Créer un nom unique
    $filename = uniqid('chat_') . '_' . $user_id . '_' . time() . '.' . $extension;
    $uploadPath = __DIR__ . '/../../uploads/messages/' . $filename;
    
    // Déplacer le fichier
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        throw new Exception('Erreur lors de la sauvegarde');
    }
    
    echo json_encode([
        'success' => true,
        'image_path' => 'uploads/messages/' . $filename
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
