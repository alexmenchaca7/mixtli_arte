<?php

namespace Controllers;
use MVC\Router;

class ProductosController {

    public static function index(Router $router) {
        $router->render('vendedor/productos/index', [
            'titulo' => 'Productos'
        ], 'vendedor-layout');
    }
}