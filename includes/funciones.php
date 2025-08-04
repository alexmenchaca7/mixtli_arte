<?php

define('TEMPLATES_URL', __DIR__ . '/templates');
define('FUNCIONES_URL', __DIR__ . 'funciones.php');
define('CARPETA_IMAGENES', $_SERVER['DOCUMENT_ROOT'] . '/imagenes/');
define('UPLOAD_PATH', __DIR__ . '/../public/img/productos');

// Constantes para Reportes
define('REPORTE_UMBRAL_CANTIDAD', 3); // Número de reportes para generar una alerta
define('REPORTE_UMBRAL_TIEMPO', '1 HOUR'); // Intervalo de tiempo para contar los reportes (formato SQL)



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
 * Formatea un texto para mostrarlo en HTML de forma segura.
 * 1. Elimina las barras invertidas extra (ej: '\\n' -> '\n').
 * 2. Sanea el texto para seguridad con htmlspecialchars.
 * 3. Convierte los saltos de línea reales (\n) en etiquetas <br>.
 */
function formatear_texto($texto) : string {
    if (is_null($texto)) {
        return '';
    }

    // Paso 1: Elimina las barras invertidas extra. Clave para datos antiguos.
    $texto_sin_slashes = stripslashes($texto);

    // Paso 2: Sanear para seguridad (previene XSS)
    $texto_safe = htmlspecialchars($texto_sin_slashes, ENT_QUOTES, 'UTF-8');

    // Paso 3: Convertir saltos de línea a <br> para visualización en HTML
    $texto_final = nl2br($texto_safe);

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

    if(!$authenticated) {
        return false;
    }

    // Si el usuario es admin, tiene acceso a todo
    if(isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
        return true;
    }

    // Si se requiere un rol específico y el usuario no es admin, verifica el rol
    if ($required_role) {
        return isset($_SESSION['rol']) && $_SESSION['rol'] === $required_role;
    }

    // Si no se requiere rol, solo basta con estar autenticado
    return true;
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
