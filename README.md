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
├── index.php                    # Interfaz web principal (ERP completo, 10 módulos)
├── index.html                   # Versión autocontenida (modo standalone/localStorage)
├── conexion.php                 # Conexión PDO a MySQL (lee variables de entorno)
├── database_master.sql          # Script SQL maestro unificado (esquema v2 + datos semilla)
├── database.sql                 # Esquema base original (compatibilidad)
├── database_v2_products.sql     # Extensión v2 productos/variantes (compatibilidad)
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
    ├── registrar_produccion.php # Órdenes de producción y cambio de fases
    ├── control_mermas.php       # Bitácora de mermas y retrabajos
    ├── calcular_rendimiento.php # Simulador de tela SRA/CRO
    ├── transferir_stock.php     # Transferencias inter-sucursal (atómico)
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
