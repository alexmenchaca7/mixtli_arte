<?php

namespace Controllers;

use MVC\Router;
use Model\Valoracion;
use Model\PuntoFuerte;
use Model\ReporteValoracion;

class ValoracionesController {
    public static function guardar(Router $router) {
        header('Content-Type: application/json');
        if (!is_auth()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autenticado']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            exit();
        }

        $usuarioId = $_SESSION['id'];
        $valoracionId = filter_var($_POST['valoracion_id'] ?? '', FILTER_VALIDATE_INT);
        $estrellas = filter_var($_POST['estrellas'] ?? '', FILTER_VALIDATE_INT);
        $comentario = trim(s($_POST['comentario'] ?? ''));
        $puntosFuertes = $_POST['puntos_fuertes'] ?? [];

        if (!$valoracionId || !$estrellas || $estrellas < 1 || $estrellas > 5) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Datos de calificación inválidos.']);
            exit();
        }

        $valoracion = Valoracion::find($valoracionId);

        if (!$valoracion || $valoracion->calificadorId != $usuarioId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para realizar esta calificación.']);
            exit();
        }

        // ESTA VALIDACIÓN PREVIENE CALIFICACIONES MÚLTIPLES
        if ($valoracion->estrellas !== null) {
            http_response_code(409); // Conflict
            echo json_encode(['success' => false, 'error' => 'Ya has enviado una calificación para esta transacción.']);
            exit();
        }

        // --- VALIDACIÓN DE LÍMITE DE TIEMPO ---
        $fechaCreacion = new \DateTime($valoracion->creado);
        $fechaActual = new \DateTime();
        $diferencia = $fechaActual->diff($fechaCreacion);
        if ($diferencia->days > 30) {
            http_response_code(403); // Forbidden
            echo json_encode(['success' => false, 'error' => 'El período de 30 días para dejar una calificación ha expirado.']);
            exit();
        }

        // --- VALIDACIÓN DE COMENTARIO OBLIGATORIO ---
        if ($estrellas == 1 && empty($comentario)) {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'error' => 'Las calificaciones de una estrella requieren un comentario obligatorio.']);
            exit();
        }

        $valoracion->estrellas = $estrellas;
        $valoracion->comentario = $comentario;
        $valoracion->moderado = 0; // 0 = Pendiente de Moderación para evitar mensajes inapropiados
        
        $resultadoValoracion = $valoracion->guardar();

        if (!$resultadoValoracion) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'No se pudo guardar la calificación.']);
            exit();
        }

        if (!empty($puntosFuertes) && is_array($puntosFuertes)) {
            foreach ($puntosFuertes as $punto) {
                $puntoFuerte = new PuntoFuerte([
                    'punto' => s($punto),
                    'valoracionId' => $valoracion->id
                ]);
                $puntoFuerte->guardar();
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Calificación guardada correctamente.']);
        exit();
    }

    public static function reportar() {
        header('Content-Type: application/json');
        if (!is_auth()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Debes iniciar sesión para reportar.']);
            exit;
        }

        $datos = json_decode(file_get_contents('php://input'), true);
        $reporte = new ReporteValoracion($datos);
        $reporte->reportadorId = $_SESSION['id'];
        
        $alertas = $reporte->validar();
        if(!empty($alertas['error'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => implode(', ', $alertas['error'])]);
            return;
        }

        // Verificar que un usuario no reporte la misma valoración múltiples veces
        $reporteExistente = ReporteValoracion::whereArray([
            'valoracionId' => $reporte->valoracionId,
            'reportadorId' => $reporte->reportadorId
        ]);

        if($reporteExistente) {
            http_response_code(409); // Conflict
            echo json_encode(['success' => false, 'error' => 'Ya has reportado esta valoración.']);
            return;
        }

        $resultado = $reporte->guardar();
        if($resultado) {
            echo json_encode(['success' => true, 'message' => 'Reporte enviado exitosamente. Nuestro equipo lo revisará a la brevedad.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'No se pudo procesar el reporte en este momento.']);
        }
    }
}