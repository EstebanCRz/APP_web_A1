<?php
/**
 * Fonctions de recherche et filtrage des événements
 * 
 * Ce fichier contient toutes les fonctions backend pour le système de recherche
 */

require_once __DIR__ . '/../../includes/config.php';

/**
 * Récupère toutes les activités avec filtres
 * 
 * @param array $filtres Tableau des filtres à appliquer
 *   - search: terme de recherche (string)
 *   - category: nom de la catégorie (string)
 *   - time_filter: moment de la journée - 'morning', 'afternoon', 'evening' (string)
 *   - date_filter: période - 'week', 'month', 'coming', 'past' (string)
 *   - limit: limite de résultats (int)
 * 
 * @return array Tableau d'activités filtrées
 */
function obtenirActivitesAvecFiltres($filtres = []) {
    $pdo = getDB();
    
    // Construction de la requête SQL de base
    $sql = "SELECT 
                a.id,
                a.title,
                a.description,
                a.excerpt,
                a.location,
                a.city,
                a.event_date,
                a.event_date as date,
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
    
    $parametres = [];
    
    // === FILTRE PAR RECHERCHE TEXTUELLE ===
    if (!empty($filtres['search'])) {
        $termeRecherche = '%' . $filtres['search'] . '%';
        $sql .= " AND (
            a.title LIKE :search1 
            OR a.location LIKE :search2 
            OR a.city LIKE :search3 
            OR a.description LIKE :search4 
            OR u.username LIKE :search5 
            OR u.first_name LIKE :search6 
            OR u.last_name LIKE :search7
        )";
        $parametres[':search1'] = $termeRecherche;
        $parametres[':search2'] = $termeRecherche;
        $parametres[':search3'] = $termeRecherche;
        $parametres[':search4'] = $termeRecherche;
        $parametres[':search5'] = $termeRecherche;
        $parametres[':search6'] = $termeRecherche;
        $parametres[':search7'] = $termeRecherche;
    }
    
    // === FILTRE PAR CATÉGORIE ===
    if (!empty($filtres['category'])) {
        $sql .= " AND c.name = :category";
        $parametres[':category'] = $filtres['category'];
    }
    
    // === FILTRE PAR PÉRIODE (DATE) ===
    if (!empty($filtres['date_filter'])) {
        switch ($filtres['date_filter']) {
            case 'week':
                // Cette semaine (7 prochains jours)
                $sql .= " AND a.event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
                break;
            
            case 'month':
                // Ce mois
                $sql .= " AND a.event_date BETWEEN CURDATE() AND LAST_DAY(CURDATE())";
                break;
            
            case 'coming':
                // À venir (tous les événements futurs)
                $sql .= " AND a.event_date >= CURDATE()";
                break;
            
            case 'past':
                // Passés
                $sql .= " AND a.event_date < CURDATE()";
                break;
        }
    }
    
    // === FILTRE PAR MOMENT DE LA JOURNÉE ===
    if (!empty($filtres['time_filter'])) {
        switch ($filtres['time_filter']) {
            case 'morning':
                // Matin : 06h00 - 11h59
                $sql .= " AND TIME(a.event_time) BETWEEN '06:00:00' AND '11:59:59'";
                break;
            
            case 'afternoon':
                // Après-midi : 12h00 - 17h59
                $sql .= " AND TIME(a.event_time) BETWEEN '12:00:00' AND '17:59:59'";
                break;
            
            case 'evening':
                // Soir : 18h00 - 23h59
                $sql .= " AND TIME(a.event_time) BETWEEN '18:00:00' AND '23:59:59'";
                break;
        }
    }
    
    // Tri par date et heure
    $sql .= " ORDER BY a.event_date ASC, a.event_time ASC";
    
    // Limite de résultats (pour page d'accueil par exemple)
    if (!empty($filtres['limit'])) {
        $sql .= " LIMIT :limit";
    }
    
    // Préparation et exécution
    try {
        $stmt = $pdo->prepare($sql);
        
        // Bind des paramètres
        foreach ($parametres as $cle => $valeur) {
            $stmt->bindValue($cle, $valeur);
        }
        
        // Bind de la limite si présente
        if (!empty($filtres['limit'])) {
            $stmt->bindValue(':limit', (int)$filtres['limit'], PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Erreur recherche activités : " . $e->getMessage());
        return [];
    }
}

/**
 * Construit l'URL avec les filtres actifs
 * 
 * @param array $filtresActuels Filtres actuellement actifs
 * @param string $nouveauFiltre Nom du nouveau filtre à ajouter
 * @param mixed $valeurFiltre Valeur du nouveau filtre
 * @return string Query string pour l'URL
 */
function construireUrlFiltres($filtresActuels, $nouveauFiltre, $valeurFiltre) {
    $parametres = [];
    
    // Conserver les filtres existants sauf celui qu'on modifie
    foreach ($filtresActuels as $cle => $valeur) {
        if (!empty($valeur) && $cle !== $nouveauFiltre) {
            $parametres[$cle] = $valeur;
        }
    }
    
    // Ajouter le nouveau filtre s'il n'est pas vide
    if (!empty($valeurFiltre)) {
        $parametres[$nouveauFiltre] = $valeurFiltre;
    }
    
    return http_build_query($parametres);
}

/**
 * Compte le nombre total de résultats pour une recherche donnée
 * 
 * @param array $filtres Filtres appliqués
 * @return int Nombre de résultats
 */
function compterResultatsRecherche($filtres = []) {
    $resultats = obtenirActivitesAvecFiltres($filtres);
    return count($resultats);
}

/**
 * Vérifie si des filtres sont actifs
 * 
 * @param array $filtres Tableau des filtres
 * @return bool True si au moins un filtre est actif
 */
function aDesFiltresActifs($filtres = []) {
    foreach ($filtres as $valeur) {
        if (!empty($valeur)) {
            return true;
        }
    }
    return false;
}

/**
 * Réinitialise tous les filtres
 * 
 * @return array Tableau de filtres vides
 */
function reinitialiserFiltres() {
    return [
        'search' => '',
        'category' => '',
        'time_filter' => '',
        'date_filter' => ''
    ];
}
