<?php
/**
 * Fonctions pour la gestion des activités
 */

require_once __DIR__ . '/config.php';

/**
 * Récupère toutes les activités avec leurs créateurs et catégories
 */
function getAllActivities($filters = []) {
    $pdo = getDB();
    
    $sql = "SELECT 
                a.id,
                a.title,
                a.description,
                a.excerpt,
                a.location,
                a.city,
                a.event_date,
                a.event_time,
                a.max_participants,
                a.current_participants,
                a.image,
                a.status,
                a.created_at,
                c.name as category_name,
                c.color as category_color,
                c.icon as category_icon,
                u.id as creator_id,
                u.username as creator_username,
                u.first_name as creator_first_name,
                u.last_name as creator_last_name,
                u.avatar as creator_avatar
            FROM activities a
            INNER JOIN activity_categories c ON a.category_id = c.id
            INNER JOIN users u ON a.creator_id = u.id
            WHERE a.status = 'active'";
    
    $params = [];
    
    // Filtrage par recherche
    if (!empty($filters['search'])) {
        $sql .= " AND (a.title LIKE :search OR a.location LIKE :search OR a.city LIKE :search OR a.description LIKE :search OR u.username LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search)";
        $params[':search'] = '%' . $filters['search'] . '%';
    }
    
    // Filtrage par catégorie
    if (!empty($filters['category'])) {
        $sql .= " AND c.name = :category";
        $params[':category'] = $filters['category'];
    }
    
    // Filtrage par date
    if (!empty($filters['date_filter'])) {
        switch ($filters['date_filter']) {
            case 'week':
                $sql .= " AND a.event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $sql .= " AND a.event_date BETWEEN CURDATE() AND LAST_DAY(CURDATE())";
                break;
            case 'coming':
                $sql .= " AND a.event_date >= CURDATE()";
                break;
        }
    }
    
    // Filtrage par heure
    if (!empty($filters['time_filter'])) {
        switch ($filters['time_filter']) {
            case 'morning':
                $sql .= " AND TIME(a.event_time) BETWEEN '06:00:00' AND '11:59:59'";
                break;
            case 'afternoon':
                $sql .= " AND TIME(a.event_time) BETWEEN '12:00:00' AND '17:59:59'";
                break;
            case 'evening':
                $sql .= " AND TIME(a.event_time) BETWEEN '18:00:00' AND '23:59:59'";
                break;
        }
    }
    
    $sql .= " ORDER BY a.event_date ASC, a.event_time ASC";
    
    // Limite pour la page d'accueil
    if (!empty($filters['limit'])) {
        $sql .= " LIMIT :limit";
    }
    
    $stmt = $pdo->prepare($sql);
    
    // Bind des paramètres
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    if (!empty($filters['limit'])) {
        $stmt->bindValue(':limit', (int)$filters['limit'], PDO::PARAM_INT);
    }
    
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Récupère une activité par son ID
 */
function getActivityById($id) {
    $pdo = getDB();
    
    $sql = "SELECT 
                a.*,
                c.name as category_name,
                c.color as category_color,
                c.icon as category_icon,
                u.id as creator_id,
                u.username as creator_username,
                u.first_name as creator_first_name,
                u.last_name as creator_last_name,
                u.avatar as creator_avatar,
                u.email as creator_email
            FROM activities a
            INNER JOIN activity_categories c ON a.category_id = c.id
            INNER JOIN users u ON a.creator_id = u.id
            WHERE a.id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

/**
 * Récupère toutes les catégories
 */
function getAllCategories() {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT * FROM activity_categories ORDER BY name");
    return $stmt->fetchAll();
}

/**
 * Vérifie si un utilisateur est inscrit à une activité
 */
function isUserRegistered($activityId, $userId) {
    $pdo = getDB();
    
    $sql = "SELECT COUNT(*) FROM activity_registrations 
            WHERE activity_id = :activity_id 
            AND user_id = :user_id 
            AND status = 'registered'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':activity_id' => $activityId,
        ':user_id' => $userId
    ]);
    
    return $stmt->fetchColumn() > 0;
}

/**
 * Inscrit un utilisateur à une activité
 */
function registerUserToActivity($activityId, $userId) {
    $pdo = getDB();
    
    try {
        $pdo->beginTransaction();
        
        // Vérifier si l'activité a encore de la place
        $activity = getActivityById($activityId);
        if (!$activity) {
            throw new Exception("Activité non trouvée");
        }
        
        if ($activity['current_participants'] >= $activity['max_participants']) {
            throw new Exception("Cette activité est complète");
        }
        
        // Vérifier si déjà inscrit
        if (isUserRegistered($activityId, $userId)) {
            throw new Exception("Vous êtes déjà inscrit à cette activité");
        }
        
        // Inscrire l'utilisateur
        $sql = "INSERT INTO activity_registrations (activity_id, user_id, status) 
                VALUES (:activity_id, :user_id, 'registered')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':activity_id' => $activityId,
            ':user_id' => $userId
        ]);
        
        // Mettre à jour le nombre de participants
        $sql = "UPDATE activities 
                SET current_participants = current_participants + 1 
                WHERE id = :activity_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':activity_id' => $activityId]);
        
        $pdo->commit();
        return true;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Désinscrit un utilisateur d'une activité
 */
