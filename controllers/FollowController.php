<?php

namespace Controllers;

use Model\Follow;

class FollowController {
    public static function toggle() {
        header('Content-Type: application/json');
        if (!is_auth()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autenticado']);
            exit;
        }

        $seguidorId = $_SESSION['id'];
        $vendedorId = $_POST['vendedorId'] ?? null;

        if (!$vendedorId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Vendedor no especificado.']);
            exit;
        }

        $follow = Follow::whereArray([
            'seguidorId' => $seguidorId,
            'seguidoId' => $vendedorId
        ]);

        if ($follow) {
            $resultado = $follow[0]->eliminar();
            $action = 'unfollowed';
        } else {
            $nuevoFollow = new Follow([
                'seguidorId' => $seguidorId,
                'seguidoId' => $vendedorId
            ]);
            $resultado = $nuevoFollow->guardar();
            $action = 'followed';
        }

        if($resultado) {
            echo json_encode(['success' => true, 'action' => $action]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error en el servidor.']);
        }
    }
}