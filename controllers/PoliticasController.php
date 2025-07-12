<?php

namespace Controllers;
use MVC\Router;

class PoliticasController {
    public static function privacidad(Router $router) {
        // Si el usuario NO está autenticado, $inicio será true.
        // Si SÍ está autenticado, $inicio será false.
        $inicio = !is_auth(); 

        // Lógica para determinar el layout
        $layout = 'layout'; 

        if (isset($_SESSION['rol'])) {
            if ($_SESSION['rol'] === 'vendedor') {
                $layout = 'vendedor-layout';
            } elseif ($_SESSION['rol'] === 'admin') {
                $layout = 'admin-layout';
            }
        }

        $router->render('paginas/politicas/privacidad', [
            'titulo' => 'Política de Privacidad',
            'inicio' => $inicio
        ], $layout);
    }

    public static function terminos(Router $router) {
        // Si el usuario NO está autenticado, $inicio será true.
        // Si SÍ está autenticado, $inicio será false.
        $inicio = !is_auth(); 

        // Lógica para determinar el layout
        $layout = 'layout'; 

        if (isset($_SESSION['rol'])) {
            if ($_SESSION['rol'] === 'vendedor') {
                $layout = 'vendedor-layout';
            } elseif ($_SESSION['rol'] === 'admin') {
                $layout = 'admin-layout';
            }
        }

        $router->render('paginas/politicas/terminos', [
            'titulo' => 'Términos y Condiciones',
            'inicio' => $inicio
        ], $layout);
    }
}