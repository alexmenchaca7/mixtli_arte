<?php

namespace Controllers;

use Model\Autenticacion;
use MVC\Router;
use Model\Usuario;
use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;

class SeguridadController {
    public static function configurar2FA(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
            exit();
        }

        $usuario = Usuario::find($_SESSION['id']);
        $usuario2fa = Autenticacion::findByUsuarioId($usuario->id);
        $g = new GoogleAuthenticator();
        $alertas = [];
        $backupCodes = [];

        // Si no existe registro 2FA, crear uno
        if(!$usuario2fa) {
            $usuario2fa = new Autenticacion([
                'usuarioId' => $usuario->id,
                'auth_secret' => $g->generateSecret()
            ]);
            
            // Validar configuración inicial
            $alertas = $usuario2fa->validarConfiguracionInicial();
            
            if(empty($alertas['error'])) {
                $backupCodes = $usuario2fa->generarBackupCodes();
                $resultado = $usuario2fa->guardar();
                
                if(!$resultado) {
                    Usuario::setAlerta('error', 'Error al guardar la configuración 2FA');
                }
            }
        }

        // Procesar formulario
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(isset($_POST['activar'])) {
                $codigo = $_POST['codigo'] ?? '';
                
                // Validar y verificar código
                if($usuario2fa->verificarCodigo($codigo)) {
                    $usuario2fa->auth_enabled = 1;
                    $alertas = $usuario2fa->validarEstado();
                    
                    if(empty($alertas['error'])) {
                        $resultado = $usuario2fa->guardar();
                        if($resultado) {
                            Usuario::setAlerta('exito', '2FA activado correctamente');
                        } else {
                            Usuario::setAlerta('error', 'Error al activar 2FA');
                        }
                    }
                } else {
                    $alertas = $usuario2fa->getAlertas();
                    if(empty($alertas['error'])) {
                        Usuario::setAlerta('error', 'Código incorrecto');
                    }
                }
            } 
            elseif(isset($_POST['desactivar'])) {
                $usuario2fa->auth_enabled = 0;
                $alertas = $usuario2fa->validarEstado();
                
                if(empty($alertas['error'])) {
                    $resultado = $usuario2fa->guardar();
                    if($resultado) {
                        Usuario::setAlerta('exito', '2FA desactivado');
                    } else {
                        Usuario::setAlerta('error', 'Error al desactivar 2FA');
                    }
                }
            }
            elseif(isset($_POST['regenerar_backup'])) {
                $backupCodes = $usuario2fa->generarBackupCodes();
                $resultado = $usuario2fa->guardar();
                
                if($resultado) {
                    Usuario::setAlerta('exito', 'Códigos de respaldo regenerados');
                } else {
                    Usuario::setAlerta('error', 'Error al regenerar códigos');
                }
            }
            
            $alertas = array_merge($alertas, Usuario::getAlertas());
        }

        $qrUrl = GoogleQrUrl::generate(
            $usuario->email,
            $usuario2fa->auth_secret,
            'MixtliArte'
        );

        $router->render('seguridad/2fa', [
            'titulo' => 'Configurar 2FA',
            'usuario' => $usuario,
            'usuario2fa' => $usuario2fa,
            'qrUrl' => $qrUrl,
            'backupCodes' => $backupCodes ?: json_decode($usuario2fa->backup_codes, true),
            'alertas' => $alertas
        ], 'vendedor-layout');
    }
}