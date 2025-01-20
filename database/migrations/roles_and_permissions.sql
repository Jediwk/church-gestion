-- Création de la table des rôles
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Création de la table des permissions
CREATE TABLE IF NOT EXISTS permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Création de la table de liaison rôles-permissions
CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT,
    permission_id INT,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

-- Ajout de la colonne role_id à la table users
ALTER TABLE users ADD COLUMN role_id INT;
ALTER TABLE users ADD FOREIGN KEY (role_id) REFERENCES roles(id);

-- Insertion des rôles par défaut
INSERT INTO roles (name, description) VALUES
('super_admin', 'Administrateur avec accès complet'),
('pastor', 'Pasteur avec accès en lecture et export'),
('treasurer', 'Trésorier avec accès aux finances'),
('secretary', 'Secrétaire avec accès aux membres');

-- Insertion des permissions
INSERT INTO permissions (name, description) VALUES
('*', 'Accès complet'),
('read_all', 'Lecture de toutes les données'),
('export_all', 'Export de toutes les données'),
('manage_finances', 'Gestion des finances'),
('read_finances', 'Lecture des finances'),
('export_finances', 'Export des finances'),
('manage_members', 'Gestion des membres'),
('read_members', 'Lecture des membres'),
('export_members', 'Export des membres');

-- Attribution des permissions aux rôles
-- Super Admin
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'super_admin' AND p.name = '*';

-- Pastor
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'pastor' AND p.name IN ('read_all', 'export_all');

-- Treasurer
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'treasurer' AND p.name IN ('manage_finances', 'read_finances', 'export_finances');

-- Secretary
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'secretary' AND p.name IN ('manage_members', 'read_members', 'export_members');
