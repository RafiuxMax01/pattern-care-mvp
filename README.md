# 🏥 Pattern Care ERP — Sistema de Gestión Operativa Textil y Uniformes Médicos

Sistema web integral tipo **Business Central ERP** (MVP) diseñado para formalizar la línea de producción, controlar inventarios multi-sucursal, mitigar mermas ocultas y gestionar la cadena de suministro de un negocio de confección textil y uniformes médicos.

Arquitectura **Full-Stack**: PHP 8.2 + MySQL 8.0 (PDO) + JavaScript Vanilla + Tailwind CSS, listo para ejecutarse en **IIS local**, **Apache/XAMPP** o **Docker** (Windows y macOS).

---

## 🎯 Objetivo del Sistema

Crear una herramienta de gestión y diagnóstico operativo que permita visualizar, estructurar y mitigar los problemas críticos del negocio, sirviendo como base interactiva para la formalización de la línea de producción y control de inventarios.

---

## 📁 Estructura del Proyecto

```
Pattern Care/
├── index.php                    # Interfaz web principal (ERP completo, 11 módulos)
├── index.html                   # Versión autocontenida (modo standalone/localStorage)
├── conexion.php                 # Conexión PDO a MySQL (lee variables de entorno)
├── database_master.sql          # Script SQL maestro unificado (esquema v2 + accesos + datos semilla)
├── database.sql                 # Esquema base original (compatibilidad)
├── database_v2_products.sql     # Extensión v2 productos/variantes (compatibilidad)
├── database_v3_usuarios.sql     # Extensión v3 roles, permisos y asignación a puntos de venta
├── Dockerfile                   # Imagen PHP 8.2 + Apache para Docker
├── docker-compose.yml           # Orquestación Docker (Web + MySQL + phpMyAdmin)
├── php.ini                      # Configuración PHP para contenedor
├── .env                         # Variables de entorno (Docker/local)
├── .env.example                 # Plantilla de variables de entorno
├── web.config                   # Configuración para servidor IIS
├── README.md                    # Este archivo
├── README_DOCKER.md             # Guía específica de despliegue con Docker
└── backend/                     # Endpoints PHP (API AJAX/Fetch)
    ├── obtener_datos.php        # Datos consolidados (KPIs, SKUs, órdenes, mermas, alertas)
    ├── guardar_inventario.php   # CRUD de inventario y stock por sucursal
    ├── gestionar_productos.php  # CRUD de productos, variantes, números de pieza, fotos
    ├── gestionar_sucursales.php # CRUD de sucursales y puntos de venta
    ├── registrar_produccion.php # Órdenes de producción y cambio de fases
    ├── control_mermas.php       # Bitácora de mermas y retrabajos
    ├── calcular_rendimiento.php # Simulador de tela SRA/CRO
    ├── transferir_stock.php     # Transferencias inter-sucursal (atómico)
    ├── modelo_accesos.php       # Capa de datos de roles, permisos y puntos de venta
    ├── middleware_auth.php      # Middleware de autorización (401 / 403 por permiso y sucursal)
    ├── auth_sesion.php          # Login, logout, sesión y sucursal activa
    ├── gestionar_usuarios.php   # CRUD de usuarios y asignación a puntos de venta
    └── instalar_bd.php          # Instalador automático de BD con 1 clic
```

---

## 🎨 Diseño UI/UX — Estilo Business Central

Inspirado en **Microsoft Dynamics 365 Business Central**:

- **Paleta corporativa:** Azul Dynamics (`#0078d4`), teal (`#008272`), gris pizarra, aciertos en ámbar y rojo sutil para alertas
- **Componentes:** Tarjetas tipo "Cue", barra superior (App Bar), ribbon de módulos, factbox lateral, modales, tooltips
- **Responsividad:** Totalmente adaptable a escritorio, tablet y móvil
- **Interactividad:** Pestañas, filtros en tiempo real, modales, acordeones, ajustes de stock `+`/`-`, gráficos Chart.js
- **Modo impresión:** Hoja de estilo `@media print` para reportes ejecutivos

---

## 🗄️ Base de Datos — Esquema Unificado v2

El archivo `database_master.sql` crea la base de datos `pattern_care` con las siguientes tablas:

