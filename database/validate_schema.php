<?php

class SchemaValidator {
    private $pdo;
    private $errors = [];
    private $warnings = [];

    public function __construct($host, $dbname, $user, $pass) {
        try {
            $this->pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    public function validate() {
        $this->validateDatabaseCharset();
        $this->validateTables();
        $this->validateForeignKeys();
        $this->validateIndexes();
        $this->validateEnumValues();
        $this->validateRequiredFields();
        $this->checkForMissingIndexes();
        $this->checkForRedundantIndexes();
        
        return [
            'errors' => $this->errors,
            'warnings' => $this->warnings
        ];
    }

    private function validateDatabaseCharset() {
        $sql = "SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME 
                FROM information_schema.SCHEMATA 
                WHERE SCHEMA_NAME = DATABASE()";
        $result = $this->pdo->query($sql)->fetch();

        if ($result['DEFAULT_CHARACTER_SET_NAME'] !== 'utf8mb4') {
            $this->errors[] = "La base de données n'utilise pas l'encodage utf8mb4";
        }
        if ($result['DEFAULT_COLLATION_NAME'] !== 'utf8mb4_unicode_ci') {
            $this->errors[] = "La base de données n'utilise pas la collation utf8mb4_unicode_ci";
        }
    }

    private function validateTables() {
        $requiredTables = [
            'users', 'remember_tokens', 'members', 'families',
            'family_relationships', 'visitors', 'transaction_categories',
            'transactions', 'budgets', 'events', 'event_participants',
            'system_logs'
        ];

        $sql = "SHOW TABLES";
        $existingTables = $this->pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);

        foreach ($requiredTables as $table) {
            if (!in_array($table, $existingTables)) {
                $this->errors[] = "Table manquante : $table";
            } else {
                $this->validateTableStructure($table);
            }
        }
    }

    private function validateTableStructure($table) {
        // Vérification du moteur de stockage
        $sql = "SHOW TABLE STATUS WHERE Name = '$table'";
        $status = $this->pdo->query($sql)->fetch();
        
        if ($status['Engine'] !== 'InnoDB') {
            $this->errors[] = "La table $table n'utilise pas le moteur InnoDB";
        }

        // Vérification du charset et de la collation
        if ($status['Collation'] !== 'utf8mb4_unicode_ci') {
            $this->errors[] = "La table $table n'utilise pas la collation utf8mb4_unicode_ci";
        }

        // Vérification des colonnes timestamp
        $columns = $this->pdo->query("SHOW COLUMNS FROM $table")->fetchAll();
        $hasCreatedAt = false;
        $hasUpdatedAt = false;

        foreach ($columns as $column) {
            if ($column['Field'] === 'created_at') $hasCreatedAt = true;
            if ($column['Field'] === 'updated_at') $hasUpdatedAt = true;
        }

        if (!$hasCreatedAt) {
            $this->warnings[] = "La table $table n'a pas de colonne created_at";
        }
        if (!$hasUpdatedAt) {
            $this->warnings[] = "La table $table n'a pas de colonne updated_at";
        }
    }

    private function validateForeignKeys() {
        $tables = $this->pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tables as $table) {
            $sql = "
                SELECT 
                    CONSTRAINT_NAME,
                    COLUMN_NAME,
                    REFERENCED_TABLE_NAME,
                    REFERENCED_COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE 
                    TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = '$table'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
            ";
            
            $foreignKeys = $this->pdo->query($sql)->fetchAll();
            
            foreach ($foreignKeys as $fk) {
                // Vérifier si la table référencée existe
                if (!in_array($fk['REFERENCED_TABLE_NAME'], $tables)) {
                    $this->errors[] = "Clé étrangère invalide dans $table : {$fk['CONSTRAINT_NAME']} référence une table inexistante {$fk['REFERENCED_TABLE_NAME']}";
                }
                
                // Vérifier si la colonne référencée est une clé primaire ou a un index
                $sql = "SHOW INDEXES FROM {$fk['REFERENCED_TABLE_NAME']} WHERE Column_name = '{$fk['REFERENCED_COLUMN_NAME']}'";
                if (!$this->pdo->query($sql)->fetch()) {
                    $this->errors[] = "Clé étrangère non indexée dans $table : {$fk['CONSTRAINT_NAME']}";
                }
            }
        }
    }

