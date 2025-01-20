# Système de Gestion d'Église

Une application web complète pour la gestion d'une église, développée avec PHP et SQLite.

## Fonctionnalités

- 👥 Gestion des membres
- 💰 Gestion des finances (dîmes, offrandes, dépenses)
- 👨‍👩‍👧‍👦 Gestion des familles
- 👤 Gestion des utilisateurs et des rôles
- 📊 Rapports financiers
- 🔒 Système d'authentification sécurisé

## Prérequis

- PHP 8.0 ou supérieur
- SQLite3
- Composer
- Extension PHP PDO SQLite

## Installation

1. Clonez le dépôt :
```bash
git clone https://github.com/votre-username/church-gestion.git
cd church-gestion
```

2. Installez les dépendances :
```bash
composer install
```

3. Créez la base de données :
```bash
php database/reset_db.php
```

4. Démarrez le serveur de développement :
```bash
php -S localhost:8000 -t public
```

5. Accédez à l'application dans votre navigateur :
```
http://localhost:8000
```

## Configuration

1. Copiez le fichier d'exemple de configuration :
```bash
cp config/app.example.php config/app.php
```

2. Modifiez les paramètres dans `config/app.php` selon vos besoins.

## Compte par défaut

- Email : admin@example.com
- Mot de passe : Admin@123

## Structure du projet

```
church-gestion/
├── config/             # Configuration de l'application
├── database/          # Scripts de base de données
├── public/            # Point d'entrée public
├── src/               # Code source
│   ├── Controllers/   # Contrôleurs
│   ├── Core/          # Classes principales
│   └── Models/        # Modèles
├── templates/         # Templates Plates
└── vendor/           # Dépendances
```

## Sécurité

- Toutes les requêtes SQL utilisent des requêtes préparées
- Les mots de passe sont hachés avec Bcrypt
- Protection CSRF sur les formulaires
- Validation des entrées utilisateur
- Gestion des sessions sécurisée

## Contribution

1. Fork le projet
2. Créez votre branche de fonctionnalité (`git checkout -b feature/AmazingFeature`)
3. Committez vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

## License

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## Support

Si vous rencontrez des problèmes ou avez des questions, n'hésitez pas à :
- Ouvrir une issue sur GitHub
- Envoyer un email à [votre-email@example.com]
