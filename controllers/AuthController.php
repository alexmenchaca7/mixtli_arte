<?php

namespace Controllers;

use MVC\Router;
use Classes\Email;
use Model\Usuario;

class AuthController {
    public static function login(Router $router) {

        $inicio = true;
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
    
            $usuario = new Usuario($_POST);

            $alertas = $usuario->validarLogin();
            
            if(empty($alertas)) {

                // Verificar quel el usuario exista
                $usuario = Usuario::where('email', $usuario->email);
                if(!$usuario || !$usuario->verificado ) {
                    Usuario::setAlerta('error', 'El usuario no existe o no esta verificado');
                } else {

                    // El Usuario existe
                    if( password_verify($_POST['pass'], $usuario->pass) ) {
                        
                        // Iniciar la sesión
                        session_start();    
                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre;
                        $_SESSION['apellido'] = $usuario->apellido;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['rol'] = $usuario->rol;

                        // Redirección
                        if($usuario->rol === 'comprador') {
                            header('Location: /marketplace');
                        } else if($usuario->rol === 'vendedor') {
                            header('Location: /marketplace');
                        } else if($usuario->rol === 'admin') {
                            header('Location: /dashboard');
                        }
                        
                    } else {
                        Usuario::setAlerta('error', 'Credenciales incorrectas');
                    }
                }
            }
        }

        $alertas = Usuario::getAlertas();
        
        // Render a la vista 
        $router->render('auth/login', [
            'titulo' => 'Iniciar Sesión',
            'inicio' => $inicio,
            'alertas' => $alertas
        ]);
    }

    public static function logout() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            $_SESSION = [];
            header('Location: /');
        }
       
    }

    public static function registro(Router $router) {

        $inicio = true;
        $alertas = [];
        $usuario = new Usuario;

        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            $usuario->sincronizar($_POST);
            
            $alertas = $usuario->validar_cuenta();

            if(empty($alertas)) {
                $existeUsuario = Usuario::where('email', $usuario->email);

                if($existeUsuario) {
                    Usuario::setAlerta('error', 'El usuario ya esta registrado');
                    $alertas = Usuario::getAlertas();
                } else {
                    // Hashear el password
                    $usuario->hashPassword();

                    // Eliminar pass2
                    unset($usuario->pass2);

                    // Generar el Token
                    $usuario->crearToken();

                    // Crear un nuevo usuario
                    $resultado =  $usuario->guardar();

                    // Enviar email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarConfirmacion();
                    

                    if($resultado) {
                        header('Location: /mensaje');
                    }
                }
            }
        }

        // Render a la vista
        $router->render('auth/registro', [
            'titulo' => 'Crea tu cuenta en MixtliArte',
            'inicio' => $inicio,
            'usuario' => $usuario, 
            'alertas' => $alertas,
            'fecha_hoy' => date('Y-m-d')
        ]);
    }

    public static function olvide(Router $router) {

        $inicio = true;
        $alertas = [];
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if(empty($alertas)) {
                // Buscar el usuario
                $usuario = Usuario::where('email', $usuario->email);

                if($usuario && $usuario->verificado) {

                    // Generar un nuevo token
                    $usuario->crearToken();
                    unset($usuario->pass2);

                    // Actualizar el usuario
                    $usuario->guardar();

                    // Enviar el email
                    $email = new Email( $usuario->email, $usuario->nombre, $usuario->token );
                    $email->enviarInstrucciones();


                    // Imprimir la alerta
                    // Usuario::setAlerta('exito', 'Hemos enviado las instrucciones a tu email');

                    $alertas['exito'][] = 'Hemos enviado las instrucciones a tu email';
                } else {
                 
                    // Usuario::setAlerta('error', 'El Usuario no existe o no esta verificado');

                    $alertas['error'][] = 'El usuario no existe o no esta verificado';
                }
            }
        }

        // Muestra la vista
        $router->render('auth/olvide', [
            'titulo' => 'Olvide mi Password',
            'inicio' => $inicio,
            'alertas' => $alertas
        ]);
    }

    public static function reestablecer(Router $router) {


        $inicio = true;
        $token_valido = true;
        $token = s($_GET['token']);

        if(!$token) header('Location: /login');

        // Identificar el usuario con este token
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)) {
            Usuario::setAlerta('error', 'Token no válido, intenta de nuevo');
            $token_valido = false;
        }


        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Añadir el nuevo password
            $usuario->sincronizar($_POST);

            // Validar el password
            $alertas = $usuario->validarPassword();

            if(empty($alertas)) {
                // Hashear el nuevo password
                $usuario->hashPassword();

                // Eliminar el Token
                $usuario->token = null;

                // Guardar el usuario en la BD
                $resultado = $usuario->guardar();

                // Redireccionar
                if($resultado) {
                    header('Location: /login');
                }
            }
        }

        $alertas = Usuario::getAlertas();
        
        // Muestra la vista
        $router->render('auth/reestablecer', [
            'titulo' => 'Reestablecer Password',
            'inicio' => $inicio,
            'alertas' => $alertas,
            'token_valido' => $token_valido
        ]);
    }

    public static function mensaje(Router $router) {

        $inicio = true;

        $router->render('auth/mensaje', [
            'titulo' => 'Cuenta Creada Exitosamente',
            'inicio' => $inicio
        ]);
    }

    public static function confirmar(Router $router) {

        $inicio = true;
        $token = s($_GET['token']);

        if(!$token) header('Location: /');

        // Encontrar al usuario con este token
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)) {
            // No se encontró un usuario con ese token
            Usuario::setAlerta('error', 'Token no válido, la cuenta no se confirmó');
        } else {
            // Confirmar la cuenta
            $usuario->verificado = 1;
            $usuario->token = '';
            unset($usuario->pass2);
            
            // Guardar en la BD
            $usuario->guardar();

            Usuario::setAlerta('exito', 'Cuenta comprobada éxitosamente');
        }

     

        $router->render('auth/confirmar', [
            'titulo' => 'Confirma tu cuenta MixtliArte',
            'inicio' => $inicio,
            'alertas' => Usuario::getAlertas()
        ]);
    }
}