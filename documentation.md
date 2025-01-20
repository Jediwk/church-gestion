# Système de Gestion d'Église
## Documentation Complète

### 1. Vue d'ensemble
Le Système de Gestion d'Église est une application web complète conçue pour aider les églises à gérer efficacement leurs membres, finances, et activités pastorales.

### 2. Fonctionnalités Principales

#### 2.1 Gestion des Membres
- **Membres individuels**
  - Informations personnelles (nom, prénom, genre, date de naissance)
  - Coordonnées (téléphone, email, adresse)
  - Statut spirituel (date de baptême)
  - Informations professionnelles
  - Statut matrimonial
  - Notes personnelles

- **Gestion des Familles**
  - Regroupement des membres par famille
  - Informations de contact familiales
  - Historique d'adhésion
  - Vue d'ensemble des membres de la famille

#### 2.2 Gestion Financière
- **Transactions**
  - Entrées (dîmes, offrandes, dons)
  - Sorties (dépenses, salaires, entretien)
  - Suivi par catégorie et type
  - Références et descriptions détaillées

- **Types de Finances**
  - Configuration personnalisable des types d'entrées/sorties
  - Catégorisation (Entrée/Sortie)
  - Statistiques par type

- **Rapports Financiers**
  - Vue d'ensemble des finances
  - Statistiques annuelles
  - Répartition par type
  - Solde et tendances

#### 2.3 Gestion Pastorale
- Suivi des activités pastorales
- Gestion des visites
- Notes et observations
- Suivi spirituel des membres

#### 2.4 Dons et Engagements
- **Gestion des Dons**
  - Enregistrement des dons
  - Suivi des donateurs
  - Historique des contributions

- **Gestion des Engagements**
  - Promesses de dons
  - Suivi des réalisations
  - Rappels et notifications

#### 2.5 Administration
- **Gestion des Utilisateurs**
  - Création et gestion des comptes
  - Niveaux d'accès
  - Sécurité et permissions

- **Import/Export**
  - Importation de données (CSV, Excel)
  - Exportation de rapports
  - Sauvegarde des données

### 3. Types d'Utilisateurs et Rôles

#### 3.1 Administrateur
- Accès complet au système
- Gestion des utilisateurs
- Configuration du système
- Accès aux rapports avancés

#### 3.2 Gestionnaire Financier
- Gestion des transactions financières
- Création de rapports financiers
- Suivi des dons et engagements

#### 3.3 Gestionnaire des Membres
- Gestion des informations des membres
- Mise à jour des données familiales
- Suivi des présences

#### 3.4 Pasteur/Leader Spirituel
- Accès aux informations pastorales
- Suivi spirituel des membres
- Notes et observations pastorales

### 4. Architecture Technique

#### 4.1 Technologies Utilisées
- Backend : PHP 8.x
- Base de données : SQLite
- Frontend : HTML5, CSS3, JavaScript
- Framework de templating : League/Plates
- Gestion des dépendances : Composer

#### 4.2 Structure du Projet
```
church-gestion/
├── src/
│   ├── Controllers/    # Contrôleurs de l'application
│   ├── Core/           # Classes fondamentales
│   ├── Models/         # Modèles de données
│   └── Services/       # Services métier
├── templates/          # Vues et layouts
├── public/            # Point d'entrée et assets
├── config/           # Configuration
├── database/         # Scripts de base de données
└── tests/           # Tests unitaires
```

### 5. Sécurité

#### 5.1 Authentification
- Système de connexion sécurisé
- Gestion des sessions
- Protection contre les attaques courantes

#### 5.2 Autorisation
- Contrôle d'accès basé sur les rôles
- Validation des permissions
- Journalisation des actions

#### 5.3 Protection des Données
- Chiffrement des données sensibles
- Sauvegarde automatique
- Validation des entrées

### 6. Installation et Configuration

#### 6.1 Prérequis
- PHP 8.0 ou supérieur
- SQLite 3
- Composer
- Serveur web (Apache/Nginx)

#### 6.2 Installation
1. Cloner le dépôt
2. Installer les dépendances via Composer
3. Configurer le fichier .env
4. Initialiser la base de données
5. Configurer le serveur web

### 7. Maintenance et Support

#### 7.1 Mises à jour
- Mises à jour de sécurité
- Nouvelles fonctionnalités
- Corrections de bugs

#### 7.2 Sauvegarde
- Sauvegarde automatique des données
- Export régulier des données
- Procédures de restauration

### 8. Roadmap et Évolutions Futures

#### 8.1 Fonctionnalités Prévues
- Application mobile
- Module de communication (SMS/Email)
- Gestion des événements
- Système de reporting avancé

#### 8.2 Améliorations Techniques
- Migration vers une base de données plus robuste
- API REST complète
- Interface administrative améliorée
- Optimisation des performances

### 9. Support et Contact

Pour toute assistance ou information supplémentaire :
- Documentation technique : `/docs`
- Support : support@church-gestion.com
- Contributions : Voir CONTRIBUTING.md

---

### Notes Importantes
1. Sauvegarder régulièrement les données
2. Maintenir les mots de passe sécurisés
3. Former les utilisateurs aux bonnes pratiques
4. Respecter les règles de confidentialité des données

### Licence
Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus de détails.
