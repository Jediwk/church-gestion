<?php
namespace Controllers;

use Core\Controller;
use Core\Session;
use Core\Security;
use Models\User;
use Models\PasswordReset;

class PasswordController extends Controller {
    private User $userModel;
    private PasswordReset $resetModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->resetModel = new PasswordReset();
    }

    protected function requiresAuth(): bool {
        return false;
    }

    public function forgotForm() {
        $this->view->assign('csrf_token', Security::generateCsrfToken());
        $this->view->render('auth/forgot-password');
    }

    public function sendResetLink() {
        if (!Security::validateCsrfToken($this->getPost('csrf_token'))) {
            Session::setFlash('danger', 'Session invalide, veuillez réessayer');
            $this->redirect('/forgot-password');
        }

        $username = $this->getPost('username');
        $user = $this->userModel->findByUsername($username);

        if ($user) {
            // Génère un token unique
            $token = Security::generateResetToken();
            
            // Enregistre le token avec une expiration de 1 heure
            $this->resetModel->create([
                'user_id' => $user['id'],
                'token' => $token,
                'expires_at' => date('Y-m-d H:i:s', time() + 3600)
            ]);

            // Envoie l'email avec le lien de réinitialisation
            $resetLink = "http://{$_SERVER['HTTP_HOST']}/reset-password/{$token}";
            $this->sendResetEmail($user['email'], $resetLink);
        }

        // Message générique pour éviter l'énumération des utilisateurs
        Session::setFlash('success', 'Si votre compte existe, vous recevrez un email avec les instructions');
        $this->redirect('/login');
    }

    public function resetForm(string $token) {
        $reset = $this->resetModel->findValidToken($token);
        
        if (!$reset) {
            Session::setFlash('danger', 'Ce lien de réinitialisation est invalide ou expiré');
            $this->redirect('/login');
        }

        $this->view->assign('token', $token);
        $this->view->assign('csrf_token', Security::generateCsrfToken());
        $this->view->render('auth/reset-password');
    }

    public function resetPassword() {
        if (!Security::validateCsrfToken($this->getPost('csrf_token'))) {
            Session::setFlash('danger', 'Session invalide, veuillez réessayer');
            $this->redirect('/login');
        }

        $token = $this->getPost('token');
        $password = $this->getPost('password');
        $confirmPassword = $this->getPost('confirm_password');

        $reset = $this->resetModel->findValidToken($token);
        
        if (!$reset) {
            Session::setFlash('danger', 'Ce lien de réinitialisation est invalide ou expiré');
            $this->redirect('/login');
        }

        if (strlen($password) < 8) {
            Session::setFlash('danger', 'Le mot de passe doit contenir au moins 8 caractères');
            $this->redirect("/reset-password/{$token}");
        }

        if ($password !== $confirmPassword) {
            Session::setFlash('danger', 'Les mots de passe ne correspondent pas');
            $this->redirect("/reset-password/{$token}");
        }

        // Met à jour le mot de passe
        $this->userModel->updatePassword($reset['user_id'], User::hashPassword($password));
        
        // Invalide le token
        $this->resetModel->invalidateToken($token);
        
        Session::setFlash('success', 'Votre mot de passe a été mis à jour avec succès');
        $this->redirect('/login');
    }

    private function sendResetEmail(string $to, string $resetLink): void {
        $subject = "Réinitialisation de votre mot de passe";
        $message = "Bonjour,\n\n";
        $message .= "Vous avez demandé la réinitialisation de votre mot de passe.\n";
        $message .= "Cliquez sur le lien suivant pour définir un nouveau mot de passe :\n\n";
        $message .= $resetLink . "\n\n";
        $message .= "Ce lien expirera dans 1 heure.\n";
        $message .= "Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.\n\n";
        $message .= "Cordialement,\n";
        $message .= "L'équipe de l'église";

        $headers = "From: no-reply@eglise.com\r\n";
        $headers .= "Reply-To: no-reply@eglise.com\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        mail($to, $subject, $message, $headers);
    }
}
