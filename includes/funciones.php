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
    $s = htmlspecialchars($html);
    return $s;
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
    // Verifica si la sesión no está iniciada para iniciar una nueva
    if (session_status() == PHP_SESSION_NONE) {  
        session_start();  
    }

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