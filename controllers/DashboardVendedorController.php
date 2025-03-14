<?php

namespace Controllers;

use Model\Usuario;
use MVC\Router;

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
        if(!is_auth('vendedor')) {
            header('Location: /login');
            exit();
        }
        
        $vendedor = Usuario::find($_SESSION['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Asignar los valores
            $args = $_POST['vendedor'] ?? [];

            // Sincronizar objeto en memoria con lo que el usuario escribió
            $vendedor->sincronizar($args);

            // Validación
            $errores = $vendedor->validar();

            if (empty($errores)) {
                // Guardar en la base de datos
                $vendedor->guardar();
            }
        }

        $router->render('vendedor/perfil/index', [
            'titulo' => 'Editar Perfil',
            'vendedor' => $vendedor
        ], 'vendedor-layout');
    }

    public static function editarTelefono(Router $router) {
        if(!is_auth('vendedor')) {
            header('Location: /login');
            exit();
        }

        $vendedor = Usuario::find($_SESSION['id']);
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $telefono = $_POST['telefono'];
            $vendedor->telefono = $telefono;

            // Validar el teléfono
            if (empty($telefono)) {
                Usuario::setAlerta('error', 'El teléfono es obligatorio');
            } else {
                // Guardar el teléfono
                $vendedor->guardar();
                header('Location: /vendedor/perfil');
                exit();
            }

            $alertas = Usuario::getAlertas();
        }

        $router->render('vendedor/perfil/editar-telefono', [
            'titulo' => 'Editar Teléfono',
            'vendedor' => $vendedor,
            'alertas' => $alertas
        ], 'vendedor-layout');
    }
}