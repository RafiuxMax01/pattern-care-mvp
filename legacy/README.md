# Esquemas SQL históricos — no los importes

Estos archivos son las versiones anteriores del esquema. Se conservan solo como
referencia de cómo evolucionó la base de datos. **Ninguno está referenciado por
el código**, y ejecutarlos contra una instalación actual la deja inconsistente.

| Archivo | Qué era | Por qué no se usa |
|---|---|---|
| `database.sql` | Esquema base original (7 tablas) | Define `productos_sku`, que el esquema actual reemplazó por `products` + `product_variants` + `product_images` |
| `database_v2_products.sql` | Extensión v2 de productos y variantes (4 tablas) | Ya incorporada en `database_master.sql` |

## Qué usar en su lugar

- **Instalación nueva:** `database_master.sql` (en la raíz). Es el que monta
  `docker-compose.yml` en `/docker-entrypoint-initdb.d/` y el que lee
  `backend/instalar_bd.php`. Contiene las 16 tablas y los datos semilla.
- **Base ya instalada sin el módulo de accesos:** `database_v3_usuarios.sql`
  (en la raíz), que es reejecutable y solo añade roles, permisos, usuarios y la
  asignación a puntos de venta.

## El riesgo concreto

`legacy/database.sql` crea `productos_sku`. El código actual
(`backend/gestionar_productos.php`, `backend/obtener_datos.php`) consulta
`products`, `product_variants` y `product_images`. Importar el archivo
histórico sobre una base actual no borra nada, pero añade una tabla huérfana y
deja la impresión de que el esquema está completo cuando faltan las diez tablas
que introdujeron las versiones v2 y v3.
