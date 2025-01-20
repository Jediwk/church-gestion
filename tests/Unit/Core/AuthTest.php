<?php
namespace Tests\Unit\Core;

use App\Core\Auth;
use App\Core\Database;
use PHPUnit\Framework\TestCase;
use PDO;

class AuthTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer une connexion PDO de test
        $this->pdo = new PDO(
            'mysql:host=127.0.0.1;dbname=church_gestion_test;charset=utf8mb4',
            'root',
            '',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );

        // Créer la base de données de test
        $this->pdo->exec('DROP DATABASE IF EXISTS church_gestion_test');
        $this->pdo->exec('CREATE DATABASE church_gestion_test');
        $this->pdo->exec('USE church_gestion_test');

        // Créer les tables nécessaires
        $this->pdo->exec('
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
        $this->pdo->exec("
            INSERT INTO users (email, password, first_name, last_name)
            VALUES (
                'test@example.com',
                '".password_hash('password123', PASSWORD_DEFAULT)."',
                'Test',
                'User'
            )
        ");
    }

    protected function tearDown(): void
    {
        $this->pdo->exec('DROP DATABASE IF EXISTS church_gestion_test');
        $this->pdo = null;
        parent::tearDown();
    }

    public function testAttemptWithValidCredentials()
    {
        $result = Auth::attempt('test@example.com', 'password123');
        $this->assertTrue($result);
        $this->assertTrue(isset($_SESSION['user_id']));
    }

    public function testAttemptWithInvalidPassword()
    {
        $result = Auth::attempt('test@example.com', 'wrongpassword');
        $this->assertFalse($result);
        $this->assertFalse(isset($_SESSION['user_id']));
    }

    public function testAttemptWithInvalidEmail()
    {
        $result = Auth::attempt('nonexistent@example.com', 'password123');
        $this->assertFalse($result);
        $this->assertFalse(isset($_SESSION['user_id']));
    }

    public function testCheckWithAuthenticatedUser()
    {
        $_SESSION['user_id'] = 1;
        $this->assertTrue(Auth::check());
    }

    public function testCheckWithUnauthenticatedUser()
    {
        unset($_SESSION['user_id']);
        $this->assertFalse(Auth::check());
    }

    public function testUserReturnsCorrectData()
    {
        $_SESSION['user_id'] = 1;
        $user = Auth::user();
        
        $this->assertIsArray($user);
        $this->assertEquals('test@example.com', $user['email']);
        $this->assertEquals('Test', $user['first_name']);
        $this->assertEquals('User', $user['last_name']);
    }

    public function testLogoutClearsSession()
    {
        $_SESSION['user_id'] = 1;
        Auth::logout();
        
        $this->assertFalse(isset($_SESSION['user_id']));
        $this->assertNull(Auth::user());
    }
}
