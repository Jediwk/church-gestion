<?php
namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Core\Auth;
use App\Core\Database;

class AuthenticationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurer la base de données de test
        $_ENV['DB_HOST'] = '127.0.0.1';
        $_ENV['DB_DATABASE'] = 'church_gestion_test';
        $_ENV['DB_USERNAME'] = 'root';
        $_ENV['DB_PASSWORD'] = '';

        // Créer la base de données de test
        $pdo = new \PDO('mysql:host=127.0.0.1', 'root', '');
        $pdo->exec('DROP DATABASE IF EXISTS church_gestion_test');
        $pdo->exec('CREATE DATABASE church_gestion_test');
        $pdo->exec('USE church_gestion_test');

        // Créer les tables nécessaires
        $pdo->exec('
            CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                first_name VARCHAR(100),
                last_name VARCHAR(100),
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ');

        // Insérer un utilisateur de test
        $pdo->exec("
            INSERT INTO users (email, password, first_name, last_name)
            VALUES (
                'admin@example.com',
                '".password_hash('admin123', PASSWORD_DEFAULT)."',
                'Admin',
                'System'
            )
        ");
    }

    protected function tearDown(): void
    {
        $pdo = new \PDO('mysql:host=127.0.0.1', 'root', '');
        $pdo->exec('DROP DATABASE IF EXISTS church_gestion_test');
        parent::tearDown();
    }

    public function testLoginWithValidCredentials()
    {
        $result = Auth::attempt('admin@example.com', 'admin123');
        $this->assertTrue($result);
    }

    public function testLoginWithInvalidPassword()
    {
        $result = Auth::attempt('admin@example.com', 'wrongpassword');
        $this->assertFalse($result);
    }

    public function testLoginWithInvalidEmail()
    {
        $result = Auth::attempt('nonexistent@example.com', 'admin123');
        $this->assertFalse($result);
    }

    public function testUserSessionAfterLogin()
    {
        Auth::attempt('admin@example.com', 'admin123');
        $user = Auth::user();
        
        $this->assertIsArray($user);
        $this->assertEquals('admin@example.com', $user['email']);
        $this->assertEquals('Admin', $user['first_name']);
        $this->assertEquals('System', $user['last_name']);
    }
}
