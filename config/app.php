<?php

// Définir le chemin de base de l'application s'il n'est pas déjà défini
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Configuration de l'application
return [
    'name' => 'Gestion d\'église',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => $_ENV['APP_DEBUG'] ?? false,
    'url' => $_ENV['APP_URL'] ?? 'http://localhost:8000',
    
    // Configuration de la base de données
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'database' => $_ENV['DB_DATABASE'] ?? 'church_gestion',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
    ],
    
    // Configuration des sessions
    'session' => [
        'name' => 'church_session',
        'lifetime' => 7200, // 2 heures
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'httponly' => true,
    ],
];
