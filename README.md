# Pattern Care &mdash; Sistema de Diagnóstico Operativo, Producción e Inventarios Textil

Sistema web integral de arquitectura full-stack diseñado para formalizar la línea de producción, resolver la merma oculta, controlar el inventario de materias primas y uniformes médicos, y mitigar cuellos de botella operativos en un taller textil.

---

## 📁 Estructura del Proyecto

```text
Pattern Care/
├── database.sql                  # Script SQL de creación de BD 'pattern_care', tablas y datos semilla
├── database_master.sql           # Script maestro unificado (multi-sucursal, catálogo y accesos)
├── database_v3_usuarios.sql      # Extensión v3: roles, permisos y asignación a puntos de venta
├── conexion.php                  # Conexión centralizada PDO a MySQL con manejo de excepciones
├── index.php                     # Interfaz web principal para servidores IIS / PHP / MySQL
├── index.html                    # Versión web autocontenida (ejecutable directo en navegador con doble clic)
├── web.config                    # Archivo de configuración para servidor Microsoft IIS
├── backend/                      # API Endpoints en PHP (Procesamiento AJAX / Fetch)
│   ├── obtener_datos.php         # Consolida KPIs ejecutivos, SKUs, órdenes activas, mermas y alertas
│   ├── guardar_inventario.php    # CRUD de productos_sku (altas, edición, ajustes +/- y eliminación)
│   ├── registrar_produccion.php  # Control de órdenes por lote y avance de las 7 fases de producción
│   ├── control_mermas.php        # Bitácora de mermas y retrabajos con costeo financiero
│   ├── calcular_rendimiento.php  # Simulador técnico de tela SRA (6 pzas/m) y CRO (4 pzas/m + 50cm)
│   ├── modelo_accesos.php        # Capa de datos de roles, permisos y asignación a puntos de venta
│   ├── gestionar_usuarios.php    # CRUD de usuarios, roles y sucursales asignadas
│   └── instalar_bd.php           # Instalador / sembrador automático de base de datos con 1 clic
└── README.md                     # Documentación técnica y manual de despliegue
```

---

## 🎯 Módulos Implementados en la Plataforma

1. **Dashboard Principal / Resumen Ejecutivo:**
   - KPIs del personal (6 personas), productos activos (Gorros, Chamarras, Cubrepolvos), nivel de stock mínimo normativo (6 piezas) y costo acumulado de mermas.
   - Gráfico de barras interactivo de existencias de stock vs. línea de umbral mínimo de seguridad.
   - Panel de alertas críticas activas con micro-animaciones de estado.

2. **Módulo 1: Información General y Estructura Organizacional:**
   - Organigrama interactivo detallando las 4 áreas y sus 6 integrantes:
     - 1x Dueña / Encargada General
     - 1x Área Administrativa
     - 2x Corte y Control de Calidad
     - 2x Confección Textil
   - Canales de venta mapeados (Hospitales, Directo a Médicos, En línea) y análisis del canal de comunicación central (WhatsApp Business) con recomendaciones de formalización.

3. **Módulo 2: Cadena de Suministro y Flujo de Producción (Interactive Flowchart):**
   - Flujograma secuencial visual interactivo con 7 etapas:
     `1. Diseño` ➔ `2. Abastecimiento` ➔ `3. Impresión (Taller Externo)` ➔ `4. Corte` ➔ `5. Confección` ➔ `6. Empaque` ➔ `7. Distribución`.
   - Selector dinámico por producto:
     - **Gorros Quirúrgicos:** Resalta el cuello de botella crítico en el paso 3 (sublimado externo, demoras de 48-72h y descalibración de tonos).
     - **Chamarras y Cubrepolvos:** Flujo directo interno, con alerta de desabasto en el paso 2 (cierres y telas impermeables).
   - Fichas técnicas desplegables por cada etapa con responsables, tiempos y planes de mitigación.

4. **Módulo 3: Gestión de Lotes y Simulador de Rendimiento de Tela:**
   - Calculadora en tiempo real para:
     - **Tela SRA:** 6 piezas exactas por metro lineal (0 cm de sobrante).
     - **Tela CRO:** 4 piezas y 50 cm de sobrante acumulado por metro.
   - Registro de parámetros de trazabilidad: Color, Colección, Talla, Lote y Notas de trazo.
   - Historial de consumos guardado directamente en la base de datos MySQL.

5. **Módulo 4: Parámetros de Stock e Inventario (Control SKU con CRUD):**
   - Tabla interactiva con filtro de texto en tiempo real, filtro por categoría y filtro por stock crítico.
   - **Regla estricta:** Alerta visual roja con parpadeo dinámico para cualquier SKU con menos de 6 piezas en almacén.
   - Acciones inmediatas: Ajuste rápido de existencias (`+` / `-`), edición completa de características y eliminación.
   - Modal para dar de alta nuevos SKUs con validación de código único.

