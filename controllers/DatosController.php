<?php

namespace Controllers;

use MVC\Router;
use Model\Follow;
use Classes\Email;
use Model\Mensaje;
use Model\Usuario;
use Model\Favorito;
use Model\Producto;
use Model\Direccion;
use Model\Valoracion;
use Model\ImagenProducto;
use Model\ReporteProducto;
use Model\PreferenciaUsuario;
use Model\HistorialInteraccion;

class DatosController {

    public static function exportarDatos() {
        if (!is_auth()) {
            header('Location: /login');
            exit();
        }

        $usuarioId = $_SESSION['id'];
        
        // Recopilar todos los datos del usuario
        $datosUsuario = [
            'perfil' => Usuario::find($usuarioId)->toArray(),
            'direcciones' => array_map(fn($d) => $d->toArray(), Direccion::whereField('usuarioId', $usuarioId)),
            'productos' => array_map(fn($p) => $p->toArray(), Producto::whereField('usuarioId', $usuarioId)),
            'valoraciones_emitidas' => array_map(fn($v) => $v->toArray(), Valoracion::whereField('calificadorId', $usuarioId)),
            'valoraciones_recibidas' => array_map(fn($v) => $v->toArray(), Valoracion::whereField('calificadoId', $usuarioId)),
            'favoritos' => array_map(fn($f) => $f->toArray(), Favorito::whereField('usuarioId', $usuarioId)),
            'preferencias' => PreferenciaUsuario::where('usuarioId', $usuarioId)->toArray(),
            'historial_interacciones' => array_map(fn($h) => $h->toArray(), HistorialInteraccion::whereField('usuarioId', $usuarioId))
        ];

        // Forzar la descarga del archivo JSON
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="datos_mixtliarte_' . $usuarioId . '.json"');
        
        echo json_encode($datosUsuario, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }

    public static function solicitarEliminacion() {
        if (!is_auth()) {
            header('Location: /login');
            exit();
        }

        $usuario = Usuario::find($_SESSION['id']);
        
        // Generar token único y fecha de expiración (p. ej., 1 hora)
        $usuario->token_eliminacion = uniqid();
        $usuario->token_eliminacion_expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        if ($usuario->guardar()) {
            // Enviar email de confirmación
            $email = new Email($usuario->email, $usuario->nombre, $usuario->token_eliminacion);
            $email->enviarConfirmacionEliminacion(); // Necesitarás crear este método en la clase Email
            
            Usuario::setAlerta('exito', 'Se ha enviado un correo electrónico para confirmar la eliminación de tu cuenta.');

            // Redirección dinámica basada en el rol del usuario
            $redirectUrl = '/'; // URL por defecto
            if (isset($_SESSION['rol'])) {
                switch ($_SESSION['rol']) {
                    case 'vendedor': // Rol de Vendedor
                        $redirectUrl = '/vendedor/perfil';
                        break;
                    case 'comprador': // Rol de Comprador
                        $redirectUrl = '/comprador/perfil/editar';
                        break;
                    default: // Otros roles o si no hay rol
                        $redirectUrl = '/';
                        break;
                }
            }

            header('Location: ' . $redirectUrl);
            exit();
        }
    }

    public static function confirmarEliminacion(Router $router) {
        $token = s($_GET['token']);
        if (!$token) {
            header('Location: /');
            exit();
        }

        $usuario = Usuario::where('token_eliminacion', $token);

        if (!$usuario || new \DateTime() > new \DateTime($usuario->token_eliminacion_expira)) {
            Usuario::setAlerta('error', 'Token no válido o ha expirado. Por favor, solicita la eliminación de nuevo.');
        } else {
            // Eliminar todos los datos asociados
            Favorito::eliminarPorUsuario($usuario->id); 
            Mensaje::eliminarPorUsuario($usuario->id); 
            PreferenciaUsuario::eliminarPorUsuario($usuario->id); 
            Producto::eliminarPorUsuario($usuario->id); 
            ImagenProducto::eliminarPorUsuario($usuario->id); 
            ReporteProducto::eliminarPorUsuario($usuario->id); 
            Valoracion ::eliminarPorUsuario($usuario->id); 
            Follow::eliminarPorUsuario($usuario->id); 
            Direccion::eliminarPorUsuario($usuario->id);
            
            // Eliminar al usuario
            $usuario->eliminar();

            // Cerrar sesión y redirigir
            session_destroy();
            header('Location: /?mensaje=cuenta-eliminada');
            exit();
        }

        $router->render('auth/confirmar-eliminacion', [
            'titulo' => 'Confirmar Eliminación',
            'alertas' => Usuario::getAlertas()
        ]);
    }
}