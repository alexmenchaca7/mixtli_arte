<?php

namespace Controllers;

use MVC\Router;
use Classes\Paginacion;
use Model\Notificacion;

class NotificacionesController {

    public static function index(Router $router) {
        if (!is_auth()) {
            header('Location: /login');
            exit();
        }

        $usuarioId = $_SESSION['id'];

        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
        if(!$pagina_actual || $pagina_actual < 1) {
            header('Location: /notificaciones?page=1');
            exit();
        }
        $registros_por_pagina = 10; // 10 notificaciones por página

        $condiciones = ["usuarioId = '{$usuarioId}'"];
        $total = Notificacion::totalCondiciones($condiciones);
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);

        if ($paginacion->total_paginas() < $pagina_actual && $pagina_actual > 1) {
            header('Location: /notificaciones?page=1');
            exit();
        }
        
        // Obtener las notificaciones paginadas
        $notificaciones = Notificacion::metodoSQL([
            'condiciones' => $condiciones,
            'orden' => 'creado DESC',
            'limite' => $registros_por_pagina,
            'offset' => $paginacion->offset()
        ]);

        // Contar las no leídas para la vista
        $noLeidasCount = Notificacion::contarNoLeidas($usuarioId);

        // Determinar el layout según el rol
        $layout = 'layout'; // Default para comprador
        if ($_SESSION['rol'] === 'vendedor') {
            $layout = 'vendedor-layout';
        } else if ($_SESSION['rol'] === 'admin') {
            $layout = 'admin-layout';
        }

        $router->render('notificaciones/index', [
            'titulo' => 'Mis Notificaciones',
            'notificaciones' => $notificaciones,
            'noLeidasCount' => $noLeidasCount,
            'paginacion' => $paginacion->paginacion()
        ], $layout);
    }

    public static function obtenerNoLeidas() {
        header('Content-Type: application/json');
        if (!is_auth()) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }

        $usuarioId = $_SESSION['id'];
        $count = Notificacion::contarNoLeidas($usuarioId);
        echo json_encode(['unread_count' => $count]);
        exit;
    }

    public static function marcarLeida() {
        header('Content-Type: application/json');
        if (!is_auth() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Acceso no permitido']);
            exit;
        }

        $usuarioId = $_SESSION['id'];
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
            exit;
        }

        $resultado = Notificacion::marcarComoLeida($id, $usuarioId);
        echo json_encode(['success' => $resultado]);
        exit;
    }

    public static function marcarNoLeida() {
        header('Content-Type: application/json');
        if (!is_auth() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Acceso no permitido']);
            exit;
        }

        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
            exit;
        }

        $resultado = Notificacion::marcarComoNoLeida($id, $_SESSION['id']);
        echo json_encode(['success' => $resultado]);
    }

    public static function marcarTodasLeidas() {
        header('Content-Type: application/json');
        if (!is_auth() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Acceso no permitido']);
            exit;
        }
        
        $resultado = Notificacion::marcarTodasComoLeidas($_SESSION['id']);
        echo json_encode(['success' => $resultado]);
    }

    public static function eliminar() {
        header('Content-Type: application/json');
        if (!is_auth() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Acceso no permitido']);
            exit;
        }
        
        $usuarioId = $_SESSION['id'];
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
            exit;
        }

        $resultado = Notificacion::eliminarPorId($id, $usuarioId);
        echo json_encode(['success' => $resultado]);
        exit;
    }
}