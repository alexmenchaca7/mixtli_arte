<?php

namespace Controllers;

use Model\HistorialInteraccion;

class HistorialInteraccionController {

    public static function registrar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Metodo no permitido']);
            exit;
        }

        if (!is_auth()) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }

        // Manejar tanto JSON como FormData
        $input = [];
        if (strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
        } else {
            // Si es FormData o urlencoded, PHP lo pone en $_POST
            $input = $_POST;
        }

        $metadata = null;
        if (isset($input['metadata'])) {
            // Si metadata viene como string (desde FormData), decodificarlo.
            $metadata = is_string($input['metadata']) ? json_decode($input['metadata'], true) : $input['metadata'];
        }

        $interaccion = new HistorialInteraccion([
            'tipo' => $input['tipo'] ?? '',
            'usuarioId' => $_SESSION['id'],
            'productoId' => $input['productoId'] ?? null,
            'metadata' => isset($input['metadata']) ? json_encode($input['metadata']) : null
        ]);

        $alertas = $interaccion->validar(); // Suponiendo que tienes un método validar en el modelo

        if (empty($alertas)) {
            $interaccion->guardar();
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'errores' => $alertas]);
        }
    }
}