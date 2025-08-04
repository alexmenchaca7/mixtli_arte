<?php

namespace Controllers;

use MVC\Router;
use Model\Usuario;
use Model\Producto;
use Classes\Paginacion;
use Model\ImagenProducto;
use Model\ReporteProducto;
use Model\UsuarioViolacion;

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
        $imagenes_producto = [];
        $historial_otros_productos = [];
        $historial_otros_reportes = [];

        // Solo continuamos si el producto asociado al reporte existe
        if ($producto) {
            // Obtenemos TODAS las imágenes para ESTE producto específico.
            // Esto es mucho más eficiente que traer todas y filtrar en PHP.
            $imagenes_producto = ImagenProducto::whereField('productoId', $producto->id);

            $vendedor = Usuario::find($producto->usuarioId);
            
            if ($vendedor) {
                // Obtenemos directamente los otros productos del vendedor desde la BD
                $historial_otros_productos = Producto::consultarSQL("SELECT * FROM productos WHERE usuarioId = {$vendedor->id} AND id != {$producto->id}");
                
                // Obtenemos los IDs de esos productos
                $ids_productos_vendedor = array_column($historial_otros_productos, 'id');

                if (!empty($ids_productos_vendedor)) {
                    // Obtenemos los reportes asociados a esos productos directamente desde la BD
                    $ids_string = implode(',', $ids_productos_vendedor);
                    $historial_otros_reportes = ReporteProducto::consultarSQL("SELECT * FROM reportes_productos WHERE productoId IN ({$ids_string})");
                }
            }
        }

        $router->render('admin/reportes/ver', [
            'titulo' => 'Detalle del Reporte',
            'reporte' => $reporte,
            'producto' => $producto,
            'vendedor' => $vendedor,
            'imagenes_producto' => $imagenes_producto ?? [], 
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
                // --- ESTE BLOQUE SOLO SE EJECUTA SI ES 'valido' ---
                if ($clasificacion === 'valido') {
                    $reporte->estado = 'valido';
                    $producto = Producto::find($reporte->productoId);
                    if ($producto) {
                        $vendedor = Usuario::find($producto->usuarioId);
                        if ($vendedor) {
                            // Aquí es donde se afecta el historial del vendedor
                            $motivoViolacion = "Reporte válido por: " . $reporte->motivo . " en producto '" . $producto->nombre . "'";
                            $vendedor->registrarViolacion($motivoViolacion, $reporte->id, 'producto');
                        }
                        $producto->eliminar(); 
                    }
                } else {  // --- ESTE BLOQUE SE EJECUTA SI ES 'no_valido' ---
                    $reporte->estado = 'resuelto';
                }
                $reporte->guardar();
            }
        }
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/admin/reportes'); // Volver a la página anterior
        exit();
    }
}