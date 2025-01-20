# Guide de déploiement - Church Gestion

## Prérequis
- PHP 8.0 ou supérieur
- MySQL 5.7 ou supérieur
- Composer
- Serveur web (Apache/Nginx)
- SSL/TLS pour HTTPS

## 1. Préparation du projet

### 1.1 Configuration de la production
1. Créez un fichier `.env.production` à partir du `.env` existant
2. Mettez à jour les variables d'environnement :
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://votre-domaine.com
   
   DB_HOST=localhost
   DB_NAME=church_gestion
   DB_USER=votre_user
   DB_PASS=votre_mot_de_passe_securise
   ```

### 1.2 Optimisation des dépendances
```bash
composer install --no-dev --optimize-autoloader
```

## 2. Configuration du serveur

### 2.1 Apache
Créez ou modifiez le fichier `.htaccess` à la racine du projet :
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Forcer HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Redirection vers public/
    RewriteRule ^$ public/ [L]
    RewriteRule (.*) public/$1 [L]
</IfModule>

# Protection des fichiers sensibles
<FilesMatch "^\.env|composer\.json|composer\.lock|package\.json|package-lock\.json|README\.md|DEPLOY\.md">
    Order allow,deny
    Deny from all
</FilesMatch>

# Compression Gzip
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/x-javascript application/json
</IfModule>

# Cache Control
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### 2.2 Nginx
Configuration recommandée pour Nginx (`/etc/nginx/sites-available/church-gestion.conf`) :
```nginx
server {
    listen 80;
    server_name votre-domaine.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name votre-domaine.com;

    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    root /path/to/church-gestion/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~* \.(jpg|jpeg|gif|png|svg|css|js|ico)$ {
        expires 1y;
        add_header Cache-Control "public, no-transform";
    }

    location ~ /\.env {
        deny all;
    }

    location ~ /\. {
        deny all;
    }
}
```

## 3. Base de données

### 3.1 Migration de la base de données
1. Exportez la base de données locale :
```bash
mysqldump -u root -p church_gestion > church_gestion_backup.sql
```

2. Importez sur le serveur de production :
```bash
mysql -u user -p church_gestion < church_gestion_backup.sql
```

### 3.2 Sécurité de la base de données
1. Créez un utilisateur dédié avec privilèges minimaux
2. Activez les sauvegardes automatiques
3. Configurez le pare-feu MySQL

## 4. Sécurité

### 4.1 Permissions des fichiers
```bash
# Définir les bonnes permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 777 storage/logs
chmod -R 777 storage/cache
```

### 4.2 Configuration SSL/TLS
1. Installez un certificat SSL (Let's Encrypt recommandé)
2. Configurez HSTS
3. Activez HTTP/2

### 4.3 Protection supplémentaire
1. Installez et configurez un pare-feu (UFW recommandé)
2. Activez fail2ban pour la protection contre les attaques par force brute
3. Configurez les en-têtes de sécurité HTTP

## 5. Maintenance

### 5.1 Mises à jour
```bash
# Mettre à jour les dépendances
composer update --no-dev --optimize-autoloader

# Vider le cache
php cli/cache.php clear
```

### 5.2 Surveillance
1. Configurez la surveillance des logs
2. Mettez en place des alertes pour les erreurs critiques
3. Surveillez l'utilisation des ressources

### 5.3 Sauvegardes
1. Configurez des sauvegardes quotidiennes de la base de données
2. Mettez en place des sauvegardes des fichiers
3. Testez régulièrement la restauration

## 6. Post-déploiement

### 6.1 Tests
1. Vérifiez toutes les fonctionnalités principales
2. Testez les formulaires et les uploads
3. Vérifiez la compatibilité mobile
4. Testez les performances avec GTmetrix ou Lighthouse

### 6.2 Monitoring
1. Mettez en place Google Analytics
2. Configurez les logs d'erreurs
3. Surveillez les performances serveur

## 7. Hébergeurs recommandés
- OVH
- DigitalOcean
- AWS
- Google Cloud Platform
- Scaleway

## Support
Pour toute assistance technique, contactez :
- Email : support@votre-domaine.com
- Documentation : https://votre-domaine.com/docs
