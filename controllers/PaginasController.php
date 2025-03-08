<?php

namespace Controllers;
use MVC\Router;

class PaginasController {
    public static function index(Router $router) {
        
        $inicio = true;

        $router->render('paginas/index', [
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

        $inicio = true;

        $router->render('paginas/contacto', [
            'inicio' => $inicio
        ]);
    }
}