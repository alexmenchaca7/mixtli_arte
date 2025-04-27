<?php

namespace Controllers;
use MVC\Router;
use Model\Usuario;
use Model\Favorito;
use Model\Producto;
use Model\Categoria;
use Classes\Paginacion;
use Model\ImagenProducto;

class MarketplaceController {
    public static function index(Router $router) {
        if(!is_auth('comprador')) {
            header('Location: /login');
            exit();
        }

        // Obtener categoria seleccionada
        $categoriaId = filter_var($_GET['categoria'] ?? null, FILTER_VALIDATE_INT);
        $condiciones = [];

        if ($categoriaId) {
            $categoria = Categoria::find($categoriaId);
            if ($categoria) {
                $condiciones[] = "categoriaId = '$categoriaId'";
            }
        }

        // Obtener total de registros CON las condiciones
        $total = Producto::totalCondiciones($condiciones); 

        // Obtener categorias disponibles
        $categorias = Categoria::all();

        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;

        if($pagina_actual < 1) {
            header('Location: /vendedor/productos?page=1');
            exit();
        }
        
        $registros_por_pagina = 10;

        // Obtener total de registros
        $total = Producto::totalCondiciones($condiciones);
        
        // Crear instancia de paginación
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);
        
        if ($paginacion->total_paginas() < $pagina_actual && $pagina_actual > 1) {
            header('Location: /marketplace?page=1');
            exit();
        }

        // Obtener productos
        $params = [
            'condiciones' => $condiciones,
            'orden' => 'nombre ASC',
            'limite' => $registros_por_pagina,
            'offset' => $paginacion->offset()
        ];
        
        $productos = Producto::metodoSQL($params);

        // Obtener las imagenes relacionadas para cada producto
        foreach($productos as $producto) {
            $imagenPrincipal = ImagenProducto::obtenerPrincipalPorProductoId($producto->id); 

            // Asigna la URL a la propiedad 'imagen_principal' del objeto Producto
            // Si no hay imagen, asigna null
            $producto->imagen_principal = $imagenPrincipal ? $imagenPrincipal->url : null; 
        }

        // Obtener categoría seleccionada para el título
        $titulo = 'Para Ti';
        if ($categoriaId) {
            $categoria = Categoria::find($categoriaId);
            $titulo = $categoria ? $categoria->nombre : $titulo;
        }

        // Obtener favoritos del usuario
        $favoritosIds = [];
        if(is_auth('comprador')) {
            $usuarioId = $_SESSION['id'];
            $favoritos = Favorito::whereField('usuarioId', $usuarioId);
            if (!is_array($favoritos)) {
                $favoritos = $favoritos ? [$favoritos] : [];
            }
            $favoritosIds = array_column($favoritos, 'productoId');
        }
        
        $router->render('marketplace/index', [
            'titulo' => $titulo,
            'productos' => $productos,
            'categorias' => $categorias,
            'paginacion' => $paginacion,
            'categoria_seleccionada' => $categoriaId,
            'favoritosIds' => $favoritosIds
        ]);
    }

    public static function producto(Router $router) {
        if(!is_auth('comprador')) {
            header('Location: /login');
            exit();
        }

        $id = $_GET['id'];
        $producto = Producto::find($id);

        if (!$producto) {
            header('Location: /marketplace');
            exit();
        }

        // Obtener categorias disponibles
        $categorias = Categoria::all();

        // Obtener imágenes
        $producto->imagenes = ImagenProducto::whereField('productoId', $producto->id);

        // Obtener información del vendedor
        $vendedor = Usuario::find($producto->usuarioId);
        
        $router->render('marketplace/producto', [
            'titulo' => "$producto->nombre",
            'producto' => $producto,
            'categorias' => $categorias,
            'vendedor' => $vendedor
        ]);
    }
}