@echo off
echo ========================================
echo Migration SQL - Ajout du champ 'name'
echo ========================================
echo.
echo Cette commande va ajouter le champ 'name' a la table 'users'
echo.
pause

REM Remplacez les valeurs par vos identifiants
set DB_USER=root
set DB_PASSWORD=
set DB_NAME=aio_db

mysql -u %DB_USER% -p%DB_PASSWORD% %DB_NAME% -e "ALTER TABLE users ADD COLUMN name VARCHAR(100) NOT NULL DEFAULT 'Utilisateur' AFTER id;"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo Migration reussie !
) else (
    echo.
    echo Erreur lors de la migration.
    echo Verifiez vos identifiants de base de donnees.
)

pause

