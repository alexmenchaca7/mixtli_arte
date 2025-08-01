<?php

namespace Controllers;

use Model\PalabraClave;
use MVC\Router;

class PalabraClaveController {

    public static function index(Router $router) {
        if(!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $palabras = PalabraClave::all();
        $router->render('admin/palabras_clave/index', [
            'titulo' => 'Palabras Clave de FAQs',
            'palabras' => $palabras
        ], 'admin-layout');
    }

    public static function crear(Router $router) {
        if(!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $palabra = new PalabraClave();
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_auth('admin')) {
                header('Location: /login');
                exit();
            }

            $palabra->sincronizar($_POST);
            $alertas = $palabra->validar();
            if (empty($alertas)) {
                $resultado = $palabra->guardar();
                if ($resultado) {
                    header('Location: /admin/palabras-clave');
                }
            }
        }

        $router->render('admin/palabras_clave/crear', [
            'titulo' => 'Añadir Palabra Clave',
            'palabra' => $palabra,
            'alertas' => $alertas
        ], 'admin-layout');
    }

    public static function editar(Router $router) {
        if(!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $id = $_GET['id'];
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if(!$id) {
            header('Location: /admin/palabras-clave');
        }

        $palabra = PalabraClave::find($id);
        if(!$palabra) {
            header('Location: /admin/palabras-clave');
        }
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_auth('admin')) {
                header('Location: /login');
                exit();
            }

            $palabra->sincronizar($_POST);
            $alertas = $palabra->validar();
            if (empty($alertas)) {
                $resultado = $palabra->guardar();
                if ($resultado) {
                    header('Location: /admin/palabras-clave');
                }
            }
        }

        $router->render('admin/palabras_clave/editar', [
            'titulo' => 'Editar Palabra Clave',
            'palabra' => $palabra,
            'alertas' => $alertas
        ], 'admin-layout');
    }

    public static function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_auth('admin')) {
                header('Location: /login');
                exit();
            }
            
            $id = $_POST['id'];
            $palabra = PalabraClave::find($id);
            if($palabra) {
                $palabra->eliminar();
                header('Location: /admin/palabras-clave');
            }
        }
    }
}