<?php

namespace Controllers;

use MVC\Router;
use Classes\Email;
use Model\Usuario;
use Intervention\Image\ImageManagerStatic as Image;

class UsuariosController {
    public static function index(Router $router) {
        if(!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        // Obtener todos los usuarios
        $usuarios = Usuario::all();

        // Pasar los usuarios a la vista
        $router->render('admin/usuarios/index', [
            'titulo' => 'Usuarios',
            'usuarios' => $usuarios
        ], 'admin-layout');
    }

    public static function crear(Router $router) {
        if(!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $usuario = new Usuario();
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_auth('admin')) {
                header('Location: /login');
                exit();
            }

            // Leer la imagen
            if(!empty($_FILES['imagen']['tmp_name'])) {
                
                $carpeta_imagenes = '../public/img/usuarios';

                // Crear la carpeta si no existe
                if(!is_dir($carpeta_imagenes)) {
                    mkdir($carpeta_imagenes, 0755, true);
                }

                $imagen_png = Image::make($_FILES['imagen']['tmp_name'])->encode('png', 90);
                $imagen_webp = Image::make($_FILES['imagen']['tmp_name'])->encode('webp', 90);

                // Generar nombre aleatorio
                $nombre_imagen = md5(uniqid(rand(), true));

                $_POST['imagen'] = $nombre_imagen;
            } 

            $usuario->sincronizar($_POST);

            // Validar formulario
            $alertas = $usuario->validar_cuenta_dashboard();

            // Guardar registro
            if(empty($alertas)) {
                $existeUsuario = Usuario::where('email', $usuario->email);

                if($existeUsuario) {
                    Usuario::setAlerta('error', 'El usuario ya esta registrado');
                    $alertas = Usuario::getAlertas();
                } else {
                    // Guardar las imagenes
                    $imagen_png->save($carpeta_imagenes . '/' . $nombre_imagen . '.png');
                    $imagen_webp->save($carpeta_imagenes . '/' . $nombre_imagen . '.webp');

                    // Generar el Token
                    $usuario->crearToken();

                    // Guardar en la BD
                    $resultado =  $usuario->guardar();

                    if($resultado) {
                        // Enviar el email de configuración de contraseña
                        $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                        $email->enviarConfirmacionContraseña();

                        // Redirigir a la vista de confirmación
                        header('Location: /admin/usuarios/crear?confirmacion=1');
                        exit();
                    }
                }
            }
        }

        // Pasar los usuarios a la vista
        $router->render('admin/usuarios/crear', [
            'titulo' => 'Registrar Usuario',
            'alertas' => $alertas,
            'usuario' => $usuario,
            'fecha_hoy' => date('Y-m-d')
        ], 'admin-layout');
    }

    public static function editar(Router $router) {
        if(!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $alertas = [];
        $id = $_GET['id'];
        $id = filter_var($id, FILTER_VALIDATE_INT);

        if(!$id) {
            header('Location: /admin/usuarios');
        }

        // Obtener usuario a editar
        $usuario = Usuario::find($id);

        if(!$usuario) {
            header('Location: /admin/usuarios');
        }

        $usuario->imagen_actual = $usuario->imagen;

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_auth('admin')) {
                header('Location: /login');
                exit();
            }

            // Capturar imagen actual antes de sincronizar
            $imagenAnterior = $usuario->imagen;
            $carpeta_imagenes = '../public/img/usuarios';

            // Manejo de nueva imagen
            if(!empty($_FILES['imagen']['tmp_name'])) {
                // Procesar nueva imagen
                $imagen_png = Image::make($_FILES['imagen']['tmp_name'])->encode('png', 90);
                $imagen_webp = Image::make($_FILES['imagen']['tmp_name'])->encode('webp', 90);
                
                // Generar nombre único
                $nombre_imagen = md5(uniqid(rand(), true));
                
                // Eliminar imagen anterior si existe
                if(!empty($imagenAnterior)) {
                    if(file_exists("$carpeta_imagenes/$imagenAnterior.png")) {
                        unlink("$carpeta_imagenes/$imagenAnterior.png");
                    }
                    if(file_exists("$carpeta_imagenes/$imagenAnterior.webp")) {
                        unlink("$carpeta_imagenes/$imagenAnterior.webp");
                    }
                }
                
                // Guardar nuevas imágenes
                $imagen_png->save("$carpeta_imagenes/$nombre_imagen.png");
                $imagen_webp->save("$carpeta_imagenes/$nombre_imagen.webp");
                
                // Asignar nuevo nombre de imagen
                $_POST['imagen'] = $nombre_imagen;
            } 
            
            // Manejar eliminación de imagen
            if(isset($_POST['eliminar_imagen']) && $_POST['eliminar_imagen'] === 'on') {
                if(!empty($imagenAnterior)) {
                    if(file_exists("$carpeta_imagenes/$imagenAnterior.png")) {
                        unlink("$carpeta_imagenes/$imagenAnterior.png");
                    }
                    if(file_exists("$carpeta_imagenes/$imagenAnterior.webp")) {
                        unlink("$carpeta_imagenes/$imagenAnterior.webp");
                    }
                }
                $_POST['imagen'] = ''; // Limpiar el campo en la base de datos
            }

            $usuario->sincronizar($_POST);
            $alertas = $usuario->validar_cuenta_dashboard();

            if(empty($alertas)) {
                // Si no se subió nueva imagen y no se elimina, mantener la anterior
                if(empty($_FILES['imagen']['tmp_name']) && !isset($_POST['eliminar_imagen'])) {
                    $usuario->imagen = $imagenAnterior;
                }

                $resultado = $usuario->guardar();

                if($resultado) {
                    header('Location: /admin/usuarios');
                }
            }
        }

        // Pasar los usuarios a la vista
        $router->render('admin/usuarios/editar', [
            'titulo' => 'Editar Usuario',
            'alertas' => $alertas,
            'usuario' => $usuario,
            'fecha_hoy' => date('Y-m-d')
        ], 'admin-layout');
    }

    public static function eliminar() {
        if(!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_auth('admin')) {
                header('Location: /login');
                exit();
            }

            $id = $_POST['id'];
            $usuario = Usuario::find($id);

            if(!isset($usuario)) {
                header('Location: /admin/usuarios');
            }

            // Eliminando las imagenes del servidor
            if ($usuario->imagen) {
                $carpeta_imagenes = '../public/img/usuarios';
                unlink($carpeta_imagenes . '/' . $usuario->imagen . ".png");
                unlink($carpeta_imagenes . '/' . $usuario->imagen . ".webp");
            }

            $resultado = $usuario->eliminar();

            if($resultado) {
                header('Location: /admin/usuarios');
            }
        }
    }
}