| Tabla | Descripción |
|-------|-------------|
| `sucursales` | Multi-location: Planta Matriz, puntos de venta, almacén digital |
| `products` | Catálogo maestro de modelos (familias de producto) |
| `product_variants` | Variantes por Color + Talla (SKU único por combinación) |
| `product_images` | Galería de fotos (estampados, detalles, empaque) |
| `numero_pieza_seq` | Secuencias atómicas para auto-generar Número de Pieza |
| `inventario_sucursales` | Existencias de cada variante en cada sucursal |
| `transferencias_stock` | Órdenes de traspaso entre sucursales (transaccional) |
| `produccion_ordenes` | Órdenes de confección con 7 fases de seguimiento |
| `mermas_retrabajos` | Bitácora de costos ocultos (merma/retrabajo por área) |
| `rendimiento_telas` | Historial de consumo de tela SRA y CRO |

**Vistas y procedimientos incluidos:**
- `v_products_full` — Productos con conteo de variantes activas
- `generar_numero_pieza(categoria, @numero)` — Genera Número de Pieza único (formato: `GQ-NUE-0001`)

---

## 🧩 Módulos del ERP (10 secciones)

| # | Módulo | Funcionalidad principal |
|---|--------|------------------------|
| 1 | **Centro de Funciones** | Dashboard con KPIs, gráfica de inventario, alertas críticas, personal (6 personas) |
| 2 | **Productos & Inventario** | Tabla de SKUs por sucursal con alerta visual < 6 pzas, filtros, ajuste rápido `+`/`-` |
| 3 | **Transferencias** | Órdenes de traspaso inter-sucursal con validación de stock atómico |
| 4 | **Producción (7 Fases)** | Flujo interactivo: Diseño → Abastecimiento → Impresión → Corte → Confección → Empaque → Distribución |
| 5 | **Rendimiento de Tela** | Calculadora SRA (6 pzas/m) vs CRO (4 pzas/m + 50cm merma), historial guardado en BD |
| 6 | **Bitácora de Mermas** | Registro de errores por área con costo económico acumulado |
| 7 | **Directorio Sucursales** | Tarjetas de las 4 sucursales con stock y críticos por ubicación |
| 8 | **Matriz de Diagnóstico** | Problemas críticos clasificados: Operativos, Logística, Infraestructura (backup en nube) |
| 9 | **Plan de Acción** | Generador de reporte ejecutivo en 3 fases (imprimible/PDF) |
| 10 | **Catálogo de Productos** | Multi-paso: Info general → Variantes (Color/Talla) → Foto de estampado, con auto-generación de Número de Pieza y SKU |

---

## 🔌 Endpoints Backend (PHP)

Todos los endpoints responden en **JSON** y usan **PDO Prepared Statements** (prevención de inyección SQL).

| Endpoint | Acción | Descripción |
|----------|--------|-------------|
| `obtener_datos.php` | GET | Datos consolidados: sucursales, productos, variantes, inventario, transferencias, órdenes, mermas, rendimientos, KPIs, alertas |
| `guardar_inventario.php` | POST | Crear, actualizar, ajustar stock (`+/-`) y eliminar productos/inventario por sucursal |
| `gestionar_productos.php` | GET/POST | Listar, obtener, crear, actualizar, eliminar productos y variantes; generar número de pieza; verificar nombre duplicado; subir foto (Cloudinary o local) |
| `registrar_produccion.php` | POST | Crear órdenes, cambiar fase, cambiar estado, eliminar órdenes de producción |
| `control_mermas.php` | POST | Registrar y eliminar mermas/retrabajos con costo de impacto |
| `calcular_rendimiento.php` | POST | Calcular y guardar rendimiento de tela SRA/CRO; listar historial |
| `transferir_stock.php` | POST | Crear transferencia inter-sucursal con transacción atómica (verifica stock, descuenta origen, suma destino) |
| `instalar_bd.php` | GET/POST | Instalador automático: crea BD, ejecuta `database_master.sql`, verifica tablas |

---

## 🚀 Opciones de Ejecución

