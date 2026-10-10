# Compilar
cd contract
mxsc build --release

# Desplegar en devnet
mxpy contract deploy \
  --bytecode=build/service-registry.wasm \
  --pem=keys/deployer.pem \
  --proxy https://devnet-gateway.multiversx.com \
  --chain-id D \
  --gas-limit 50000000 \
  --send --wait-result



cd $HOME/projecte-blockchain/contract

# Build WASM bytecode
sc-meta build

# Deploy to Devnet
mxpy contract deploy \
  --bytecode=output/service-registry.wasm \
  --pem=$HOME/projecte-blockchain/keys/wallet.pem \
  --proxy=https://devnet-gateway.multiversx.com \
  --chain-id=D \
  --gas-limit=50000000 \
  --send --wait-result




------------------

sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx mariadb-server php-fpm php-mysql php-cli php-curl php-xml php-mbstring git curl rsync python3 python3-pip python3-venv

sudo systemctl enable --now nginx mariadb

# Install Rust for debian user
curl --proto '=https' --tlsv1.2 -sSf https://sh.rustup.rs | sh -s -- -y
source "$HOME/.cargo/env"
rustup target add wasm32-unknown-unknown
cargo install multiversx-sc-meta

# Install mxpy globally in /opt
sudo python3 -m venv /opt/mxpy-env
sudo /opt/mxpy-env/bin/pip install --upgrade pip multiversx-sdk-cli
sudo ln -sf /opt/mxpy-env/bin/mxpy /usr/local/bin/mxpy