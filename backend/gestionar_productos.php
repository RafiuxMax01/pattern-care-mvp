<?php
/**
 * PATTERN CARE ERP v2 - BACKEND: GESTIÓN DE PRODUCTOS CON VARIANTES
 * 
 * Endpoints:
 *   GET  ?action=listar            → Lista todos los productos (con variantes)
 *   GET  ?action=obtener&id=N      → Un producto con sus variantes
 *   POST action=crear              → Crea producto + variantes
 *   POST action=actualizar         → Actualiza producto
 *   POST action=eliminar           → Elimina (soft delete)
 *   POST action=agregar_variante   → Agrega variante a producto existente
 *   POST action=eliminar_variante  → Elimina variante
 *   GET  ?action=generar_numero    → Auto-genera número de pieza para categoría
 *   GET  ?action=verificar_nombre  → Verifica si el nombre ya existe
 *   POST action=subir_foto         → Recibe base64, guarda en Cloudinary o local
 */

require_once __DIR__ . '/middleware_auth.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input  = [];

if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?? $_POST;
} else {
    $input = $_GET;
}

$action = $input['action'] ?? 'listar';

// Autorización por acción (SCRUM-20)
$permisosPorAccion = [
    'crear'             => 'catalogo.editar',
    'actualizar'        => 'catalogo.editar',
    'eliminar'          => 'catalogo.editar',
    'agregar_variante'  => 'catalogo.editar',
    'eliminar_variante' => 'catalogo.editar',
    'generar_numero'    => 'catalogo.editar',
    'subir_foto'        => 'catalogo.editar'
];
$perfil = requerirPermiso($permisosPorAccion[$action] ?? 'catalogo.ver');

// ──────────────────────────────────────────────────────────────
// CONFIGURACIÓN CLOUDINARY (opcional – rellenar con tus datos)
// Si no está configurado, se guarda en carpeta uploads/ local
// ──────────────────────────────────────────────────────────────
define('CLOUDINARY_CLOUD_NAME', getenv('CLOUDINARY_CLOUD_NAME') ?: '');
define('CLOUDINARY_API_KEY',    getenv('CLOUDINARY_API_KEY')    ?: '');
define('CLOUDINARY_API_SECRET', getenv('CLOUDINARY_API_SECRET') ?: '');
define('CLOUDINARY_ENABLED',    CLOUDINARY_CLOUD_NAME !== '');

$pdo = getDBConnection();

