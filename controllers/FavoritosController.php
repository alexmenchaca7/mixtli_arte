<?php

namespace Controllers;
use MVC\Router;
use Model\Favorito;
use Model\Producto;
use Model\ImagenProducto;

class FavoritosController {
    public static function index(Router $router) {
        if(!is_auth('comprador')) {
            header('Location: /login');
            exit();
        }

        $usuarioId = $_SESSION['id'];

        // Obtener IDs de favoritos
        $favoritos = Favorito::whereField('usuarioId', $usuarioId);
        $favoritosIds = array_column($favoritos ?? [], 'productoId');
        
        // Obtener productos favoritos
        $query = "SELECT p.* FROM favoritos f
                  INNER JOIN productos p ON f.productoId = p.id
                  WHERE f.usuarioId = '$usuarioId'";
        $productos = Producto::consultarSQL($query);

        // Obtener imágenes principales
        foreach($productos as $producto) {
            $imagenPrincipal = ImagenProducto::obtenerPrincipalPorProductoId($producto->id);
            $producto->imagen_principal = $imagenPrincipal ? $imagenPrincipal->url : null;
        }

        $router->render('marketplace/favoritos', [
            'titulo' => 'Favoritos',
            'productos' => $productos,
            'favoritosIds' => $favoritosIds
        ]);
    }

    public static function toggle() {
        // Establecer cabecera JSON primero
        header('Content-Type: application/json');
        
        if(!is_auth('comprador')) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }

        $usuarioId = $_SESSION['id'];
        $productoId = $_POST['productoId'] ?? null;

        if(!$productoId || !is_numeric($productoId)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido']);
            exit;
        }

        // Buscar si ya existe usando whereArray
        $favoritos = Favorito::whereArray([
            'usuarioId' => $usuarioId,
            'productoId' => $productoId
        ]);

        $favorito = $favoritos ? $favoritos[0] : null;

        if($favorito) {
            $resultado = $favorito->eliminar();
            $accion = 'removed';
        } else {
            $favorito = new Favorito([
                'usuarioId' => $usuarioId,
                'productoId' => $productoId,
                'creado' => date('Y-m-d H:i:s')
            ]);
            $resultado = $favorito->guardar();
            $accion = 'added';
        }

        // Asegurar que siempre se devuelva JSON válido
        if($resultado) {
            echo json_encode(['success' => true, 'action' => $accion]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error en el servidor, intente de nuevo más tarde']);
        }
        exit; // Añadir exit al final
    }
}