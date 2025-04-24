<?php

namespace Controllers;

use MVC\Router;
use Classes\Email;
use Model\Usuario;
use Model\Direccion;
use Classes\Paginacion;
use Intervention\Image\ImageManagerStatic as Image;

class UsuariosController {
    public static function index(Router $router) {
        if(!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        // Obtener término de búsqueda si existe
        $busqueda = $_GET['busqueda'] ?? '';
        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;

        if($pagina_actual < 1) {
            header('Location: /admin/usuarios?page=1');
            exit();
        }

        $registros_por_pagina = 10;
        $condiciones = [];

        if(!empty($busqueda)) {
            $condiciones = Usuario::buscar($busqueda);
        }

        // Obtener total de registros
        $total = Usuario::totalCondiciones($condiciones);
        
        // Crear instancia de paginación
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);
        
        if ($paginacion->total_paginas() < $pagina_actual && $pagina_actual > 1) {
            header('Location: /admin/usuarios?page=1');
            exit();
        }

        // Obtener usuarios
        $params = [
            'condiciones' => $condiciones,
            'orden' => 'nombre ASC',
            'limite' => $registros_por_pagina,
            'offset' => $paginacion->offset()
        ];
        
        $usuarios = Usuario::metodoSQL($params);

        // Pasar los usuarios a la vista
        $router->render('admin/usuarios/index', [
            'titulo' => 'Usuarios',
            'usuarios' => $usuarios,
            'paginacion' => $paginacion->paginacion(),
            'busqueda' => $busqueda
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

            $carpeta_imagenes = '../public/img/usuarios';
            $nombre_imagen = '';

            // Leer la imagen
            if(!empty($_FILES['imagen']['tmp_name'])) {

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
                    // Guardar las imagenes solo si se subió una imagen
                    if(!empty($_FILES['imagen']['tmp_name'])) {
                        $imagen_png->save($carpeta_imagenes . '/' . $nombre_imagen . '.png');
                        $imagen_webp->save($carpeta_imagenes . '/' . $nombre_imagen . '.webp');
                    }

                    // Generar el Token
                    $usuario->crearToken();

                    // Guardar en la BD
                    $resultado =  $usuario->guardar();

                    if($resultado) {
                        // Procesar direcciones
                        if(in_array($usuario->rol, ['comprador', 'vendedor'])) {
                            // Dirección residencial
                            if(!empty($_POST['calle_residencial'])) {
                                (new Direccion([
                                    'tipo' => 'residencial',
                                    'calle' => $_POST['calle_residencial'],
                                    'colonia' => $_POST['colonia_residencial'],
                                    'ciudad' => $_POST['ciudad_residencial'],
                                    'estado' => $_POST['estado_residencial'],
                                    'codigo_postal' => $_POST['codigo_postal_residencial'],
                                    'usuarioId' => $usuario->id
                                ]))->guardar();
                            }

                            // Dirección comercial (solo vendedores)
                            if($usuario->rol === 'vendedor' && !empty($_POST['calle_comercial'])) {
                                (new Direccion([
                                    'tipo' => 'comercial',
                                    'calle' => $_POST['calle_comercial'],
                                    'colonia' => $_POST['colonia_comercial'],
                                    'ciudad' => $_POST['ciudad_comercial'],
                                    'estado' => $_POST['estado_comercial'],
                                    'codigo_postal' => $_POST['codigo_postal_comercial'],
                                    'usuarioId' => $usuario->id
                                ]))->guardar();
                            }
                        }
                        
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

        // Obtener direcciones existentes
        $direcciones = Direccion::whereField('usuarioId', $usuario->id);

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
                    // Eliminar direcciones existentes
                    Direccion::eliminarPorUsuario($usuario->id);

                    // Guardar nuevas direcciones si es comprador/vendedor
                    if(in_array($usuario->rol, ['comprador', 'vendedor'])) {
                        // Dirección residencial
                        if(!empty($_POST['calle_residencial'])) {
                            (new Direccion([
                                'tipo' => 'residencial',
                                'calle' => $_POST['calle_residencial'],
                                'colonia' => $_POST['colonia_residencial'],
                                'ciudad' => $_POST['ciudad_residencial'],
                                'estado' => $_POST['estado_residencial'],
                                'codigo_postal' => $_POST['codigo_postal_residencial'],
                                'usuarioId' => $usuario->id
                            ]))->guardar();
                        }

                        // Dirección comercial (solo vendedores)
                        if($usuario->rol === 'vendedor' && !empty($_POST['calle_comercial'])) {
                            (new Direccion([
                                'tipo' => 'comercial',
                                'calle' => $_POST['calle_comercial'],
                                'colonia' => $_POST['colonia_comercial'],
                                'ciudad' => $_POST['ciudad_comercial'],
                                'estado' => $_POST['estado_comercial'],
                                'codigo_postal' => $_POST['codigo_postal_comercial'],
                                'usuarioId' => $usuario->id
                            ]))->guardar();
                        }
                    }

                    header('Location: /admin/usuarios');
                }
            }
        }

        // Pasar los usuarios a la vista
        $router->render('admin/usuarios/editar', [
            'titulo' => 'Editar Usuario',
            'alertas' => $alertas,
            'usuario' => $usuario,
            'fecha_hoy' => date('Y-m-d'),
            'direcciones' => $direcciones
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

            // Eliminando todas las direcciones asociadas
            Direccion::eliminarPorUsuario($usuario->id);

            // Eliminando las imagenes del servidor
            if ($usuario->imagen) {
                $carpeta_imagenes = '../public/img/usuarios';
                unlink($carpeta_imagenes . '/' . $usuario->imagen . ".png");
                unlink($carpeta_imagenes . '/' . $usuario->imagen . ".webp");
            }

            // Eliminando al usuario
            $resultado = $usuario->eliminar();

            if($resultado) {
                header('Location: /admin/usuarios');
            }
        }
    }
}
