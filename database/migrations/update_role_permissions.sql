-- Supprimer les permissions existantes
DELETE FROM role_permissions;

-- Insérer les nouvelles permissions pour chaque rôle

-- Super Admin (accès complet)
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id 
FROM roles r 
CROSS JOIN permissions p
WHERE r.name = 'super_admin';

-- Pastor (lecture et export de toutes les données)
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id 
FROM roles r 
CROSS JOIN permissions p
WHERE r.name = 'pastor' 
AND p.name IN ('read_all', 'export_all', 'view_dashboard');

-- Treasurer (gestion des finances)
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id 
FROM roles r 
CROSS JOIN permissions p
WHERE r.name = 'treasurer' 
AND p.name IN (
    'manage_finances',
    'read_finances',
    'export_finances',
    'manage_finance_types',
    'view_finance_reports',
    'view_dashboard',
    'generate_reports'
);

-- Secretary (gestion des membres)
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id 
FROM roles r 
CROSS JOIN permissions p
WHERE r.name = 'secretary' 
AND p.name IN (
    'manage_members',
    'read_members',
    'export_members',
    'manage_families',
    'view_dashboard'
);
