<?php

namespace App\Core;

class Controller {
    protected $db;
    protected $session;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->session = new Session();
    }

    protected function render($view, $data = []) {
        return View::render($view, $data);
    }

    protected function redirect($path) {
        header("Location: $path");
        exit;
    }

    protected function flash($type, $message) {
        Session::setFlash($type, $message);
    }

    protected function hasPermission($permission) {
        return Auth::hasPermission($permission);
    }

    protected function middleware($middleware) {
        switch ($middleware) {
            case 'auth':
                if (!Auth::check()) {
                    $this->flash('error', 'Veuillez vous connecter pour accéder à cette page');
                    $this->redirect('/login');
                }
                break;
            case 'guest':
                if (Auth::check()) {
                    $this->redirect('/dashboard');
                }
                break;
            case 'admin':
                if (!Auth::hasPermission('*')) {
                    $this->flash('error', 'Accès non autorisé');
                    $this->redirect('/dashboard');
                }
                break;
        }
    }

    protected function validateUser($data) {
        $errors = [];

        // Valider le nom d'utilisateur
        if (empty($data['username'])) {
            $errors['username'] = "Le nom d'utilisateur est requis";
        } elseif (strlen($data['username']) < 3) {
            $errors['username'] = "Le nom d'utilisateur doit contenir au moins 3 caractères";
        }

        // Valider l'email
        if (empty($data['email'])) {
            $errors['email'] = "L'email est requis";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "L'email n'est pas valide";
        }

        // Valider le mot de passe pour les nouveaux utilisateurs
        if (!isset($data['id'])) {
            if (empty($data['password'])) {
                $errors['password'] = "Le mot de passe est requis";
            } elseif (strlen($data['password']) < 6) {
                $errors['password'] = "Le mot de passe doit contenir au moins 6 caractères";
            }
        }

        // Valider le rôle
        if (empty($data['role_id'])) {
            $errors['role_id'] = "Le rôle est requis";
        }

        return $errors;
    }

    protected function validateMember($data) {
        $errors = [];

        if (empty($data['first_name'])) {
            $errors['first_name'] = "Le prénom est requis";
        }

        if (empty($data['last_name'])) {
            $errors['last_name'] = "Le nom est requis";
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "L'email n'est pas valide";
        }

        if (!empty($data['phone']) && !preg_match("/^[0-9\-\(\)\/\+\s]*$/", $data['phone'])) {
            $errors['phone'] = "Le numéro de téléphone n'est pas valide";
        }

        return $errors;
    }
}