<?php
namespace App\Core;

class Router {
    private $routes = [];
    private $publicRoutes = ['login', 'login/submit', 'forgot-password', 'reset-password', 'profile', 'profile/update'];
    private $permissionMap = [
        'users' => 'manage_users',
        'finances' => 'manage_finances',
        'members' => 'manage_members',
        'families' => 'manage_families',
        'reports' => 'view_reports'
    ];

    public function __construct() {
        $this->routes = require __DIR__ . '/../../config/routes.php';
    }

    public function dispatch() {
        $uri = $_SERVER['REQUEST_URI'];
        $uri = strtok($uri, '?');  // Supprimer les paramètres de requête
        $uri = trim($uri, '/');    // Supprimer les slashes au début et à la fin

        // Si l'URI est vide, rediriger vers la page d'accueil
        if (empty($uri)) {
            $uri = '';
        }

        // Vérifier l'authentification pour les routes non publiques
        if (!in_array($uri, $this->publicRoutes) && !Auth::check()) {
            header('Location: /login');
            exit;
        }

        // Vérifier les permissions
        if (Auth::check() && !in_array($uri, $this->publicRoutes)) {
            $baseUri = explode('/', $uri)[0];
            if (isset($this->permissionMap[$baseUri]) && !Auth::hasPermission($this->permissionMap[$baseUri])) {
                // Rediriger vers le tableau de bord avec un message d'erreur
                $_SESSION['flash']['danger'] = "Vous n'avez pas la permission d'accéder à cette page.";
                header('Location: /dashboard');
                exit;
            }
        }

        // Trouver la route correspondante
        foreach ($this->routes as $route => $config) {
            $pattern = $this->convertRouteToRegex($route);
            if (preg_match($pattern, $uri, $matches)) {
                // Vérifier la méthode HTTP
                if ($config['method'] !== $_SERVER['REQUEST_METHOD']) {
                    if ($config['method'] === 'POST' && $_SERVER['REQUEST_METHOD'] === 'GET') {
                        header('Location: /dashboard');
                        exit;
                    }
                    http_response_code(405);
                    echo "Méthode non autorisée";
                    exit;
                }

                // Extraire les paramètres de l'URL
                array_shift($matches); // Supprimer la correspondance complète
                $params = array_values($matches);

                // Instancier le contrôleur et appeler l'action
                $controllerName = "App\\Controllers\\" . $config['controller'];
                $controller = new $controllerName();
                $action = $config['action'];

                // Appeler l'action avec les paramètres
                call_user_func_array([$controller, $action], $params);
                return;
            }
        }

        // Route non trouvée
        http_response_code(404);
        View::render('errors/404', [
            'title' => 'Page non trouvée'
        ]);
    }

    private function convertRouteToRegex($route) {
        if (empty($route)) {
            return '#^$#';
        }
        return '#^' . str_replace('/', '\/', preg_replace('/\{[a-zA-Z]+\}/', '([^\/]+)', $route)) . '$#';
    }
}
