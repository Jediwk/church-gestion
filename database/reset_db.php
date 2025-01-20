<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Core\Auth;

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Supprimer les tables existantes dans l'ordre inverse des dépendances
    $tables = [
        'finances',
        'finance_types',
        'user_roles',
        'role_permissions',
        'permissions',
        'roles',
        'users'
    ];

    foreach ($tables as $table) {
        $db->exec("DROP TABLE IF EXISTS $table");
        echo "Table $table supprimée.\n";
    }
    
    // 2. Créer les tables
    
    // Table des utilisateurs
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
    echo "Table users créée.\n";
    
    // Table des rôles
    $db->exec("CREATE TABLE roles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Table roles créée.\n";
    
    // Table des permissions
    $db->exec("CREATE TABLE permissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Table permissions créée.\n";
    
    // Table pivot role_permissions
    $db->exec("CREATE TABLE role_permissions (
        role_id INTEGER NOT NULL,
        permission_id INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (role_id, permission_id),
        FOREIGN KEY (role_id) REFERENCES roles(id),
        FOREIGN KEY (permission_id) REFERENCES permissions(id)
    )");
    echo "Table role_permissions créée.\n";
    
    // Table pivot user_roles
    $db->exec("CREATE TABLE user_roles (
        user_id INTEGER NOT NULL,
        role_id INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, role_id),
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (role_id) REFERENCES roles(id)
    )");
    echo "Table user_roles créée.\n";
    
    // Table des types de finances
    $db->exec("CREATE TABLE finance_types (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        category TEXT NOT NULL CHECK(category IN ('Entrée', 'Sortie')),
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Table finance_types créée.\n";
    
    // Table des finances
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
    echo "Table finances créée.\n";

    // 3. Insérer les données par défaut
    
    // Permissions par défaut
    $defaultPermissions = [
        ['*', 'Toutes les permissions'],
        ['manage_users', 'Gérer les utilisateurs'],
        ['manage_roles', 'Gérer les rôles'],
        ['manage_finances', 'Gérer les finances'],
        ['manage_members', 'Gérer les membres'],
        ['manage_families', 'Gérer les familles'],
        ['view_reports', 'Voir les rapports']
    ];
    
    foreach ($defaultPermissions as $permission) {
        $db->exec("INSERT INTO permissions (name, description) VALUES ('{$permission[0]}', '{$permission[1]}')");
    }
    echo "Permissions par défaut créées.\n";
    
    // Rôles par défaut
    $defaultRoles = [
        ['super_admin', 'Super Administrateur'],
        ['admin', 'Administrateur'],
        ['pastor', 'Pasteur'],
        ['treasurer', 'Trésorier'],
        ['secretary', 'Secrétaire']
    ];
    
    foreach ($defaultRoles as $role) {
        $db->exec("INSERT INTO roles (name, description) VALUES ('{$role[0]}', '{$role[1]}')");
    }
    echo "Rôles par défaut créés.\n";
    
    // Attribuer toutes les permissions aux rôles super_admin et admin
    $db->exec("
        INSERT INTO role_permissions (role_id, permission_id)
        SELECT 
            r.id as role_id,
            p.id as permission_id
        FROM roles r
        CROSS JOIN permissions p
        WHERE r.name IN ('super_admin', 'admin')
    ");
    echo "Permissions attribuées au super_admin et admin.\n";
    
    // Créer l'utilisateur admin par défaut
    $adminPassword = Auth::hashPassword('Admin@123');
    $db->exec("
        INSERT INTO users (username, email, password, status)
        VALUES ('admin', 'admin@example.com', '$adminPassword', 1)
    ");
    echo "Utilisateur admin créé.\n";
    
    // Attribuer le rôle admin à l'utilisateur admin
    $db->exec("
        INSERT INTO user_roles (user_id, role_id)
        SELECT 
            (SELECT id FROM users WHERE username = 'admin'),
            (SELECT id FROM roles WHERE name = 'admin')
    ");
    echo "Rôle admin attribué à l'admin.\n";
    
    // Types de finances par défaut
    $defaultFinanceTypes = [
        ['Dîmes', 'Entrée', 'Dîmes des membres'],
        ['Offrandes', 'Entrée', 'Offrandes générales'],
        ['Dons', 'Entrée', 'Dons spéciaux'],
        ['Salaires', 'Sortie', 'Salaires du personnel'],
        ['Factures', 'Sortie', 'Factures diverses'],
        ['Entretien', 'Sortie', 'Entretien des locaux']
    ];
    
    foreach ($defaultFinanceTypes as $type) {
        $db->exec("
            INSERT INTO finance_types (name, category, description)
            VALUES ('{$type[0]}', '{$type[1]}', '{$type[2]}')
        ");
    }
    echo "Types de finances par défaut créés.\n";
    
    echo "\nBase de données réinitialisée avec succès !\n";
    echo "Vous pouvez vous connecter avec :\n";
    echo "Email : admin@example.com\n";
    echo "Mot de passe : Admin@123\n";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
    exit(1);
}