// ──────────────────────────────────────────────────────────────
// ROUTER
// ──────────────────────────────────────────────────────────────
switch ($action) {

    // ── LISTAR PRODUCTOS ──────────────────────────────────────
    case 'listar':
        if (!$pdo) {
            jsonResponse(false, null, 'Base de datos no disponible', 503);
        }
        $stmt = $pdo->prepare("
            SELECT
                p.*,
                COUNT(DISTINCT pv.id)                                AS total_variantes,
                SUM(CASE WHEN pv.activo = 1 THEN 1 ELSE 0 END)      AS variantes_activas,
                (SELECT GROUP_CONCAT(DISTINCT pv2.color ORDER BY pv2.color SEPARATOR ', ')
                 FROM product_variants pv2 WHERE pv2.product_id = p.id AND pv2.activo=1) AS colores,
                (SELECT GROUP_CONCAT(DISTINCT pv3.talla ORDER BY FIELD(pv3.talla,'XS','S','M','L','XL','XXL','Unitalla') SEPARATOR ', ')
                 FROM product_variants pv3 WHERE pv3.product_id = p.id AND pv3.activo=1) AS tallas
            FROM products p
            WHERE p.activo = 1
            GROUP BY p.id
            ORDER BY p.categoria, p.nombre
        ");
        $stmt->execute();
        $productos = $stmt->fetchAll();

        // Para cada producto obtener sus variantes
        foreach ($productos as &$prod) {
            $stmtV = $pdo->prepare("
                SELECT pv.*, 
                       COALESCE(SUM(inv.stock_actual), 0) AS stock_total
                FROM   product_variants pv
                LEFT JOIN inventario_sucursales inv ON inv.sku_variante_id = pv.id
                WHERE  pv.product_id = ? AND pv.activo = 1
                GROUP BY pv.id
                ORDER BY pv.color, pv.talla
            ");
            $stmtV->execute([$prod['id']]);
            $prod['variantes'] = $stmtV->fetchAll();
        }
        jsonResponse(true, $productos, 'OK');
        break;

    // ── OBTENER UNO ───────────────────────────────────────────
    case 'obtener':
        if (!$pdo) { jsonResponse(false, null, 'BD no disponible', 503); }
        $id = intval($input['id'] ?? 0);
        if (!$id) { jsonResponse(false, null, 'ID inválido', 400); }

        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND activo = 1");
        $stmt->execute([$id]);
        $prod = $stmt->fetch();
        if (!$prod) { jsonResponse(false, null, 'Producto no encontrado', 404); }

        $stmtV = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY color, talla");
        $stmtV->execute([$id]);
        $prod['variantes'] = $stmtV->fetchAll();

        $stmtI = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY orden, id");
        $stmtI->execute([$id]);
        $prod['imagenes'] = $stmtI->fetchAll();

        jsonResponse(true, $prod, 'OK');
        break;

    // ── CREAR PRODUCTO + VARIANTES ────────────────────────────
    case 'crear':
        if (!$pdo) { jsonResponse(false, null, 'BD no disponible', 503); }

        $nombre    = trim($input['nombre']    ?? '');
        $categoria = trim($input['categoria'] ?? '');
        $coleccion = trim($input['coleccion'] ?? 'General');
        $tipo_tela = trim($input['tipo_tela'] ?? 'SRA');
        $precio    = floatval($input['precio_base']  ?? 0);
        $minimo    = intval($input['stock_minimo']   ?? 6);
        $descripcion = trim($input['descripcion']    ?? '');
        $notas     = trim($input['notas']     ?? '');
        $variantes = $input['variantes']      ?? [];
        $foto_url  = trim($input['foto_url']  ?? '');
        $foto_pid  = trim($input['foto_public_id'] ?? '');
        $numero_pieza = trim($input['numero_pieza'] ?? '');

        if (!$nombre || !$categoria) {
            jsonResponse(false, null, 'Nombre y categoría son obligatorios', 400);
        }

        // Verificar nombre duplicado (case-insensitive)
        $stmtChk = $pdo->prepare("SELECT id FROM products WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?)) AND activo = 1");
        $stmtChk->execute([$nombre]);
        if ($stmtChk->fetch()) {
            jsonResponse(false, null, 'Ya existe un producto con ese nombre. Verifica el catálogo.', 409);
        }

        // Auto-generar número de pieza si no se proporcionó
        if (!$numero_pieza) {
            $numero_pieza = generarNumeroPieza($pdo, $categoria);
        } else {
            // Validar formato y unicidad
            $stmtNP = $pdo->prepare("SELECT id FROM products WHERE numero_pieza = ?");
            $stmtNP->execute([$numero_pieza]);
            if ($stmtNP->fetch()) {
                jsonResponse(false, null, 'El número de pieza ya existe. Usa otro o déjalo en blanco para auto-generar.', 409);
            }
            if (!preg_match('/^[A-Z]{2,4}-[A-Z0-9]{2,6}-\d{4}$/', $numero_pieza)) {
                jsonResponse(false, null, 'Formato de número de pieza inválido. Usa: XX-XXX-NNNN (ej. GQ-CLA-0001)', 400);
            }
        }

        $pdo->beginTransaction();
        try {
            // Insertar producto base
            $stmt = $pdo->prepare("
                INSERT INTO products 
                    (numero_pieza, nombre, categoria, coleccion, tipo_tela, precio_base, stock_minimo, descripcion, foto_url, foto_public_id, notas, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $numero_pieza, $nombre, $categoria, $coleccion, $tipo_tela,
                $precio, $minimo, $descripcion, $foto_url, $foto_pid, $notas,
                $input['created_by'] ?? 'Administración'
            ]);
            $productId = $pdo->lastInsertId();

            // Insertar variantes
            $variantesCreadas = [];
            foreach ($variantes as $v) {
                $color    = trim($v['color']    ?? '');
                $talla    = trim($v['talla']    ?? '');
                $colCodigo = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $color), 0, 3));
                $tallaCodigo = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $talla));

                if (!$color || !$talla) continue;

                $skuVariante = generarSKUVariante($numero_pieza, $colCodigo, $tallaCodigo);

                $stmtV = $pdo->prepare("
                    INSERT INTO product_variants (product_id, sku, color, color_codigo, talla, precio_variante, notas)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        color = VALUES(color),
                        talla = VALUES(talla),
                        activo = 1
                ");
                $stmtV->execute([
                    $productId, $skuVariante, $color, $colCodigo, $talla,
                    !empty($v['precio_variante']) ? floatval($v['precio_variante']) : null,
                    $v['notas'] ?? null
                ]);
                $variantesCreadas[] = ['sku' => $skuVariante, 'color' => $color, 'talla' => $talla];
            }

            $pdo->commit();
            jsonResponse(true, [
                'id'           => $productId,
                'numero_pieza' => $numero_pieza,
                'variantes'    => $variantesCreadas
            ], "Producto '{$nombre}' creado con " . count($variantesCreadas) . " variantes.");
        } catch (Exception $e) {
            $pdo->rollBack();
            jsonResponse(false, null, 'Error al crear producto: ' . $e->getMessage(), 500);
        }
        break;

    // ── ACTUALIZAR PRODUCTO ───────────────────────────────────
    case 'actualizar':
        if (!$pdo) { jsonResponse(false, null, 'BD no disponible', 503); }
        $id = intval($input['id'] ?? 0);
        if (!$id) { jsonResponse(false, null, 'ID requerido', 400); }

        $campos = [];
        $params = [];
        $editables = ['nombre','categoria','coleccion','tipo_tela','precio_base','stock_minimo','descripcion','foto_url','foto_public_id','notas'];
        foreach ($editables as $campo) {
            if (isset($input[$campo])) {
                $campos[] = "`$campo` = ?";
                $params[]  = $input[$campo];
            }
        }
        if (empty($campos)) { jsonResponse(false, null, 'Nada que actualizar', 400); }

        // Verificar duplicado de nombre (excluyendo el mismo producto)
        if (isset($input['nombre'])) {
            $stmtChk = $pdo->prepare("SELECT id FROM products WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?)) AND id <> ? AND activo = 1");
            $stmtChk->execute([$input['nombre'], $id]);
            if ($stmtChk->fetch()) {
                jsonResponse(false, null, 'Ya existe otro producto con ese nombre.', 409);
            }
        }

        $params[] = $id;
        $sql = "UPDATE products SET " . implode(', ', $campos) . " WHERE id = ?";
        $pdo->prepare($sql)->execute($params);
        jsonResponse(true, ['id' => $id], 'Producto actualizado correctamente.');
        break;

    // ── ELIMINAR PRODUCTO (soft delete) ──────────────────────
    case 'eliminar':
        if (!$pdo) { jsonResponse(false, null, 'BD no disponible', 503); }
        $id = intval($input['id'] ?? 0);
        if (!$id) { jsonResponse(false, null, 'ID requerido', 400); }
        $pdo->prepare("UPDATE products SET activo = 0 WHERE id = ?")->execute([$id]);
        jsonResponse(true, null, 'Producto desactivado del catálogo.');
        break;

    // ── AGREGAR VARIANTE A PRODUCTO EXISTENTE ─────────────────
    case 'agregar_variante':
        if (!$pdo) { jsonResponse(false, null, 'BD no disponible', 503); }
        $productId = intval($input['product_id'] ?? 0);
        $color     = trim($input['color']  ?? '');
        $talla     = trim($input['talla']  ?? '');
        if (!$productId || !$color || !$talla) {
            jsonResponse(false, null, 'product_id, color y talla son obligatorios', 400);
        }

        // Obtener número de pieza del producto
        $stmtP = $pdo->prepare("SELECT numero_pieza FROM products WHERE id = ?");
        $stmtP->execute([$productId]);
        $prod = $stmtP->fetch();
        if (!$prod) { jsonResponse(false, null, 'Producto no encontrado', 404); }

        $colCodigo  = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $color), 0, 3));
        $tallaCodigo = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $talla));
        $sku = generarSKUVariante($prod['numero_pieza'], $colCodigo, $tallaCodigo);

        try {
            $stmt = $pdo->prepare("
                INSERT INTO product_variants (product_id, sku, color, color_codigo, talla, precio_variante, notas, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)
                ON DUPLICATE KEY UPDATE activo = 1, notas = VALUES(notas)
            ");
            $stmt->execute([
                $productId, $sku, $color, $colCodigo, $talla,
                !empty($input['precio_variante']) ? floatval($input['precio_variante']) : null,
                $input['notas'] ?? null
            ]);
            jsonResponse(true, ['sku' => $sku], "Variante {$color}/{$talla} agregada con SKU: {$sku}");
        } catch (Exception $e) {
            jsonResponse(false, null, 'Error al agregar variante: ' . $e->getMessage(), 500);
        }
        break;

    // ── ELIMINAR VARIANTE ─────────────────────────────────────
    case 'eliminar_variante':
        if (!$pdo) { jsonResponse(false, null, 'BD no disponible', 503); }
        $varId = intval($input['variant_id'] ?? 0);
        if (!$varId) { jsonResponse(false, null, 'variant_id requerido', 400); }
        $pdo->prepare("UPDATE product_variants SET activo = 0 WHERE id = ?")->execute([$varId]);
        jsonResponse(true, null, 'Variante desactivada.');
        break;

    // ── GENERAR NÚMERO DE PIEZA ───────────────────────────────
    case 'generar_numero':
        if (!$pdo) {
            // Offline: generar en cliente con timestamp
            $cat = $input['categoria'] ?? 'Otros';
            $codigo = mapCategoriaACodigo($cat);
            jsonResponse(true, [
                'numero_pieza' => $codigo . '-NUE-' . str_pad(rand(1,9999), 4, '0', STR_PAD_LEFT),
                'modo'         => 'offline'
            ], 'Generado offline');
        }
        $categoria = $input['categoria'] ?? '';
        if (!$categoria) { jsonResponse(false, null, 'Categoría requerida', 400); }
        $numero = generarNumeroPieza($pdo, $categoria);
        jsonResponse(true, ['numero_pieza' => $numero], 'Número de pieza generado.');
        break;

    // ── VERIFICAR NOMBRE ──────────────────────────────────────
    case 'verificar_nombre':
        if (!$pdo) { jsonResponse(true, ['disponible' => true], 'BD offline – no se puede verificar'); }
        $nombre = trim($input['nombre'] ?? '');
        $excluirId = intval($input['excluir_id'] ?? 0);
        if (!$nombre) { jsonResponse(false, null, 'nombre requerido', 400); }

        $sql = "SELECT id, nombre FROM products WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?)) AND activo = 1";
        $params = [$nombre];
        if ($excluirId) {
            $sql .= " AND id <> ?";
            $params[] = $excluirId;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $existing = $stmt->fetch();

        jsonResponse(true, [
            'disponible' => !$existing,
            'conflicto'  => $existing ? $existing['nombre'] : null
        ], $existing ? 'Nombre duplicado' : 'Nombre disponible');
        break;

    // ── SUBIR FOTO (base64 a Cloudinary o local) ──────────────
    case 'subir_foto':
        $base64  = $input['base64']     ?? '';
        $prodId  = intval($input['product_id'] ?? 0);
        $tipo    = $input['tipo']       ?? 'Estampado';
        $nombre  = preg_replace('/[^a-z0-9_-]/i', '_', $input['nombre_archivo'] ?? 'foto');

        if (!$base64) { jsonResponse(false, null, 'base64 requerido', 400); }

        // Limpiar prefijo data URL si viene incluido
        if (strpos($base64, ',') !== false) {
            $base64 = explode(',', $base64)[1];
        }

        if (CLOUDINARY_ENABLED) {
            // ── Subir a Cloudinary ─────────────────────────────
            $timestamp  = time();
            $publicId   = 'pattern_care/products/' . ($prodId ?: 'new') . '/' . $nombre . '_' . $timestamp;
            $signature  = sha1("public_id={$publicId}&timestamp={$timestamp}" . CLOUDINARY_API_SECRET);

            $ch = curl_init("https://api.cloudinary.com/v1_1/" . CLOUDINARY_CLOUD_NAME . "/image/upload");
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => [
                    'file'       => 'data:image/jpeg;base64,' . $base64,
                    'upload_preset' => '',
                    'public_id'  => $publicId,
                    'api_key'    => CLOUDINARY_API_KEY,
                    'timestamp'  => $timestamp,
                    'signature'  => $signature,
                    'folder'     => 'pattern_care/products'
                ]
            ]);
            $response  = curl_exec($ch);
            $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $res = json_decode($response, true);
                $url = $res['secure_url'] ?? '';
                $pid = $res['public_id']  ?? '';

                // Guardar referencia en BD
                if ($pdo && $prodId && $url) {
                    // Actualizar foto principal en producto
                    $pdo->prepare("UPDATE products SET foto_url = ?, foto_public_id = ? WHERE id = ?")
                        ->execute([$url, $pid, $prodId]);
                    // También insertar en galería
                    $pdo->prepare("INSERT INTO product_images (product_id, url, public_id, tipo) VALUES (?,?,?,?)")
                        ->execute([$prodId, $url, $pid, $tipo]);
                }
                jsonResponse(true, ['url' => $url, 'public_id' => $pid], 'Imagen subida a Cloudinary.');
            } else {
                jsonResponse(false, null, 'Error Cloudinary: ' . $response, 500);
            }
        } else {
            // ── Guardar localmente en /uploads ─────────────────
            $uploadDir = __DIR__ . '/../uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = ($prodId ?: 'new') . '_' . $nombre . '_' . time() . '.jpg';
            $filepath = $uploadDir . $filename;
            $decoded  = base64_decode($base64);

            if (file_put_contents($filepath, $decoded) === false) {
                jsonResponse(false, null, 'No se pudo guardar el archivo en el servidor.', 500);
            }

            // URL relativa para servir desde IIS/Apache
            $url = '/uploads/products/' . $filename;

            if ($pdo && $prodId) {
                $pdo->prepare("UPDATE products SET foto_url = ? WHERE id = ?")->execute([$url, $prodId]);
                $pdo->prepare("INSERT INTO product_images (product_id, url, tipo) VALUES (?,?,?)")
                    ->execute([$prodId, $url, $tipo]);
            }
            jsonResponse(true, ['url' => $url, 'public_id' => null, 'modo' => 'local'], 'Imagen guardada localmente.');
        }
        break;

    default:
        jsonResponse(false, null, "Acción '$action' no reconocida.", 400);
}

