<?php
require_once '../includes/config.php';
require_once '../includes/activities_functions.php';

header('Content-Type: application/json');

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? 'all';

try {
    $activitiesFromDB = getAllActivities();
    $filteredActivities = [];

    foreach ($activitiesFromDB as $act) {
        $matchesSearch = empty($search) || stripos($act['title'], $search) !== false || stripos($act['location'], $search) !== false;
        $matchesCategory = $category === 'all' || (isset($act['category_name']) && $act['category_name'] === $category);

        if ($matchesSearch && $matchesCategory) {
            $filteredActivities[] = [
                'id' => $act['id'],
                'title' => $act['title'],
                'type' => $act['category_name'] ?? 'Activité',
                'loc' => ($act['location'] ?? '') . ', ' . ($act['city'] ?? ''),
                'date' => isset($act['event_date']) ? formatEventDate($act['event_date']) : '',
                'user' => $act['creator_first_name'] ?? ($act['creator_username'] ?? 'AmiGo'),
                'color' => $act['category_color'] ?? '#55D5E0',
                'img' => $act['image'] ?? 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=400'
            ];
        }
    }

    echo json_encode($filteredActivities);
} catch (Exception $e) {
    echo json_encode([]);
}