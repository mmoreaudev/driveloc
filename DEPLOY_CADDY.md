# Deploiement DriveLoc avec Caddy + PHP-FPM (sans Docker)

Ce guide configure l'application dans `/var/www/driveloc`.

## 1) Prerequis

- Ubuntu/Debian avec sudo
- Caddy installe
- PHP 8.2 FPM + extensions: pdo, pdo_mysql
- MySQL/MariaDB accessible

## 2) Copier l'application

```bash
sudo mkdir -p /var/www/driveloc
sudo rsync -av --delete ./ /var/www/driveloc/
sudo chown -R www-data:www-data /var/www/driveloc
```

## 3) Variables d'environnement

```bash
cd /var/www/driveloc
cp .env.example .env
```

Exemple minimal de variables a definir dans l'environnement du service (systemd ou shell):

```bash
APP_ENV=production
APP_URL=https://votre-domaine
AUTO_INSTALL_DB=false
DB_HOST=localhost
DB_PORT=3306
DB_NAME=driveloc
DB_USER=driveloc
DB_PASS=mot_de_passe_fort
```

## 4) Configurer Caddy

1. Ouvrir le fichier Caddy global:

```bash
sudo nano /etc/caddy/Caddyfile
```

2. Coller le contenu de `Caddyfile` du projet et remplacer:
- `driveloc.example.com` par votre domaine
- `php8.2-fpm.sock` par votre socket PHP-FPM si necessaire

3. Verifier et recharger:

```bash
sudo caddy fmt --overwrite /etc/caddy/Caddyfile
sudo caddy validate --config /etc/caddy/Caddyfile
sudo systemctl reload caddy
```

## 5) PHP-FPM

Verifier que PHP-FPM tourne:

```bash
sudo systemctl status php8.2-fpm
```

Si votre distribution utilise TCP au lieu d'un socket Unix, adaptez la ligne Caddy:

```caddy
php_fastcgi 127.0.0.1:9000
```

## 6) Permissions et securite

```bash
sudo mkdir -p /var/www/driveloc/uploads/vehicles
sudo chown -R www-data:www-data /var/www/driveloc/uploads
sudo find /var/www/driveloc -type d -exec chmod 755 {} \;
sudo find /var/www/driveloc -type f -exec chmod 644 {} \;
```

## 7) Verification rapide

- `https://votre-domaine/health` doit renvoyer `ok`
- `https://votre-domaine/register` doit charger sans erreur CSRF
- Les images externes (URL https) doivent s'afficher

## Notes

- Le routage est gere par `index.php` (front controller), pas besoin de `.htaccess` avec Caddy.
- Les regles Caddy bloquent:
  - l'acces aux fichiers sensibles (`.sql`, `.log`, `.md`, `.env`, `.git`)
  - l'execution de scripts dans `/uploads`
