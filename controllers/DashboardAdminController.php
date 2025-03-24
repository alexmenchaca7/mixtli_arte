<?php

namespace Controllers;

use Model\Usuario;
use MVC\Router;

class DashboardAdminController {
    public static function index(Router $router) {
        if(!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $router->render('admin/dashboard/index', [
            'titulo' => 'Panel de Administracion Admin'
        ], 'admin-layout');
    }
}