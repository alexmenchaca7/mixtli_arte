<?php

namespace Controllers;

use MVC\Router;

class MensajesController {
    public static function index(Router $router) {
        if(!is_auth('vendedor')) {
            header('Location: /login');
            exit();
        }

        $router->render('vendedor/mensajes/index', [
            'titulo' => 'Mensajes'
        ], 'vendedor-layout');
    }
}