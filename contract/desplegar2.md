sudo apt update && sudo apt install -y pipx python3-pip
pipx ensurepath

. "$HOME/.cargo/env"
rustup target add wasm32-unknown-unknown