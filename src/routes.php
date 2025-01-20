<?php

return [
    // Page d'accueil et tableau de bord
    '' => ['controller' => 'HomeController', 'action' => 'index', 'method' => 'GET'],
    'dashboard' => ['controller' => 'DashboardController', 'action' => 'index', 'method' => 'GET'],

    // Routes d'authentification
    'login' => ['controller' => 'AuthController', 'action' => 'showLoginForm', 'method' => 'GET'],
    'login/submit' => ['controller' => 'AuthController', 'action' => 'login', 'method' => 'POST'],
    'logout' => ['controller' => 'AuthController', 'action' => 'logout', 'method' => 'GET'],

    // Routes pour le profil
    'profile' => ['controller' => 'ProfileController', 'action' => 'show', 'method' => 'GET'],
    'profile/update' => ['controller' => 'ProfileController', 'action' => 'update', 'method' => 'POST'],

    // Routes pour les utilisateurs
    'users' => ['controller' => 'UserController', 'action' => 'index', 'method' => 'GET'],
    'users/create' => ['controller' => 'UserController', 'action' => 'create', 'method' => 'GET'],
    'users/store' => ['controller' => 'UserController', 'action' => 'store', 'method' => 'POST'],
    'users/edit/{id}' => ['controller' => 'UserController', 'action' => 'edit', 'method' => 'GET'],
    'users/update/{id}' => ['controller' => 'UserController', 'action' => 'update', 'method' => 'POST'],
    'users/delete/{id}' => ['controller' => 'UserController', 'action' => 'delete', 'method' => 'GET'],

    // Routes pour les finances
    'finances' => ['controller' => 'FinanceController', 'action' => 'index', 'method' => 'GET'],
    'finances/create' => ['controller' => 'FinanceController', 'action' => 'create', 'method' => 'GET'],
    'finances/store' => ['controller' => 'FinanceController', 'action' => 'store', 'method' => 'POST'],
    'finances/edit/{id}' => ['controller' => 'FinanceController', 'action' => 'edit', 'method' => 'GET'],
    'finances/update/{id}' => ['controller' => 'FinanceController', 'action' => 'update', 'method' => 'POST'],
    'finances/delete/{id}' => ['controller' => 'FinanceController', 'action' => 'delete', 'method' => 'GET'],
];
