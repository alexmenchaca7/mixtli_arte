<?php

namespace Controllers;

use MVC\Router;
use Classes\Email;
use Model\Usuario;
use Model\Categoria;
use Model\Autenticacion;
use Model\PreferenciaUsuario;
use Sonata\GoogleAuthenticator\GoogleAuthenticator;

class AuthController {
    public static function login(Router $router) {
        $inicio = true;
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarLogin();
            
            if(empty($alertas)) {
                // Verificar que el usuario exista
                $usuario = Usuario::where('email', $usuario->email);

                if(!$usuario || !$usuario->verificado ) {
                    Usuario::setAlerta('error', 'El usuario no existe o no esta verificado');
                } else {
                    // El Usuario existe
                    if( password_verify($_POST['pass'], $usuario->pass) ) {
                        // LÓGICA DE VERIFICACIÓN DE BLOQUEO
                        $estadoBloqueo = $usuario->estaBloqueado();
                        if ($estadoBloqueo['bloqueado']) {
                            if ($estadoBloqueo['tipo'] === 'permanente') {
                                header('Location: /bloqueado/permanente');
                                exit();
                            } elseif ($estadoBloqueo['tipo'] === 'temporal') {
                                // Guardar la fecha de fin de bloqueo en la sesión para mostrarla
                                $_SESSION['bloqueado_hasta'] = $estadoBloqueo['hasta'];
                                header('Location: /bloqueado/temporal');
                                exit();
                            }
                        }

                        // Si no está bloqueado, procede con el login normal...
                        if($usuario->verificado === "1") {
                            // Verificar si tiene 2FA activado
                            $usuario2fa = Autenticacion::findByUsuarioId($usuario->id);
                            
                            if($usuario2fa && $usuario2fa->auth_enabled) {
                                $_SESSION['usuario_2fa'] = $usuario->id;
                                $_SESSION['2fa_pending'] = true;
                                header('Location: /verificar-2fa');
                                exit();
                            } else {
                                // Iniciar sesión normalmente
                                self::iniciarSesion($usuario);
                            }
                        } else {
                            Usuario::setAlerta('error', 'Tu cuenta no ha sido confirmada. Revisa tu correo');
                        }
                    } else {
                        Usuario::setAlerta('error', 'Credenciales incorrectas');
                    }
                }
            }
        }

