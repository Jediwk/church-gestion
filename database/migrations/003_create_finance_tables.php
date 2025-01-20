<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    // Supprimer les tables existantes
    $db->exec("DROP TABLE IF EXISTS finances");
    $db->exec("DROP TABLE IF EXISTS finance_types");
    
    // Créer la table des types de finances
    $db->exec("CREATE TABLE IF NOT EXISTS finance_types (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        category TEXT NOT NULL CHECK(category IN ('Entrée', 'Sortie')),
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Créer la table des finances
    $db->exec("CREATE TABLE IF NOT EXISTS finances (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        type_id INTEGER NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        date DATE NOT NULL,
        description TEXT,
        reference TEXT,
        created_by INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (type_id) REFERENCES finance_types(id),
        FOREIGN KEY (created_by) REFERENCES users(id)
    )");
    
    // Insérer quelques types de finances par défaut
    $defaultTypes = [
        ['Dîme', 'Entrée', 'Dîmes des membres'],
        ['Offrande', 'Entrée', 'Offrandes lors des cultes'],
        ['Don', 'Entrée', 'Dons spéciaux'],
        ['Loyer', 'Sortie', 'Loyer du bâtiment'],
        ['Électricité', 'Sortie', 'Factures d\'électricité'],
        ['Eau', 'Sortie', 'Factures d\'eau'],
        ['Entretien', 'Sortie', 'Entretien et réparations'],
        ['Fournitures', 'Sortie', 'Fournitures de bureau et culte']
    ];
    
    $stmt = $db->prepare("INSERT INTO finance_types (name, category, description) VALUES (?, ?, ?)");
    foreach ($defaultTypes as $type) {
        $stmt->execute($type);
    }
    
    echo "Tables des finances créées avec succès !\n";
    echo "Types de finances par défaut ajoutés !\n";
    
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
    exit(1);
}
