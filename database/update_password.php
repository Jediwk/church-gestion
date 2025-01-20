<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    
    // Créer un nouveau hash pour le mot de passe "password123"
    $password = "password123";
    $hash = password_hash($password, PASSWORD_BCRYPT);
    
    // Mettre à jour le mot de passe de l'admin
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$hash, 'admin@church.com']);
    
    echo "Mot de passe mis à jour avec succès !\n";
    echo "Nouveau hash : " . $hash . "\n";
    
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
    exit(1);
}
