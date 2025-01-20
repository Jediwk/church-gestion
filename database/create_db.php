<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    // Supprimer les tables existantes dans l'ordre inverse des dépendances
    $db->exec("DROP TABLE IF EXISTS user_roles");
    $db->exec("DROP TABLE IF EXISTS role_permissions");
    $db->exec("DROP TABLE IF EXISTS finances");
    $db->exec("DROP TABLE IF EXISTS finance_types");
    $db->exec("DROP TABLE IF EXISTS permissions");
    $db->exec("DROP TABLE IF EXISTS roles");
    $db->exec("DROP TABLE IF EXISTS users");
    
    // Créer la table des utilisateurs
    $db->exec("CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        status INTEGER DEFAULT 1,
        last_login DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Créer la table des rôles
    $db->exec("CREATE TABLE roles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Créer la table des permissions
    $db->exec("CREATE TABLE permissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Créer la table pivot role_permissions
    $db->exec("CREATE TABLE role_permissions (
        role_id INTEGER NOT NULL,
        permission_id INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (role_id, permission_id),
        FOREIGN KEY (role_id) REFERENCES roles(id),
        FOREIGN KEY (permission_id) REFERENCES permissions(id)
    )");
    
    // Créer la table pivot user_roles
    $db->exec("CREATE TABLE user_roles (
        user_id INTEGER NOT NULL,
        role_id INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, role_id),
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (role_id) REFERENCES roles(id)
    )");
    
    // Créer la table des types de finances
    $db->exec("CREATE TABLE finance_types (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        category TEXT NOT NULL CHECK(category IN ('Entrée', 'Sortie')),
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Créer la table des finances
    $db->exec("CREATE TABLE finances (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        type_id INTEGER NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        date DATE NOT NULL,
        description TEXT,
        reference TEXT,
        created_by INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (type_id) REFERENCES finance_types(id),
        FOREIGN KEY (created_by) REFERENCES users(id)
    )");
    
    // Insérer les permissions par défaut
    $defaultPermissions = [
        ['*', 'Toutes les permissions'],
        ['manage_users', 'Gérer les utilisateurs'],
        ['manage_roles', 'Gérer les rôles'],
        ['manage_finances', 'Gérer les finances'],
        ['manage_members', 'Gérer les membres'],
        ['manage_families', 'Gérer les familles'],
        ['view_reports', 'Voir les rapports']
    ];
    
    $stmt = $db->prepare("INSERT INTO permissions (name, description) VALUES (?, ?)");
    foreach ($defaultPermissions as $permission) {
        $stmt->execute($permission);
    }
    
    // Insérer les rôles par défaut
    $defaultRoles = [
        ['super_admin', 'Super Administrateur'],
        ['admin', 'Administrateur'],
        ['treasurer', 'Trésorier'],
        ['secretary', 'Secrétaire']
    ];
    
    $stmt = $db->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
    foreach ($defaultRoles as $role) {
        $stmt->execute($role);
    }
    
    // Créer l'utilisateur admin
    $stmt = $db->prepare("
        INSERT INTO users (username, email, password, status) 
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        'admin',
        'admin@example.com',
        password_hash('Admin@123', PASSWORD_DEFAULT),
        1
    ]);
    
    // Donner toutes les permissions au super admin
    $db->exec("
        INSERT INTO role_permissions (role_id, permission_id)
        SELECT 
            (SELECT id FROM roles WHERE name = 'super_admin'),
            id
        FROM permissions
    ");
    
    // Assigner le rôle super_admin à l'utilisateur admin
    $db->exec("
        INSERT INTO user_roles (user_id, role_id)
        SELECT 
            (SELECT id FROM users WHERE username = 'admin'),
            (SELECT id FROM roles WHERE name = 'super_admin')
    ");
    
    // Insérer quelques types de finances par défaut
    $defaultTypes = [
        ['Dîme', 'Entrée', 'Dîmes des membres'],
        ['Offrande', 'Entrée', 'Offrandes lors des cultes'],
        ['Don', 'Entrée', 'Dons spéciaux'],
        ['Loyer', 'Sortie', 'Loyer du bâtiment'],
        ['Électricité', 'Sortie', 'Factures d\'électricité'],
        ['Eau', 'Sortie', 'Factures d\'eau'],
        ['Entretien', 'Sortie', 'Entretien et réparations'],
        ['Fournitures', 'Sortie', 'Fournitures de bureau et culte']
    ];
    
    $stmt = $db->prepare("INSERT INTO finance_types (name, category, description) VALUES (?, ?, ?)");
    foreach ($defaultTypes as $type) {
        $stmt->execute($type);
    }
    
    echo "Base de données créée avec succès !\n";
    echo "Utilisateur admin créé avec succès !\n";
    echo "Permissions et rôles configurés avec succès !\n";
    echo "Types de finances ajoutés avec succès !\n";
    
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
    exit(1);
}
