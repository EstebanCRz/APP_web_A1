<?php
// Script de correction automatique des fichiers pour utiliser session.php

$files = [
    'pages/forum-topic.php',
    'pages/forum.php',
    'pages/forum-create.php',
    'pages/messages.php',
    'pages/settings.php',
    'pages/payment.php',
    'pages/mes-groupes.php',
    'pages/mentions-legales.php',
    'pages/cgu.php',
    'pages/faq.php',
    'pages/contact.php',
    'pages/friends.php',
    'pages/leaderboard.php',
    'profile/profile-edit.php',
    'profile/profile.php',
    'profile/profile-created.php',
    'profile/profile-registered.php',
    'profile/profile-waitlist.php',
    'profile/profile-favorites.php',
    'profile/profile-other.php',
    'profile/choose-interests.php',
    'profile/recommendations.php',
    'events/event-create.php',
    'events/event-details.php',
    'events/events-list.php',
    'events/activity-review.php',
    'admin/admin-dashboard.php',
    'admin/admin-users.php',
    'admin/admin-events.php',
    'admin/admin-content.php',
    'admin/admin-forum.php',
    'admin/admin-messages.php',
];

$basePath = __DIR__;
$count = 0;

foreach ($files as $file) {
    $fullPath = $basePath . '/' . $file;
    
    if (!file_exists($fullPath)) {
        continue;
    }
    
    // Lire le fichier
    $content = file_get_contents($fullPath);
    
    // Supprimer le BOM UTF-8 si présent
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
    
    // Patterns à remplacer
    $patterns = [
        // Pattern 1: <?php session_start(); header(...);
        '/^<\?php\s+session_start\(\);\s+header\([\'"]Content-Type: text\/html; charset=UTF-8[\'"]\);/m' => '<?php' . PHP_EOL . 'require_once \'../includes/session.php\';',
        
        // Pattern 2: <?php session_start(); (sans header)
        '/^<\?php\s+session_start\(\);/m' => '<?php' . PHP_EOL . 'require_once \'../includes/session.php\';',
        
        // Pattern 3: session_start() sur une ligne séparée
        '/^\s*session_start\(\);\s*$/m' => '',
        
        // Pattern 4: header Content-Type sur une ligne séparée
        '/^\s*header\([\'"]Content-Type: text\/html; charset=UTF-8[\'"]\);\s*$/m' => '',
    ];
    
    $originalContent = $content;
    foreach ($patterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    // Si le contenu a changé, sauvegarder
    if ($content !== $originalContent) {
        // Sauvegarder sans BOM
        file_put_contents($fullPath, $content);
        $count++;
        echo "✓ $file\n";
    }
}

echo "\n✓ $count fichiers corrigés\n";
