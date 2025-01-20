-- Insertion des utilisateurs par défaut
-- Note: Les mots de passe sont hashés avec password_hash() en PHP
-- Le mot de passe par défaut est 'password123' pour tous les utilisateurs

-- Super Admin
INSERT INTO users (email, password, first_name, last_name, is_active) 
VALUES ('admin@church.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Super', 1);

SET @admin_id = LAST_INSERT_ID();
INSERT INTO user_roles (user_id, role_id) 
SELECT @admin_id, id FROM roles WHERE name = 'super_admin';

-- Pastor
INSERT INTO users (email, password, first_name, last_name, is_active)
VALUES ('pastor@church.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pastor', 'Church', 1);

SET @pastor_id = LAST_INSERT_ID();
INSERT INTO user_roles (user_id, role_id)
SELECT @pastor_id, id FROM roles WHERE name = 'pastor';

-- Treasurer
INSERT INTO users (email, password, first_name, last_name, is_active)
VALUES ('treasurer@church.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Treasurer', 'Church', 1);

SET @treasurer_id = LAST_INSERT_ID();
INSERT INTO user_roles (user_id, role_id)
SELECT @treasurer_id, id FROM roles WHERE name = 'treasurer';

-- Secretary
INSERT INTO users (email, password, first_name, last_name, is_active)
VALUES ('secretary@church.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Secretary', 'Church', 1);

SET @secretary_id = LAST_INSERT_ID();
INSERT INTO user_roles (user_id, role_id)
SELECT @secretary_id, id FROM roles WHERE name = 'secretary';
