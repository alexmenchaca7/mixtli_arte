<?php

namespace Controllers;

use MVC\Router;
use Model\Usuario;
use Model\Producto;
use Model\Valoracion;
use Classes\Paginacion;
use Model\ReporteValoracion;

class AdminReportesValoracionesController {

    public static function index(Router $router) {
        if (!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
        $registros_por_pagina = 10;

        $total = ReporteValoracion::total();
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);

        if ($paginacion->total_paginas() > 0 && $pagina_actual > $paginacion->total_paginas()) {
            header('Location: /admin/reportes-valoraciones?page=1');
            exit();
        }

        $reportes = ReporteValoracion::paginar($registros_por_pagina, $paginacion->offset());

        foreach ($reportes as $reporte) {
            $reporte->valoracion = Valoracion::find($reporte->valoracionId);
            if($reporte->valoracion) {
                $reporte->autorComentario = Usuario::find($reporte->valoracion->calificadorId);

                // 2. Añadimos la consulta del producto
                if($reporte->valoracion->productoId) {
                    $reporte->producto = Producto::find($reporte->valoracion->productoId);
                }
            }
            $reporte->reportador = Usuario::find($reporte->reportadorId);
        }

        $router->render('admin/reportes_valoraciones/index', [
            'titulo' => 'Reportes de Comentarios',
            'reportes' => $reportes,
            'paginacion' => $paginacion->paginacion()
        ], 'admin-layout');
    }

    public static function resolver() {
        if (!is_auth('admin') || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit();
        }

        $reporte_id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        $accion = s($_POST['accion']);

        $reporte = ReporteValoracion::find($reporte_id);
        if (!$reporte) {
            header('Location: /admin/reportes-valoraciones');
            exit();
        }

        if ($accion === 'descartar') {
            $reporte->estado = 'descartado';
            $reporte->guardar();
            Usuario::setAlerta('exito', 'Reporte descartado correctamente.');

        } elseif ($accion === 'sancionar') {
            // 1. Marcar el reporte como resuelto
            $reporte->estado = 'resuelto';
            $reporte->guardar();

            // 2. Ocultar el comentario (marcarlo como rechazado)
            $valoracion = Valoracion::find($reporte->valoracionId);
            if ($valoracion) {
                $valoracion->moderado = 2; // 2 = Rechazado
                $valoracion->guardar();

                // 3. Sancionar al autor del comentario
                $autorComentario = Usuario::find($valoracion->calificadorId);
                if ($autorComentario) {
                    $motivo = "Comentario inapropiado reportado: '" . substr($valoracion->comentario, 0, 50) . "...'";
                    $autorComentario->registrarViolacion($motivo, $reporte->id, 'valoracion');
                }
            }
            Usuario::setAlerta('exito', 'El comentario ha sido ocultado y el usuario sancionado.');
        }

        header('Location: /admin/reportes-valoraciones');
        exit();
    }
}