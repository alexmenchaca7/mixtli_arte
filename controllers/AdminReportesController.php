<?php

namespace Controllers;

use MVC\Router;
use Model\Usuario;
use Model\Producto;
use Classes\Paginacion;
use Model\ImagenProducto;
use Model\ReporteProducto;

class AdminReportesController {

    public static function index(Router $router) {
        if (!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
        $registros_por_pagina = 10;

        $total = ReporteProducto::total();
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);

        if ($paginacion->total_paginas() < $pagina_actual && $pagina_actual > 1) {
            header('Location: /admin/reportes?page=1');
            exit();
        }

        $reportes = ReporteProducto::paginar($registros_por_pagina, $paginacion->offset());

        foreach ($reportes as $reporte) {
            $reporte->producto = Producto::find($reporte->productoId);
        }

        $router->render('admin/reportes/index', [
            'titulo' => 'Gestión de Reportes de Productos',
            'reportes' => $reportes,
            'paginacion' => $paginacion->paginacion()
        ], 'admin-layout');
    }

    public static function ver(Router $router) {
        if (!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: /admin/reportes');
            exit();
        }

        $reporte = ReporteProducto::find($id);
        if (!$reporte) {
            header('Location: /admin/reportes');
            exit();
        }

        // 1. Detalles del producto reportado
        $producto = Producto::find($reporte->productoId);
        
        // Inicializamos todas las variables que pasaremos a la vista
        $vendedor = null;
        $imagen_producto = null;
        $historial_otros_productos = [];
        $historial_otros_reportes = [];

        // Solo continuamos si el producto asociado al reporte existe
        if ($producto) {
            // Buscamos la primera imagen asociada a este producto
            $imagenes = ImagenProducto::all();
            foreach($imagenes as $img) {
                if ($img->productoId == $producto->id) {
                    $imagen_producto = $img;
                    break;
                }
            }

            $vendedor = Usuario::find($producto->usuarioId);
            
            if ($vendedor) {
                // Obtenemos historial de otros productos del vendedor 
                $todos_los_productos = Producto::all();
                $historial_otros_productos = array_filter($todos_los_productos, function($p) use ($vendedor) {
                    return $p->usuarioId === $vendedor->id;
                });
                
                $ids_productos_vendedor = array_column($historial_otros_productos, 'id');

                if (!empty($ids_productos_vendedor)) {
                    // Obtenemos historial de otros reportes del vendedor
                    $todos_los_reportes = ReporteProducto::all();
                    $historial_otros_reportes = array_filter($todos_los_reportes, function($r) use ($ids_productos_vendedor) {
                        return in_array($r->productoId, $ids_productos_vendedor);
                    });
                }
            }
        }

        $router->render('admin/reportes/ver', [
            'titulo' => 'Detalle del Reporte',
            'reporte' => $reporte,
            'producto' => $producto,
            'vendedor' => $vendedor,
            'imagen_producto' => $imagen_producto, 
            'historial_otros_productos' => $historial_otros_productos,
            'historial_otros_reportes' => $historial_otros_reportes
        ], 'admin-layout');
    }

    public static function clasificar() {
        if (!is_auth('admin') || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit();
        }

        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        $clasificacion = $_POST['clasificacion']; // 'valido' o 'no_valido'

        if ($id && ($clasificacion === 'valido' || $clasificacion === 'no_valido')) {
            $reporte = ReporteProducto::find($id);
            if ($reporte) {
                if ($clasificacion === 'valido') {
                    $reporte->estado = 'valido';
                    $producto = Producto::find($reporte->productoId);
                    if ($producto) {
                        // Reutilizamos la lógica de eliminación completa del producto
                        $producto->eliminar(); 
                    }
                } else { // no_valido
                    $reporte->estado = 'resuelto';
                }
                $reporte->guardar();
            }
        }
        header('Location: /admin/reportes');
        exit();
    }
}