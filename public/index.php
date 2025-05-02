<?php

require_once __DIR__ . '/../includes/app.php'; 

use MVC\Router;
use Controllers\AuthController;
use Controllers\CategoriasController;
use Controllers\PaginasController;
use Controllers\MensajesController;
use Controllers\UsuariosController;
use Controllers\ProductosController;
use Controllers\SeguridadController;
use Controllers\MarketplaceController;
use Controllers\DashboardAdminController;
use Controllers\DashboardVendedorController;
use Controllers\FavoritosController;

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

// Establecer password
$router->get('/establecer-password', [AuthController::class, 'establecerPassword']);
$router->post('/establecer-password', [AuthController::class, 'establecerPassword']);

// Confirmación de Cuenta
$router->get('/mensaje', [AuthController::class, 'mensaje']);
$router->get('/confirmar-cuenta', [AuthController::class, 'confirmar']);

// Rutas de seguridad
$router->get('/seguridad/2fa', [SeguridadController::class, 'configurar2FA']);
$router->post('/seguridad/2fa', [SeguridadController::class, 'configurar2FA']);
$router->get('/verificar-2fa', [AuthController::class, 'verificar2FA']);
$router->post('/verificar-2fa', [AuthController::class, 'verificar2FA']);




// Pagina de Inicio (VISTA PRINCIPAL)
$router->get('/', [PaginasController::class, 'index']);
$router->get('/nosotros', [PaginasController::class, 'nosotros']);
$router->get('/contacto', [PaginasController::class, 'contacto']);




// Marketplace (VISTA COMPRADOR)
$router->get('/marketplace', [MarketplaceController::class, 'index']);
$router->get('/marketplace/autocompletar', [MarketplaceController::class, 'autocompletar']);
$router->get('/marketplace/producto', [MarketplaceController::class, 'producto']);

$router->get('/favoritos', [FavoritosController::class, 'index']);
$router->post('/favoritos/toggle', [FavoritosController::class, 'toggle']);

$router->get('/mensajes', [MensajesController::class, 'index']);
$router->post('/mensajes/enviar', [MensajesController::class, 'enviar']);
$router->post('/mensajes/upload', [MensajesController::class, 'subirArchivo']);
$router->get('/mensajes/chat', [MensajesController::class, 'chat']);



// Dashboard del Artesano (VISTA VENDEDOR)
$router->get('/vendedor/dashboard', [DashboardVendedorController::class, 'index']);

$router->get('/vendedor/productos', [ProductosController::class, 'index']);
$router->get('/vendedor/productos/crear', [ProductosController::class, 'crear']);
$router->post('/vendedor/productos/crear', [ProductosController::class, 'crear']);
$router->get('/vendedor/productos/editar', [ProductosController::class, 'editar']);
$router->post('/vendedor/productos/editar', [ProductosController::class, 'editar']);
$router->post('/vendedor/productos/eliminar', [ProductosController::class, 'eliminar']);

$router->get('/vendedor/perfil', [DashboardVendedorController::class, 'perfil']);
$router->post('/vendedor/perfil', [DashboardVendedorController::class, 'perfil']);

$router->get('/vendedor/cambiar-password', [DashboardVendedorController::class, 'cambiarPassword']);
$router->post('/vendedor/cambiar-password', [DashboardVendedorController::class, 'cambiarPassword']);

$router->get('/vendedor/mensajes', [MensajesController::class, 'index']);




// Dashboard del Admin (VISTA ADMINISTRADOR)
$router->get('/admin/dashboard', [DashboardAdminController::class, 'index']);

$router->get('/admin/usuarios', [UsuariosController::class, 'index']);
$router->get('/admin/usuarios/crear', [UsuariosController::class, 'crear']);
$router->post('/admin/usuarios/crear', [UsuariosController::class, 'crear']);
$router->get('/admin/usuarios/editar', [UsuariosController::class, 'editar']);
$router->post('/admin/usuarios/editar', [UsuariosController::class, 'editar']);
$router->post('/admin/usuarios/eliminar', [UsuariosController::class, 'eliminar']);

$router->get('/admin/productos', [ProductosController::class, 'index']);
$router->get('/admin/productos/crear', [ProductosController::class, 'crear']);
$router->post('/admin/productos/crear', [ProductosController::class, 'crear']);
$router->get('/admin/productos/editar', [ProductosController::class, 'editar']);
$router->post('/admin/productos/editar', [ProductosController::class, 'editar']);
$router->get('/admin/productos/eliminar', [ProductosController::class, 'eliminar']);

$router->get('/admin/categorias', [CategoriasController::class, 'index']);
$router->get('/admin/categorias/crear', [CategoriasController::class, 'crear']);
$router->post('/admin/categorias/crear', [CategoriasController::class, 'crear']);
$router->get('/admin/categorias/editar', [CategoriasController::class, 'editar']);
$router->post('/admin/categorias/editar', [CategoriasController::class, 'editar']);
$router->post('/admin/categorias/eliminar', [CategoriasController::class, 'eliminar']);




// Comprobar y validar que las rutas existan para asignarles las funciones del Controlador
$router->comprobarRutas();