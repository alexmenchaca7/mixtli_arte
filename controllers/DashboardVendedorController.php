<?php

namespace Controllers;
use MVC\Router;

class DashboardVendedorController {

    public static function index(Router $router) {
        $router->render('vendedor/dashboard/index', [
            'titulo' => 'Panel de Administracion Vendedor'
        ], 'vendedor-layout');
    }
}