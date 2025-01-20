<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

// Charger les variables d'environnement
// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
// $dotenv->load();

// Définir le chemin de base
define('BASE_PATH', dirname(__DIR__));

// Charger la configuration
$config = require BASE_PATH . '/config/app.php';
define('APP_NAME', $config['name']);

// Initialiser le moteur de template
$templates = new League\Plates\Engine(BASE_PATH . '/templates');

// Nettoyer l'URL
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Si le fichier existe physiquement, on le sert directement
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

use App\Core\Router;
use App\Core\Database;
use App\Core\Session;

// Initialiser la session
Session::init();

// Initialiser la base de données
$db = Database::getInstance();

// Initialiser le routeur
$router = new App\Core\Router();
$router->dispatch();
