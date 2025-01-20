-- Mot de passe par défaut : Admin@123
INSERT INTO users (username, password, email, role) VALUES 
('admin', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN.jf.OGWo1xlVEc3NKom', 'admin@church.com', 'super_admin')
ON DUPLICATE KEY UPDATE 
    password = VALUES(password),
    email = VALUES(email),
    role = VALUES(role);