    private function validateIndexes() {
        $tables = $this->pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tables as $table) {
            $sql = "SHOW INDEXES FROM $table";
            $indexes = $this->pdo->query($sql)->fetchAll();
            
            $hasNonUniqueIndex = false;
            foreach ($indexes as $index) {
                if ($index['Non_unique'] == 1) {
                    $hasNonUniqueIndex = true;
                }
                
                // Vérifier la cardinalité des index
                if ($index['Cardinality'] !== null && $index['Cardinality'] == 0) {
                    $this->warnings[] = "Index potentiellement inutile sur $table : {$index['Key_name']}";
                }
            }
            
            if (!$hasNonUniqueIndex) {
                $this->warnings[] = "La table $table pourrait bénéficier d'index non uniques pour les recherches";
            }
        }
    }

    private function validateEnumValues() {
        $enumFields = [
            'users' => ['role' => ['super_admin', 'pastor', 'treasurer', 'secretary'],
                       'status' => ['active', 'inactive']],
            'members' => ['gender' => ['M', 'F'],
                         'marital_status' => ['single', 'married', 'divorced', 'widowed'],
                         'status' => ['active', 'inactive', 'visitor']],
            'transactions' => ['type' => ['income', 'expense'],
                             'payment_method' => ['cash', 'check', 'transfer', 'other']]
        ];

        foreach ($enumFields as $table => $fields) {
            foreach ($fields as $field => $expectedValues) {
                $sql = "SHOW COLUMNS FROM $table WHERE Field = '$field'";
                $column = $this->pdo->query($sql)->fetch();

                if ($column['Type'] !== "enum('" . implode("','", $expectedValues) . "')") {
                    $this->errors[] = "Valeurs ENUM incorrectes pour $table.$field";
                }
            }
        }
    }

    private function validateRequiredFields() {
        $requiredFields = [
            'users' => ['email', 'password', 'role'],
            'members' => ['first_name', 'last_name', 'gender'],
            'transactions' => ['amount', 'type', 'transaction_date'],
            'transaction_categories' => ['name', 'type']
        ];

        foreach ($requiredFields as $table => $fields) {
            foreach ($fields as $field) {
                $sql = "SHOW COLUMNS FROM $table WHERE Field = '$field'";
                $column = $this->pdo->query($sql)->fetch();

                if ($column['Null'] !== 'NO') {
                    $this->errors[] = "Le champ $table.$field devrait être NOT NULL";
                }
            }
        }
    }

    private function checkForMissingIndexes() {
        $recommendedIndexes = [
            'members' => ['email', 'phone', 'status'],
            'transactions' => ['transaction_date', 'type', 'member_id'],
            'events' => ['start_date', 'end_date', 'status']
        ];

        foreach ($recommendedIndexes as $table => $fields) {
            foreach ($fields as $field) {
                $sql = "SHOW INDEXES FROM $table WHERE Column_name = '$field'";
                if (!$this->pdo->query($sql)->fetch()) {
                    $this->warnings[] = "Index recommandé manquant sur $table.$field";
                }
            }
        }
    }

    private function checkForRedundantIndexes() {
        $tables = $this->pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tables as $table) {
            $sql = "SHOW INDEXES FROM $table";
            $indexes = $this->pdo->query($sql)->fetchAll();
            
            $columnIndexes = [];
            foreach ($indexes as $index) {
                $columns = $index['Column_name'];
                
                // Vérifier les index redondants
                foreach ($columnIndexes as $existingColumns) {
                    if (strpos($existingColumns, $columns) === 0) {
                        $this->warnings[] = "Index potentiellement redondant sur $table : {$index['Key_name']}";
                    }
                }
                
                $columnIndexes[] = $columns;
            }
        }
    }
}

// Configuration
$config = [
    'host' => 'localhost',
    'dbname' => 'church_gestion',
    'user' => 'root',
    'pass' => ''
];

// Exécution de la validation
$validator = new SchemaValidator(
    $config['host'],
    $config['dbname'],
    $config['user'],
    $config['pass']
);

$results = $validator->validate();

// Affichage des résultats
echo "\n=== RAPPORT DE VALIDATION DU SCHÉMA ===\n\n";

if (empty($results['errors']) && empty($results['warnings'])) {
    echo "✅ Aucun problème détecté. Le schéma est valide.\n";
} else {
    if (!empty($results['errors'])) {
        echo "❌ ERREURS :\n";
        foreach ($results['errors'] as $error) {
            echo "  - $error\n";
        }
        echo "\n";
    }
    
    if (!empty($results['warnings'])) {
        echo "⚠️ AVERTISSEMENTS :\n";
        foreach ($results['warnings'] as $warning) {
            echo "  - $warning\n";
        }
        echo "\n";
    }
}
