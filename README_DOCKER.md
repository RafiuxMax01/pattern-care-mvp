# 🐳 Pattern Care ERP - Despliegue con Docker

Este archivo explica cómo levantar el sistema **Pattern Care Business Central ERP** en Docker, compatible con **Windows** y **macOS**.

---

## 📋 Requisitos previos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado y en ejecución
- Docker Compose (incluido en Docker Desktop)

---

## 📁 Estructura de archivos para Docker

```
Pattern Care/
├── docker-compose.yml      # Orquestación de contenedores
├── Dockerfile              # Imagen PHP + Apache
├── php.ini                 # Configuración PHP
├── .env                    # Variables de entorno
├── .env.example            # Plantilla de variables
├── index.php               # Interfaz principal del ERP
├── conexion.php            # Conexión PDO (lee variables de entorno)
├── database_master.sql     # Script SQL de inicialización (se ejecuta automáticamente)
├── backend/                # Endpoints PHP (AJAX / Fetch)
│   ├── obtener_datos.php
│   ├── guardar_inventario.php
│   ├── gestionar_productos.php
│   ├── registrar_produccion.php
│   ├── control_mermas.php
│   ├── calcular_rendimiento.php
│   ├── transferir_stock.php
│   └── instalar_bd.php
└── README_DOCKER.md        # Este archivo
```

---

## 🚀 Inicio rápido (Windows y macOS)

### 1. Clonar / abrir la carpeta del proyecto

Asegúrate de estar dentro del directorio `Pattern Care` donde se encuentra `docker-compose.yml`.

### 2. Levantar los contenedores

Desde una terminal (PowerShell en Windows, Terminal en macOS):

```bash
docker compose up -d --build
```

Esto creará e iniciará tres contenedores:
- **pattern_care_web** → Servidor web PHP + Apache en `http://localhost:8000`
- **pattern_care_db**  → MySQL 8.0 en `localhost:3307`
- **pattern_care_pma** → phpMyAdmin en `http://localhost:8080`

### 3. Verificar que todo esté corriendo

```bash
docker compose ps
```

Deberías ver los tres contenedores con estado `Up` y `healthy`.

### 4. Acceder a la aplicación

| Servicio | URL | Usuario | Contraseña |
|----------|-----|---------|------------|
| **ERP Pattern Care** | `http://localhost:8000` | — | — |
| **phpMyAdmin** | `http://localhost:8080` | `pattern_user` | `pattern_pass` |
| **MySQL (directo)** | `localhost:3307` | `root` | `rootpassword` |

La base de datos `pattern_care` se crea automáticamente al iniciar el contenedor MySQL, y el script `database_master.sql` se ejecuta para crear tablas y datos semilla.

---

## 🛠️ Comandos útiles

```bash
# Ver logs en tiempo real
docker compose logs -f

# Detener contenedores (sin borrar datos)
docker compose stop

# Detener y eliminar contenedores (mantiene el volumen de datos de MySQL)
docker compose down

# Detener y eliminar todo, incluido el volumen de datos de MySQL ⚠️
docker compose down -v

# Reconstruir y levantar (después de modificar código PHP)
docker compose up -d --build

# Acceder al contenedor web (línea de comandos)
docker exec -it pattern_care_web bash

# Acceder al contenedor de MySQL
docker exec -it pattern_care_db mysql -u pattern_user -p pattern_care
```

---

## 🔧 Configuración de conexión (conexion.php)

El archivo `conexion.php` ya está preparado para leer las credenciales desde **variables de entorno** con fallback a valores por defecto:

```php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'pattern_care');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
```

En Docker, `docker-compose.yml` inyecta automáticamente:
- `DB_HOST = db` (nombre del servicio MySQL)
- `DB_PORT = 3306`
- `DB_NAME = pattern_care`
- `DB_USER = pattern_user`
- `DB_PASS = pattern_pass`

Si ejecutas el proyecto fuera de Docker (IIS, XAMPP, Apache local), usa los valores por defecto (`localhost`, `root`, sin contraseña) o define las variables de entorno en tu sistema.

---

## 📦 Persistencia de datos

- Los datos de MySQL se almacenan en el volumen Docker llamado `pattern_care_db_data`.
- Las subidas de imágenes (estampados de gorros/chamarras) se guardan en `./uploads/` (montado como volumen).
- Si quieres resetear la base de datos completamente:
  ```bash
  docker compose down -v
  docker compose up -d --build
  ```

---

## ⚠️ Notas por sistema operativo

| Sistema | Notas |
|---------|-------|
| **Windows** | Docker Desktop usa WSL2 backend. Asegúrate de que WSL2 esté habilitado. Los paths en `volumes:` usan rutas relativas (`./`) que funcionan correctamente en Windows. |
| **macOS** | Docker Desktop para Mac funciona nativamente. No se requieren configuraciones adicionales. |

---

## 🛡️ Seguridad (producción)

Para entornos de producción:
1. Cambia las contraseñas por defecto en `.env`.
2. No expongas el puerto 3307/3306 directamente en redes públicas.
3. Considera eliminar el servicio `phpmyadmin` o restringir su acceso.
4. Usa `restart: always` en lugar de `unless-stopped`.
5. Configura HTTPS con un reverse proxy (Nginx, Traefik, etc.).

---

## 📞 Soporte

Si encuentras problemas al levantar los contenedores, ejecuta:

```bash
docker compose logs db
docker compose logs web
```

Y verifica que Docker Desktop esté en ejecución antes de continuar.
