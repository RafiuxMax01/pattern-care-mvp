<?php
/**
 * PATTERN CARE - ARRANQUE COMÚN DE LOS ENDPOINTS JSON
 *
 * Reúne el preámbulo que estaba copiado en cada endpoint: preflight CORS,
 * lectura del cuerpo de la petición, resolución de la acción, control de
 * permisos y obtención de la conexión.
 *
 * Uso típico (sustituye a unas 23 líneas repetidas):
 *
 *   require_once __DIR__ . '/bootstrap_endpoint.php';
 *
 *   [$pdo, $data, $action, $perfil] = arrancarEndpointJson([
 *       'crear'     => 'inventario.editar',
 *       'actualizar'=> 'inventario.editar'
 *   ], 'inventario.ver');
 *
 * Los endpoints con un preámbulo distinto (obtener_datos, auth_sesion,
 * gestionar_usuarios, gestionar_productos y gestionar_sucursales) usan por
 * separado los auxiliares de abajo, que no imponen ningún orden.
 */

require_once __DIR__ . '/middleware_auth.php';

/**
 * Atiende el preflight CORS y termina la petición si corresponde.
 */
function atenderPreflightCors($codigo = 200) {
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        http_response_code($codigo);
        exit;
    }
}

/**
 * Lee el cuerpo de la petición como JSON, con respaldo en $_POST.
 *
 * ATENCIÓN: se conserva el comportamiento histórico de los endpoints, que es
 * laxo a propósito para no cambiar las respuestas en este refactor. Un cuerpo
 * JSON mal formado no produce un error: json_decode() devuelve null y la
 * petición continúa con $_POST (normalmente vacío), de modo que se ejecuta la
 * acción por defecto sin datos. Un JSON válido pero vacío ("{}") decodifica a
 * array vacío, que también es falsy, y cae igualmente en $_POST.
 *
 * gestionar_sucursales.php sí distingue el JSON inválido y responde 400. Si se
 * decide unificar ese criterio, este es el único punto que hay que cambiar,
 * revisando antes qué espera el frontend.
 */
function leerEntradaJson() {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!$data) {
        $data = $_POST;
    }

    return $data;
}

/**
 * Lee la entrada según el método: cuerpo JSON en POST, query string en el resto.
 *
 * Es el patrón de auth_sesion, gestionar_usuarios y gestionar_productos, y su
 * semántica NO es la de leerEntradaJson(): aquí el respaldo en $_POST se aplica
 * solo cuando json_decode() devuelve null, de modo que un cuerpo "{}" se
 * conserva como array vacío en lugar de caer en $_POST. Se mantienen como dos
 * funciones distintas justamente para no alterar ese matiz.
 */
function leerEntradaPorMetodo() {
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $raw = file_get_contents('php://input');
        return json_decode($raw, true) ?? $_POST;
    }

    return $_GET;
}

/**
 * Devuelve la conexión PDO o corta la petición si la base de datos no responde.
 */
function conexionObligatoria($mensaje = 'Error de conexión con la base de datos.', $codigo = 500) {
    $pdo = getDBConnection();

    if (!$pdo) {
        jsonResponse(false, null, $mensaje, $codigo);
    }

    return $pdo;
}

/**
 * Arranque uniforme de un endpoint de escritura.
 *
 * @param array  $permisosPorAccion  Mapa acción => permiso requerido.
 * @param string $permisoLectura     Permiso exigido a cualquier otra acción.
 * @param string $accionPorDefecto   Acción asumida si la petición no indica una.
 * @param array  $opciones           'clave_accion', 'mensaje_db' y 'codigo_db'.
 * @return array [$pdo, $data, $accion, $perfil]
 */
function arrancarEndpointJson(
    array $permisosPorAccion,
    $permisoLectura,
    $accionPorDefecto = 'crear',
    array $opciones = []
) {
    $claveAccion = $opciones['clave_accion'] ?? 'action';
    $mensajeDb   = $opciones['mensaje_db']   ?? 'Error de conexión con la base de datos.';
    $codigoDb    = $opciones['codigo_db']    ?? 500;

    $data   = leerEntradaJson();
    $accion = $data[$claveAccion] ?? $accionPorDefecto;

    // El permiso se comprueba antes de exigir la conexión. Es equivalente al
    // orden anterior: sin base de datos el middleware entra en modo
    // compatibilidad, concede todos los permisos y el corte acaba ocurriendo
    // igualmente en la comprobación de conexión, con el mismo código y mensaje.
    $perfil = requerirPermiso($permisosPorAccion[$accion] ?? $permisoLectura);
    $pdo    = conexionObligatoria($mensajeDb, $codigoDb);

    return [$pdo, $data, $accion, $perfil];
}
