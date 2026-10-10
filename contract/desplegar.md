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