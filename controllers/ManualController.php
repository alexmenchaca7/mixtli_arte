<?php

namespace Controllers;

use MVC\Router;

class ManualController {
    public static function index(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
            exit();
        }

        $tipo = $_GET['tipo'] ?? 'comprador';
        $rol = $_SESSION['rol'] ?? '';

        // Comprobación de seguridad: los compradores solo pueden ver el manual de comprador.
        if ($rol === 'comprador' && $tipo !== 'comprador') {
             header('Location: /');
             exit();
        }

        $pdfPath = '';
        $titulo = '';

        if ($tipo === 'vendedor' && ($rol === 'vendedor' || $rol === 'admin')) {
            $pdfPath = '/servir-manual.php?tipo=vendedor';
            $titulo = 'Manual de Usuario - Vendedor';
        } elseif ($tipo === 'comprador') {
            $pdfPath = '/servir-manual.php?tipo=comprador';
            $titulo = 'Manual de Usuario - Comprador';
        } else {
            // Si el tipo es inválido o el usuario no tiene permiso, redirigir.
            header('Location: /');
            exit();
        }

        // Determinar qué layout usar según el rol.
        $layout = 'layout';
        if ($rol === 'vendedor') {
            $layout = 'vendedor-layout';
        } elseif ($rol === 'admin') {
            $layout = 'admin-layout';
        }

        $router->render('manual/index', [
            'titulo' => $titulo,
            'pdfPath' => $pdfPath
        ], $layout);
    }
}