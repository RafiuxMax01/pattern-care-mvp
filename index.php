<?php
/**
 * PATTERN CARE - PUNTO DE ENTRADA SERVIDO POR APACHE / IIS
 *
 * La interfaz completa vive en index.html, que es la fuente unica de verdad y
 * a la vez el modo standalone: se puede abrir con doble clic (file://) y la
 * aplicacion detecta que no hay backend al que consultar, cayendo a la sesion
 * de demostracion que define PatternCareAuth.
 *
 * Este archivo existe solo porque el servidor resuelve index.php como documento
 * por defecto y un .php abierto por file:// se descargaria en vez de mostrarse.
 * Antes era una copia literal de index.html mantenida a mano en cada commit, y
 * las dos versiones ya habian divergido.
 *
 * No agregues marcado ni scripts aqui: edita index.html.
 */

$pagina = __DIR__ . '/index.html';

if (!is_readable($pagina)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'No se encuentra index.html, que contiene la interfaz de la aplicacion.';
    exit;
}

header('Content-Type: text/html; charset=UTF-8');
readfile($pagina);
