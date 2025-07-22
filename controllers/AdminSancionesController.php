<?php

namespace Controllers;

use MVC\Router;
use Model\Usuario;
use Model\AdminAjusteSancion;
use Classes\Paginacion;

class AdminSancionesController {

    public static function index(Router $router) {
        if (!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
        $registros_por_pagina = 10;

        $total = Usuario::totalCondiciones(["rol = 'vendedor'"]);
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);

        $vendedores = Usuario::metodoSQL([
            'condiciones' => ["rol = 'vendedor'"],
            'orden' => 'violaciones_count DESC',
            'limite' => $registros_por_pagina,
            'offset' => $paginacion->offset()
        ]);

        $router->render('admin/sanciones/index', [
            'titulo' => 'Gestión de Sanciones de Vendedores',
            'vendedores' => $vendedores,
            'paginacion' => $paginacion->paginacion(),
        ], 'admin-layout');
    }

    public static function ajustar() {
        if (!is_auth('admin') || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit();
        }

        $vendedor_id = filter_var($_POST['vendedor_id'], FILTER_VALIDATE_INT);
        $sancion_nueva = filter_var($_POST['sancion_nueva'], FILTER_VALIDATE_INT);
        $comentario = s($_POST['comentario']);
        $admin_id = $_SESSION['id'];

        if (!$vendedor_id || !is_numeric($sancion_nueva) || empty($comentario)) {
            Usuario::setAlerta('error', 'Todos los campos son obligatorios para el ajuste.');
            header('Location: /admin/sanciones');
            exit();
        }

        $vendedor = Usuario::find($vendedor_id);
        if (!$vendedor || $vendedor->rol !== 'vendedor') {
            Usuario::setAlerta('error', 'Usuario no válido o no es un vendedor.');
            header('Location: /admin/sanciones');
            exit();
        }

        $sancion_anterior = $vendedor->violaciones_count;

        // Registrar el ajuste
        $ajuste = new AdminAjusteSancion([
            'admin_id' => $admin_id,
            'vendedor_id' => $vendedor_id,
            'sancion_anterior' => $sancion_anterior,
            'sancion_nueva' => $sancion_nueva,
            'comentario' => $comentario
        ]);

        $alertas = $ajuste->validar();
        if (!empty($alertas['error'])) {
            Usuario::setAlerta('error', implode(', ', $alertas['error']));
            header('Location: /admin/sanciones');
            exit();
        }

        $ajuste->guardar();

        // Actualizar las sanciones del vendedor
        $vendedor->violaciones_count = $sancion_nueva;

        // Re-evaluar estado de bloqueo
        if ($vendedor->violaciones_count >= 10) {
            $vendedor->bloqueado_permanentemente = 1;
            $vendedor->bloqueado_hasta = null;
        } elseif ($vendedor->violaciones_count >= 3) {
            $vendedor->bloqueado_permanentemente = 0;
            $fecha_bloqueo = new \DateTime();
            $fecha_bloqueo->modify('+1 week');
            $vendedor->bloqueado_hasta = $fecha_bloqueo->format('Y-m-d H:i:s');
        } else {
            $vendedor->bloqueado_permanentemente = 0;
            $vendedor->bloqueado_hasta = null;
        }

        $vendedor->guardar();

        Usuario::setAlerta('exito', 'La sanción del vendedor ha sido ajustada correctamente.');
        header('Location: /admin/sanciones');
        exit();
    }

    public static function historial(Router $router) {
        if (!is_auth('admin')) {
            header('Location: /login');
            exit();
        }

        $ajustes = AdminAjusteSancion::consultarSQL("
            SELECT 
                ajustes.sancion_anterior, 
                ajustes.sancion_nueva, 
                ajustes.comentario, 
                ajustes.fecha_ajuste, 
                vendedor.nombre AS vendedor_nombre, 
                vendedor.apellido AS vendedor_apellido,
                admin.nombre AS admin_nombre,
                admin.apellido AS admin_apellido
            FROM admin_ajustes_sanciones AS ajustes
            JOIN usuarios AS vendedor ON ajustes.vendedor_id = vendedor.id
            JOIN usuarios AS admin ON ajustes.admin_id = admin.id
            ORDER BY ajustes.fecha_ajuste DESC
        ");

        $router->render('admin/sanciones/historial', [
            'titulo' => 'Historial de Ajustes de Sanciones',
            'ajustes' => $ajustes
        ], 'admin-layout');
    }
}