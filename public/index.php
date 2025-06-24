<?php

require_once __DIR__ . '/../includes/app.php'; 

use MVC\Router;
use Controllers\AuthController;
use Controllers\FaqsController;
use Controllers\FollowController;
use Controllers\PaginasController;
use Controllers\MensajesController;
use Controllers\UsuariosController;
use Controllers\FavoritosController;
use Controllers\ProductosController;
use Controllers\SeguridadController;
use Controllers\CategoriasController;
use Controllers\MarketplaceController;
use Controllers\AdminSoporteController;
use Controllers\ValoracionesController;
use Controllers\DashboardAdminController;
use Controllers\AdminValoracionesController;
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

// Rutas para la selección de preferencias
$router->get('/seleccionar-preferencias', [AuthController::class, 'seleccionarPreferencias']);
$router->post('/seleccionar-preferencias', [AuthController::class, 'seleccionarPreferencias']);




// Pagina de Inicio (VISTA PRINCIPAL)
$router->get('/', [PaginasController::class, 'index']);
$router->get('/nosotros', [PaginasController::class, 'nosotros']);
$router->get('/contacto', [PaginasController::class, 'contacto']);
$router->post('/contacto', [PaginasController::class, 'contacto']);
$router->get('/faqs', [FaqsController::class, 'index']); 
$router->post('/faqs', [FaqsController::class, 'index']);




// Rutas en común para todos los usuarios
$router->post('/follow/toggle', [FollowController::class, 'toggle']);




// Marketplace (VISTA COMPRADOR)
$router->get('/marketplace', [MarketplaceController::class, 'index']);
$router->get('/marketplace/autocompletar', [MarketplaceController::class, 'autocompletar']);
$router->get('/marketplace/producto', [MarketplaceController::class, 'producto']);
$router->post('/producto/reportar', [MarketplaceController::class, 'reportarProducto']);

$router->get('/comprador/perfil', [MarketplaceController::class, 'perfil']);
$router->get('/comprador/perfil-publico', [MarketplaceController::class, 'compradorPublico']);
$router->get('/perfil', [MarketplaceController::class, 'vendedorPublico']);
$router->get('/comprador/perfil/editar', [MarketplaceController::class, 'editarPerfil']);
$router->post('/comprador/perfil/editar', [MarketplaceController::class, 'editarPerfil']);
$router->get('/comprador/perfil/cambiar-password', [MarketplaceController::class, 'cambiarPassword']);
$router->post('/comprador/perfil/cambiar-password', [MarketplaceController::class, 'cambiarPassword']);

$router->get('/favoritos', [FavoritosController::class, 'index']);
$router->post('/favoritos/toggle', [FavoritosController::class, 'toggle']);

$router->get('/mensajes', [MensajesController::class, 'index']);
$router->post('/mensajes/enviar', [MensajesController::class, 'enviar']);
$router->post('/mensajes/upload', [MensajesController::class, 'subirArchivo']);
$router->get('/mensajes/chat', [MensajesController::class, 'chat']);
$router->get('/mensajes/nuevos', [MensajesController::class, 'obtenerNuevosMensajes']);
$router->get('/mensajes/buscar', [MensajesController::class, 'buscarConversaciones']);
$router->get('/mensajes/lista-conversaciones', [MensajesController::class, 'obtenerListaConversaciones']);
$router->get('/mensajes/unread-count', [MensajesController::class, 'getUnreadCount']);
$router->post('/mensajes/marcar-vendido', [MensajesController::class, 'marcarVendido']);
$router->post('/api/heartbeat', [AuthController::class, 'heartbeat']);

$router->post('/valoraciones/guardar', [ValoracionesController::class, 'guardar']);
$router->post('/valoraciones/reportar', [ValoracionesController::class, 'reportar']);




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

$router->get('/vendedor/valoraciones', [DashboardVendedorController::class, 'valoraciones']);




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

$router->get('/admin/valoraciones', [AdminValoracionesController::class, 'index']);
$router->post('/admin/valoraciones/aprobar', [AdminValoracionesController::class, 'aprobar']);
$router->post('/admin/valoraciones/rechazar', [AdminValoracionesController::class, 'rechazar']);

$router->get('/admin/faqs', [FaqsController::class, 'adminIndex']);
$router->get('/admin/faqs/crear', [FaqsController::class, 'adminCrear']);
$router->post('/admin/faqs/crear', [FaqsController::class, 'adminCrear']);
$router->get('/admin/faqs/editar', [FaqsController::class, 'adminEditar']);
$router->post('/admin/faqs/editar', [FaqsController::class, 'adminEditar']);
$router->post('/admin/faqs/eliminar', [FaqsController::class, 'adminEliminar']);
$router->get('/admin/faqs/frequent-questions', [FaqsController::class, 'adminFrequentQuestions']); // To view frequent questions
$router->post('/admin/faqs/mark-frequent-reviewed', [FaqsController::class, 'adminMarkFrequentQuestionReviewed']); // To mark as reviewed/discarded
$router->post('/admin/faqs/convert-frequent', [FaqsController::class, 'adminConvertFrequentToFaq']); // To convert to a formal FAQ

$router->get('/admin/soporte', [AdminSoporteController::class, 'index']);
$router->get('/admin/soporte/ver', [AdminSoporteController::class, 'ver']);
$router->post('/admin/soporte/ver', [AdminSoporteController::class, 'ver']);
$router->post('/admin/soporte/eliminar', [AdminSoporteController::class, 'eliminar']);



// Comprobar y validar que las rutas existan para asignarles las funciones del Controlador
$router->comprobarRutas();