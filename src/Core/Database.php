<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $connection = null;
    private static $config = [
        'driver' => 'sqlite',
        'path' => __DIR__ . '/../../database/database.sqlite'
    ];

    private function __construct() {
        try {
            // Créer le dossier database s'il n'existe pas
            $databaseDir = dirname(self::$config['path']);
            if (!file_exists($databaseDir)) {
                mkdir($databaseDir, 0777, true);
            }

            // Connexion SQLite
            $dsn = self::$config['driver'] . ':' . self::$config['path'];
            $this->connection = new PDO($dsn);
            
            // Configuration de PDO
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
        } catch (PDOException $e) {
            throw new \Exception("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->connection;
    }

    public function query(string $sql, array $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new \Exception("Erreur d'exécution de la requête : " . $e->getMessage());
        }
    }

    public function prepare(string $query) {
        return $this->connection->prepare($query);
    }

    public function exec(string $query) {
        return $this->connection->exec($query);
    }

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    public function commit() {
        return $this->connection->commit();
    }

    public function rollBack() {
        return $this->connection->rollBack();
    }

    /**
     * Insert data into a table
     */
    public function insert(string $table, array $data) {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            implode(', ', $fields),
            implode(', ', $placeholders)
        );
        
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(array_values($data));
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de l'insertion : " . $e->getMessage());
        }
    }

    /**
     * Update data in a table
     */
    public function update(string $table, array $data, array $where) {
        $fields = array_map(function($field) {
            return "$field = ?";
        }, array_keys($data));
        
        $whereConditions = array_map(function($field) {
            return "$field = ?";
        }, array_keys($where));
        
        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s",
            $table,
            implode(', ', $fields),
            implode(' AND ', $whereConditions)
        );
        
        try {
            $stmt = $this->connection->prepare($sql);
            $values = array_merge(array_values($data), array_values($where));
            $stmt->execute($values);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de la mise à jour : " . $e->getMessage());
        }
    }

    /**
     * Delete data from a table
     */
    public function delete(string $table, array $where) {
        $whereConditions = array_map(function($field) {
            return "$field = ?";
        }, array_keys($where));
        
        $sql = sprintf(
            "DELETE FROM %s WHERE %s",
            $table,
            implode(' AND ', $whereConditions)
        );
        
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(array_values($where));
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de la suppression : " . $e->getMessage());
        }
    }

    // Empêcher le clonage de l'instance
    private function __clone() {}

    // Empêcher la désérialisation de l'instance
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }
}
