-- Supprimer les tables existantes si elles existent
DROP TABLE IF EXISTS finances;
DROP TABLE IF EXISTS finance_types;
DROP TABLE IF EXISTS members;
DROP TABLE IF EXISTS families;
DROP TABLE IF EXISTS users;

-- Création de la table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Création de la table des types de finances
CREATE TABLE IF NOT EXISTS finance_types (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    category TEXT NOT NULL CHECK(category IN ('Entrée', 'Sortie')),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Création de la table des finances
CREATE TABLE IF NOT EXISTS finances (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type_id INTEGER NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    date DATE NOT NULL,
    description TEXT,
    reference TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (type_id) REFERENCES finance_types(id)
);

-- Création de la table des familles
CREATE TABLE IF NOT EXISTS families (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    address TEXT,
    phone TEXT,
    email TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Création de la table des membres
CREATE TABLE IF NOT EXISTS members (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    gender TEXT NOT NULL CHECK(gender IN ('M', 'F')),
    birthdate DATE,
    phone TEXT,
    email TEXT,
    address TEXT,
    profession TEXT,
    family_id INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (family_id) REFERENCES families(id)
);

-- Insertion des types de finances de base
INSERT INTO finance_types (name, category) VALUES
('Dîme', 'Entrée'),
('Offrande', 'Entrée'),
('Don', 'Entrée'),
('Projet de construction', 'Entrée'),
('Mission', 'Entrée'),
('Salaire', 'Sortie'),
('Facture d''électricité', 'Sortie'),
('Facture d''eau', 'Sortie'),
('Entretien', 'Sortie'),
('Fournitures', 'Sortie'),
('Mission', 'Sortie'),
('Aide sociale', 'Sortie');

-- Insertion d'un utilisateur par défaut (mot de passe: admin123)
INSERT INTO users (username, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
