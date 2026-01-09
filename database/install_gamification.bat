@echo off
echo ========================================
echo Installation des tables de Gamification
echo ========================================
echo.

REM Configuration - Modifier selon vos paramètres
set MYSQL_HOST=localhost
set MYSQL_USER=root
set MYSQL_PASSWORD=
set MYSQL_DATABASE=amigo_db
set MYSQL_PORT=3306

REM Chemin vers MySQL
set MYSQL_PATH="C:\xampp\mysql\bin\mysql.exe"

echo Configuration:
echo - Hote: %MYSQL_HOST%
echo - Port: %MYSQL_PORT%
echo - Base de donnees: %MYSQL_DATABASE%
echo - Utilisateur: %MYSQL_USER%
echo.

echo Installation en cours...
echo.

REM Exécuter le script SQL
%MYSQL_PATH% -h %MYSQL_HOST% -P %MYSQL_PORT% -u %MYSQL_USER% --password=%MYSQL_PASSWORD% %MYSQL_DATABASE% < gamification_tables.sql

if errorlevel 1 (
    echo.
    echo [ERREUR] L'installation a echoue!
    echo Verifiez:
    echo  - Que MySQL est lance
    echo  - Que les parametres de connexion sont corrects
    echo  - Que le fichier gamification_tables.sql existe
    echo.
    pause
    exit /b 1
)

echo.
echo ========================================
echo Installation terminee avec succes!
echo ========================================
echo.
echo Les tables suivantes ont ete creees:
echo  - user_points
echo  - points_history
echo  - badges
echo  - user_badges
echo.
echo Les badges par defaut ont ete ajoutes.
echo.
pause
