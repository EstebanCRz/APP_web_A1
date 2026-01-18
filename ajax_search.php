<?php
// ajax_search.php
require_once 'includes/config.php';
require_once 'includes/activities_functions.php';

// On indique au navigateur qu'on va renvoyer du JSON (format de données)
header('Content-Type: application/json');

// On récupère les variables envoyées par le JavaScript
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? 'all';

try {
    // On appelle la fonction SQL avec les filtres
    // Note : Ta fonction getAllActivities doit être capable de gérer ces filtres
    $results = getAllActivities([
        'search' => $search, 
        'category' => $category,
        'limit' => 20
    ]);

    $activities = [];
    if ($results) {
        foreach ($results as $act) {
            $activities[] = [
                'id'    => $act['id'],
                'title' => $act['title'],
                'type'  => $act['category_name'],
                'loc'   => ($act['location'] ?? '') . ', ' . ($act['city'] ?? ''),
                'color' => $act['category_color'] ?? '#55D5E0',
                'img'   => $act['image'] ?? 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=400'
            ];
        }
    }

    // On transforme le tableau PHP en objet JSON pour le JavaScript
    echo json_encode($activities);

} catch (Exception $e) {
    // En cas d'erreur, on renvoie le message d'erreur en JSON
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}