-- Ajouter la colonne email à la table users
ALTER TABLE users ADD COLUMN email VARCHAR(100) UNIQUE NOT NULL AFTER username;

-- Mettre à jour la structure des index
CREATE INDEX idx_email ON users(email);

-- Insérer un utilisateur admin par défaut
INSERT INTO users (username, email, password, role, status) 
VALUES (
    'admin',
    'admin@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: Admin@123
    'super_admin',
    1
);