### Opción A: Modo Standalone (sin servidor)
1. Abre `index.html` directamente en el navegador (Edge, Chrome, Firefox).
2. El sistema usa `localStorage` con datos semilla precargados.
3. Ideal para pruebas rápidas sin configurar nada.

### Opción B: IIS Local con PHP y MySQL
1. Instala **IIS** con PHP (FastCGI) y **MySQL 5.7+ / 8.0+** o **MariaDB**.
2. Habilita extensiones: `pdo_mysql`, `mysqli`, `mbstring`, `json`.
3. Importa `database_master.sql` en MySQL.
4. Ajusta credenciales en `conexion.php` (o define variables de entorno).
5. Publica la carpeta en `C:\inetpub\wwwroot\pattern-care`.
6. Navega a `http://localhost/pattern-care/index.php`.

### Opción C: Docker (Windows y macOS) — Recomendado

**Requisitos:** Docker Desktop instalado y en ejecución.

```bash
# 1. Desde la carpeta del proyecto
cd "C:\Users\Rafota\Desktop\Proyectos\Pattern Care"

# 2. Levantar contenedores
docker compose up -d --build

# 3. Verificar estado
docker compose ps
```

**Accesos:**

| Servicio | URL | Usuario | Contraseña |
|----------|-----|---------|------------|
| **ERP Pattern Care** | `http://localhost:8000` | — | — |
| **phpMyAdmin** | `http://localhost:8080` | `pattern_user` | `pattern_pass` |
| **MySQL directo** | `localhost:3307` | `root` | `rootpassword` |

**Comandos útiles:**
```bash
docker compose logs -f          # Ver logs en tiempo real
docker compose down             # Detener contenedores (mantiene datos MySQL)
docker compose down -v          # Borrar todo incluido volumen de MySQL ⚠️
docker compose up -d --build    # Reconstruir tras cambios de código
```

Consulta `README_DOCKER.md` para la guía completa de Docker.

---

## 🔑 Características Clave para la Administradora

1. **Crear productos definiendo características principales** (SKU, Color, Talla) antes de asignar cantidades.
2. **Evitar duplicados:** Función de verificación de nombre de producto (case-insensitive) antes de guardar.
3. **Tablas `products` y `product_variants`:** Separación limpia entre modelo base y variantes por color/talla.
4. **Auto-generación de Número de Pieza:** Formato único `XX-XXX-NNNN` (ej. `GQ-CLA-0001`) mediante procedimiento almacenado atómico.
5. **Formulario multi-paso:** Paso 1 (Info general) → Paso 2 (Variantes Color/Talla) → Paso 3 (Foto de estampado).
6. **Almacenamiento de fotos:** Soporte para **Cloudinary** (nube) o carpeta local `/uploads/products/`.
7. **Control multi-sucursal:** Inventario individual por sucursal con alerta crítica cuando stock < 6 pzas.
8. **Transferencias inter-sucursal:** Traspaso atómico con validación de stock suficiente en origen.

---

## 🔐 Roles, Permisos y Puntos de Venta

El acceso se define con tres piezas: el **rol** (qué puede hacer), los **permisos**
en formato `modulo.accion` (ej. `inventario.ajustar`) y la **asignación a puntos de
venta** (sobre qué sucursales puede hacerlo).

| Rol | Alcance | Puede |
| --- | --- | --- |
| Dueña / Encargada General | Todas las sucursales | Todo, incluida la administración de accesos |
| Área Administrativa | Sucursales asignadas | Operación completa salvo accesos y alta de sucursales |
| Corte y Control de Calidad | Sucursales asignadas | Producción, mermas, rendimiento e inventario |
| Confección Textil | Sucursales asignadas | Avance de fases y registro de retrabajos |
| Mostrador / Punto de Venta | Su punto de venta | Consultar y ajustar existencias de su sucursal |

Cuentas sembradas (`usuario` / contraseña inicial `PatternCare2026`):
`encargada`, `administracion`, `corte1`, `corte2`, `confeccion1`, `confeccion2`,
`pv.hospital`.

Para instalar el módulo sobre una base ya creada:

```bash
mysql -u root -p < database_v3_usuarios.sql
```

### Cómo se aplica el control de accesos

