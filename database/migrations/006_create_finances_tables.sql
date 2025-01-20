-- Table des catégories de transactions
CREATE TABLE transaction_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des transactions
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    amount DECIMAL(15,0) NOT NULL COMMENT 'Montant en XOF CFA',
    type ENUM('income', 'expense') NOT NULL,
    date DATE NOT NULL,
    description TEXT,
    payment_method ENUM('cash', 'check', 'bank_transfer', 'mobile_money', 'other') NOT NULL,
    reference_number VARCHAR(100),
    member_id INT,
    status ENUM('pending', 'completed', 'cancelled') NOT NULL DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (category_id) REFERENCES transaction_categories(id),
    FOREIGN KEY (member_id) REFERENCES members(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des dons
CREATE TABLE donations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT,
    amount DECIMAL(15,0) NOT NULL COMMENT 'Montant en XOF CFA',
    date DATE NOT NULL,
    type ENUM('tithe', 'offering', 'special', 'project') NOT NULL,
    campaign VARCHAR(100),
    payment_method ENUM('cash', 'check', 'bank_transfer', 'mobile_money', 'other') NOT NULL,
    reference_number VARCHAR(100),
    notes TEXT,
    status ENUM('pending', 'completed', 'cancelled') NOT NULL DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (member_id) REFERENCES members(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des promesses de dons
CREATE TABLE donation_pledges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    amount DECIMAL(15,0) NOT NULL COMMENT 'Montant en XOF CFA',
    type ENUM('tithe', 'offering', 'special', 'project') NOT NULL,
    campaign VARCHAR(100),
    start_date DATE NOT NULL,
    end_date DATE,
    frequency ENUM('one_time', 'weekly', 'monthly', 'quarterly', 'yearly'),
    notes TEXT,
    status ENUM('active', 'completed', 'cancelled') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (member_id) REFERENCES members(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des paiements de promesses
CREATE TABLE pledge_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pledge_id INT NOT NULL,
    amount DECIMAL(15,0) NOT NULL COMMENT 'Montant en XOF CFA',
    date DATE NOT NULL,
    payment_method ENUM('cash', 'check', 'bank_transfer', 'mobile_money', 'other') NOT NULL,
    reference_number VARCHAR(100),
    notes TEXT,
    status ENUM('pending', 'completed', 'cancelled') NOT NULL DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (pledge_id) REFERENCES donation_pledges(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Données initiales pour les catégories de transactions
INSERT INTO transaction_categories (name, type, description, created_by) VALUES
('Dîmes', 'income', 'Dîmes des membres', 1),
('Offrandes', 'income', 'Offrandes régulières', 1),
('Dons spéciaux', 'income', 'Dons pour des projets spécifiques', 1),
('Projets', 'income', 'Dons pour les projets de l''église', 1),
('Salaires', 'expense', 'Salaires du personnel', 1),
('Entretien', 'expense', 'Entretien des locaux', 1),
('Factures', 'expense', 'Eau, électricité, etc.', 1),
('Matériel', 'expense', 'Achats de matériel', 1),
('Événements', 'expense', 'Organisation d''événements', 1),
('Missions', 'expense', 'Soutien aux missions', 1),
('Aide sociale', 'expense', 'Aide aux membres dans le besoin', 1);
