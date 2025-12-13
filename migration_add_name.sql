-- Migration : Ajouter le champ 'name' à la table users
-- À exécuter si la table users existe déjà sans le champ name

USE aio_db;

-- Vérifie si la colonne existe déjà (optionnel, pour éviter les erreurs)
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'aio_db' 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'name'
);

-- Ajoute la colonne seulement si elle n'existe pas
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE users ADD COLUMN name VARCHAR(100) NOT NULL DEFAULT ''Utilisateur'' AFTER id',
    'SELECT ''La colonne name existe déjà'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Alternative simple (si vous êtes sûr que la colonne n'existe pas) :
-- ALTER TABLE users ADD COLUMN name VARCHAR(100) NOT NULL DEFAULT 'Utilisateur' AFTER id;
