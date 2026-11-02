# Guia de Desplegament del Projecte (`deploy.md`)

Aquesta guia detalla els passos necessaris per instal·lar i desplegar l'aplicació en un sistema Debian (darrera versió). Els alumnes utilitzaran el *smart contract* ja desplegat, per la qual cosa **no cal instal·lar Rust ni compilar el contracte**, però sí que es requereix la instal·lació de l'eina `mxpy`.

---

## 1. Requisits de l'Usuari
Per garantir la compatibilitat amb les rutes del projecte, creeu i utilitzeu l'usuari `debian` amb permisos de `sudo`:

```bash
# Si esteu com a usuari root:
useradd -m -s /bin/bash debian
usermod -aG sudo debian
passwd debian
su - debian
```

---

## 2. Actualització del Sistema i Dependències
Actualitzeu el sistema i instal·leu el servidor web (Nginx), el servidor de base de dades (MariaDB), el motor de PHP i les llibreries auxiliars:

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx mariadb-server php-fpm php-mysql php-cli php-curl php-xml php-mbstring git curl rsync python3 python3-pip python3-venv pkg-config libssl-dev build-essential
```

Habiliteu i inicieu els serveis de Nginx i MariaDB:

```bash
sudo systemctl enable --now nginx mariadb
```

---

## 3. Instal·lació Global de `mxpy` (MultiversX CLI)
Instal·leu l'eina `mxpy` en un entorn virtual a `/opt` i creeu un enllaç simbòlic per a un accés global:

```bash
sudo python3 -m venv /opt/mxpy-env
sudo /opt/mxpy-env/bin/pip install --upgrade pip
sudo /opt/mxpy-env/bin/pip install multiversx-sdk-cli
sudo ln -sf /opt/mxpy-env/bin/mxpy /usr/local/bin/mxpy
```

Comproveu que s'ha instal·lat correctament:

```bash
mxpy --version
```

---

## 4. Configuració de la Base de Dades MariaDB
Creeu la base de dades `projecte` i l'usuari corresponent amb els permisos necessaris:

```bash
sudo mariadb <<'EOF'
CREATE DATABASE IF NOT EXISTS projecte CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'projecte'@'localhost' IDENTIFIED BY 'projecte';
GRANT ALL PRIVILEGES ON projecte.* TO 'projecte'@'localhost';
FLUSH PRIVILEGES;
EOF
```

---

## 5. Clonar el Repositori
Cloneu el projecte directament al directori personal de l'usuari `debian`:

```bash
git clone https://github.com/FBalles/projecte-blockchain $HOME/projecte-blockchain
cd $HOME/projecte-blockchain
```

---

## 6. Preparació del Directori Web `/var/www/projecte`
Creeu la carpeta de destí i assigneu-ne la propietat a l'usuari `debian` i al grup `www-data`:

```bash
sudo mkdir -p /var/www/projecte
sudo chown -R debian:www-data /var/www/projecte
sudo chmod -R 775 /var/www/projecte
```

---

## 7. Importació de l'Esquema de la Base de Dades
Importeu el fitxer de migració inicial a MariaDB:

```bash
mariadb -u projecte -p'projecte' projecte < $HOME/projecte-blockchain/migrations/001_init.sql
```

---

## 8. Configuració del Fitxer `.env`
Copieu el fitxer d'exemple i modifiqueu les variables d'entorn si és necessari:

```bash
cp $HOME/projecte-blockchain/.env.example $HOME/projecte-blockchain/.env
nano $HOME/projecte-blockchain/.env
```

---

## 9. Execució del Script de Desplegament
Doneu permisos d'execució al script `deploy.sh` i executeu-lo per sincronitzar els fitxers a `/var/www/projecte`:

```bash
chmod u+x $HOME/projecte-blockchain/deploy.sh
$HOME/projecte-blockchain/deploy.sh
```

---

## 10. Configuració i Reinici de Nginx
Habiliteu el lloc web a la configuració de Nginx, desactiveu el lloc per defecte, comproveu la sintaxi i recarregueu el servei:

```bash
sudo cp $HOME/projecte-blockchain/nginx/projecte.conf /etc/nginx/sites-available/projecte
sudo ln -sf /etc/nginx/sites-available/projecte /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx
```


## Install dbeaver 