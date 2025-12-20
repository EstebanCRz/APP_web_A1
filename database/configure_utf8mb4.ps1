# Script de configuration de la base de données avec support UTF8MB4
# Exécutez ce script pour corriger les problèmes d'emojis

Write-Host "================================================" -ForegroundColor Cyan
Write-Host "Configuration UTF8MB4 pour AmiGo Database" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

# Paramètres par défaut
$dbHost = Read-Host "Hote MySQL (defaut: localhost)"
if ([string]::IsNullOrWhiteSpace($dbHost)) { $dbHost = "localhost" }

$dbName = Read-Host "Nom de la base de donnees (defaut: amigo_db)"
if ([string]::IsNullOrWhiteSpace($dbName)) { $dbName = "amigo_db" }

$dbUser = Read-Host "Utilisateur MySQL (defaut: root)"
if ([string]::IsNullOrWhiteSpace($dbUser)) { $dbUser = "root" }

$dbPass = Read-Host "Mot de passe MySQL (defaut: root)" -AsSecureString
$dbPassPlain = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($dbPass))
if ([string]::IsNullOrWhiteSpace($dbPassPlain)) { $dbPassPlain = "root" }

Write-Host ""
Write-Host "Configuration:" -ForegroundColor Yellow
Write-Host "  Hote: $dbHost"
Write-Host "  Base: $dbName"
Write-Host "  Utilisateur: $dbUser"
Write-Host ""

$confirm = Read-Host "Continuer avec ces parametres? (O/N)"
if ($confirm -ne "O" -and $confirm -ne "o") {
    Write-Host "Operation annulee." -ForegroundColor Red
    Read-Host "Appuyez sur Entree pour quitter"
    exit
}

Write-Host ""
Write-Host "Etape 1: Configuration de la base de donnees en UTF8MB4..." -ForegroundColor Green

# Créer le fichier SQL de configuration
$configSql = @"
-- Configuration UTF8MB4
ALTER DATABASE $dbName CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Vérification
SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME 
FROM INFORMATION_SCHEMA.SCHEMATA 
WHERE SCHEMA_NAME = '$dbName';
"@

$configFile = "temp_config_utf8mb4.sql"
$configSql | Out-File -FilePath $configFile -Encoding UTF8

# Exécuter la configuration
try {
    $mysqlCmd = "mysql -h $dbHost -u $dbUser -p$dbPassPlain $dbName < $configFile 2>&1"
    $result = Invoke-Expression $mysqlCmd
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  [OK] Base de donnees configuree en UTF8MB4" -ForegroundColor Green
    } else {
        Write-Host "  [ERREUR] Impossible de configurer la base" -ForegroundColor Red
        Write-Host $result
    }
} catch {
    Write-Host "  [ERREUR] $($_.Exception.Message)" -ForegroundColor Red
}

# Nettoyer le fichier temporaire
Remove-Item $configFile -ErrorAction SilentlyContinue

Write-Host ""
Write-Host "Etape 2: Import de la structure et des donnees..." -ForegroundColor Green

try {
    $importCmd = "mysql -h $dbHost -u $dbUser -p$dbPassPlain $dbName < activities_table.sql 2>&1"
    $result = Invoke-Expression $importCmd
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  [OK] Import reussi!" -ForegroundColor Green
    } else {
        Write-Host "  [ERREUR] L'import a echoue" -ForegroundColor Red
        Write-Host $result
        Write-Host ""
        Write-Host "Essayez avec le fichier sans emojis:" -ForegroundColor Yellow
        Write-Host "  mysql -h $dbHost -u $dbUser -p$dbPassPlain $dbName < activities_table_no_emoji.sql"
    }
} catch {
    Write-Host "  [ERREUR] $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "================================================" -ForegroundColor Cyan
Write-Host "Configuration terminee!" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Prochaines etapes:" -ForegroundColor Yellow
Write-Host "1. Testez l'installation: http://localhost/APP_web_A1/database/test_connection.php"
Write-Host "2. Accedez a l'application: http://localhost/APP_web_A1/"
Write-Host ""
Read-Host "Appuyez sur Entree pour quitter"
