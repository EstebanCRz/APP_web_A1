<?php
/**
 * Système de gamification
 * Gestion des points, niveaux et badges
 */

// Points attribués pour chaque action
const POINTS = [
    'event_create' => 50,        // Créer un événement
    'event_attend' => 20,        // Participer à un événement
    'event_complete' => 30,      // Compléter un événement (après la date)
    'review_leave' => 10,        // Laisser un avis
    'friend_add' => 5,           // Ajouter un ami
    'group_create' => 15,        // Créer un groupe
    'message_send' => 1,         // Envoyer un message (limité)
    'profile_complete' => 25,    // Compléter son profil
    'first_login' => 10,         // Première connexion
];

// Niveaux et points requis
function getLevelFromPoints($points) {
    // Formule: niveau = floor(sqrt(points / 100))
    // Niveau 1: 0-99 points
    // Niveau 2: 100-399 points
    // Niveau 3: 400-899 points
    // etc.
    return max(1, floor(sqrt($points / 100)) + 1);
}

function getPointsForNextLevel($currentLevel) {
    // Points nécessaires pour atteindre le niveau suivant
    return pow($currentLevel, 2) * 100;
}

/**
 * Ajouter des points à un utilisateur
 */
function addPoints($userId, $points, $actionType, $description = '', $referenceId = null) {
    $pdo = getDB();
    
    try {
        $pdo->beginTransaction();
        
        // Ajouter l'historique
        $stmt = $pdo->prepare("
            INSERT INTO points_history (user_id, points, action_type, description, reference_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $points, $actionType, $description, $referenceId]);
        
        // Mettre à jour le total et le niveau
        $stmt = $pdo->prepare("
            INSERT INTO user_points (user_id, total_points, level)
            VALUES (?, ?, 1)
            ON DUPLICATE KEY UPDATE total_points = total_points + ?
        ");
        $stmt->execute([$userId, $points, $points]);
        
        // Récupérer les nouveaux points totaux
        $stmt = $pdo->prepare("SELECT total_points FROM user_points WHERE user_id = ?");
        $stmt->execute([$userId]);
        $totalPoints = $stmt->fetchColumn();
        
        // Calculer et mettre à jour le niveau
        $newLevel = getLevelFromPoints($totalPoints);
        $stmt = $pdo->prepare("UPDATE user_points SET level = ? WHERE user_id = ?");
        $stmt->execute([$newLevel, $userId]);
        
        $pdo->commit();
        
        // Vérifier les nouveaux badges
        checkBadges($userId);
        
        return [
            'success' => true,
            'points' => $points,
            'total_points' => $totalPoints,
            'level' => $newLevel
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Erreur addPoints: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Obtenir les statistiques d'un utilisateur
 */
function getUserStats($userId) {
    $pdo = getDB();
    
    // Valeurs par défaut en cas d'erreur
    $defaultStats = [
        'total_points' => 0,
        'level' => 1,
        'rank' => 1,
        'points_for_next_level' => 100,
        'points_in_current_level' => 0,
        'points_needed_for_next' => 100,
        'progress_percent' => 0,
        'badges' => [],
        'badge_count' => 0,
        'stats' => [
            'events_created' => 0,
            'events_attended' => 0,
            'reviews_count' => 0,
            'friends_count' => 0,
            'groups_created' => 0,
            'messages_sent' => 0
        ]
    ];
    
    try {
        // Points et niveau
        $stmt = $pdo->prepare("
            SELECT total_points, level 
            FROM user_points 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $pointsData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$pointsData) {
            // Initialiser si n'existe pas
            $stmt = $pdo->prepare("
                INSERT INTO user_points (user_id, total_points, level) 
                VALUES (?, 0, 1)
            ");
            $stmt->execute([$userId]);
            $pointsData = ['total_points' => 0, 'level' => 1];
        }
        
        // Statistiques détaillées
        // Note: Les tables groups et group_messages peuvent ne pas avoir les bonnes colonnes
        $stmt = $pdo->prepare("
            SELECT 
                (SELECT COUNT(*) FROM activities WHERE creator_id = ?) as events_created,
                (SELECT COUNT(*) FROM activity_registrations WHERE user_id = ? AND status = 'confirmed') as events_attended,
                (SELECT COUNT(*) FROM activity_reviews WHERE user_id = ?) as reviews_count,
                (SELECT COUNT(*) FROM friendships WHERE (user_id = ? OR friend_id = ?) AND status = 'accepted') as friends_count,
                0 as groups_created,
                0 as messages_sent
        ");
        $stmt->execute([$userId, $userId, $userId, $userId, $userId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Badges
        $stmt = $pdo->prepare("
            SELECT b.*, ub.earned_at
            FROM user_badges ub
            JOIN badges b ON ub.badge_id = b.id
            WHERE ub.user_id = ?
            ORDER BY ub.earned_at DESC
        ");
        $stmt->execute([$userId]);
        $badges = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Classement - avec gestion d'erreur améliorée
        $rank = 1;
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) + 1 as rank
                FROM user_points
                WHERE total_points > (SELECT IFNULL(total_points, 0) FROM user_points WHERE user_id = ?)
            ");
            $stmt->execute([$userId]);
            $calculatedRank = $stmt->fetchColumn();
            
            if ($calculatedRank && $calculatedRank > 0) {
                $rank = $calculatedRank;
            }
        } catch (Exception $e) {
            // En cas d'erreur, calculer le rang d'une autre manière
            $stmt = $pdo->query("SELECT COUNT(*) FROM user_points");
            $totalUsers = $stmt->fetchColumn();
            $rank = max(1, $totalUsers); // Dernier rang par défaut
        }
        
        $currentLevel = (int)$pointsData['level'];
        $totalPoints = (int)$pointsData['total_points'];
        $pointsForNext = getPointsForNextLevel($currentLevel);
        $pointsForCurrent = $currentLevel > 1 ? getPointsForNextLevel($currentLevel - 1) : 0;
        $progressInLevel = $totalPoints - $pointsForCurrent;
        $pointsNeeded = $pointsForNext - $pointsForCurrent;
        
        return [
            'total_points' => $totalPoints,
            'level' => $currentLevel,
            'rank' => $rank,
            'points_for_next_level' => $pointsForNext,
            'points_in_current_level' => $progressInLevel,
            'points_needed_for_next' => $pointsNeeded,
            'progress_percent' => round(($progressInLevel / $pointsNeeded) * 100),
            'badges' => $badges,
            'badge_count' => count($badges),
            'stats' => $stats
        ];
        
    } catch (Exception $e) {
        error_log("Erreur getUserStats: " . $e->getMessage());
        // Retourner les valeurs par défaut en cas d'erreur
        return $defaultStats;
    }
}

/**
 * Vérifier et attribuer les badges
 */
function checkBadges($userId) {
    $pdo = getDB();
    
    try {
        $stats = getUserStats($userId);
        if (!$stats) return;
        
        // Récupérer tous les badges non encore obtenus
        $stmt = $pdo->prepare("
            SELECT b.*
            FROM badges b
            WHERE b.id NOT IN (
                SELECT badge_id FROM user_badges WHERE user_id = ?
            )
        ");
        $stmt->execute([$userId]);
        $availableBadges = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $newBadges = [];
        
        foreach ($availableBadges as $badge) {
            $earned = false;
            
            switch ($badge['condition_type']) {
                case 'events_created':
                    $earned = $stats['stats']['events_created'] >= $badge['condition_value'];
                    break;
                case 'events_attended':
                    $earned = $stats['stats']['events_attended'] >= $badge['condition_value'];
                    break;
                case 'friends_count':
                    $earned = $stats['stats']['friends_count'] >= $badge['condition_value'];
                    break;
                case 'reviews_count':
                    $earned = $stats['stats']['reviews_count'] >= $badge['condition_value'];
                    break;
                case 'groups_created':
                    $earned = $stats['stats']['groups_created'] >= $badge['condition_value'];
                    break;
                case 'messages_sent':
                    $earned = $stats['stats']['messages_sent'] >= $badge['condition_value'];
                    break;
                case 'level':
                    $earned = $stats['level'] >= $badge['condition_value'];
                    break;
            }
            
            if ($earned) {
                // Attribuer le badge
                $stmt = $pdo->prepare("
                    INSERT IGNORE INTO user_badges (user_id, badge_id)
                    VALUES (?, ?)
                ");
                $stmt->execute([$userId, $badge['id']]);
                
                if ($stmt->rowCount() > 0) {
                    $newBadges[] = $badge;
                }
            }
        }
        
        return $newBadges;
        
    } catch (Exception $e) {
        error_log("Erreur checkBadges: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtenir le classement global
 */
function getLeaderboard($limit = 50, $offset = 0) {
    $pdo = getDB();
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                u.id,
                u.username,
                u.first_name,
                u.last_name,
                up.total_points,
                up.level,
                (SELECT COUNT(*) FROM user_badges WHERE user_id = u.id) as badge_count,
                (SELECT COUNT(*) FROM activities WHERE creator_id = u.id) as events_created,
                (SELECT COUNT(*) FROM activity_registrations WHERE user_id = u.id AND status = 'confirmed') as events_attended
            FROM users u
            JOIN user_points up ON u.id = up.user_id
            ORDER BY up.total_points DESC, up.level DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ajouter le rang
        foreach ($leaderboard as $index => &$user) {
            $user['rank'] = $offset + $index + 1;
        }
        
        return $leaderboard;
        
    } catch (Exception $e) {
        error_log("Erreur getLeaderboard: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtenir les derniers badges obtenus (feed d'activité)
 */
function getRecentBadges($limit = 10) {
    $pdo = getDB();
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                u.username,
                u.first_name,
                u.last_name,
                b.icon,
                b.name_fr,
                b.name_en,
                ub.earned_at
            FROM user_badges ub
            JOIN users u ON ub.user_id = u.id
            JOIN badges b ON ub.badge_id = b.id
            ORDER BY ub.earned_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Erreur getRecentBadges: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtenir le nom de niveau en fonction du niveau actuel
 */
function getLevelName($level) {
    if ($level >= 50) return ['fr' => 'Légende', 'en' => 'Legend'];
    if ($level >= 40) return ['fr' => 'Maître', 'en' => 'Master'];
    if ($level >= 30) return ['fr' => 'Expert', 'en' => 'Expert'];
    if ($level >= 20) return ['fr' => 'Professionnel', 'en' => 'Professional'];
    if ($level >= 10) return ['fr' => 'Expérimenté', 'en' => 'Experienced'];
    if ($level >= 5) return ['fr' => 'Intermédiaire', 'en' => 'Intermediate'];
    return ['fr' => 'Débutant', 'en' => 'Beginner'];
}

/**
 * Obtenir la couleur du niveau
 */
function getLevelColor($level) {
    if ($level >= 50) return '#FFD700'; // Or
    if ($level >= 40) return '#E5E4E2'; // Platine
    if ($level >= 30) return '#CD7F32'; // Bronze
    if ($level >= 20) return '#4169E1'; // Bleu royal
    if ($level >= 10) return '#32CD32'; // Vert lime
    if ($level >= 5) return '#87CEEB';  // Bleu ciel
    return '#808080'; // Gris
}
