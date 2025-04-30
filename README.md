
# PlayZeta 🎮

**PlayZeta** es una plataforma web de juegos online casuales y competitivos, desarrollada con Laravel y Tailwind CSS.

---

## 🔧 Requisitos para arrancar el proyecto

Antes de empezar, asegúrate de tener instalado en tu equipo:

- PHP 8.x
- Composer
- Node.js y npm
- XAMPP (o equivalente, para MySQL)
- Git

---

## 🚀 Pasos para clonar y arrancar el proyecto

1. Clona el repositorio:

```bash
git clone https://github.com/TUNOMBRE/playzeta.git
cd playzeta
```

2. Instala las dependencias de PHP:

```bash
composer install
```

3. Copia el archivo `.env.example` a `.env`:

```bash
cp .env.example .env
```

4. Crea una base de datos en `phpMyAdmin` llamada `playzeta`, y luego abre `.env` para configurar estos datos:

```
DB_DATABASE=playzeta
DB_USERNAME=root
DB_PASSWORD=
```

> Usa tu contraseña de MySQL si tienes una.

5. Ejecuta las migraciones:

```bash
php artisan migrate
```

6. Instala dependencias de Node (Tailwind y Vite):

```bash
npm install
npm run dev
```

7. Levanta el servidor:

```bash
php artisan serve
```

Abre el navegador en [http://127.0.0.1:8000](http://127.0.0.1:8000) y verás PlayZeta funcionando.

---

## 🧠 Notas importantes

- El archivo `.env` no se sube al repositorio por seguridad. Cada miembro del equipo debe crearlo.
- Los directorios `vendor/` y `node_modules/` están ignorados por Git. Usa los comandos anteriores para regenerarlos.
- Usa ramas separadas para trabajar en equipo: `git checkout -b nombre-de-tu-rama`

---

## 🧑‍💻 Stack utilizado

- Laravel 10.x
- Blade + Tailwind CSS
- PHP 8.x + Composer
- Vite + npm
- MySQL

# Script para automatizar la preparación de servidor(hay algún problema con la base de datos)

```bash
#!/bin/bash

# Script automático para instalar PlayZeta desde cero en un servidor limpio

set -e  # Salir si algo falla

# Variables
REPO_URL="https://github.com/Hugoop1811/playzeta"
DB_NAME="playzeta"
DB_USER="root"
DB_PASS=""
APP_PORT=8000

echo "🔄 Actualizando paquetes del sistema..."
sudo apt update -y
sudo apt upgrade -y

echo "📦 Instalando PHP 8.2 y extensiones necesarias..."
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update -y
sudo apt install -y php8.2 php8.2-cli php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-curl php8.2-zip php8.2-mysql unzip curl git mysql-server software-properties-common

# Establecer PHP 8.2 como predeterminado
sudo update-alternatives --set php /usr/bin/php8.2

echo "📦 Instalando Node.js 20.x..."
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

echo "🔧 Instalando Composer de forma segura..."
cd /tmp
curl -sS https://getcomposer.org/installer -o composer-setup.php
HASH=$(curl -sS https://composer.github.io/installer.sig)
php -r "if (hash_file('sha384', 'composer-setup.php') === '$HASH') { echo '✔️ Installer verificado'; } else { echo '❌ Installer corrupto'; unlink('composer-setup.php'); exit(1); }"
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php
cd -

echo "⚙️ Configurando MySQL..."
# Asegurar que el servicio esté corriendo
sudo systemctl start mysql
sudo systemctl enable mysql

# Crear base de datos
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "🚀 Clonando PlayZeta..."
git clone "$REPO_URL"
cd playzeta

echo "📦 Instalando dependencias PHP (Laravel)..."
composer install --no-interaction --prefer-dist

echo "📝 Configurando archivo .env..."
cp .env.example .env

# Configurar conexión DB en el .env
sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env

echo "🔑 Generando clave de aplicación Laravel..."
php artisan key:generate

echo "🛢️ Ejecutando migraciones..."
php artisan migrate --force

echo "🎨 Instalando dependencias Node.js..."
npm install

echo "⚡ Compilando assets frontend (vite)..."
npm run build

echo "🌐 Levantando servidor Laravel en puerto $APP_PORT..."
php artisan serve --host=0.0.0.0 --port=$APP_PORT &

IP_ADDRESS=$(hostname -I | awk '{print $1}')
echo "✅ ¡PlayZeta instalado correctamente!"
echo "👉 Accede a http://$IP_ADDRESS:$APP_PORT desde tu navegador."
```
