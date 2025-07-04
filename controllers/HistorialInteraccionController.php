<?php

namespace Controllers;

use Model\HistorialInteraccion;

class HistorialInteraccionController {

    public static function registrar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Metodo no permitido']);
            return; // Usamos return en lugar de exit
        }

        // Manejar tanto JSON como FormData
        $input = [];
        // Verificamos que CONTENT_TYPE esté definido antes de usarlo
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
        } else {
            $input = $_POST;
        }

        if (empty($input)) {
            http_response_code(400);
            echo json_encode(['error' => 'No se recibieron datos']);
            return;
        }

        // Hacemos la autenticación opcional. Si hay sesión, guardamos el ID. Si no, será null.
        $usuarioId = is_auth() ? $_SESSION['id'] : null;

        $interaccion = new HistorialInteraccion([
            'tipo' => $input['tipo'] ?? '',
            'usuarioId' => $usuarioId, // Asignamos el ID de usuario (puede ser null)
            'productoId' => $input['productoId'] ?? null,
            'metadata' => isset($input['metadata']) ? json_encode($input['metadata']) : null
        ]);

        $alertas = $interaccion->validar();

        if (empty($alertas)) {
            // Verificamos el resultado de guardar()
            $resultado = $interaccion->guardar();

            if ($resultado) {
                echo json_encode(['success' => true, 'data' => $interaccion]);
            } else {
                http_response_code(500); // Error de servidor
                echo json_encode(['success' => false, 'error' => 'No se pudo guardar la interacción en la base de datos.']);
            }

        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'errores' => $alertas]);
        }
    }
}