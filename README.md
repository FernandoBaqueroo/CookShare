# CookShare - Guía de Inicio Rápido

## Requisitos Previos

- **Docker** ([Linux](https://docs.docker.com/engine/install/), [Windows](https://docs.docker.com/desktop/install/windows-install/))
- **Git**
- **WSL2** (solo Windows, recomendado)
- **PHP 8.1+** (solo si usas Sail desde Composer, no necesario si usas vendor/bin/sail)
- **Node.js** y **npm** (opcional, para assets front-end)

---

## 1. Clonar el Proyecto

```bash
git clone <URL_DEL_REPOSITORIO>
cd cookshare
```

---

## 2. Copiar y Configurar el archivo `.env`

```bash
cp .env.example .env
```

Edita el archivo `.env` y revisa estas variables:

```
APP_URL=http://localhost
DB_HOST=mysql
DB_DATABASE=cookshare
DB_USERNAME=sail
DB_PASSWORD=password
APP_IMAGES_PATH=/var/www/html/storage/images   # Ruta interna en Sail
APP_IMAGES_LOCAL_PATH=storage/images           # Ruta local para mapeo
```

---

## 3. Instalar dependencias de Composer

**Linux/WSL2:**
```bash
./vendor/bin/sail composer install
```
**Windows (Docker Desktop, sin WSL):**
```powershell
vendor\bin\sail.bat composer install
```

---

## 4. Instalar dependencias de NPM (opcional)

```bash
npm install
npm run build
```

---

## 5. Levantar los contenedores con Sail

**Linux/WSL2:**
```bash
./vendor/bin/sail up -d
```
**Windows (Docker Desktop, sin WSL):**
```powershell
vendor\bin\sail.bat up -d
```

---

## 6. Crear las carpetas necesarias para imágenes

**Dentro del contenedor Sail:**
```bash
./vendor/bin/sail shell
mkdir -p storage/images/posts
mkdir -p storage/images/profiles
exit
```

**En tu máquina local (Linux):**
```bash
mkdir -p storage/images/posts
mkdir -p storage/images/profiles
```
**En tu máquina local (Windows):**
```powershell
mkdir storage\images\posts
mkdir storage\images\profiles
```

---

## 7. Asignar permisos a las carpetas de imágenes

**Linux (local y dentro de Sail):**
```bash
chmod -R 775 storage/images
chown -R $USER:www-data storage/images
```
**Windows:**  
No es necesario cambiar permisos, pero asegúrate de que Docker Desktop tenga acceso a la carpeta del proyecto.

---

## 8. Mapeo de carpetas entre Sail y local

Asegúrate de que en el archivo `docker-compose.yml` esté el volumen mapeado así:

```yaml
services:
  laravel.test:
    volumes:
      - ./storage/images:/var/www/html/storage/images
```

Esto asegura que las imágenes subidas desde la app (dentro de Sail) se guarden también en tu máquina local.

---

## 9. Generar la clave de la aplicación y migrar la base de datos

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
```

---

## 10. Acceder a la aplicación

- **Backend API:**  [http://localhost/api](http://localhost/api)
- **Frontend (si tienes):**  [http://localhost](http://localhost)

---

## 11. Comandos útiles

- **Ver logs de Laravel:**
  ```bash
  ./vendor/bin/sail artisan tail
  ```
- **Entrar al contenedor:**
  ```bash
  ./vendor/bin/sail shell
  ```
- **Parar los contenedores:**
  ```bash
  ./vendor/bin/sail down
  ```

---

## 12. Notas importantes

- Si usas **Windows** y tienes problemas de permisos, asegúrate de que Docker Desktop tenga acceso a la carpeta del proyecto (Settings > Resources > File Sharing).
- Si cambias la ruta de imágenes, actualiza tanto en `.env` como en `docker-compose.yml`.
- Si usas WSL2, ejecuta todos los comandos desde la terminal de Ubuntu (no desde CMD o PowerShell).
- Si tienes problemas con los assets front-end, ejecuta `npm run build` dentro del contenedor o en local.

---

## 13. Resumen de comandos rápidos

### Linux/WSL2
```bash
git clone <URL_DEL_REPOSITORIO>
cd cookshare
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail composer install
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
mkdir -p storage/images/posts storage/images/profiles
chmod -R 775 storage/images
```

### Windows (Docker Desktop sin WSL)
```powershell
git clone <URL_DEL_REPOSITORIO>
cd cookshare
copy .env.example .env
vendor\bin\sail.bat up -d
vendor\bin\sail.bat composer install
vendor\bin\sail.bat artisan key:generate
vendor\bin\sail.bat artisan migrate --seed
mkdir storage\images\posts
mkdir storage\images\profiles
```

---

## 14. Documentación de la API

Consulta el archivo `API_DOCUMENTATION.md` y `POSTMAN_EXAMPLES.md` para ver todos los endpoints y ejemplos de uso.

---

¡Listo! Tu profesor podrá iniciar el proyecto fácilmente en Linux o Windows siguiendo estos pasos. 