<?php

require_once __DIR__ . '/../includes/app.php'; 

use MVC\Router;
use Controllers\UsuariosController;
use Controllers\AuthController;
use Controllers\PaginasController;
use Controllers\MensajesController;
use Controllers\ProductosController;
use Controllers\MarketplaceController;
use Controllers\DashboardAdminController;
use Controllers\DashboardVendedorController;

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




// Marketplace (VISTA COMPRADOR)
$router->get('/marketplace', [MarketplaceController::class, 'index']);


// Dashboard del Artesano (VISTA VENDEDOR)
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

$router->get('/vendedor/mensajes', [MensajesController::class, 'index']);



// Dashboard del Admin (VISTA ADMINISTRADOR)
$router->get('/admin/dashboard', [DashboardAdminController::class, 'index']);

$router->get('/admin/usuarios', [UsuariosController::class, 'index']);
$router->get('/admin/usuarios/crear', [UsuariosController::class, 'crear']);
$router->post('/admin/usuarios/crear', [UsuariosController::class, 'crear']);
$router->get('/admin/usuarios/editar', [UsuariosController::class, 'editar']);
$router->post('/admin/usuarios/editar', [UsuariosController::class, 'editar']);
$router->get('/admin/usuarios/eliminar', [UsuariosController::class, 'eliminar']);

$router->get('/admin/productos', [ProductosController::class, 'index']);
$router->get('/admin/productos/crear', [ProductosController::class, 'crear']);
$router->post('/admin/productos/crear', [ProductosController::class, 'crear']);
$router->get('/admin/productos/editar', [ProductosController::class, 'editar']);
$router->post('/admin/productos/editar', [ProductosController::class, 'editar']);
$router->get('/admin/productos/eliminar', [ProductosController::class, 'eliminar']);

$router->get('/admin/perfil', [DashboardAdminController::class, 'perfil']);
$router->post('/admin/perfil', [DashboardAdminController::class, 'perfil']);

$router->get('/admin/editar-telefono', [DashboardAdminController::class, 'editarTelefono']);
$router->post('/admin/editar-telefono', [DashboardAdminController::class, 'editarTelefono']);




// Comprobar y validar que las rutas existan para asignarles las funciones del Controlador
$router->comprobarRutas();