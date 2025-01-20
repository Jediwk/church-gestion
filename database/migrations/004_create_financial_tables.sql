-- Table des catégories de transactions
CREATE TABLE IF NOT EXISTS transaction_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    description TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des transactions
CREATE TABLE IF NOT EXISTS transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    member_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    payment_method ENUM('cash', 'check', 'transfer', 'other') NOT NULL,
    reference_number VARCHAR(50),
    description TEXT,
    transaction_date DATE NOT NULL,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (category_id) REFERENCES transaction_categories(id),
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_type (type),
    INDEX idx_date (transaction_date),
    INDEX idx_member (member_id),
    INDEX idx_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des budgets
CREATE TABLE IF NOT EXISTS budgets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    description TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (category_id) REFERENCES transaction_categories(id),
    INDEX idx_date (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des catégories par défaut
INSERT INTO transaction_categories (name, type, description, created_at, updated_at) VALUES
('Dîmes', 'income', 'Dîmes des membres', NOW(), NOW()),
('Offrandes', 'income', 'Offrandes générales', NOW(), NOW()),
('Dons spéciaux', 'income', 'Dons pour projets spéciaux', NOW(), NOW()),
('Salaires', 'expense', 'Salaires du personnel', NOW(), NOW()),
('Entretien', 'expense', 'Entretien des locaux', NOW(), NOW()),
('Utilities', 'expense', 'Eau, électricité, etc.', NOW(), NOW()),
('Matériel', 'expense', 'Achats de matériel', NOW(), NOW()),
('Missions', 'expense', 'Dépenses missionnaires', NOW(), NOW());
