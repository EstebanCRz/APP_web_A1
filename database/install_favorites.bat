@echo off
echo Installation de la table user_favorites...
echo.

REM Charger la configuration depuis config.php
for /f "tokens=1,2 delims==" %%a in ('findstr /C:"define('DB_" ..\includes\config.php') do (
    set line=%%b
    setlocal enabledelayedexpansion
    for /f "tokens=2 delims=')" %%c in ("!line!") do (
        if "%%a"=="define('DB_HOST'" set DB_HOST=%%c
        if "%%a"=="define('DB_NAME'" set DB_NAME=%%c
        if "%%a"=="define('DB_USER'" set DB_USER=%%c
        if "%%a"=="define('DB_PASS'" set DB_PASS=%%c
    )
    endlocal
)

mysql -h %DB_HOST% -u %DB_USER% -p%DB_PASS% %DB_NAME% < favorites_table.sql

if %ERRORLEVEL% EQU 0 (
    echo.
    echo [SUCCESS] Table user_favorites creee avec succes !
    echo.
) else (
    echo.
    echo [ERREUR] Echec de la creation de la table.
    echo.
)

pause
