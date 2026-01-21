$files = Get-ChildItem -Path "c:\Users\esteb\Documents\GitHub\APP_web_A1" -Filter "*.php" -Recurse -Exclude "vendor" | Where-Object { $_.FullName -notmatch '\\vendor\\|\\node_modules\\' }

$count = 0
$utf8NoBom = New-Object System.Text.UTF8Encoding($false)

foreach ($file in $files) {
    $content = [System.IO.File]::ReadAllBytes($file.FullName)
    
    # Vérifier si le fichier commence par BOM UTF-8 (EF BB BF)
    if ($content.Length -ge 3 -and $content[0] -eq 0xEF -and $content[1] -eq 0xBB -and $content[2] -eq 0xBF) {
        # Retirer le BOM
        $contentWithoutBom = $content[3..($content.Length - 1)]
        [System.IO.File]::WriteAllBytes($file.FullName, $contentWithoutBom)
        $count++
        Write-Host "✓ BOM retiré: $($file.FullName.Replace('c:\Users\esteb\Documents\GitHub\APP_web_A1\', ''))"
    }
    
    # Vérifier aussi les espaces/lignes vides avant <?php
    $text = [System.IO.File]::ReadAllText($file.FullName)
    if ($text -match '^\s+<\?php') {
        $text = $text -replace '^\s+<\?php', '<?php'
        [System.IO.File]::WriteAllText($file.FullName, $text, $utf8NoBom)
        Write-Host "✓ Espaces retirés: $($file.FullName.Replace('c:\Users\esteb\Documents\GitHub\APP_web_A1\', ''))"
        $count++
    }
}

Write-Host "`n✓ $count fichiers nettoyés"
