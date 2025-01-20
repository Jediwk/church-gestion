<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Role;

class UserController extends Controller {
    private $userModel;
    private $roleModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->roleModel = new Role();
        
        // Vérifier si l'utilisateur est super_admin pour les actions sensibles
        $this->middleware('auth');
    }

    public function index() {
        // Vérifier les permissions
        if (!$this->hasPermission('read_all')) {
            $this->flash('error', 'Accès non autorisé');
            return $this->redirect('/dashboard');
        }

        $users = $this->userModel->getAllWithRoles();
        $roles = $this->roleModel->getAll();

        return $this->render('users/index', [
            'users' => $users,
            'roles' => $roles,
            'title' => 'Gestion des utilisateurs'
        ]);
    }

    public function create() {
        if (!$this->hasPermission('*')) {
            $this->flash('error', 'Seul le super admin peut créer des utilisateurs');
            return $this->redirect('/users');
        }

        return $this->render('users/create', [
            'roles' => $this->roleModel->getAll(),
            'title' => 'Créer un utilisateur'
        ]);
    }

    public function store() {
        if (!$this->hasPermission('*')) {
            $this->flash('error', 'Seul le super admin peut créer des utilisateurs');
            return $this->redirect('/users');
        }

        $data = [
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'role_id' => $_POST['role_id'] ?? null,
            'status' => isset($_POST['status']) ? 1 : 1  // Toujours actif par défaut
        ];

        // Validation
        $errors = $this->validateUserData($data);
        
        if (empty($errors)) {
            try {
                if ($this->userModel->create($data)) {
                    $this->flash('success', 'Utilisateur créé avec succès');
                    return $this->redirect('/users');
                }
            } catch (\Exception $e) {
                $this->flash('error', 'Erreur lors de la création de l\'utilisateur : ' . $e->getMessage());
            }
        }

        return $this->render('users/create', [
            'errors' => $errors,
            'data' => $data,
            'roles' => $this->roleModel->getAll(),
            'title' => 'Créer un utilisateur'
        ]);
    }

    public function edit($id) {
        if (!$this->hasPermission('*')) {
            $this->flash('error', 'Seul le super admin peut modifier les utilisateurs');
            return $this->redirect('/users');
        }

        $user = $this->userModel->getById($id);
        if (!$user) {
            $this->flash('error', 'Utilisateur non trouvé');
            return $this->redirect('/users');
        }

        // Récupérer le rôle actuel de l'utilisateur
        $sql = "SELECT role_id FROM user_roles WHERE user_id = ? LIMIT 1";
        $role = $this->userModel->getDb()->query($sql, [$id])->fetch();

        return $this->render('users/edit', [
            'user' => $user,
            'currentRoleId' => $role ? $role['role_id'] : null,
            'roles' => $this->roleModel->getAll(),
            'title' => 'Modifier l\'utilisateur'
        ]);
    }

    public function update($id) {
        if (!$this->hasPermission('*')) {
            $this->flash('error', 'Seul le super admin peut modifier les utilisateurs');
            return $this->redirect('/users');
        }

        $data = [
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'role_id' => $_POST['role_id'] ?? null,
            'status' => isset($_POST['status']) ? 1 : 0
        ];

        // Ajouter le mot de passe uniquement s'il est fourni
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }

        // Validation
        $errors = $this->validateUserData($data, $id);
        
        if (empty($errors)) {
            try {
                if ($this->userModel->update($id, $data)) {
                    $this->flash('success', 'Utilisateur mis à jour avec succès');
                    return $this->redirect('/users');
                }
            } catch (\Exception $e) {
                $this->flash('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
            }
        }

        $user = $this->userModel->getById($id);
        return $this->render('users/edit', [
            'user' => $user,
            'errors' => $errors,
            'roles' => $this->roleModel->getAll(),
            'title' => 'Modifier l\'utilisateur'
        ]);
    }

    public function delete($id) {
        if (!$this->hasPermission('*')) {
            $this->flash('error', 'Seul le super admin peut supprimer des utilisateurs');
            return $this->redirect('/users');
        }

        try {
            if ($this->userModel->delete($id)) {
                $this->flash('success', 'Utilisateur supprimé avec succès');
            } else {
                $this->flash('error', 'Erreur lors de la suppression de l\'utilisateur');
            }
        } catch (\Exception $e) {
            $this->flash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }

        return $this->redirect('/users');
    }

    private function validateUserData($data, $userId = null) {
        $errors = [];

        if (empty($data['username'])) {
            $errors['username'] = 'Le nom d\'utilisateur est requis';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'L\'email est requis';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'L\'email n\'est pas valide';
        } elseif ($this->userModel->emailExists($data['email'], $userId)) {
            $errors['email'] = 'Cet email est déjà utilisé';
        }

        if (!$userId && empty($data['password'])) {
            $errors['password'] = 'Le mot de passe est requis';
        } elseif (!empty($data['password']) && strlen($data['password']) < 6) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 6 caractères';
        }

        if (empty($data['role_id'])) {
            $errors['role_id'] = 'Le rôle est requis';
        }

        return $errors;
    }
}
