<?php
// Router para el servidor incorporado de PHP

// Obtenemos la ruta solicitada por el navegador
$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Construimos la ruta al archivo solicitado dentro de la carpeta /public
$publicPath = __DIR__ . '/public' . $uri;

// Esta es la lógica clave:
// 1. Si la ruta solicitada NO es la raíz ('/') Y existe como un archivo físico 
//    en la carpeta /public (como un .css, .js, o una imagen)...
if ($uri !== '/' && file_exists($publicPath)) {
    // ...entonces devuelve 'false'. Esto le dice al servidor de PHP
    // que sirva el archivo directamente tal como lo encontró.
    return false;
}

// 2. Si la ruta no es un archivo físico (ej. /login), entonces cargamos
//    nuestro controlador frontal para que el Router de la aplicación se encargue.
require_once __DIR__ . '/public/index.php';