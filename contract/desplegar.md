cd $HOME/projecte-blockchain/contract

# Build WASM bytecode
sc-meta all build

# Deploy contract
mxpy contract deploy \
  --bytecode=output/service-registry.wasm \
  --pem=path/to/teacher-wallet.pem \
  --proxy=https://devnet-gateway.multiversx.com \
  --chain-id=D \
  --gas-limit=50000000 \
  --send --wait-result