<?php

return [
    // Pages publiques
    '' => ['controller' => 'HomeController', 'action' => 'index', 'method' => 'GET'],
    'login' => ['controller' => 'AuthController', 'action' => 'showLoginForm', 'method' => 'GET'],
    'login/submit' => ['controller' => 'AuthController', 'action' => 'login', 'method' => 'POST'],
    'logout' => ['controller' => 'AuthController', 'action' => 'logout', 'method' => 'GET'],
    'forgot-password' => ['controller' => 'AuthController', 'action' => 'forgotPassword', 'method' => 'GET'],
    'reset-password' => ['controller' => 'AuthController', 'action' => 'resetPassword', 'method' => 'GET'],

    // Dashboard
    'dashboard' => ['controller' => 'DashboardController', 'action' => 'index', 'method' => 'GET'],

    // Profil utilisateur
    'profile' => ['controller' => 'ProfileController', 'action' => 'show', 'method' => 'GET'],
    'profile/update' => ['controller' => 'ProfileController', 'action' => 'update', 'method' => 'POST'],

    // Gestion des utilisateurs
    'users' => ['controller' => 'UserController', 'action' => 'index', 'method' => 'GET'],
    'users/create' => ['controller' => 'UserController', 'action' => 'create', 'method' => 'GET'],
    'users/store' => ['controller' => 'UserController', 'action' => 'store', 'method' => 'POST'],
    'users/edit/:id' => ['controller' => 'UserController', 'action' => 'edit', 'method' => 'GET'],
    'users/update/:id' => ['controller' => 'UserController', 'action' => 'update', 'method' => 'POST'],
    'users/delete/:id' => ['controller' => 'UserController', 'action' => 'delete', 'method' => 'POST'],

    // Gestion des finances
    'finances' => ['controller' => 'FinanceController', 'action' => 'index', 'method' => 'GET'],
    'finances/create' => ['controller' => 'FinanceController', 'action' => 'create', 'method' => 'GET'],
    'finances/store' => ['controller' => 'FinanceController', 'action' => 'store', 'method' => 'POST'],
    'finances/edit/:id' => ['controller' => 'FinanceController', 'action' => 'edit', 'method' => 'GET'],
    'finances/update/:id' => ['controller' => 'FinanceController', 'action' => 'update', 'method' => 'POST'],
    'finances/delete/:id' => ['controller' => 'FinanceController', 'action' => 'delete', 'method' => 'POST'],
    'finances/receipt/:id' => ['controller' => 'FinanceController', 'action' => 'generateReceipt', 'method' => 'GET'],
    'finances/reports' => ['controller' => 'FinanceController', 'action' => 'reports', 'method' => 'GET'],
    'finances/transactions' => ['controller' => 'FinanceController', 'action' => 'index', 'method' => 'GET'],
    'finances/types/store' => ['controller' => 'FinanceController', 'action' => 'storeType', 'method' => 'POST'],

    // Gestion des membres
    'members' => ['controller' => 'MemberController', 'action' => 'index', 'method' => 'GET'],
    'members/create' => ['controller' => 'MemberController', 'action' => 'create', 'method' => 'GET'],
    'members/store' => ['controller' => 'MemberController', 'action' => 'store', 'method' => 'POST'],
    'members/edit/:id' => ['controller' => 'MemberController', 'action' => 'edit', 'method' => 'GET'],
    'members/update/:id' => ['controller' => 'MemberController', 'action' => 'update', 'method' => 'POST'],
    'members/delete/:id' => ['controller' => 'MemberController', 'action' => 'delete', 'method' => 'POST'],

    // Gestion des familles
    'families' => ['controller' => 'FamilyController', 'action' => 'index', 'method' => 'GET'],
    'families/create' => ['controller' => 'FamilyController', 'action' => 'create', 'method' => 'GET'],
    'families/store' => ['controller' => 'FamilyController', 'action' => 'store', 'method' => 'POST'],
    'families/edit/:id' => ['controller' => 'FamilyController', 'action' => 'edit', 'method' => 'GET'],
    'families/update/:id' => ['controller' => 'FamilyController', 'action' => 'update', 'method' => 'POST'],
    'families/delete/:id' => ['controller' => 'FamilyController', 'action' => 'delete', 'method' => 'POST'],
];
