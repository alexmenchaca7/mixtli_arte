<?php

namespace Controllers;
use MVC\Router;
use Model\Usuario;
use Model\Favorito;
use Model\Producto;
use Model\Categoria;
use Model\Direccion;
use Model\Valoracion;
use Classes\Paginacion;
use Model\ImagenProducto;
use Model\PreferenciaUsuario;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

// --- FUNCIÓN HELPER PARA LA VISTA ---
function obtenerDireccion($direcciones, $tipo, $campo) {
    foreach($direcciones as $direccion) {
        if($direccion->tipo === $tipo) {
            return htmlspecialchars($direccion->$campo ?? '');
        }
    }
    return '';
}

class MarketplaceController {
    public static function index(Router $router) {
        if(!is_auth('comprador')) {
            header('Location: /login');
            exit();
        }

        $busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
        $categoriaId = filter_var($_GET['categoria'] ?? null, FILTER_VALIDATE_INT);
        $condiciones = [];
        $titulo = 'Para Ti';
        $usuarioId = $_SESSION['id'];

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
        } else {
            // --- LÓGICA DE PERSONALIZACIÓN ---
            $preferencias = PreferenciaUsuario::where('usuarioId', $usuarioId);
            $categoriasIdsPref = $preferencias ? json_decode($preferencias->categorias, true) : [];

            if (!empty($categoriasIdsPref)) {
                $titulo = 'Para Ti';
                $idsSeguros = array_map('intval', $categoriasIdsPref);
                $idsString = implode(',', $idsSeguros);
                if (!empty($idsString)) {
                    $condiciones[] = "categoriaId IN ($idsString)";
                }
            } else {
                // FALLBACK: Si no hay preferencias, mostrar de categorías populares
                $titulo = 'Productos Populares';
                // Consulta para obtener IDs de las 5 categorías con más productos
                $queryPopulares = "SELECT categoriaId, COUNT(id) as total FROM productos GROUP BY categoriaId ORDER BY total DESC LIMIT 5";
                $resultadoPopulares = Producto::consultarSQL($queryPopulares);
                
                $idsPopulares = [];
                foreach($resultadoPopulares as $fila) {
                    $idsPopulares[] = $fila->categoriaId;
                }

                if (!empty($idsPopulares)) {
                    $idsString = implode(',', $idsPopulares);
                    $condiciones[] = "categoriaId IN ($idsString)";
                }
            }
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
            'orden' => 'creado DESC',
            'limite' => $registros_por_pagina,
            'offset' => $paginacion->offset()
        ];
        
        $productos = Producto::metodoSQL($params);

        // Obtener favoritos del usuario
        $favoritosIds = [];
        $favoritos = Favorito::whereField('usuarioId', $usuarioId);
        $favoritosIds = $favoritos ? array_column($favoritos, 'productoId') : [];

