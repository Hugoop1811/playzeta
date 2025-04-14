
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
