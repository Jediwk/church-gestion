-- Création de la table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) DEFAULT NULL,
    last_name VARCHAR(100) DEFAULT NULL,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Création de la table des rôles
CREATE TABLE IF NOT EXISTS roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Création de la table des permissions
CREATE TABLE IF NOT EXISTS permissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Création de la table pivot user_roles
CREATE TABLE IF NOT EXISTS user_roles (
    user_id INTEGER NOT NULL,
    role_id INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- Création de la table pivot role_permissions
CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INTEGER NOT NULL,
    permission_id INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

-- Insertion des permissions de base
INSERT OR IGNORE INTO permissions (name, description) VALUES
('*', 'Toutes les permissions'),
('manage_users', 'Gérer les utilisateurs'),
('read_users', 'Voir les utilisateurs'),
('manage_members', 'Gérer les membres'),
('read_members', 'Voir les membres'),
('export_members', 'Exporter les membres'),
('manage_families', 'Gérer les familles'),
('read_families', 'Voir les familles'),
('export_families', 'Exporter les familles'),
('manage_finances', 'Gérer les finances'),
('read_finances', 'Voir les finances'),
('export_finances', 'Exporter les finances'),
('manage_finance_types', 'Gérer les types de finances'),
('view_finance_reports', 'Voir les rapports financiers'),
('view_dashboard', 'Voir le tableau de bord'),
('generate_reports', 'Générer des rapports');

-- Insertion des rôles de base
INSERT OR IGNORE INTO roles (name, description) VALUES
('super_admin', 'Super Administrateur avec tous les droits'),
('pastor', 'Pasteur avec accès à la gestion des membres et événements'),
('treasurer', 'Trésorier avec accès aux finances'),
('secretary', 'Secrétaire avec accès limité');