6. **Módulo 5: Seguimiento de Órdenes de Trabajo:**
   - Panel tipo Kanban con progreso porcentual de las 7 fases de confección.
   - Botón directo de 1 clic para avanzar la prenda de una fase a la siguiente (ej. de Corte a Confección).
   - Interruptor de estado para marcar órdenes como *Detenidas / En Alerta*.

7. **Módulo 6: Bitácora de Mermas y Retrabajos (Solución a Merma Oculta):**
   - Registro de incidencias clasificadas por tipo (*Merma* o *Retrabajo*) y área (*Corte, Impresión externa, Confección, Empaque*).
   - Cuantificación económica automática del impacto acumulado ($ MXN).

8. **Módulo 7: Matriz de Problemas Críticos y Diagnóstico:**
   - Acordeón clasificado en 3 dimensiones: Operativos y Calidad, Logística e Inventarios, e Infraestructura (Alerta máxima por ausencia de respaldos en la nube).

9. **Módulo 8: Generador de Plan de Acción Inmediato:**
   - Generación instantánea de una propuesta ejecutiva formal estructurada en 3 fases temporales (Semanas 1-2, Semanas 3-4, Mes 2) lista para imprimir o guardar en PDF.

---

## 🚀 Opciones de Ejecución

### Opción A: Ejecución Inmediata en Navegador (Modo Standalone / Offline)
1. Haz doble clic sobre el archivo **`index.html`** para abrirlo en cualquier navegador web moderno (Edge, Chrome, Firefox).
2. El sistema detectará automáticamente el entorno local y utilizará un motor de persistencia inteligente en `localStorage` con los datos semilla precargados.
3. Podrás interactuar con todos los módulos, agregar productos, calcular telas, simular órdenes y generar reportes sin requerir servidores levantados.

---

### Opción B: Despliegue en IIS con PHP y MySQL (Entorno de Producción Local)

#### 1. Requisitos Previos:
- Servidor web **IIS** con el rol CGI / FastCGI habilitado.
- **PHP 7.4 o superior / PHP 8.x** instalado y configurado en el Administrador de IIS (Mapeo de Controladores para `*.php` apuntando a `php-cgi.exe`).
- Extensiones activadas en `php.ini`:
  ```ini
  extension=pdo_mysql
  extension=mysqli
  extension=mbstring
  extension=json
  ```
- Servidor de base de datos **MySQL 5.7+ / 8.0+** o **MariaDB** escuchando en el puerto 3306.

#### 2. Configurar la Base de Datos:
1. Abre tu gestor de base de datos preferido (phpMyAdmin, MySQL Workbench, DBeaver o consola MySQL).
2. Importa el archivo **`database.sql`** ubicado en la raíz del proyecto:
   ```bash
   mysql -u root -p < database.sql
   ```
   *(Esto creará la base de datos `pattern_care`, las 4 tablas principales y los datos iniciales de prueba).*

#### 3. Configurar Credenciales en `conexion.php`:
Abre el archivo `conexion.php` y ajusta las constantes según tu servidor MySQL si utilizas contraseña o puerto diferente:
```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'pattern_care');
define('DB_USER', 'root');
define('DB_PASS', ''); // Coloca tu contraseña si aplica
```

#### 4. Publicar en IIS:
1. Copia la carpeta del proyecto a la raíz de IIS:
   `C:\inetpub\wwwroot\pattern-care` (o crea un Sitio Web / Aplicación apuntando a esta carpeta).
2. Asegúrate de que el usuario `IUSR` y `IIS_IUSRS` tengan permisos de lectura sobre la carpeta.
3. Abre tu navegador y navega a:
   ```text
   http://localhost/pattern-care/index.php
   ```
4. El indicador en la barra superior cambiará a **"MySQL Conectado (IIS/PDO)"** con punto verde, confirmando la persistencia activa en base de datos.
5. Si deseas inicializar o resetear la base de datos directamente desde el navegador, puedes hacer clic en **"Reinstalar / Sembrar BD"** en el pie de página o visitar `http://localhost/pattern-care/backend/instalar_bd.php`.

---

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


## 🛡️ Seguridad y Buenas Prácticas
- Consultas parametrizadas con **PDO Prepared Statements** en todos los endpoints PHP para prevenir inyecciones SQL.
- Cabeceras de seguridad HTTP configuradas en `web.config` y `conexion.php`.
- Formato de respuesta JSON estandarizado con códigos de estado HTTP apropiados (200, 400, 500, 503).
- Arquitectura adaptable y tolerante a fallos: si MySQL no está disponible temporalmente, la interfaz informa al usuario con elegancia sin romperse.