// ──────────────────────────────────────────────────────────────
// FUNCIONES AUXILIARES
// ──────────────────────────────────────────────────────────────

/**
 * Genera un número de pieza único de forma atómica usando la tabla de secuencias.
 * Formato: GQ-NUE-0001
 */
function generarNumeroPieza(PDO $pdo, string $categoria): string {
    $codigo = mapCategoriaACodigo($categoria);

    $pdo->prepare("UPDATE numero_pieza_seq SET ultimo_numero = ultimo_numero + 1 WHERE categoria_codigo = ?")
        ->execute([$codigo]);

    $stmt = $pdo->prepare("SELECT ultimo_numero FROM numero_pieza_seq WHERE categoria_codigo = ?");
    $stmt->execute([$codigo]);
    $n = intval($stmt->fetchColumn());

    return $codigo . '-NUE-' . str_pad($n, 4, '0', STR_PAD_LEFT);
}

/**
 * Mapea el nombre completo de categoría al código de 2 letras.
 */
function mapCategoriaACodigo(string $cat): string {
    return match ($cat) {
        'Gorros Quirúrgicos' => 'GQ',
        'Chamarras Médicas'  => 'CH',
        'Cubrepolvos'        => 'CP',
        default              => 'OT'
    };
}

/**
 * Genera SKU de variante: NumeroPieza-COLCOD-TALLACOD
 * Ej: GQ-CLA-0001-AZC-U
 */
function generarSKUVariante(string $numeroPieza, string $colorCodigo, string $tallaCodigo): string {
    return $numeroPieza . '-' . $colorCodigo . '-' . $tallaCodigo;
}
