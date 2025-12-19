@echo off
echo ========================================
echo Installation de la base de donnees
echo Systeme de gestion des activites AmiGo
echo ========================================
echo.

set /p DB_HOST="Hote MySQL (par defaut: localhost:3306): "
if "%DB_HOST%"=="" set DB_HOST=localhost:3306

set /p DB_NAME="Nom de la base de donnees (par defaut: amigo_db): "
if "%DB_NAME%"=="" set DB_NAME=amigo_db

set /p DB_USER="Utilisateur MySQL (par defaut: root): "
if "%DB_USER%"=="" set DB_USER=root

set /p DB_PASS="Mot de passe MySQL (par defaut: root): "
if "%DB_PASS%"=="" set DB_PASS=root

echo.
echo Configuration:
echo - Hote: %DB_HOST%
echo - Base: %DB_NAME%
echo - Utilisateur: %DB_USER%
echo.

set /p CONFIRM="Confirmer l'installation? (O/N): "
if /i not "%CONFIRM%"=="O" (
    echo Installation annulee.
    pause
    exit /b
)

echo.
echo Installation en cours...
echo.

REM Extraire le host sans le port pour mysql
for /f "tokens=1 delims=:" %%a in ("%DB_HOST%") do set HOST_ONLY=%%a

mysql -h %HOST_ONLY% -u %DB_USER% -p%DB_PASS% %DB_NAME% < activities_table.sql

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo Installation reussie!
    echo ========================================
    echo.
    echo Les tables suivantes ont ete creees:
    echo - users
    echo - activity_categories
    echo - activities
    echo - activity_registrations
    echo.
    echo 8 utilisateurs de test ont ete ajoutes
    echo 8 categories par defaut ont ete ajoutees
    echo 8 activites d'exemple ont ete ajoutees
    echo.
    echo Vous pouvez maintenant utiliser l'application!
    echo.
) else (
    echo.
    echo ========================================
    echo Erreur lors de l'installation
    echo ========================================
    echo.
    echo Verifiez:
    echo - Que MySQL est bien demarre (MAMP/WAMP/XAMPP)
    echo - Que les informations de connexion sont correctes
    echo - Que la base de donnees existe
    echo.
)

pause
