<?php

namespace Controllers;

use MVC\Router;
use Classes\Email;
use Model\Usuario;

class DashboardAdminController {
    public static function index(Router $router) {
        if(!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $router->render('admin/dashboard/index', [
            'titulo' => 'Panel de Administracion Admin'
        ], 'admin-layout');
    }

    public static function perfil(Router $router) {
        if (!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $admin = Usuario::find($_SESSION['id']);
        $admin->imagen_actual = $admin->imagen;
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Procesamiento de imagen
            $carpeta_imagenes = '../public/img/usuarios';
            $nombre_imagen = '';

            // Manejar nueva imagen
            if (!empty($_FILES['imagen']['tmp_name'])) {
                if (!is_dir($carpeta_imagenes)) {
                    mkdir($carpeta_imagenes, 0755, true);
                }

                // Generar nombre único
                $nombre_imagen = md5(uniqid(rand(), true));
                $_POST['imagen'] = $nombre_imagen;

                // Procesar la imagen en formatos PNG y WebP
                $imagen_origen = imagecreatefromstring(file_get_contents($_FILES['imagen']['tmp_name']));
                if ($imagen_origen) {
                    // Guardar en formato PNG
                    imagepng($imagen_origen, "$carpeta_imagenes/{$nombre_imagen}.png", 9);

                    // Guardar en formato WebP
                    imagewebp($imagen_origen, "$carpeta_imagenes/{$nombre_imagen}.webp", 85);

                    // Liberar memoria
                    imagedestroy($imagen_origen);
                }
            }

            // Manejar eliminación de imagen
            if (isset($_POST['eliminar_imagen']) && $_POST['eliminar_imagen'] === 'on') {
                if (!empty($admin->imagen_actual)) {
                    if (file_exists("$carpeta_imagenes/{$admin->imagen_actual}.png")) {
                        unlink("$carpeta_imagenes/{$admin->imagen_actual}.png");
                    }
                    if (file_exists("$carpeta_imagenes/{$admin->imagen_actual}.webp")) {
                        unlink("$carpeta_imagenes/{$admin->imagen_actual}.webp");
                    }
                }
                $_POST['imagen'] = '';
            }

            // Sincronizar con los datos del POST
            $admin->sincronizar($_POST);
            
            // Procesar preferencias de notificación
            $prefsData = $_POST['prefs'] ?? [];
            $preferenciasParaGuardar = [
                'notif_nuevo_reporte_email' => isset($prefsData['notif_nuevo_reporte_email']),
                'notif_nuevo_reporte_sistema' => isset($prefsData['notif_nuevo_reporte_sistema'])
            ];
            $admin->preferencias_notificaciones = json_encode($preferenciasParaGuardar);

            $alertas = $admin->validar_cuenta_dashboard();

            // Si hay alertas (del perfil O de las direcciones), las guardamos todas en la sesión
            if (!empty($alertas['error'])) {
                Usuario::setAlerta('error', implode('<br>', $alertas['error']));
            }

            if (empty($alertas)) {
                // Si no se subió nueva imagen y no se elimina, mantener la anterior
                if (empty($_FILES['imagen']['tmp_name']) && !isset($_POST['eliminar_imagen'])) {
                    $admin->imagen = $admin->imagen_actual;
                }

                $resultado = $admin->guardar();

                if ($resultado) {
                    // Actualizar datos de sesión
                    $_SESSION['imagen'] = $admin->imagen;

                    Usuario::setAlerta('exito', 'Perfil actualizado correctamente');
                }
            }
        }

        $router->render('admin/perfil/index', [
            'titulo' => 'Editar Perfil',
            'usuario' => $admin,
        ], 'admin-layout');
    }

    public static function cambiarPassword(Router $router) {
        if(!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $alertas = [];
        $usuario = Usuario::find($_SESSION['id']);

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevoPassword();

            if(empty($alertas)) {
                if($usuario->comprobar_password()) {
                    $usuario->pass = $usuario->password_nuevo;
                    $usuario->hashPassword();
                    $resultado = $usuario->guardar();

                    if($resultado) {
                        Usuario::setAlerta('exito', 'Password actualizado correctamente');
                        
                        // Enviar email de notificación de cambio
                        $email = new Email($usuario->email, $usuario->nombre, ''); // El token no es necesario aquí
                        $email->enviarNotificacionContraseña();
                        
                    }
                } else {
                    Usuario::setAlerta('error', 'El password actual es incorrecto');
                }
            }
        }

        $router->render('admin/perfil/cambiar-password', [
            'titulo' => 'Cambiar Password',
        ], 'admin-layout');
    }
}