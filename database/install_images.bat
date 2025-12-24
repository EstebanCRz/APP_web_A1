@echo off
echo ===================================
echo   Migration: Support d'images
echo ===================================
echo.
echo Ce script va ajouter le support d'images dans les messages.
echo.
pause

REM Chemin vers MySQL de MAMP
set MYSQL_PATH="C:\MAMP\bin\mysql\bin\mysql.exe"

REM Vérifier si MySQL existe
if not exist %MYSQL_PATH% (
    echo ERREUR: MySQL introuvable a %MYSQL_PATH%
    echo Veuillez ajuster le chemin dans ce script.
    pause
    exit /b 1
)

echo Execution du script SQL...
%MYSQL_PATH% -u root -proot amigo_db < add_images_to_messages.sql

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ===================================
    echo   Migration reussie !
    echo ===================================
    echo.
    echo Le support d'images est maintenant active.
    echo Vous pouvez maintenant envoyer des images dans :
    echo - Les messages prives
    echo - Les chats d'activite
    echo.
) else (
    echo.
    echo ERREUR: La migration a echoue.
    echo Verifiez que MySQL est demarre.
    echo.
)

pause
