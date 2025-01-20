<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\User;

class ProfileController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        
        // Vérifier que l'utilisateur est connecté
        $this->middleware('auth');
    }

    public function show() {
        $user = Auth::user();
        $userId = $user['id'];

        // Récupérer les informations complètes de l'utilisateur
        $userDetails = $this->userModel->getById($userId);
        
        // Récupérer le rôle de l'utilisateur
        $sql = "SELECT r.name as role_name, r.description as role_description 
                FROM roles r 
                JOIN user_roles ur ON r.id = ur.role_id 
                WHERE ur.user_id = ?";
        $role = $this->userModel->getDb()->query($sql, [$userId])->fetch();

        return $this->render('profile/show', [
            'user' => $userDetails,
            'role' => $role,
            'title' => 'Mon Profil'
        ]);
    }

    public function update() {
        $user = Auth::user();
        $userId = $user['id'];

        $data = [
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? ''
        ];

        // Si un nouveau mot de passe est fourni
        if (!empty($_POST['new_password'])) {
            // Vérifier l'ancien mot de passe
            $currentUser = $this->userModel->getById($userId);
            if (!Auth::verifyPassword($_POST['current_password'], $currentUser['password'])) {
                $this->flash('error', 'Le mot de passe actuel est incorrect');
                return $this->redirect('/profile');
            }

            // Vérifier que le nouveau mot de passe est valide
            if (strlen($_POST['new_password']) < 6) {
                $this->flash('error', 'Le nouveau mot de passe doit contenir au moins 6 caractères');
                return $this->redirect('/profile');
            }

            if ($_POST['new_password'] !== $_POST['confirm_password']) {
                $this->flash('error', 'Les nouveaux mots de passe ne correspondent pas');
                return $this->redirect('/profile');
            }

            $data['password'] = Auth::hashPassword($_POST['new_password']);
        }

        // Validation de base
        $errors = [];
        if (empty($data['username'])) {
            $errors['username'] = 'Le nom d\'utilisateur est requis';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'L\'email est requis';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'L\'email n\'est pas valide';
        } elseif ($this->userModel->emailExists($data['email'], $userId)) {
            $errors['email'] = 'Cet email est déjà utilisé par un autre utilisateur';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->flash('error', $error);
            }
            return $this->redirect('/profile');
        }

        try {
            if ($this->userModel->update($userId, $data)) {
                // Mettre à jour la session avec les nouvelles informations
                $_SESSION['user']['username'] = $data['username'];
                $_SESSION['user']['email'] = $data['email'];
                
                $this->flash('success', 'Profil mis à jour avec succès');
            }
        } catch (\Exception $e) {
            $this->flash('error', 'Erreur lors de la mise à jour du profil : ' . $e->getMessage());
        }

        return $this->redirect('/profile');
    }
}
