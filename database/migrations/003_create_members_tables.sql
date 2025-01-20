-- Table des membres
CREATE TABLE IF NOT EXISTS members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    gender ENUM('M', 'F') NOT NULL,
    date_of_birth DATE,
    phone VARCHAR(20),
    email VARCHAR(255),
    address TEXT,
    city VARCHAR(100),
    profession VARCHAR(100),
    marital_status ENUM('single', 'married', 'divorced', 'widowed'),
    baptism_date DATE,
    membership_date DATE,
    status ENUM('active', 'inactive', 'visitor') DEFAULT 'active',
    notes TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_name (last_name, first_name),
    INDEX idx_status (status),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des familles
CREATE TABLE IF NOT EXISTS families (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    address TEXT,
    notes TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des relations familiales
CREATE TABLE IF NOT EXISTS family_relationships (
    id INT PRIMARY KEY AUTO_INCREMENT,
    family_id INT NOT NULL,
    member_id INT NOT NULL,
    relationship_type ENUM('head', 'spouse', 'child', 'other') NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (family_id) REFERENCES families(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    UNIQUE KEY unique_member_family (member_id, family_id),
    INDEX idx_family (family_id),
    INDEX idx_member (member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des visiteurs
CREATE TABLE IF NOT EXISTS visitors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    visit_date DATE NOT NULL,
    source VARCHAR(100),
    follow_up_status ENUM('pending', 'contacted', 'visited', 'joined', 'declined') DEFAULT 'pending',
    follow_up_notes TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    INDEX idx_visit_date (visit_date),
    INDEX idx_status (follow_up_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
