sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx mariadb-server php-fpm php-mysql php-cli php-curl php-xml php-mbstring git curl rsync python3 python3-pip python3-venv

# Enable web server and database services
sudo systemctl enable --now nginx mariadb


# 1. Install Rust for debian user
curl --proto '=https' --tlsv1.2 -sSf https://sh.rustup.rs | sh -s -- -y
source "$HOME/.cargo/env"
rustup target add wasm32-unknown-unknown
cargo install multiversx-sc-meta

# 2. Install mxpy globally in /opt
sudo python3 -m venv /opt/mxpy-env
sudo /opt/mxpy-env/bin/pip install --upgrade pip
sudo /opt/mxpy-env/bin/pip install multiversx-sdk-cli
sudo ln -sf /opt/mxpy-env/bin/mxpy /usr/local/bin/mxpy

# MARIADB
sudo mariadb <<'EOF'
CREATE DATABASE IF NOT EXISTS projecte CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'projecte'@'localhost' IDENTIFIED BY 'projecte';
GRANT ALL PRIVILEGES ON projecte.* TO 'projecte'@'localhost';
FLUSH PRIVILEGES;
EOF

# Clone in home
git clone https://github.com/FBalles/projecte-blockchain $HOME/projecte-blockchain
cd $HOME/projecte-blockchain

# Prepare /var/www web folder permissions
sudo mkdir -p /var/www/projecte
sudo chown -R debian:www-data /var/www/projecte
sudo chmod -R 775 /var/www/projecte


-----

# Import schema
mariadb -u projecte -p'projecte' projecte < $HOME/projecte-blockchain/migrations/001_init.sql

# Create .env
cp $HOME/projecte-blockchain/.env.example $HOME/projecte-blockchain/.env

nano .env


------

# Build Rust smart contract
cd $HOME/projecte-blockchain/contract
sc-meta build

# Create wallet
mkdir -p $HOME/projecte-blockchain/keys
mxpy wallet new --format pem --outfile $HOME/projecte-blockchain/keys/wallet.pem

# faucet

# Run deployment script to push contract builds and keys to /var/www/projecte
chmod u+x $HOME/projecte-blockchain/deploy.sh
$HOME/projecte-blockchain/deploy.sh


-----

# Enable site and disable default
sudo cp nginx/projecte.conf /etc/nginx/sites-available/projecte
sudo ln -sf /etc/nginx/sites-available/projecte /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx