# Document Technique - Solution de Gestion d'Église

## Table des Matières
1. Introduction
2. Spécifications Techniques
3. Architecture Système
4. Structure de Base de Données
5. Modules et Fonctionnalités
6. Sécurité
7. Interfaces Utilisateur
8. Plan de Développement
9. Tests et Déploiement

## 1. Introduction

### 1.1 Objectif
Développement d'une solution de gestion d'église axée sur la gestion des membres et des finances, avec accès différencié selon les rôles utilisateurs.

### 1.2 Périmètre
- Gestion des membres et visiteurs
- Gestion financière
- Tableaux de bord adaptés aux rôles
- Interface administrative

## 2. Spécifications Techniques

### 2.1 Technologies Required
- Backend: PHP 8.1+
- Frontend: HTML5, CSS3, JavaScript
- Framework CSS: Bootstrap 5.x
- Base de données: MySQL 8.0+
- Serveur: Apache 2.4+

### 2.2 Contraintes Techniques
- PHP natif (sans framework)
- Responsive design obligatoire
- Compatibilité navigateurs: 
  * Chrome (dernières versions)
  * Firefox (dernières versions)
  * Safari (dernières versions)
  * Edge (dernières versions)
- Temps de réponse < 2 secondes
- Support minimum résolution: 1024x768

### 2.3 Configurations Requises
- Serveur:
  * Minimum 4GB RAM
  * 50GB espace disque
  * Processeur dual-core
- Client:
  * Navigateur moderne
  * JavaScript activé
  * Cookies activés

## 3. Architecture Système

### 3.1 Structure MVC
```
church_management/
├── config/
│   ├── database.php      # Configuration BD
│   ├── config.php        # Configuration générale
│   └── constants.php     # Constantes système
├── public/
│   ├── index.php         # Point d'entrée
│   └── assets/           # Ressources statiques
├── src/
│   ├── Controllers/      # Contrôleurs
│   ├── Models/           # Modèles
│   ├── Services/         # Services métier
│   └── Helpers/          # Fonctions utilitaires
├── templates/            # Vues
└── logs/                 # Logs système
```

### 3.2 Composants Système
- Router personnalisé
- Gestionnaire de sessions
- Système de templates
- Gestionnaire d'erreurs
- Logger système

## 4. Structure de Base de Données

### 4.1 Schéma de Base
```sql
-- Configuration du jeu de caractères
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 1;

-- Users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin','pastor','treasurer','secretary') NOT NULL,
    status TINYINT DEFAULT 1,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Members
CREATE TABLE members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    join_date DATE NOT NULL,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_name (last_name, first_name),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Family Links
CREATE TABLE family_links (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    related_member_id INT NOT NULL,
    relationship ENUM('spouse','child','sibling') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id),
    FOREIGN KEY (related_member_id) REFERENCES members(id),
    UNIQUE KEY unique_relationship (member_id, related_member_id, relationship)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Visitors
CREATE TABLE visitors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    origin_church VARCHAR(100),
    visit_date DATE NOT NULL,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transactions
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_date DATE NOT NULL,
    type ENUM('dime','offering','thanksgiving','expense','donation') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_type (type),
    INDEX idx_date (transaction_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 5. Modules et Fonctionnalités

### 5.1 Module d'Authentification
- Login sécurisé
- Gestion des sessions
- Récupération mot de passe
- Timeout automatique (30 minutes)

### 5.2 Module Membres
- CRUD complet
- Import/Export (Excel, CSV)
- Gestion visiteurs
- Structure familiale
- Recherche avancée

### 5.3 Module Finances
- Saisie transactions
- Rapports financiers
- Export données
- Tableau de bord

### 5.4 Dashboard Pastoral
- Vue globale
- Statistiques
- Rapports consolidés

## 6. Sécurité

### 6.1 Authentification
- Hash des mots de passe (Argon2id)
- Protection contre bruteforce
- Validation des formulaires
- Token CSRF

### 6.2 Autorisations
```php
// Exemple de matrice des droits
const PERMISSIONS = [
    'super_admin' => ['*'],
    'pastor' => ['read_all', 'export_all'],
    'treasurer' => ['manage_finances', 'read_finances', 'export_finances'],
    'secretary' => ['manage_members', 'read_members', 'export_members']
];
```

## 7. Interfaces Utilisateur

### 7.1 Templates Bootstrap
- Layout responsive
- Composants réutilisables
- Thème personnalisé

### 7.2 JavaScript
- Validation côté client
- Datables pour les listes
- Charts pour les statistiques

## 8. Plan de Développement

### 8.1 Phase 1 : Core (1 semaine)
- Setup environnement
- Structure MVC
- Authentification

### 8.2 Phase 2 : Membres (2 semaines)
- CRUD Membres
- Gestion famille
- Import/Export

### 8.3 Phase 3 : Finances (2 semaines)
- Transactions
- Rapports
- Dashboard

## 9. Tests et Déploiement

### 9.1 Tests
- Tests unitaires (PHPUnit)
- Tests d'intégration
- Tests utilisateurs

### 9.2 Déploiement
- Environnement de production
- Migration données
- Formation utilisateurs

### 9.3 Performances
- Optimisation requêtes
- Cache système
- Monitoring