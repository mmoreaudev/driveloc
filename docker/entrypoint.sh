#!/bin/sh
# Entrypoint Render/Railway/Docker
# Les plateformes PaaS injectent un $PORT dynamique (≠ 80). Ce script l'applique à Apache
# avant de démarrer le serveur.
# En local Docker Compose, PORT n'est pas défini → fallback 80.

PORT=${PORT:-80}

# Remplace le port d'écoute dans la config Apache
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/*:80>/*:${PORT}>/g"         /etc/apache2/sites-available/000-default.conf

exec apache2-foreground
