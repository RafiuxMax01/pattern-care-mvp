<?php
/**
 * PATTERN CARE - INSTALADOR Y VERIFICADOR AUTOMÁTICO DE BASE DE DATOS
 * Crea la base de datos 'pattern_care', las tablas y los datos semilla iniciales.
 */

require_once __DIR__ . '/middleware_auth.php';

// Reinstalar o sembrar la base solo lo puede hacer quien administra accesos (SCRUM-20)
requerirPermiso('usuarios.gestionar');

// Permitir peticiones GET o POST
$serverConn = getServerConnection();

if (!$serverConn) {
    jsonResponse(false, null, 'No fue posible conectar al servidor MySQL en ' . DB_HOST . ':' . DB_PORT . '. Asegúrate de que el servicio MySQL esté iniciado.', 500);
}

try {
    // 1. Crear Base de Datos si no existe
    $serverConn->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci");
    $serverConn->exec("USE `" . DB_NAME . "`");

    // 2. Leer archivo database_master.sql (esquema unificado v2)
    $sqlFile = __DIR__ . '/../database_master.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("El archivo database_master.sql no se encuentra en el directorio raíz.");
    }

    $sqlContent = file_get_contents($sqlFile);
    
    // Ejecutar lote SQL
    $serverConn->exec($sqlContent);

    // Verificar conteo de tablas creadas
    $stmt = $serverConn->query("SHOW TABLES FROM `" . DB_NAME . "`");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    jsonResponse(true, [
        'database' => DB_NAME,
        'tables_installed' => $tables,
        'status' => 'OK'
    ], 'Base de datos y tablas inicializadas correctamente con datos semilla.', 200);

} catch (Exception $e) {
    jsonResponse(false, [
        'error_details' => $e->getMessage()
    ], 'Error al inicializar la base de datos: ' . $e->getMessage(), 500);
}
