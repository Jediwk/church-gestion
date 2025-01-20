<?php
namespace App\Controllers;

use App\Core\View;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;

class AuthController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function showLoginForm() {
        if (Auth::check()) {
            header('Location: /dashboard');
            exit;
        }
        
        return View::render('auth/login', ['title' => 'Connexion']);
    }

    public function login() {
        try {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                Session::setFlash('error', 'Veuillez remplir tous les champs');
                header('Location: /login');
                exit;
            }

            // Vérifier les identifiants
            $sql = "SELECT * FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                Session::setFlash('error', 'Utilisateur non trouvé');
                header('Location: /login');
                exit;
            }

            // Vérifier si le compte est actif
            if (!$user['status']) {
                Session::setFlash('error', 'Ce compte est désactivé');
                header('Location: /login');
                exit;
            }

            if (!password_verify($password, $user['password'])) {
                Session::setFlash('error', 'Mot de passe incorrect');
                header('Location: /login');
                exit;
            }

            // Connexion réussie
            Auth::login($user);
            
            // Mettre à jour la date de dernière connexion
            $sql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$user['id']]);
            
            Session::setFlash('success', 'Connexion réussie');
            header('Location: /dashboard');
            exit;
            
        } catch (\Exception $e) {
            // En mode debug, afficher l'erreur complète
            if ($_ENV['APP_DEBUG'] ?? false) {
                Session::setFlash('error', 'Erreur : ' . $e->getMessage());
            } else {
                Session::setFlash('error', 'Une erreur est survenue lors de la connexion');
            }
            header('Location: /login');
            exit;
        }
    }

    public function logout() {
        Auth::logout();
        Session::setFlash('success', 'Déconnexion réussie');
        header('Location: /login');
        exit;
    }
}
