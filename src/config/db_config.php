<?php

/**
 * Configuration de la base de données
 * Remplacez les valeurs par vos propres identifiants MariaDB
 */

return [
    'host' => 'localhost',
    'dbname' => 'aio_db',
    'username' => 'root',
    'password' => 'root',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];

