<?php

namespace Controllers;

use MVC\Router;
use Classes\Email;
use Model\Usuario;
use Model\Direccion;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class DashboardVendedorController {
    public static function index(Router $router) {
        if(!is_auth('vendedor')) {
            header('Location: /login');
            exit();
        }

        $router->render('vendedor/dashboard/index', [
            'titulo' => 'Panel de Administracion Vendedor'
        ], 'vendedor-layout');
    }

    public static function perfil(Router $router) {
        if (!is_auth('vendedor')) {
            header('Location: /login');
            exit();
        }

        $vendedor = Usuario::find($_SESSION['id']);
        $vendedor->imagen_actual = $vendedor->imagen;
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
                if (!empty($vendedor->imagen_actual)) {
                    if (file_exists("$carpeta_imagenes/{$vendedor->imagen_actual}.png")) {
                        unlink("$carpeta_imagenes/{$vendedor->imagen_actual}.png");
                    }
                    if (file_exists("$carpeta_imagenes/{$vendedor->imagen_actual}.webp")) {
                        unlink("$carpeta_imagenes/{$vendedor->imagen_actual}.webp");
                    }
                }
                $_POST['imagen'] = '';
            }

            // Sincronizar con los datos del POST
            $vendedor->sincronizar($_POST);

            // Validación
            $alertas = $vendedor->validar_cuenta_dashboard();

            if (empty($alertas)) {
                // Si no se subió nueva imagen y no se elimina, mantener la anterior
                if (empty($_FILES['imagen']['tmp_name']) && !isset($_POST['eliminar_imagen'])) {
                    $vendedor->imagen = $vendedor->imagen_actual;
                }

                $resultado = $vendedor->guardar();

                if ($resultado) {
                    // Actualizar datos de sesión
                    $_SESSION['imagen'] = $vendedor->imagen;

                    Usuario::setAlerta('exito', 'Perfil actualizado correctamente');
                    $alertas = Usuario::getAlertas();
                }
            }
        }

        $router->render('vendedor/perfil/index', [
            'titulo' => 'Editar Perfil',
            'usuario' => $vendedor,
            'alertas' => $alertas
        ], 'vendedor-layout');
    }

    public static function cambiarPassword(Router $router) {
        if(!is_auth('vendedor')) {
            header('Location: /login');
            exit();
        }

        $alertas = [];
        $usuario = Usuario::find($_SESSION['id']);

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->nuevo_password();

            if(empty($alertas)) {
                // Verificar que el password actual sea correcto
                if($usuario->comprobar_password()) {
                    // Asignar el nuevo password
                    $usuario->pass = $usuario->password_nuevo;
                    // Hashear el nuevo password
                    $usuario->hashPassword();
                    
                    // Guardar en la BD
                    $resultado = $usuario->guardar();

                    if($resultado) {
                        Usuario::setAlerta('exito', 'Password actualizado correctamente');
                        $alertas = Usuario::getAlertas();
                        
                        // Enviar email de notificación
                        $email = new Email($usuario->email, $usuario->nombre, '');
                        $email->enviarNotificacionContraseña();
                    }
                } else {
                    Usuario::setAlerta('error', 'El password actual es incorrecto');
                    $alertas = Usuario::getAlertas();
                }
            }
        }

        $router->render('vendedor/perfil/cambiar-password', [
            'titulo' => 'Cambiar Password',
            'alertas' => $alertas
        ], 'vendedor-layout');
    }
}