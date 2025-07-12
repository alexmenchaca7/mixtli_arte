<?php

namespace Controllers;
use MVC\Router;

class PoliticasController {
    public static function privacidad(Router $router) {
        $router->render('paginas/politicas/privacidad', [
            'titulo' => 'Política de Privacidad'
        ]);
    }

    public static function terminos(Router $router) {
        $router->render('paginas/politicas/terminos', [
            'titulo' => 'Términos y Condiciones'
        ]);
    }
}