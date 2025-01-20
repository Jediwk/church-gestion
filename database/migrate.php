<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    
    // Lire et exécuter le fichier de migration
    $sql = file_get_contents(__DIR__ . '/migrations/001_init_auth.sql');
    
    // Diviser le fichier en requêtes individuelles
    $queries = explode(';', $sql);
    
    // Exécuter chaque requête
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            $db->exec($query);
        }
    }
    
    echo "Migration réussie !\n";
    
} catch (PDOException $e) {
    echo "Erreur lors de la migration : " . $e->getMessage() . "\n";
    exit(1);
}