function unregisterUserFromActivity($activityId, $userId) {
    $pdo = getDB();
    
    try {
        $pdo->beginTransaction();
        
        // Supprimer l'inscription
        $sql = "DELETE FROM activity_registrations 
                WHERE activity_id = :activity_id 
                AND user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':activity_id' => $activityId,
            ':user_id' => $userId
        ]);
        
        // Mettre à jour le nombre de participants
        $sql = "UPDATE activities 
                SET current_participants = current_participants - 1 
                WHERE id = :activity_id 
                AND current_participants > 0";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':activity_id' => $activityId]);
        
        $pdo->commit();
        return true;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Crée une nouvelle activité
 */
function createActivity($data) {
    $pdo = getDB();
    
    $sql = "INSERT INTO activities (
                title, description, excerpt, category_id, creator_id,
                location, city, event_date, event_time, max_participants, image
            ) VALUES (
                :title, :description, :excerpt, :category_id, :creator_id,
                :location, :city, :event_date, :event_time, :max_participants, :image
            )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':title' => $data['title'],
        ':description' => $data['description'],
        ':excerpt' => $data['excerpt'] ?? substr($data['description'], 0, 200),
        ':category_id' => $data['category_id'],
        ':creator_id' => $data['creator_id'],
        ':location' => $data['location'],
        ':city' => $data['city'],
        ':event_date' => $data['event_date'],
        ':event_time' => $data['event_time'],
        ':max_participants' => $data['max_participants'],
        ':image' => $data['image'] ?? null
    ]);
    
    return $pdo->lastInsertId();
}

/**
 * Formate une date pour l'affichage
 */
function formatEventDate($date) {
    $dateObj = new DateTime($date);
    
    // Utiliser IntlDateFormatter si disponible, sinon utiliser une version de secours
    if (extension_loaded('intl')) {
        $formatter = new IntlDateFormatter(
            'fr_FR',
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE,
            'Europe/Paris',
            IntlDateFormatter::GREGORIAN,
            'EEE d MMM'
        );
        return $formatter->format($dateObj);
    } else {
        // Version de secours sans extension intl
        $jours = ['dim', 'lun', 'mar', 'mer', 'jeu', 'ven', 'sam'];
        $mois = ['', 'jan', 'fév', 'mar', 'avr', 'mai', 'juin', 'juil', 'aoû', 'sep', 'oct', 'nov', 'déc'];
        
        $jour = $jours[$dateObj->format('w')];
        $numJour = $dateObj->format('j');
        $nomMois = $mois[(int)$dateObj->format('n')];
        
        return "$jour. $numJour $nomMois";
    }
}

/**
 * Formate une heure pour l'affichage
 */
function formatEventTime($time) {
    $timeObj = new DateTime($time);
    return $timeObj->format('H\hi');
}

/**
 * Récupère les activités créées par un utilisateur
 */
function getUserCreatedActivities($userId) {
    $pdo = getDB();
    
    $sql = "SELECT 
                a.*,
                c.name as category_name,
                c.color as category_color
            FROM activities a
            INNER JOIN activity_categories c ON a.category_id = c.id
            WHERE a.creator_id = :user_id
            ORDER BY a.event_date DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll();
}

/**
 * Récupère les activités auxquelles un utilisateur est inscrit
 */
function getUserRegisteredActivities($userId) {
    $pdo = getDB();
    
    $sql = "SELECT 
                a.*,
                c.name as category_name,
                c.color as category_color,
                u.username as creator_username,
                u.first_name as creator_first_name,
                u.last_name as creator_last_name
            FROM activities a
            INNER JOIN activity_categories c ON a.category_id = c.id
            INNER JOIN users u ON a.creator_id = u.id
            INNER JOIN activity_registrations r ON a.id = r.activity_id
            WHERE r.user_id = :user_id 
            AND r.status = 'registered'
            ORDER BY a.event_date ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll();
}

/**
 * Récupère la liste des participants d'une activité
 */
function getActivityParticipants($activityId) {
    $pdo = getDB();
    
    $sql = "SELECT 
                u.id,
                u.username,
                u.first_name,
                u.last_name,
                u.avatar,
                r.registered_at
            FROM activity_registrations r
            INNER JOIN users u ON r.user_id = u.id
            WHERE r.activity_id = :activity_id 
            AND r.status = 'registered'
            ORDER BY r.registered_at ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':activity_id' => $activityId]);
    return $stmt->fetchAll();
}
