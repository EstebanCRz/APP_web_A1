$files = @(
  "auth\forgot-password.php",
  "auth\reset-password.php",
  "auth\verify-login.php",
  "events\event-create.php",
  "events\event-details.php",
  "events\events-list.php",
  "events\activity-review.php",
  "pages\badges.php",
  "pages\cgu.php",
  "pages\contact.php",
  "pages\faq.php",
  "pages\forum.php",
  "pages\forum-create.php",
  "pages\forum-topic.php",
  "pages\friends.php",
  "pages\leaderboard.php",
  "pages\mentions-legales.php",
  "pages\mes-groupes.php",
  "pages\messages.php",
  "pages\payment.php",
  "pages\settings.php",
  "profile\choose-interests.php",
  "profile\profile.php",
  "profile\profile-created.php",
  "profile\profile-edit.php",
  "profile\profile-favorites.php",
  "profile\profile-other.php",
  "profile\profile-registered.php",
  "profile\profile-waitlist.php",
  "profile\recommendations.php",
  "admin\admin-content.php",
  "admin\admin-dashboard.php",
  "admin\admin-events.php",
  "admin\admin-forum.php",
  "admin\admin-messages.php",
  "admin\admin-users.php"
)

$count = 0
foreach ($file in $files) {
    $path = "c:\Users\esteb\Documents\GitHub\APP_web_A1\$file"
    if (Test-Path $path) {
        $content = [System.IO.File]::ReadAllText($path)
        
        # Supprimer BOM si présent
        if ($content[0] -eq [char]0xFEFF) {
            $content = $content.Substring(1)
        }
        
        # Si le fichier commence par <?php suivi de session_start
        if ($content -match '^\s*<\?php\s+session_start\(\);') {
            $depth = if ($file -match '^admin\\|^pages\\|^profile\\|^auth\\|^events\\') { '../includes/session.php' } else { 'includes/session.php' }
            $content = $content -replace '^\s*<\?php\s+session_start\(\);', "<?php`nrequire_once '$depth';"
            $content = $content -replace 'header\(''Content-Type: text/html; charset=UTF-8''\);[\r\n]+', ''
            
            # Sauvegarder sans BOM
            $utf8NoBom = New-Object System.Text.UTF8Encoding($false)
            [System.IO.File]::WriteAllText($path, $content, $utf8NoBom)
            $count++
            Write-Host "✓ $file"
        }
    }
}

Write-Host "`n✓ $count fichiers corrigés"
