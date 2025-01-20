<?php
namespace Tests\Unit\Core;

use App\Core\Database;
use PHPUnit\Framework\TestCase;
use PDO;

class DatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurer les variables d'environnement pour les tests
        $_ENV['DB_HOST'] = '127.0.0.1';
        $_ENV['DB_DATABASE'] = 'church_gestion_test';
        $_ENV['DB_USERNAME'] = 'root';
        $_ENV['DB_PASSWORD'] = '';
    }

    public function testGetInstanceReturnsPDOInstance()
    {
        $db = Database::getInstance();
        $this->assertInstanceOf(PDO::class, $db);
    }

    public function testGetInstanceReturnsSameInstance()
    {
        $db1 = Database::getInstance();
        $db2 = Database::getInstance();
        $this->assertSame($db1, $db2);
    }

    public function testDatabaseConnection()
    {
        $db = Database::getInstance();
        $stmt = $db->query('SELECT 1');
        $result = $stmt->fetch(PDO::FETCH_NUM);
        $this->assertEquals(1, $result[0]);
    }

    public function testPreparedStatements()
    {
        $db = Database::getInstance();
        
        // Créer une table de test
        $db->exec('
            CREATE TABLE IF NOT EXISTS test_table (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100)
            )
        ');

        // Tester l'insertion avec une requête préparée
        $stmt = $db->prepare('INSERT INTO test_table (name) VALUES (?)');
        $name = 'Test Name';
        $result = $stmt->execute([$name]);
        $this->assertTrue($result);

        // Vérifier que l'insertion a fonctionné
        $stmt = $db->prepare('SELECT name FROM test_table WHERE id = ?');
        $stmt->execute([1]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals($name, $result['name']);

        // Nettoyer
        $db->exec('DROP TABLE test_table');
    }
}
