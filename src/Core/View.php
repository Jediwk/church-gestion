<?php
namespace App\Core;

use League\Plates\Engine;

class View {
    private static $engine;

    private static function getEngine() {
        if (!self::$engine) {
            self::$engine = new Engine(__DIR__ . '/../../templates');
            
            // Ajouter des fonctions personnalisées
            self::$engine->registerFunction('hasPermission', function($permission) {
                return Auth::hasPermission($permission);
            });

            self::$engine->registerFunction('isAuthenticated', function() {
                return Auth::check();
            });

            self::$engine->registerFunction('user', function() {
                return Auth::user();
            });
        }
        return self::$engine;
    }

    public static function render($template, $data = []) {
        try {
            echo self::getEngine()->render($template, $data);
        } catch (\Exception $e) {
            // Log l'erreur
            error_log($e->getMessage());
            
            // Afficher une page d'erreur
            http_response_code(500);
            echo self::getEngine()->render('errors/500', [
                'title' => 'Erreur serveur',
                'message' => $e->getMessage()
            ]);
        }
    }
}
