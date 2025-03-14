<?php

require_once __DIR__ . '/../includes/app.php'; 

use MVC\Router;
use Controllers\AuthController;
use Controllers\DashboardVendedorController;
use Controllers\MarketplaceController;
use Controllers\PaginasController;
use Controllers\ProductosController;

$router = new Router();


// Login
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

// Crear Cuenta
$router->get('/registro', [AuthController::class, 'registro']);
$router->post('/registro', [AuthController::class, 'registro']);

// Formulario de olvide mi password
$router->get('/olvide', [AuthController::class, 'olvide']);
$router->post('/olvide', [AuthController::class, 'olvide']);

// Colocar el nuevo password
$router->get('/reestablecer', [AuthController::class, 'reestablecer']);
$router->post('/reestablecer', [AuthController::class, 'reestablecer']);

// Confirmación de Cuenta
$router->get('/mensaje', [AuthController::class, 'mensaje']);
$router->get('/confirmar-cuenta', [AuthController::class, 'confirmar']);




// Pagina de Inicio
$router->get('/', [PaginasController::class, 'index']);
$router->get('/nosotros', [PaginasController::class, 'nosotros']);
$router->get('/contacto', [PaginasController::class, 'contacto']);




// Marketplace (vista de compradores)
$router->get('/marketplace', [MarketplaceController::class, 'index']);


// Dashboard del Artesano (VENDEDOR)

$router->get('/vendedor/dashboard', [DashboardVendedorController::class, 'index']);

$router->get('/vendedor/productos', [ProductosController::class, 'index']);
$router->get('/vendedor/productos/crear', [ProductosController::class, 'crear']);
$router->post('/vendedor/productos/crear', [ProductosController::class, 'crear']);
$router->get('/vendedor/productos/editar', [ProductosController::class, 'editar']);
$router->post('/vendedor/productos/editar', [ProductosController::class, 'editar']);
$router->get('/vendedor/productos/eliminar', [ProductosController::class, 'eliminar']);

$router->get('/vendedor/perfil', [DashboardVendedorController::class, 'perfil']);
$router->post('/vendedor/perfil', [DashboardVendedorController::class, 'perfil']);

$router->get('/vendedor/editar-telefono', [DashboardVendedorController::class, 'editarTelefono']);
$router->post('/vendedor/editar-telefono', [DashboardVendedorController::class, 'editarTelefono']);


// Comprobar y validar que las rutas existan para asignarles las funciones del Controlador
$router->comprobarRutas();