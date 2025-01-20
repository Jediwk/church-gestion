<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    
    // Lire et exécuter le fichier de migration
    $sql = file_get_contents(__DIR__ . '/migrations/001_create_tables.sql');
    
    // Diviser le fichier en requêtes individuelles
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    // Exécuter chaque requête
    foreach ($queries as $query) {
        if (!empty($query)) {
            $db->exec($query);
        }
    }
    
    echo "Migration réussie !\n";
} catch (Exception $e) {
    echo "Erreur lors de la migration : " . $e->getMessage() . "\n";
    exit(1);
}