        // Obtener imágenes principales para cada producto
        foreach($productos as $producto) {
            $imagenPrincipal = ImagenProducto::obtenerPrincipalPorProductoId($producto->id); 
            $producto->imagen_principal = $imagenPrincipal ? $imagenPrincipal->url : null;
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
        $vendedor->direccion = Direccion::where('usuarioId', $vendedor->id);
        
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

    public static function perfil(Router $router) {
        if (!is_auth('comprador')) {
            header('Location: /login');
            exit();
        }
    
        $idUsuario = $_SESSION['id'];
        $usuario = Usuario::find($idUsuario);
        if (!$usuario) {
            header('Location: /');
            exit();
        }

        $direcciones = Direccion::whereField('usuarioId', $idUsuario);
        
        $preferencias = PreferenciaUsuario::where('usuarioId', $idUsuario);
        $categoriasIds = $preferencias ? json_decode($preferencias->categorias, true) : [];
        
        $categoriasInteres = [];
        if(!empty($categoriasIds)) {
            $idsSeguros = array_map('intval', $categoriasIds);
            $idsString = implode(',', $idsSeguros);
            if (!empty($idsString)) {
                $categoriasInteres = Categoria::consultarSQL("SELECT * FROM categorias WHERE id IN ($idsString)");
            }
        }
        
        $valoracionesRecibidas = Valoracion::whereArray([
            'calificadoId' => $idUsuario,
            'estrellas IS NOT' => 'NULL',
            'moderado' => 1
        ]);

        foreach($valoracionesRecibidas as $valoracion) {
            $valoracion->calificador = Usuario::find($valoracion->calificadorId);
            $valoracion->producto = Producto::find($valoracion->productoId);
        }

        $router->render('marketplace/perfil/index', [
            'titulo' => 'Mi Perfil',
            'usuario' => $usuario,
            'direcciones' => $direcciones,
            'categoriasInteres' => $categoriasInteres,
            'valoraciones' => $valoracionesRecibidas,
            'show_hero' => false
        ]);
    }

    public static function editarPerfil(Router $router) {
        if (!is_auth('comprador')) {
            header('Location: /login');
            exit();
        }

        $usuario = Usuario::find($_SESSION['id']);
        $usuario->imagen_actual = $usuario->imagen;
        $alertas = [];
    
        $direcciones = Direccion::whereField('usuarioId', $usuario->id);
        $categorias = Categoria::all();
        $preferencias = PreferenciaUsuario::where('usuarioId', $usuario->id);
        $categoriasSeleccionadas = $preferencias ? json_decode($preferencias->categorias, true) : [];
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Procesamiento de imagen
            $carpeta_imagenes = '../public/img/usuarios';
            $nombre_imagen = '';

            // Manejar nueva imagen
            if (!empty($_FILES['imagen']['tmp_name'])) {
                if (!is_dir($carpeta_imagenes)) {
                    mkdir($carpeta_imagenes, 0755, true);
                }

                // Generar nombre único
                $nombre_imagen = md5(uniqid(rand(), true));
                $_POST['imagen'] = $nombre_imagen;

                // Procesar la imagen en formatos PNG y WebP
                $imagen_origen = imagecreatefromstring(file_get_contents($_FILES['imagen']['tmp_name']));
                if ($imagen_origen) {
                    // Guardar en formato PNG
                    imagepng($imagen_origen, "$carpeta_imagenes/{$nombre_imagen}.png", 9);

                    // Guardar en formato WebP
                    imagewebp($imagen_origen, "$carpeta_imagenes/{$nombre_imagen}.webp", 85);

                    // Liberar memoria
                    imagedestroy($imagen_origen);
                }
            }

            // Manejar eliminación de imagen
            if (isset($_POST['eliminar_imagen']) && $_POST['eliminar_imagen'] === 'on') {
                if (!empty($vendedor->imagen_actual)) {
                    if (file_exists("$carpeta_imagenes/{$vendedor->imagen_actual}.png")) {
                        unlink("$carpeta_imagenes/{$vendedor->imagen_actual}.png");
                    }
                    if (file_exists("$carpeta_imagenes/{$vendedor->imagen_actual}.webp")) {
                        unlink("$carpeta_imagenes/{$vendedor->imagen_actual}.webp");
                    }
                }
                $_POST['imagen'] = '';
            }
            
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validar_cuenta_dashboard();
    
            if (empty($alertas)) {
                $usuario->guardar();
    
                // Actualizar dirección
                Direccion::eliminarPorUsuario($usuario->id);
                if (!empty($_POST['calle_residencial'])) {
                    $direccion = new Direccion();
                    $direccion->tipo = 'residencial';
                    $direccion->calle = $_POST['calle_residencial'] ?? '';
                    $direccion->colonia = $_POST['colonia_residencial'] ?? '';
                    $direccion->ciudad = $_POST['ciudad_residencial'] ?? '';
                    $direccion->estado = $_POST['estado_residencial'] ?? '';
                    $direccion->codigo_postal = $_POST['codigo_postal_residencial'] ?? '';
                    $direccion->usuarioId = $usuario->id;
                    $direccion->guardar();
                }

                // Actualizar preferencias
                $categoriasPost = $_POST['categorias'] ?? [];
                if ($preferencias) {
                    // Si ya existen, se actualizan
                    $preferencias->categorias = json_encode($categoriasPost);
                    $preferencias->guardar();
                } else {
                    // Si no existen (por si omitió el paso inicial), se crean
                    $nuevaPreferencia = new PreferenciaUsuario([
                        'usuarioId' => $usuario->id, 
                        'categorias' => json_encode($categoriasPost)
                    ]);
                    $nuevaPreferencia->guardar();
                }   

                header('Location: /comprador/perfil');
                exit();
            }
        }
    
        // Renderiza usando el layout principal (layout.php)
        $router->render('marketplace/perfil/editar', [
            'titulo' => 'Editar Mi Perfil',
            'usuario' => $usuario,
            'alertas' => $alertas,
            'direcciones' => $direcciones,
            'categorias' => $categorias,
            'categoriasSeleccionadas' => $categoriasSeleccionadas,
            'fecha_hoy' => date('Y-m-d')
        ]);
    }


    public static function valoraciones(Router $router) {
        if(!is_auth('comprador')) {
            header('Location: /login');
            exit();
        }

        $idUsuario = $_SESSION['id'];

        $valoracionesRecibidas = Valoracion::whereArray([
            'calificadoId' => $idUsuario,
            'estrellas IS NOT' => 'NULL'
        ]);

        $totalEstrellas = 0;
        foreach($valoracionesRecibidas as $valoracion) {
            $valoracion->calificador = Usuario::find($valoracion->calificadorId);
            $valoracion->producto = Producto::find($valoracion->productoId);
            if ($valoracion->estrellas) $totalEstrellas += $valoracion->estrellas;
        }

        $promedio = !empty($valoracionesRecibidas) ? $totalEstrellas / count($valoracionesRecibidas) : 0;
        
        $router->render('marketplace/perfil/valoraciones', [
            'titulo' => 'Valoraciones que he Recibido',
            'valoraciones' => $valoracionesRecibidas,
            'promedio' => number_format($promedio, 1)
        ]);
    }
}