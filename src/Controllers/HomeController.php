<?php
namespace App\Controllers;

use App\Core\View;
use App\Core\Auth;

class HomeController {
    public function index() {
        // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }
        
        // Sinon, afficher la page d'accueil
        View::render('home', [
            'title' => "Accueil",
            'user' => Auth::user()
        ]);
    }
}