`backend/middleware_auth.php` se incluye al inicio de cada endpoint y corta la
petición antes de tocar la base de datos:

| Situación | Respuesta |
| --- | --- |
| Sin sesión iniciada | `401` — el frontend abre la pantalla de acceso |
| Permiso faltante para la acción | `403` + renglón en `bitacora_accesos` |
| Sucursal no asignada al usuario | `403`, aunque el permiso sí exista |

La sesión se maneja en `backend/auth_sesion.php` con las acciones `login`,
`logout`, `sesion` y `seleccionar_sucursal`.

Si la base de datos todavía no tiene instalado el módulo de accesos, el
middleware deja pasar la petición en **modo compatibilidad** para no romper
entornos anteriores a esta historia.


### Contexto de autenticación en el frontend

`PatternCareAuth` (en `index.php` / `index.html`) guarda la sesión y la expone
a toda la interfaz:

| Método | Para qué sirve |
| --- | --- |
| `tienePermiso('inventario.ajustar')` | Decidir si se muestra o se habilita una acción |
| `sucursalesPermitidas()` | Ids asignados; `null` cuando el rol es global |
| `puedeEnSucursal(id, 'ajustar')` | Validar la sucursal antes de operar |

Todas las llamadas al backend pasan por `fetchConSesion()`, que envía la cookie
de sesión y convierte el `401` en la pantalla de acceso y el `403` en un aviso
visible. Al abrir `index.html` con doble clic no hay backend, así que el
contexto usa una sesión local de demostración y el MVP sigue navegable.


### Protección de rutas

Cada botón del ribbon declara el permiso que exige en `data-permiso`:

- `aplicarProteccionDeRutas()` oculta los módulos que el rol no puede abrir y,
  si el módulo activo deja de estar permitido, mueve al usuario al primero que sí.
- `cambiarModulo()` repite la comprobación, así que ni la búsqueda global ni un
  enlace directo saltan la regla.
- El selector de sucursales solo lista los puntos de venta asignados; la vista
  consolidada de todas es exclusiva del alcance global.

El módulo **Usuarios y Accesos** (permiso `usuarios.ver`) muestra el padrón del
equipo con sus asignaciones y la bitácora de accesos. Asignar puntos de venta o
activar cuentas exige además `usuarios.gestionar`, de modo que solo la Dueña o
la Encargada General puede hacerlo.


## 🛡️ Seguridad y Buenas Prácticas

- Consultas parametrizadas con **PDO Prepared Statements** en todos los endpoints.
- Cabeceras de seguridad HTTP configuradas (`X-Content-Type-Options`, `X-Frame-Options`, `X-XSS-Protection`).
- CORS habilitado para desarrollo local.
- Validación de datos de entrada en backend y frontend.
- Soft delete en productos y variantes (marca `activo = 0` en lugar de eliminar físicamente).
- Transacciones atómicas en transferencias de stock.

---

## 📊 Datos Semilla Incluidos

- **4 Sucursales:** Matriz & Planta, Sucursal Hospital General, Sucursal Zona Norte, Almacén Digital.
- **7 Productos base:** Gorros Quirúrgicos, Chamarras Médicas, Cubrepolvos.
- **19 Variantes:** Combinaciones de Color y Talla con SKUs únicos.
- **Inventario distribuido:** Múltiples sucursales con stock por debajo del mínimo (6 pzas) para detonar alertas críticas.
- **3 Transferencias:** Incluyendo una en tránsito.
- **4 Órdenes de Producción:** En distintas fases (Impresión detenida, Confección, Corte, Empaque).
- **4 Mermas/Retrabajos:** Con costo económico acumulado ($1,620.00 MXN).
- **2 Rendimientos de Tela:** SRA y CRO con lotes de referencia.

---

## 📞 Soporte y Contacto

Para problemas de despliegue:
1. Revisa `docker compose logs -f` (modo Docker) o los logs de IIS/PHP.
2. Verifica que MySQL esté ejecutándose y accesible.
3. Ejecuta `backend/instalar_bd.php` para reinicializar la base de datos.
4. Consulta `README_DOCKER.md` para instrucciones específicas de Docker.

**Pattern Care ERP MVP** — Control operativo, multi-sucursal y listo para la nube.
