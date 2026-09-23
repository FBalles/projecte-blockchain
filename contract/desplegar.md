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