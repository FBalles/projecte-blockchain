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



# 1. Load Rust environment
. "$HOME/.cargo/env"

# 2. Build the contract
cd contract
mxpy contract build

# 3. Deploy to devnet
mxpy contract deploy \
  --bytecode=output/service-registry.wasm \
  --pem=../keys/wallet.pem \
  --proxy=https://devnet-gateway.multiversx.com \
  --chain-id=D \
  --gas-limit=60000000 \
  --send --wait-result