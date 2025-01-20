<?php

namespace App\Models;

use App\Core\Database;

class Role {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll() {
        return $this->db->query("SELECT * FROM roles ORDER BY name")->fetchAll();
    }

    public function getById($id) {
        return $this->db->query("SELECT * FROM roles WHERE id = ?", [$id])->fetch();
    }

    public function getPermissions($roleId) {
        return $this->db->query(
            "SELECT p.* FROM permissions p 
            JOIN role_permissions rp ON p.id = rp.permission_id 
            WHERE rp.role_id = ?",
            [$roleId]
        )->fetchAll();
    }

    public function hasPermission($roleId, $permission) {
        // Super admin a toutes les permissions
        $isSuperAdmin = $this->db->query(
            "SELECT 1 FROM roles r 
            JOIN role_permissions rp ON r.id = rp.role_id 
            JOIN permissions p ON rp.permission_id = p.id 
            WHERE r.id = ? AND p.name = '*'",
            [$roleId]
        )->fetch();

        if ($isSuperAdmin) {
            return true;
        }

        // Vérifier la permission spécifique
        $hasPermission = $this->db->query(
            "SELECT 1 FROM role_permissions rp 
            JOIN permissions p ON rp.permission_id = p.id 
            WHERE rp.role_id = ? AND p.name = ?",
            [$roleId, $permission]
        )->fetch();

        return !empty($hasPermission);
    }

    public function create($data) {
        $this->db->query(
            "INSERT INTO roles (name, description) VALUES (?, ?)",
            [$data['name'], $data['description']]
        );
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        return $this->db->query(
            "UPDATE roles SET name = ?, description = ? WHERE id = ?",
            [$data['name'], $data['description'], $id]
        );
    }

    public function delete($id) {
        return $this->db->query("DELETE FROM roles WHERE id = ?", [$id]);
    }

    public function assignPermissions($roleId, $permissions) {
        // Supprimer les permissions existantes
        $this->db->query("DELETE FROM role_permissions WHERE role_id = ?", [$roleId]);

        // Ajouter les nouvelles permissions
        foreach ($permissions as $permissionId) {
            $this->db->query(
                "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)",
                [$roleId, $permissionId]
            );
        }
    }
}
