#!/usr/bin/env bash
set -e

# Configura la ruta de tu repositorio local y la de Nginx
REPO_DIR="$HOME/projecte-blockchain"
WEB_DIR="/var/www/projecte"

# 1. Si no existe la carpeta en tu home, la clona. Si existe, descarga los cambios.
if [ ! -d "$REPO_DIR" ]; then
  git clone https://github.com/FBalles/projecte-blockchain "$REPO_DIR"
else
  cd "$REPO_DIR"
  git pull origin main
fi

# 2. Copia todos los archivos a la carpeta de Nginx
sudo mkdir -p "$WEB_DIR"
sudo cp -r "$REPO_DIR"/* "$WEB_DIR"/

# 3. Asigna permisos a Nginx y recarga
sudo chown -R www-data:www-data "$WEB_DIR"
sudo nginx -t && sudo systemctl reload nginx

echo "Copiat a /var/www/projecte i Nginx actualitzat!"