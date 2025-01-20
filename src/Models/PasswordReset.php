<?php
namespace Models;

use Core\Model;

class PasswordReset extends Model {
    protected string $table = 'password_resets';
    protected array $fillable = ['user_id', 'token', 'expires_at'];

    public function findValidToken(string $token): ?array {
        $query = "SELECT * FROM {$this->table} 
                 WHERE token = :token 
                 AND used = 0 
                 AND expires_at > NOW() 
                 LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(['token' => $token]);
        return $stmt->fetch() ?: null;
    }

    public function invalidateToken(string $token): bool {
        $query = "UPDATE {$this->table} 
                 SET used = 1 
                 WHERE token = :token";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['token' => $token]);
    }

    public function cleanExpiredTokens(): bool {
        $query = "DELETE FROM {$this->table} 
                 WHERE expires_at < NOW() 
                 OR used = 1";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute();
    }
}
