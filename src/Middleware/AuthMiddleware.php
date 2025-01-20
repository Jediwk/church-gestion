<?php
namespace App\Middleware;

use Core\Auth;
use Core\Session;

class AuthMiddleware {
    /**
     * Vérifie si l'utilisateur est connecté
     */
    public static function isLoggedIn(): bool {
        if (!Auth::isLoggedIn()) {
            Session::set('redirect_after_login', $_SERVER['REQUEST_URI']);
            Session::setFlash('warning', 'Veuillez vous connecter pour accéder à cette page');
            header('Location: /login');
            exit;
        }
        return true;
    }

    /**
     * Vérifie si l'utilisateur a une permission
     */
    public static function hasPermission(string $permission): bool {
        if (!self::isLoggedIn()) {
            return false;
        }

        if (!Auth::hasPermission($permission)) {
            Session::setFlash('danger', 'Vous n\'avez pas la permission d\'accéder à cette page');
            header('Location: /dashboard');
            exit;
        }

        return true;
    }

    /**
     * Vérifie si l'utilisateur a au moins une des permissions
     */
    public static function hasAnyPermission(array $permissions): bool {
        if (!self::isLoggedIn()) {
            return false;
        }

        if (!Auth::hasAnyPermission($permissions)) {
            Session::setFlash('danger', 'Vous n\'avez pas la permission d\'accéder à cette page');
            header('Location: /dashboard');
            exit;
        }

        return true;
    }

    /**
     * Vérifie si l'utilisateur a toutes les permissions
     */
    public static function hasAllPermissions(array $permissions): bool {
        if (!self::isLoggedIn()) {
            return false;
        }

        if (!Auth::hasAllPermissions($permissions)) {
            Session::setFlash('danger', 'Vous n\'avez pas toutes les permissions nécessaires');
            header('Location: /dashboard');
            exit;
        }

        return true;
    }

    /**
     * Vérifie le token CSRF
     */
    public static function verifyCsrf(): bool {
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            Session::setFlash('danger', 'Token CSRF invalide');
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/dashboard');
            exit;
        }
        return true;
    }
}
