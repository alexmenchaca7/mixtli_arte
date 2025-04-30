<?php

namespace Controllers;

use MVC\Router;

class MensajesController {
    public static function index(Router $router) {
        if(!is_auth('comprador')) {
            header('Location: /login');
            exit();
        }

        $router->render('marketplace/mensajes', [
            'titulo' => 'Mensajes'
        ], 'layout');
    }
}