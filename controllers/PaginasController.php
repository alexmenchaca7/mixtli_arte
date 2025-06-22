<?php

namespace Controllers;
use MVC\Router;
use Classes\Email;
use Model\Soporte;

class PaginasController {
    public static function index(Router $router) {
        
        $inicio = true;

        $router->render('paginas/index', [
            'titulo' => 'Inicio',
            'inicio' => $inicio
        ]);
    }

    public static function nosotros(Router $router) {

        $inicio = true;

        $router->render('paginas/nosotros', [
            'inicio' => $inicio
        ]);
    }

    public static function contacto(Router $router) {

        $inicio = false; // Initialize $inicio here, it's typically 'false' for internal pages
        $alertas = [];
        $consulta = new Soporte(); // Instanciar el nuevo modelo

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $consulta->sincronizar($_POST);
            $alertas = $consulta->validar();

            if(empty($alertas)) {
                $consulta->generarNumeroCaso(); // Generar número de caso único

                $resultado = $consulta->guardar(); // Guardar en la base de datos

                if($resultado) {
                    // Enviar email de confirmación al usuario
                    $emailUsuario = new Email($consulta->email, 'Usuario MixtliArte', $consulta->numero_caso);
                    $emailUsuario->enviarConfirmacionSoporteUsuario($consulta->numero_caso, $consulta->asunto);

                    // Enviar email al equipo de soporte
                    $emailSoporte = new Email($_ENV['EMAIL_ADMIN_SUPPORT'], 'Equipo de Soporte MixtliArte', $consulta->numero_caso);
                    $emailSoporte->enviarNotificacionSoporteAdmin(
                        $consulta->email,
                        $consulta->asunto,
                        $consulta->mensaje,
                        $consulta->numero_caso
                    );

                    Soporte::setAlerta('exito', 'Tu consulta ha sido enviada. Se te ha enviado un correo con el número de caso: ' . $consulta->numero_caso);
                    $consulta = new Soporte(); // Limpiar el formulario
                } else {
                    Soporte::setAlerta('error', 'Hubo un error al enviar tu consulta. Por favor, inténtalo de nuevo.');
                }
            }
        }

        $alertas = Soporte::getAlertas();
        
        // Lógica para determinar el layout
        $layout = 'layout'; // Layout por defecto (for guests and general public)
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['rol'])) {
            if ($_SESSION['rol'] === 'comprador') {
                $layout = 'layout'; // The main marketplace layout is already 'layout.php'
            } elseif ($_SESSION['rol'] === 'vendedor') {
                $layout = 'vendedor-layout'; // Use the specific vendor layout
            } elseif ($_SESSION['rol'] === 'admin') {
                $layout = 'admin-layout'; // Use the specific admin layout
            }
        }

        $router->render('paginas/contacto', [
            'titulo' => 'Contacto',
            'inicio' => $inicio, // This will now always be defined
            'alertas' => $alertas,
            'consulta' => $consulta // Pasar la instancia para mantener los datos en caso de error
        ], $layout); // Usar la variable $layout aquí
    }
}