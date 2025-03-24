<?php

namespace Controllers;

use MVC\Router;
use Classes\Email;
use Model\Usuario;

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

            $usuario->sincronizar($_POST);
            $alertas = $usuario->validar_cuenta_dashboard();

            if(empty($alertas)) {
                $existeUsuario = Usuario::where('email', $usuario->email);

                if($existeUsuario) {
                    Usuario::setAlerta('error', 'El usuario ya esta registrado');
                    $alertas = Usuario::getAlertas();
                } else {
                    // Generar el Token
                    $usuario->crearToken();

                    // Crear un nuevo usuario
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

        $usuario = Usuario::find($id);

        if(!$usuario) {
            header('Location: /admin/usuarios');
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_auth('admin')) {
                header('Location: /login');
                exit();
            }

            $usuario->sincronizar($_POST);
            $alertas = $usuario->validar_cuenta_dashboard();

            if(empty($alertas)) {
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

            $resultado = $usuario->eliminar();

            if($resultado) {
                header('Location: /admin/usuarios');
            }
        }
    }
}
