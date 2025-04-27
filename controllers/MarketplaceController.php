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

        // Obtener término de búsqueda si existe
        $busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
        $categoriaId = filter_var($_GET['categoria'] ?? null, FILTER_VALIDATE_INT);
        
        $condiciones = [];
        $titulo = 'Para Ti';

        // Lógica de búsqueda
        if (!empty($busqueda)) {
            // Buscar productos que coincidan directamente con el término
            $condicionesProducto = Producto::buscar($busqueda);
            
            // Buscar vendedores que coincidan con el término
            $usuarios = Usuario::whereArray([
                'nombre LIKE' => "%{$busqueda}%",
            ]);
            $usuarioIds = $usuarios ? array_column($usuarios, 'id') : [];
            
            // Buscar categorías que coincidan con el término
            $categorias = Categoria::whereArray([
                'nombre LIKE' => "%{$busqueda}%",
            ]);
            $categoriaIds = $categorias ? array_column($categorias, 'id') : [];
            
            // Construir condiciones complejas
            $condicionesComplejas = [];
            if (!empty($condicionesProducto)) {
                $condicionesComplejas[] = "(" . implode(' OR ', $condicionesProducto) . ")";
            }
            
            if (!empty($usuarioIds)) {
                $usuarioIdsStr = implode(',', $usuarioIds);
                $condicionesComplejas[] = "usuarioId IN ($usuarioIdsStr)";
            }
            
            if (!empty($categoriaIds)) {
                $categoriaIdsStr = implode(',', $categoriaIds);
                $condicionesComplejas[] = "categoriaId IN ($categoriaIdsStr)";
            }
            
            if (!empty($condicionesComplejas)) {
                $condiciones[] = "(" . implode(' OR ', $condicionesComplejas) . ")";
            }
            
            $titulo = "Resultados para: '{$busqueda}'";
        } elseif ($categoriaId) {
            // Si no hay búsqueda pero sí categoría seleccionada
            $condiciones[] = "categoriaId = '$categoriaId'";
            $categoria = Categoria::find($categoriaId);
            $titulo = $categoria ? $categoria->nombre : $titulo;
        }

        // Configuración de paginación
        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
        $registros_por_pagina = 10;
        
        // Obtener total de registros con las condiciones
        $total = Producto::totalCondiciones($condiciones);
        
        // Validar página actual
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);
        
        if ($paginacion->total_paginas() < $pagina_actual && $pagina_actual > 1) {
            header('Location: /marketplace?page=1');
            exit();
        }

        // Obtener productos con paginación
        $params = [
            'condiciones' => $condiciones,
            'orden' => 'nombre ASC',
            'limite' => $registros_por_pagina,
            'offset' => $paginacion->offset()
        ];
        
        $productos = Producto::metodoSQL($params);

        // Obtener imágenes principales para cada producto
        foreach($productos as $producto) {
            $imagenPrincipal = ImagenProducto::obtenerPrincipalPorProductoId($producto->id); 
            $producto->imagen_principal = $imagenPrincipal ? $imagenPrincipal->url : null;
        }

        // Obtener favoritos del usuario
        $favoritosIds = [];
        if(is_auth('comprador')) {
            $usuarioId = $_SESSION['id'];
            $favoritos = Favorito::whereField('usuarioId', $usuarioId);
            $favoritosIds = $favoritos ? array_column($favoritos, 'productoId') : [];
        }
        
        // Obtener todas las categorías para el menú
        $categorias = Categoria::all();
        
        $router->render('marketplace/index', [
            'titulo' => $titulo,
            'productos' => $productos,
            'categorias' => $categorias,
            'paginacion' => $paginacion,
            'categoria_seleccionada' => $categoriaId,
            'favoritosIds' => $favoritosIds,
            'busqueda' => $busqueda
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

    public static function autocompletar() {
        if (!is_auth('comprador')) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            exit();
        }
    
        $termino = $_GET['q'] ?? '';
        if (empty($termino)) {
            echo json_encode([]);
            exit();
        }
    
        $termino = trim($termino);
    
        // Buscar productos
        $productos = Producto::whereArray([
            'nombre LIKE' => "%{$termino}%",
        ]);
    
        // Buscar categorías
        $categorias = Categoria::whereArray([
            'nombre LIKE' => "%{$termino}%",
        ]);
    
        // Buscar artesanos/artistas (usuarios)
        $usuarios = Usuario::whereArray([
            'nombre LIKE' => "%{$termino}%",
        ]);
    
        // Formatear resultados
        $resultados = [
            'productos' => array_map(fn($producto) => ['id' => $producto->id, 'nombre' => $producto->nombre], $productos),
            'categorias' => array_map(fn($categoria) => ['id' => $categoria->id, 'nombre' => $categoria->nombre], $categorias),
            'usuarios' => array_map(fn($usuario) => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'apellido' => $usuario->apellido // Asegúrate de incluir el apellido aquí
            ], $usuarios),
        ];
    
        echo json_encode($resultados);
        exit();
    }
}