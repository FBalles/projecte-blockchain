# Despliegue — Catálogo de Servicios

## Requisitos previos
- VPS con Ubuntu 24.04 LTS
- Dominio apuntando al VPS (o IP para pruebas)
- Acceso SSH como usuario con sudo

---

```bash
## Paso 1: Preparar el sistema
ssh tu_usuario@tu_vps
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx php-fpm php-mysql php-cli mysql-server git python3-pip
pip3 install mxpy
sudo ufw allow 'Nginx Full'
sudo ufw allow OpenSSH
sudo ufw enable

# Paso 2: Crear base de datos
sudo mysql <<'EOF'
CREATE DATABASE IF NOT EXISTS catalogo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'catalogo'@'localhost' IDENTIFIED BY 'PON_TU_PASSWORD_AQUI';
GRANT ALL ON catalogo.* TO 'catalogo'@'localhost';
FLUSH PRIVILEGES;
EOF

#Paso 3: Clonar el código
sudo git clone https://github.com/TU_USUARIO/catalogo-servicios.git /var/www/catalogo
cd /var/www/catalogo
Paso 4: Importar esquema
sudo mysql -u catalogo -p catalogo < migrations/001_init.sql
Paso 5: Configurar .env
cp .env.example .env
sudo nano .env
Rellenar:
    • DB_PASS → la contraseña que pusiste en el paso 2
    • MX_CONTRACT_ADDRESS → dirección del smart contract (te la da el profesor)

## Paso 6: Crear wallet y obtener xEGLD
mkdir -p keys
mxpy wallet new keys/wallet.pem
Obtener xEGLD en devnet:
    1. Ir a https://devnet-wallet.multiversx.com/unlock
    2. Importar la wallet (pega el contenido del .pem)
    3. Ir a "Faucet" → "Request Tokens" → 5 xEGLD
    4. Alternativa: https://r3d4.fr/faucet
## Verificar:
mxpy wallet address --pem keys/wallet.pem

## Paso 7: Configurar Nginx
sudo cp nginx/catalogo.conf /etc/nginx/sites-available/catalogo
sudo ln -s /etc/nginx/sites-available/catalogo /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
Si no tienes dominio, cambia server_name por la IP del VPS.

## Paso 8: Permisos
sudo chown -R www-data:www-data /var/www/catalogo
sudo chmod 600 /var/www/catalogo/keys/wallet.pem

## Paso 9: HTTPS (si tienes dominio)
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d catalogo.tudominio.com

## Paso 10: Verificar
    1. Abrir http://tu_vps (o https://catalogo.tudominio.com)
    2. Login admin: admin@instituto.es / admin123
    3. Registrar una empresa de prueba
    4. Crear un servicio como admin
    5. Solicitarlo como empresa
    6. Aceptar como admin → verificar en https://devnet-explorer.multiversx.com