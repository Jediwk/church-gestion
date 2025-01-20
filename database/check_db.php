<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    // Vérifier la structure de la table users
    $stmt = $db->query("PRAGMA table_info(users)");
    echo "Structure de la table users :\n";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Vérifier les utilisateurs existants
    $stmt = $db->query("SELECT * FROM users");
    echo "\nUtilisateurs existants :\n";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
    exit(1);
}
