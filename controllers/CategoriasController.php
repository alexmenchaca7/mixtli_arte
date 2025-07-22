<?php

namespace Controllers;

use MVC\Router;
use Model\Categoria;
use Classes\Paginacion;

class CategoriasController {
    public static function index(Router $router) {
        if(!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        // Obtener término de búsqueda si existe
        $busqueda = $_GET['busqueda'] ?? '';
        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;

        if($pagina_actual < 1) {
            header('Location: /admin/categorias?page=1');
            exit();
        }

        $registros_por_pagina = 10;
        $condiciones = [];

        if(!empty($busqueda)) {
            $condiciones = Categoria::buscar($busqueda);
        }

        // Obtener total de registros
        $total = Categoria::totalCondiciones($condiciones);
        
        // Crear instancia de paginación
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);
        
        if ($paginacion->total_paginas() < $pagina_actual && $pagina_actual > 1) {
            header('Location: /admin/categorias?page=1');
            exit();
        }

        // Obtener categorias
        $params = [
            'condiciones' => $condiciones,
            'orden' => 'nombre ASC',
            'limite' => $registros_por_pagina,
            'offset' => $paginacion->offset()
        ];
        
        $categorias = Categoria::metodoSQL($params);

        // Pasar las categorias a la vista
        $router->render('admin/categorias/index', [
            'titulo' => 'Categorias',
            'categorias' => $categorias,
            'paginacion' => $paginacion->paginacion(),
            'busqueda' => $busqueda
        ], 'admin-layout');
    }

    public static function crear(Router $router) {
        if(!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $categoria = new Categoria();
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_auth('admin')) {
                header('Location: /login');
                exit();
            }

            $categoria->sincronizar($_POST);
            $alertas = $categoria->validar();

            // Guardar registro
            if(empty($alertas['error'])) {
                $existeCategoria = Categoria::where('nombre', $categoria->nombre);

                if($existeCategoria) {
                    Categoria::setAlerta('error', 'La categoria ya esta registrada');
                } else {
                    // Guardar en la BD
                    $resultado =  $categoria->guardar();
                }

                if($resultado) {
                    header('Location: /admin/categorias');
                    exit();
                }
            }
        }

        // Pasar las categorias a la vista
        $router->render('admin/categorias/crear', [
            'titulo' => 'Registrar Categoria',
            'categoria' => $categoria
        ], 'admin-layout');
    }

    public static function editar(Router $router) {
        if(!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $alertas = [];
        $id = $_GET['id'];
        $id = filter_var($id, FILTER_VALIDATE_INT);

        if(!$id) {
            header('Location: /admin/categorias');
        }

        // Obtener categoria a editar
        $categoria = Categoria::find($id);

        if(!$categoria) {
            header('Location: /admin/categorias');
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_auth('admin')) {
                header('Location: /login');
                exit();
            }

            $categoria->sincronizar($_POST);
            $alertas = $categoria->validar();

            if(empty($alertas['error'])) {
                $resultado = $categoria->guardar();

                if($resultado) {
                    header('Location: /admin/categorias');
                }
            }
        }

        // Pasar las categorias a la vista
        $router->render('admin/categorias/editar', [
            'titulo' => 'Editar Categoria',
            'categoria' => $categoria
        ], 'admin-layout');
    }

    public static function eliminar() {
        if(!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_auth('admin')) {
                header('Location: /login');
                exit();
            }

            $id = $_POST['id'];
            $categoria = Categoria::find($id);

            if(!isset($categoria)) {
                header('Location: /admin/categorias');
            }

            // Eliminando la categoria
            $resultado = $categoria->eliminar();

            if($resultado) {
                header('Location: /admin/categorias');
            }
        }
    }
}
