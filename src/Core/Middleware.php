<?php
namespace Core;

abstract class Middleware {
    /**
     * Méthode principale du middleware
     */
    abstract public function handle(): bool;
}

class AuthMiddleware extends Middleware {
    public function handle(): bool {
        if (!Auth::check()) {
            Session::setFlash('warning', 'Veuillez vous connecter pour accéder à cette page');
            header('Location: ' . View::url('/login'));
            return false;
        }

        if (!Security::checkSessionExpiration()) {
            Session::setFlash('warning', 'Votre session a expiré, veuillez vous reconnecter');
            header('Location: ' . View::url('/login'));
            return false;
        }

        return true;
    }
}

class PermissionMiddleware extends Middleware {
    private array $permissions;

    public function __construct(array $permissions) {
        $this->permissions = $permissions;
    }

    public function handle(): bool {
        if (!Auth::hasAllPermissions($this->permissions)) {
            Session::setFlash('danger', 'Vous n\'avez pas les permissions nécessaires');
            header('Location: ' . View::url('/dashboard'));
            return false;
        }
        return true;
    }
}
