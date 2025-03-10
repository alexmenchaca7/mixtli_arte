<?php

namespace Controllers;
use MVC\Router;

class MarketplaceController {
    public static function index(Router $router) {
        
        $router->render('marketplace/index', [

        ]);
    }
}