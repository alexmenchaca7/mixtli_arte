<?php

namespace Controllers;

use MVC\Router;
use Classes\Paginacion;
use Model\PalabraClave;

class PalabraClaveController {

    public static function index(Router $router) {
        if (!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        // Obtener término de búsqueda si existe
        $busqueda = $_GET['busqueda'] ?? '';
        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;

        if($pagina_actual < 1) {
            header('Location: /admin/palabras-clave?page=1');
            exit();
        }

        $registros_por_pagina = 20;
        $condiciones = [];

        if(!empty($busqueda)) {
            $condiciones = PalabraClave::buscar($busqueda);
        }

        // Obtener total de registros
        $total = PalabraClave::totalCondiciones($condiciones);

        // Crear instancia de paginación
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);
        
        if ($paginacion->total_paginas() < $pagina_actual && $pagina_actual > 1) {
            header('Location: /admin/palabras-clave?page=1');
            exit();
        }

        // Obtener palabras clave con paginación
        $params = [
            'condiciones' => $condiciones,
            'orden' => 'palabra ASC',
            'limite' => $registros_por_pagina,
            'offset' => $paginacion->offset()
        ];
        
        $palabras = PalabraClave::metodoSQL($params);

        $router->render('admin/palabras_clave/index', [
            'titulo' => 'Palabras Clave de FAQs',
            'palabras' => $palabras,
            'paginacion' => $paginacion->paginacion(),
            'busqueda' => $busqueda
        ], 'admin-layout');
    }

    public static function crear(Router $router) {
        if (!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $palabra = new PalabraClave();
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!is_auth('admin')) {
                header('Location: /login');
                exit();
            }

            $palabra->sincronizar($_POST);
            $alertas = $palabra->validar();

            if (empty($alertas)) {
                // Normalizar y verificar si la palabra ya existe
                $palabraNormalizada = strtolower($palabra->palabra);
                $existePalabra = PalabraClave::where('palabra', $palabraNormalizada);

                if ($existePalabra) {
                    PalabraClave::setAlerta('error', 'La palabra clave que intentas añadir ya existe.');
                } else {
                    $palabra->palabra = $palabraNormalizada; // Guardar en minúsculas
                    $resultado = $palabra->guardar();
                    if ($resultado) {
                        // Crear alerta de éxito en la sesión
                        $_SESSION['alertas']['exito'][] = 'Palabra Clave creada correctamente.';
                        header('Location: /admin/palabras-clave');
                        exit();
                    }
                }
            }
        }

        $router->render('admin/palabras_clave/crear', [
            'titulo' => 'Añadir Palabra Clave',
            'palabra' => $palabra,
        ], 'admin-layout');
    }

    public static function editar(Router $router) {
        if (!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $id = $_GET['id'];
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: /admin/palabras-clave');
            exit();
        }

        $palabra = PalabraClave::find($id);
        if (!$palabra) {
            header('Location: /admin/palabras-clave');
            exit();
        }
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!is_auth('admin')) {
                header('Location: /login');
                exit();
            }

            $palabra->sincronizar($_POST);
            $alertas = $palabra->validar();

            if (empty($alertas)) {
                $palabraNormalizada = strtolower($palabra->palabra);
                $existePalabra = PalabraClave::where('palabra', $palabraNormalizada);

                // Verificar que no exista otra palabra con el mismo nombre (excluyendo la actual)
                if ($existePalabra && $existePalabra->id !== $palabra->id) {
                     PalabraClave::setAlerta('error', 'Ya existe otra palabra clave con ese nombre.');
                } else {
                    $palabra->palabra = $palabraNormalizada;
                    $resultado = $palabra->guardar();
                    if ($resultado) {
                        $_SESSION['alertas']['exito'][] = 'Palabra Clave actualizada correctamente.';
                        header('Location: /admin/palabras-clave');
                        exit();
                    }
                }
            }
        }

        $router->render('admin/palabras_clave/editar', [
            'titulo' => 'Editar Palabra Clave',
            'palabra' => $palabra,
        ], 'admin-layout');
    }

    public static function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!is_auth('admin')) {
                header('Location: /login');
                exit();
            }
            
            $id = $_POST['id'];
            $palabra = PalabraClave::find($id);
            if ($palabra) {
                $resultado = $palabra->eliminar();
                if ($resultado) {
                    $_SESSION['alertas']['exito'][] = 'Palabra Clave eliminada correctamente.';
                    header('Location: /admin/palabras-clave');
                    exit();
                }
            }
        }
    }
}