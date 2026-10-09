# 1. Update system & install dependencies
sudo apt update && sudo apt install -y \
    curl build-essential git python3 python3-pip python3-venv \
    pkg-config libssl-dev clang llvm

# 2. Install Rust
curl --proto '=https' --tlsv1.2 -sSf https://sh.rustup.rs | sh -s -- -y
source "$HOME/.cargo/env"

# 3. Add WebAssembly target for Rust
rustup target add wasm32-unknown-unknown

# 4. Install MultiversX CLI (mxpy)
wget -O mxpy-up.py https://raw.githubusercontent.com/multiversx/mx-sdk-py-cli/main/mxpy-up.py
python3 mxpy-up.py
source ~/.profile   # or source ~/.bashrc