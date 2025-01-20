<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    
    // Vérifier l'utilisateur admin
    $sql = "SELECT * FROM users WHERE email = 'admin@church.com'";
    $user = $db->query($sql)->fetch();
    
    if (!$user) {
        echo "Utilisateur admin@church.com non trouvé\n";
        exit(1);
    }
    
    // Tester le mot de passe
    $password = 'password';
    if (password_verify($password, $user['password'])) {
        echo "Le mot de passe est correct\n";
    } else {
        echo "Le mot de passe est incorrect\n";
        
        // Mettre à jour le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ? WHERE email = 'admin@church.com'";
        $db->prepare($sql)->execute([$hashedPassword]);
        
        echo "Le mot de passe a été mis à jour\n";
    }
    
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
    exit(1);
}
