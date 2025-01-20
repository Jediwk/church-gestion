<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Auth;

class User extends Model {
    protected $table = 'users';

    /**
     * Récupère tous les utilisateurs avec leurs rôles
     */
    public function getAllWithRoles() {
        $sql = "SELECT u.*, r.name as role_name 
                FROM {$this->table} u 
                LEFT JOIN user_roles ur ON u.id = ur.user_id 
                LEFT JOIN roles r ON ur.role_id = r.id 
                ORDER BY u.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Trouve un utilisateur par son email
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        return $this->db->query($sql, ['email' => $email])->fetch();
    }

    /**
     * Vérifie si un email existe déjà
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        return $this->db->query($sql, $params)->fetchColumn() > 0;
    }

    /**
     * Crée un nouvel utilisateur
     */
    public function create(array $data) {
        $this->db->beginTransaction();
        try {
            // Extraire le rôle du tableau de données
            $roleId = isset($data['role_id']) ? $data['role_id'] : null;
            unset($data['role_id']);

            // Hasher le mot de passe
            if (isset($data['password'])) {
                $data['password'] = Auth::hashPassword($data['password']);
            }

            // Ajouter les timestamps
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');

            // Créer l'utilisateur
            $userId = $this->db->insert($this->table, $data);

            // Associer le rôle
            if ($userId && $roleId) {
                $this->db->query(
                    "INSERT INTO user_roles (user_id, role_id, created_at) VALUES (?, ?, CURRENT_TIMESTAMP)",
                    [$userId, $roleId]
                );
            }

            $this->db->commit();
            return $userId;
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Met à jour un utilisateur
     */
    public function update($id, array $data) {
        $this->db->beginTransaction();
        try {
            // Extraire le rôle du tableau de données
            $roleId = isset($data['role_id']) ? $data['role_id'] : null;
            unset($data['role_id']);

            // Hasher le mot de passe si fourni
            if (isset($data['password'])) {
                $data['password'] = Auth::hashPassword($data['password']);
            }

            // Ajouter le timestamp de mise à jour
            $data['updated_at'] = date('Y-m-d H:i:s');

            // Mettre à jour l'utilisateur
            $success = $this->db->update($this->table, $data, ['id' => $id]);

            // Mettre à jour le rôle si fourni
            if ($success && $roleId !== null) {
                // Supprimer l'ancien rôle
                $this->db->query("DELETE FROM user_roles WHERE user_id = ?", [$id]);
                
                // Ajouter le nouveau rôle
                $this->db->query(
                    "INSERT INTO user_roles (user_id, role_id, created_at) VALUES (?, ?, CURRENT_TIMESTAMP)",
                    [$id, $roleId]
                );
            }

            $this->db->commit();
            return $success;
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Récupère les rôles d'un utilisateur
     */
    public function getRoles($userId) {
        $sql = "SELECT r.* 
                FROM roles r 
                JOIN user_roles ur ON r.id = ur.role_id 
                WHERE ur.user_id = ?";
        return $this->db->query($sql, [$userId])->fetchAll();
    }

    /**
     * Vérifie si un utilisateur a un rôle spécifique
     */
    public function hasRole($userId, $roleName) {
        $sql = "SELECT COUNT(*) 
                FROM user_roles ur 
                JOIN roles r ON ur.role_id = r.id 
                WHERE ur.user_id = ? AND r.name = ?";
        return $this->db->query($sql, [$userId, $roleName])->fetchColumn() > 0;
    }

    /**
     * Active ou désactive un utilisateur
     */
    public function setActive($id, $active) {
        return $this->update($id, [
            'is_active' => $active,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Stocke un token "Se souvenir de moi"
     */
    public function storeRememberToken($userId, $token, $userAgent) {
        $data = [
            'user_id' => $userId,
            'token' => hash('sha256', $token),
            'user_agent' => $userAgent,
            'expires_at' => date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)),
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->db->insert('remember_tokens', $data) > 0;
    }

    /**
     * Vérifie un token "Se souvenir de moi"
     */
    public function verifyRememberToken($token, $userAgent) {
        $sql = "SELECT u.* FROM users u
                INNER JOIN remember_tokens rt ON u.id = rt.user_id
                WHERE rt.token = :token
                AND rt.user_agent = :user_agent
                AND rt.expires_at > NOW()
                AND u.is_active = 1
                LIMIT 1";

        $params = [
            'token' => hash('sha256', $token),
            'user_agent' => $userAgent
        ];

        return $this->db->query($sql, $params)->fetch();
    }

    /**
     * Supprime les tokens "Se souvenir de moi" d'un utilisateur
     */
    public function deleteRememberTokens($userId) {
        return $this->db->delete('remember_tokens', ['user_id' => $userId]);
    }

    /**
     * Supprime tous les tokens "Se souvenir de moi" expirés
     */
    public function cleanupRememberTokens() {
        return $this->db->query("DELETE FROM remember_tokens WHERE expires_at < NOW()")->rowCount();
    }

    /**
     * Retourne l'instance de la base de données
     */
    public function getDb() {
        return $this->db;
    }
}