        $router->render('auth/login', [
            'titulo' => 'Iniciar Sesión',
            'inicio' => $inicio,
        ]);
    }


    public static function verificar2FA(Router $router) {
        if(!isset($_SESSION['2fa_pending'])) {
            header('Location: /login');
            exit();
        }

        $inicio = true;
        $alertas = [];
        $usuario = Usuario::find($_SESSION['usuario_2fa']);
        $usuario2fa = Autenticacion::findByUsuarioId($usuario->id);
        $g = new GoogleAuthenticator();

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo = $_POST['codigo'] ?? '';
            
            // Verificar código normal
            if($g->checkCode($usuario2fa->auth_secret, $codigo)) {
                unset($_SESSION['2fa_pending'], $_SESSION['usuario_2fa']);
                self::iniciarSesion($usuario);
            } 
            // Verificar código de respaldo
            elseif ($usuario2fa->verificarBackupCode($codigo)) {
                $usuario2fa->guardar();
                unset($_SESSION['2fa_pending'], $_SESSION['usuario_2fa']);
                self::iniciarSesion($usuario);
            } 
            else {
                Usuario::setAlerta('error', 'Código de verificación incorrecto');
            }
        }

        $router->render('auth/verificar-2fa', [
            'titulo' => 'Verificación en dos pasos',
            'inicio' => $inicio,
        ]);
    }

    

    private static function iniciarSesion($usuario) {
        session_start();
        $_SESSION['id'] = $usuario->id;
        $_SESSION['nombre'] = $usuario->nombre;
        $_SESSION['apellido'] = $usuario->apellido;
        $_SESSION['email'] = $usuario->email;
        $_SESSION['verificado'] = $usuario->verificado;
        $_SESSION['rol'] = $usuario->rol;
        $_SESSION['login'] = true;

        // Redirección según rol
        if($usuario->rol === 'comprador') {
            // Verificar si el comprador ya tiene preferencias guardadas
            $preferenciasExistentes = PreferenciaUsuario::where('usuarioId', $usuario->id);
            if(!$preferenciasExistentes) {
                // Si no tiene, redirigir a la página de selección de preferencias
                header('Location: /seleccionar-preferencias');
                exit();
            }
            header('Location: /marketplace');
        } elseif($usuario->rol === 'vendedor') {
            header('Location: /vendedor/dashboard');
        } elseif($usuario->rol === 'admin') {
            header('Location: /admin/dashboard');
        }
        exit();
    }


    public static function logout() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
        }

        // 1. Verificamos si hay un usuario autenticado en la sesión
        if (isset($_SESSION['id'])) {
            // 2. Buscamos al usuario en la base de datos
            $usuario = Usuario::find($_SESSION['id']);
            if ($usuario) {
                // 3. Forzamos su estado a "inactivo" actualizando last_active
                //    Lo ponemos 4 minutos en el pasado para asegurar que el sistema lo vea inactivo.
                $usuario->last_active = date('Y-m-d H:i:s', time() - 240);
                $usuario->guardar(); // Guardamos el cambio en la BD
            }
        }

        $_SESSION = [];
        session_destroy();
        header('Location: /');
        exit;
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
                    Usuario::setAlerta('exito', 'Hemos enviado las instrucciones a tu email');
                } else {
                    Usuario::setAlerta('error', 'El Usuario no existe o no esta verificado');
                }
            }
        }

        // Muestra la vista
        $router->render('auth/olvide', [
            'titulo' => 'Olvide mi Password',
            'inicio' => $inicio,
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
        
        // Muestra la vista
        $router->render('auth/reestablecer', [
            'titulo' => 'Reestablecer Password',
            'inicio' => $inicio,
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
        ]);
    }

    public static function establecerPassword(Router $router) {

        $inicio = true;
        $alertas = [];
        $token = isset($_GET['token']) ? $_GET['token'] : null;
        $token_valido = true;

        if(!$token) {
            Usuario::setAlerta('error', 'Token no válido');
            $token_valido = false;
        } else {
            $usuario = Usuario::where('token', $token);

            if(empty($usuario)) {
                Usuario::setAlerta('error', 'Token no válido');
                $token_valido = false;
            }
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarPassword();

            if($_POST['pass'] !== $_POST['pass2']) {
                Usuario::setAlerta('error', 'Los passwords no coinciden');
            }

            if(empty($alertas)) {
                $usuario->hashPassword();
                $usuario->verificado = 1; // Confirmar la cuenta
                $usuario->token = null;
                $resultado = $usuario->guardar();

                if($resultado) {
                    header('Location: /login');
                }
            }
        }

        $router->render('auth/establecer-password', [
            'titulo' => 'Establecer Password',
            'inicio' => $inicio,
            'token_valido' => $token_valido
        ]);
    }

    public static function seleccionarPreferencias(Router $router) {
        if(!is_auth('comprador')) {
            header('Location: /login');
            exit();
        }
        
        // Evitar que usuarios que ya tienen preferencias accedan a esta página
        if(PreferenciaUsuario::where('usuarioId', $_SESSION['id'])) {
            header('Location: /marketplace');
            exit();
        }

        $alertas = [];
        $categorias = Categoria::all(); // Obtener todas las categorías

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $categoriasSeleccionadas = $_POST['categorias'] ?? [];
            
            $preferencia = new PreferenciaUsuario([
                'usuarioId' => $_SESSION['id'],
                'categorias' => json_encode($categoriasSeleccionadas)
            ]);
            
            $resultado = $preferencia->guardar();

            if($resultado) {
                header('Location: /marketplace'); // Redirigir al marketplace tras guardar
                exit();
            } else {
                PreferenciaUsuario::setAlerta('error', 'Hubo un error al guardar tus preferencias. Inténtalo de nuevo.');
            }
        }
        
        $router->render('auth/preferencias', [
            'titulo' => 'Selecciona tus Intereses',
            'categorias' => $categorias,
        ]);
    }

    public static function heartbeat() {
        if(!is_auth()) {
            // Si no está autenticado, no hacer nada o devolver error
            http_response_code(401);
            echo json_encode(['status' => 'unauthorized']);
            return;
        }

        $usuario = Usuario::find($_SESSION['id']);
        if($usuario) {
            // Actualiza la marca de tiempo a la hora actual
            $usuario->last_active = date('Y-m-d H:i:s');
            $usuario->guardar();

            // Responde con éxito
            header('Content-Type: application/json');
            echo json_encode(['status' => 'ok']);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'user not found']);
        }
    }
}