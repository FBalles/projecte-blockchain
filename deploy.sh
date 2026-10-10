#!/bin/bash
set -e

# Sync repository files to /var/www/projecte
rsync -av --delete \
  --exclude='.git*' \
  --exclude='contract/target' \
  --exclude='node_modules' \
  $HOME/projecte-blockchain/ /var/www/projecte/

# Ensure directory permissions for www-data
sudo chown -R debian:www-data /var/www/projecte
sudo chmod -R 775 /var/www/projecte

# Restrict wallet key read permissions to group www-data
#if [ -f /var/www/projecte/keys/wallet.pem ]; then
#    sudo chmod 640 /var/www/projecte/keys/wallet.pem
#fi

echo "Deployment to /var/www/projecte complete!"

#chmod +x $HOME/projecte-blockchain/deploy.sh