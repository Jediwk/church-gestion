<?php
namespace App\Core;

use App\Models\User;
use App\Models\Role;

class Auth {
    public static function check() {
        return isset($_SESSION['user']);
    }

    public static function user() {
        return $_SESSION['user'] ?? null;
    }

    public static function login($userData) {
        // Récupérer les permissions de l'utilisateur
        $db = Database::getInstance()->getConnection();
        
        // Récupérer le rôle et les permissions
        $sql = "SELECT p.name as permission_name 
                FROM permissions p 
                JOIN role_permissions rp ON p.id = rp.permission_id 
                JOIN user_roles ur ON rp.role_id = ur.role_id 
                WHERE ur.user_id = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$userData['id']]);
        $permissions = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        // Stocker l'utilisateur et ses permissions en session
        $_SESSION['user'] = [
            'id' => $userData['id'],
            'username' => $userData['username'],
            'email' => $userData['email'],
            'status' => $userData['status'],
            'permissions' => $permissions
        ];
    }

    public static function logout() {
        unset($_SESSION['user']);
    }

    public static function hasPermission($permission) {
        if (!self::check()) {
            return false;
        }

        $userPermissions = $_SESSION['user']['permissions'] ?? [];

        // Si l'utilisateur a la permission '*', il a accès à tout
        if (in_array('*', $userPermissions)) {
            return true;
        }

        // Vérifier la permission spécifique
        return in_array($permission, $userPermissions);
    }

    public static function hasAnyPermission($permissions) {
        foreach ($permissions as $permission) {
            if (self::hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public static function hasAllPermissions($permissions) {
        foreach ($permissions as $permission) {
            if (!self::hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Hash a password using PASSWORD_BCRYPT
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify a password against a hash
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Check if a password needs to be rehashed
     */
    public static function passwordNeedsRehash($hash) {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Generate a secure random token
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
}
