<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Vérifier si la colonne existe déjà
    $stmt = $pdo->query("PRAGMA table_info(users)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $emailExists = false;
    foreach ($columns as $column) {
        if ($column['name'] === 'email') {
            $emailExists = true;
            break;
        }
    }

    // Ajouter la colonne email si elle n'existe pas
    if (!$emailExists) {
        $pdo->exec("ALTER TABLE users ADD COLUMN email TEXT");
        echo "Colonne email ajoutée à la table users\n";
    }

    // Vérifier si l'admin existe déjà
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $adminExists = $stmt->fetchColumn() > 0;

    if (!$adminExists) {
        // Insérer l'utilisateur admin par défaut
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, role, status) 
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            'admin',
            'admin@example.com',
            password_hash('Admin@123', PASSWORD_DEFAULT),
            'super_admin',
            1
        ]);
        echo "Utilisateur admin créé avec succès\n";
    }

    echo "Migration terminée avec succès\n";
} catch (Exception $e) {
    echo "Erreur lors de la migration : " . $e->getMessage() . "\n";
    exit(1);
}
