<?php

require_once __DIR__ . '/../includes/app.php'; 

use MVC\Router;
use Controllers\PaginasController;
$router = new Router();


// ZONA PRIVADA


// ZONA PUBLICA
$router->get('/', [PaginasController::class, 'index']);
$router->get('/nosotros', [PaginasController::class, 'nosotros']);
$router->get('/contacto', [PaginasController::class, 'contacto']);



// Comprobar y validar que las rutas existan para asignarles las funciones del Controlador
$router->comprobarRutas();