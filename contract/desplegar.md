cd $HOME/projecte-blockchain/contract

# Build WASM bytecode
sc-meta all build

# Deploy contract
mxpy contract deploy \
  --bytecode=output/service-registry.wasm \
  --pem=../keys/teacher-wallet.pem \
  --proxy=https://devnet-gateway.multiversx.com \
  --gas-limit=50000000 \
  --send --wait-result