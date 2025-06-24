<?php

define('TEMPLATES_URL', __DIR__ . '/templates');
define('FUNCIONES_URL', __DIR__ . 'funciones.php');
define('CARPETA_IMAGENES', $_SERVER['DOCUMENT_ROOT'] . '/imagenes/');


function incluirTemplate(string $nombre, bool $inicio = false) {
    include TEMPLATES_URL . '/' . $nombre . '.php';
}

function estaAutenticado() {
    session_start();

    if(!$_SESSION['login']) {
        header('Location: /');
    }
}

function debuguear($variable) : string {
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    exit;
}


// Escapar/Sanitizar el HTML
function s($html) : string {
    if (is_null($html)) {
        return '';
    }
    return htmlspecialchars($html);
}

/**
 * Formatea un texto de descripción:
 * 1. Elimina los saltos de línea literales (\r\n) del principio.
 * 2. Sanea el texto para seguridad con htmlspecialchars.
 * 3. Reemplaza los saltos de línea literales restantes por <br>.
 */
function formatear_descripcion($texto) : string {
    if (is_null($texto)) {
        return '';
    }

    // Paso 1: Eliminar secuencias \r\n o \n del principio del texto.
    $pattern = '/^(\\\r\\\n|\r\n|\\\n|\n)+/';
    $texto_trim = preg_replace($pattern, '', $texto);

    // Paso 2: Sanear el resultado para seguridad
    $texto_safe = s($texto_trim); // Usamos tu función s() que ya es null-safe

    // Paso 3: Reemplazar las secuencias de nueva línea restantes por <br>
    // Se buscan tanto '\r\n' como el 'rn' que pudiera quedar de un stripslashes
    $texto_final = str_replace(['\r\n', 'rn'], '<br>', $texto_safe);

    return $texto_final;
}


// Validar ID y redireccionar a Inicio si no es una ID valida
function validar_redireccionar(string $url) {
    // Validar la URL por ID valido
    $id = $_GET['id'];
    $id = filter_var($id, FILTER_VALIDATE_INT);

    if(!$id) {
        header("Location: $url");
    }

    return $id;
} 

function pagina_actual($path) : bool {
    return str_contains($_SERVER['PATH_INFO'], $path) ? true : false;
}

function is_auth($required_role = null) : bool {
    // Verifica si el usuario está autenticado
    $authenticated = isset($_SESSION['login']) && $_SESSION['login'] === true && isset($_SESSION['verificado']) && $_SESSION['verificado'] === "1";

    // Si se requiere un rol específico, verifica también el rol del usuario
    if ($required_role) {
        return $authenticated && isset($_SESSION['rol']) && $_SESSION['rol'] === $required_role;
    }

    return $authenticated;
}

function obtenerDireccion($direcciones, $tipo, $campo) {
    foreach($direcciones as $direccion) {
        if($direccion->tipo === $tipo) {
            return htmlspecialchars($direccion->$campo ?? '');
        }
    }
    return '';
}

function get_asset($filename) {
    $manifest_path = __DIR__ . '/../public/build/rev-manifest.json';

    if (!file_exists($manifest_path)) {
        return "/build/" . $filename;
    }

    $manifest = json_decode(file_get_contents($manifest_path), true);

    // La clave es el nombre del archivo original, ej: "app.css"
    if (isset($manifest[$filename])) {
        // Obtenemos la extensión (css o js) para construir la subcarpeta
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Construimos la ruta correcta: /build/ + css/ + app-52b33daa5a.css
        return '/build/' . $ext . '/' . $manifest[$filename];
    }
    
    return "/build/" . $filename;
}
