<?php

namespace Controllers;
use MVC\Router;
use Model\Follow;
use Classes\Email;
use Model\Usuario;
use Model\Favorito;
use Model\Producto;
use Model\Categoria;
use Model\Direccion;
use Model\Valoracion;
use Classes\Paginacion;
use Model\Notificacion;
use Model\ImagenProducto;
use Model\ReporteProducto;
use Model\PreferenciaUsuario;
use Model\HistorialInteraccion;
use Model\ProductoNoInteresado;
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
        $titulo = 'Para Ti';
        $usuarioId = $_SESSION['id'];

        // Guardando las busquedas realizadas en el historial de interacciones
        if (!empty($busqueda) && is_auth()) {
            $interaccion = new HistorialInteraccion([
                'tipo' => 'busqueda',
                'usuarioId' => $_SESSION['id'],
                'metadata' => json_encode(['termino' => $busqueda])
            ]);
            $interaccion->guardar();
        }

        // Obtener productos que NO le interesan al usuario
        $productosNoInteresados = ProductoNoInteresado::whereField('usuarioId', $usuarioId);
        $idsNoInteresados = array_column($productosNoInteresados, 'productoId');

        // Inicializar condiciones
        $condiciones = ["estado != 'agotado'"];

        // Añadir condición para excluir productos no interesados
        if (!empty($idsNoInteresados)) {
            $idsStringNoInteresados = implode(',', $idsNoInteresados);
            $condiciones[] = "id NOT IN ($idsStringNoInteresados)";
        }

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
            // --- LÓGICA DE PERSONALIZACIÓN Y RECOMENDACIÓN ---
            $categoriasRecomendadasIds = RecomendacionController::obtenerCategoriasRecomendadas($usuarioId);

            if (!empty($categoriasRecomendadasIds)) {
                $titulo = 'Para Ti';
                $idsString = implode(',', $categoriasRecomendadasIds);
                $condiciones[] = "categoriaId IN ($idsString)";
                // Para mantener el orden de relevancia, podemos usar FIELD en la cláusula ORDER BY
                $ordenPersonalizado = "FIELD(categoriaId, $idsString)";
            } else {
                // FALLBACK: Si no hay NADA (ni preferencias, ni interacciones), mostrar de categorías populares
                $titulo = 'Productos Populares';
                // Consulta para obtener IDs de las 5 categorías con más productos
                $queryPopulares = "SELECT categoriaId, COUNT(id) as total FROM productos WHERE estado != 'agotado' GROUP BY categoriaId ORDER BY total DESC LIMIT 5";
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
        $registros_por_pagina = 20;
        
        // Obtener total de registros con las condiciones
        $total = Producto::totalCondiciones($condiciones);
        
        // Validar página actual
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);
        
        if ($paginacion->total_paginas() < $pagina_actual && $pagina_actual > 1) {
            header('Location: /marketplace?page=1');
            exit();
        }

        // Modificamos el orden para priorizar la recomendación
        $orden = isset($ordenPersonalizado) ? $ordenPersonalizado . ', RAND()' : 'creado DESC';

        // Obtener productos con paginación
        $params = [
            'condiciones' => $condiciones,
            'orden' => $orden,
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
            'paginacion' => $paginacion->paginacion(),
            'categoria_seleccionada' => $categoriaId,
            'favoritosIds' => $favoritosIds,
            'busqueda' => $busqueda
        ]);
    }

    public static function masVendido(Router $router) {
        if(!is_auth('comprador')) {
            header('Location: /login');
            exit();
        }

        $titulo = 'Lo Más Vendido';
        $usuarioId = $_SESSION['id'];

        // Query para obtener los productos más vendidos basándose en las valoraciones
        $query = "
            SELECT p.*, AVG(v.estrellas) as promedio_valoraciones
            FROM productos p
            LEFT JOIN valoraciones v ON p.id = v.productoId
            WHERE p.estado != 'agotado'
            GROUP BY p.id
            ORDER BY promedio_valoraciones DESC
            LIMIT 20;
        ";

        $productos = Producto::consultarSQL($query);

        // Obtener favoritos del usuario
        $favoritosIds = [];
        $favoritos = Favorito::whereField('usuarioId', $usuarioId);
        $favoritosIds = $favoritos ? array_column($favoritos, 'productoId') : [];

        // Obtener imágenes principales para cada producto
        foreach($productos as $producto) {
            $imagenPrincipal = ImagenProducto::obtenerPrincipalPorProductoId($producto->id);
            $producto->imagen_principal = $imagenPrincipal ? $imagenPrincipal->url : null;
        }

        $categorias = Categoria::all();

        $router->render('marketplace/mas-vendido', [
            'titulo' => $titulo,
            'productos' => $productos,
            'categorias' => $categorias,
            'favoritosIds' => $favoritosIds,
        ]);
    }

    public static function novedades(Router $router) {
        if(!is_auth('comprador')) {
            header('Location: /login');
            exit();
        }

        $titulo = 'Novedades';
        $usuarioId = $_SESSION['id'];

        $condiciones = ["estado != 'agotado'"];
        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
        $registros_por_pagina = 20;

        $total = Producto::totalCondiciones($condiciones);
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);

        if ($paginacion->total_paginas() < $pagina_actual && $pagina_actual > 1) {
            header('Location: /novedades?page=1');
            exit();
        }

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

        $categorias = Categoria::all();

        $router->render('marketplace/novedades', [
            'titulo' => $titulo,
            'productos' => $productos,
            'categorias' => $categorias,
            'paginacion' => $paginacion->paginacion(),
            'favoritosIds' => $favoritosIds,
        ]);
    }

    public static function artesanosDestacados(Router $router) {
        if(!is_auth('comprador')) {
            header('Location: /login');
            exit();
        }

        $titulo = 'Artesanos Destacados';

        // Query para obtener los artesanos destacados basándose en sus valoraciones
        $query = "
            SELECT u.*, AVG(v.estrellas) as promedio_valoraciones, COUNT(v.id) as total_valoraciones
            FROM usuarios u
            LEFT JOIN valoraciones v ON u.id = v.calificadoId
            WHERE u.rol = 'vendedor'
            GROUP BY u.id
            HAVING total_valoraciones > 0
            ORDER BY promedio_valoraciones DESC, total_valoraciones DESC
            LIMIT 20;
        ";

        $artesanos = Usuario::consultarSQL($query);

        $router->render('marketplace/artesanos-destacados', [
            'titulo' => $titulo,
            'artesanos' => $artesanos,
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

        // Obtener valoraciones del vendedor
        $valoracionesVendedor = Valoracion::whereArray([
            'calificadoId' => $vendedor->id,
            'moderado' => 1,
            'estrellas IS NOT' => 'NULL'
        ]);
        
        $totalEstrellas = 0;
        foreach ($valoracionesVendedor as $valoracion) {
            $valoracion->calificador = Usuario::find($valoracion->calificadorId); // Cargar datos del comprador que calificó
            $totalEstrellas += $valoracion->estrellas;
        }
        
        $totalCalificaciones = count($valoracionesVendedor);
        $promedioEstrellas = $totalCalificaciones > 0 ? round($totalEstrellas / $totalCalificaciones, 1) : 0;
        
        // Lógica para productos relacionados o alternativos
        $productosRelacionados = [];
        $condiciones = [
            "categoriaId = '{$producto->categoriaId}'",
            "id != '{$producto->id}'",
            "estado != 'agotado'" // No mostrar otros productos agotados
        ];
        $productosRelacionados = Producto::metodoSQL([
            'condiciones' => $condiciones,
            'orden' => 'RAND()',
            'limite' => 4
        ]);

        // Cargar imagen principal para productos relacionados
        foreach($productosRelacionados as $relacionado) {
            $imagenPrincipal = ImagenProducto::obtenerPrincipalPorProductoId($relacionado->id); 
            $relacionado->imagen_principal = $imagenPrincipal ? $imagenPrincipal->url : null;
        }
        
        $router->render('marketplace/producto', [
            'titulo' => $producto->nombre,
            'producto' => $producto,
            'vendedor' => $vendedor,
            'promedioEstrellas' => $promedioEstrellas,
            'totalCalificaciones' => $totalCalificaciones,
            'valoraciones' => $valoracionesVendedor, 
            'productosRelacionados' => $productosRelacionados, 
            'categorias' => Categoria::all()
        ]);
    }

    public static function reportarProducto() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!is_auth()) {
                echo json_encode(['success' => false, 'error' => 'Debes iniciar sesión para reportar.']);
                return;
            }

            $datos = json_decode(file_get_contents('php://input'), true);

            // Verificar si ya existe un reporte previo
            $reporteExistente = ReporteProducto::existeReportePrevio($datos['productoId'], $_SESSION['id']);
            if($reporteExistente) {
                http_response_code(409); // 409 Conflict
                echo json_encode(['success' => false, 'error' => 'Ya has reportado este producto anteriormente.']);
                return;
            }
            
            $reporte = new ReporteProducto($datos);
            $reporte->usuarioId = $_SESSION['id'];
            
            // La validación se hace en el modelo
            $alertas = $reporte->validar();
            if(!empty($alertas['error'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => implode(', ', $alertas['error'])]);
                return;
            }

            // Primero, guardamos el reporte
            $resultado = $reporte->guardar();

            // Si el reporte NO se pudo guardar, detenemos todo.
            if(!$resultado) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'No se pudo procesar el reporte en este momento.']);
                return;
            }

            try {
                // Obtener información adicional para el reporte
                $producto = Producto::find($reporte->productoId);
                $vendedor = $producto ? Usuario::find($producto->usuarioId) : null;
                $nombreVendedor = $vendedor ? $vendedor->nombre . " " . $vendedor->apellido : "Desconocido";

                // PASO 1: Verifiquemos si encontramos administradores.
                $admins = Usuario::findAdmins();
                if (empty($admins)) {
                    // Si no hay admins, lo registramos en el log de errores de PHP
                    error_log("DEBUG: No se encontraron administradores para notificar.");
                }
                $urlProducto = "/producto?id=" . $reporte->productoId;

                // PASO 2: Creamos la notificación individual por reporte
                foreach($admins as $admin) {
                    $notificacion = new Notificacion([
                        'tipo' => 'reporte_producto',
                        'descripcion' => "Un usuario reportó el producto '{$producto->nombre}' por: {$reporte->motivo}.",
                        'mensaje' => "Nuevo reporte de producto", 
                        'url' => $urlProducto, 
                        'usuarioId' => $admin->id
                    ]);
                    // Verificamos si se guardó
                    $guardado = $notificacion->guardar();
                    if(!$guardado) {
                        error_log("DEBUG: FALLO al guardar notificación individual para admin ID {$admin->id}");
                    }
                }

                // PASO 3: Verificamos si se cumple el umbral para la alerta
                $conteoReciente = ReporteProducto::contarReportesRecientes($reporte->productoId, REPORTE_UMBRAL_TIEMPO);
                
                if ($conteoReciente >= REPORTE_UMBRAL_CANTIDAD) { 
                    foreach($admins as $admin) {
                        $notificacionAlerta = new Notificacion([
                            'tipo' => 'alerta_reporte_multiple',
                            'descripcion' => "El producto '{$producto->nombre}' ha recibido {$conteoReciente} reportes recientemente.",
                            'mensaje' => "Alerta de reportes múltiples", 
                            'url' => $urlProducto,
                            'usuarioId' => $admin->id
                        ]);
                        // Verificamos si se guardó
                        $guardadoAlerta = $notificacionAlerta->guardar();
                        if(!$guardadoAlerta) {
                            error_log("DEBUG: FALLO al guardar notificación de ALERTA para admin ID {$admin->id}");
                        }
                    }
                }

                // Si todo va bien, enviamos la respuesta de éxito
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Producto reportado exitosamente. Gracias por tu ayuda.']);
            } catch (\Throwable $th) {
                // Si algo truena (ej. la base de datos se desconecta), lo capturamos
                error_log("ERROR CRÍTICO EN NOTIFICACIONES: " . $th->getMessage());
                // Aun así, enviamos una respuesta de éxito al usuario porque el reporte SÍ se guardó.
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Producto reportado (con error al notificar).']);
            }
        }
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
            'estado !=' => 'agotado'
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

    public static function vendedorPublico(Router $router) {
        if (!is_auth()) {
            header('Location: /login');
            exit();
        }

        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: /marketplace');
            exit();
        }

        $vendedor = Usuario::find($id);
        if (!$vendedor || $vendedor->rol !== 'vendedor') {
            header('Location: /404');
            exit();
        }

        // --- LÓGICA DE PAGINACIÓN PARA PRODUCTOS ---
        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
        if(!$pagina_actual || $pagina_actual < 1) {
            header("Location: /perfil?id={$vendedor->id}&page=1");
            exit();
        }
        
        $registros_por_pagina = 8; // Puedes ajustar este número
        $condiciones = ["usuarioId = '{$vendedor->id}'"];
        
        $total_productos = Producto::totalCondiciones($condiciones);
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total_productos);
        
        if($paginacion->total_paginas() > 0 && $paginacion->total_paginas() < $pagina_actual) {
            header("Location: /perfil?id={$vendedor->id}&page=1");
            exit();
        }
        
        $params = [
            'condiciones' => $condiciones,
            'orden' => 'creado DESC',
            'limite' => $registros_por_pagina,
            'offset' => $paginacion->offset()
        ];
        $productos = Producto::metodoSQL($params);
        // --- FIN DE LÓGICA DE PAGINACIÓN ---


        // Obtener la dirección comercial
        $direcciones = Direccion::whereArray(['usuarioId' => $vendedor->id, 'tipo' => 'comercial']);
        $direccionComercial = !empty($direcciones) ? $direcciones[0] : null;

        // Obtener las imágenes de los productos paginados
        foreach($productos as $producto) {
            $imagenPrincipal = ImagenProducto::obtenerPrincipalPorProductoId($producto->id);
            $producto->imagen_principal = $imagenPrincipal ? $imagenPrincipal->url : null;
        }

        $valoraciones = Valoracion::whereArray(['calificadoId' => $vendedor->id, 'moderado' => 1]);

        // --- INICIO: NUEVA LÓGICA DE ESTADÍSTICAS ---
        $totalCalificaciones = 0;
        $totalEstrellas = 0;
        $desgloseEstrellas = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

        foreach($valoraciones as $valoracion) {
            if ($valoracion->estrellas !== null) {
                $totalCalificaciones++;
                $totalEstrellas += $valoracion->estrellas;
                if (isset($desgloseEstrellas[$valoracion->estrellas])) {
                    $desgloseEstrellas[$valoracion->estrellas]++;
                }
            }
            // Cargar datos del producto y calificador para el contexto
            $valoracion->calificador = Usuario::find($valoracion->calificadorId);
            $valoracion->producto = Producto::find($valoracion->productoId);
        }

        $promedioEstrellas = $totalCalificaciones > 0 ? round($totalEstrellas / $totalCalificaciones, 1) : 0;
        
        $esSeguidor = false;
        $favoritosIds = [];
        if (isset($_SESSION['id'])) {
            $follow = Follow::whereArray(['seguidorId' => $_SESSION['id'], 'seguidoId' => $vendedor->id]);
            if ($follow) $esSeguidor = true;

            $favoritos = Favorito::whereField('usuarioId', $_SESSION['id']);
            $favoritosIds = $favoritos ? array_column($favoritos, 'productoId') : [];
        }
        
        $categorias = Categoria::all();

        $router->render('marketplace/vendedor', [
            'titulo' => 'Perfil del Vendedor',
            'vendedor' => $vendedor,
            'productos' => $productos,
            'valoraciones' => $valoraciones,
            'promedioEstrellas' => $promedioEstrellas,
            'totalCalificaciones' => $totalCalificaciones,
            'desgloseEstrellas' => $desgloseEstrellas,
            'esSeguidor' => $esSeguidor,
            'favoritosIds' => $favoritosIds,
            'categorias' => $categorias,
            'direccionComercial' => $direccionComercial,
            'paginacion' => $paginacion->paginacion() // Pasar la paginación a la vista
        ]);
    }

    public static function compradorPublico(Router $router) {
        // 1. Seguridad: Solo los vendedores pueden ver este perfil
        if (!is_auth('vendedor')) {
            header('Location: /');
            exit();
        }

        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: /vendedor/mensajes'); // Redirigir si no hay ID
            exit();
        }

        // 2. Obtener datos del comprador
        $comprador = Usuario::find($id);
        if (!$comprador || $comprador->rol !== 'comprador') {
            header('Location: /404'); // O si el ID no corresponde a un comprador
            exit();
        }

        // 3. Obtener las calificaciones que ha recibido el comprador (y que están aprobadas)
        $valoraciones = Valoracion::whereArray([
            'calificadoId' => $comprador->id,
            'moderado' => 1,
            'tipo' => 'vendedor' // Solo valoraciones hechas por vendedores
        ]);

        $totalCalificaciones = 0;
        $totalEstrellas = 0;
        foreach($valoraciones as $valoracion) {
            // Cargar el producto asociado a cada valoración
            $valoracion->producto = Producto::find($valoracion->productoId);
            if($valoracion->estrellas) {
                $totalEstrellas += $valoracion->estrellas;
                $totalCalificaciones++;
            }
        }
        $promedioEstrellas = $totalCalificaciones > 0 ? round($totalEstrellas / $totalCalificaciones, 1) : 0;

        // 4. Renderizar la vista
        $router->render('marketplace/comprador', [
            'titulo' => 'Perfil del Comprador',
            'comprador' => $comprador,
            'valoraciones' => $valoraciones,
            'totalCalificaciones' => $totalCalificaciones,
            'promedioEstrellas' => $promedioEstrellas
        ], 'vendedor-layout'); // Usar el layout de vendedor para consistencia
    }

    public static function editarPerfil(Router $router) {
        if (!is_auth('comprador')) {
            header('Location: /login');
            exit();
        }
    
        $alertas = [];
        $usuario = Usuario::find($_SESSION['id']);
        $usuario->imagen_actual = $usuario->imagen; // Guardar referencia a la imagen actual
    
        $categorias = Categoria::all();
        
        // Cargar preferencias existentes
        $preferencias = PreferenciaUsuario::where('usuarioId', $usuario->id);
        $categoriasSeleccionadas = $preferencias ? json_decode($preferencias->categorias, true) : [];
    
        // Inicializar con la dirección de la BD o un objeto vacío
        $direccionesDB = Direccion::whereField('usuarioId', $usuario->id);
        $direccionResidencial = !empty($direccionesDB) ? $direccionesDB[0] : new Direccion(['tipo' => 'residencial']);
        $direcciones = [$direccionResidencial];
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // --- INICIO: LÓGICA DE IMAGEN ---
            $imagen_previa = $usuario->imagen; // Guardamos el nombre de la imagen para usarlo después
    
            $manager = new ImageManager(new Driver());
            $carpeta_imagenes = '../public/img/usuarios';
            if (!is_dir($carpeta_imagenes)) {
                mkdir($carpeta_imagenes, 0755, true);
            }
            
            if (!empty($_FILES['imagen']['tmp_name'])) {
                if ($imagen_previa) {
                    if (file_exists("{$carpeta_imagenes}/{$imagen_previa}.png")) unlink("{$carpeta_imagenes}/{$imagen_previa}.png");
                    if (file_exists("{$carpeta_imagenes}/{$imagen_previa}.webp")) unlink("{$carpeta_imagenes}/{$imagen_previa}.webp");
                }
                $nombre_imagen = md5(uniqid(rand(), true));
                $imagen_procesada = $manager->read($_FILES['imagen']['tmp_name']);
                $imagen_procesada->resize(400, 400, fn($c) => $c->aspectRatio()->upsize());
                $imagen_procesada->toWebp(90)->save("{$carpeta_imagenes}/{$nombre_imagen}.webp");
                $imagen_procesada->toPng()->save("{$carpeta_imagenes}/{$nombre_imagen}.png");
                $_POST['imagen'] = $nombre_imagen;
            }
            
            if (isset($_POST['eliminar_imagen'])) {
                if ($imagen_previa) {
                    if (file_exists("{$carpeta_imagenes}/{$imagen_previa}.png")) unlink("{$carpeta_imagenes}/{$imagen_previa}.png");
                    if (file_exists("{$carpeta_imagenes}/{$imagen_previa}.webp")) unlink("{$carpeta_imagenes}/{$imagen_previa}.webp");
                }
                $_POST['imagen'] = '';
            }
            // --- FIN: LÓGICA DE IMAGEN ---
    
            // Sincronizar usuario y otros datos
            $usuario->sincronizar($_POST);
    
            // Si no se subió una nueva imagen ni se marcó para eliminar, mantener la anterior
            if (empty($_FILES['imagen']['tmp_name']) && !isset($_POST['eliminar_imagen'])) {
                $usuario->imagen = $imagen_previa;
            }
    
            // Actualizamos 'imagen_actual' para que la vista muestre la imagen correcta si falla la validación
            $usuario->imagen_actual = $usuario->imagen;
            
            $direccionResidencial->sincronizar([
                'calle' => $_POST['calle_residencial'] ?? '',
                'colonia' => $_POST['colonia_residencial'] ?? '',
                'codigo_postal' => $_POST['codigo_postal_residencial'] ?? '',
                'ciudad' => $_POST['ciudad_residencial'] ?? '',
                'estado' => $_POST['estado_residencial'] ?? ''
            ]);
            $categoriasSeleccionadas = $_POST['categorias'] ?? [];
    
            // Validaciones
            $alertas = $usuario->validar_cuenta_dashboard();
    
            $camposDireccion = ['calle', 'colonia', 'codigo_postal', 'ciudad', 'estado'];
            $camposLlenos = 0;
            foreach ($camposDireccion as $campo) {
                if (!empty($direccionResidencial->$campo)) {
                    $camposLlenos++;
                }
            }
            if ($camposLlenos > 0 && $camposLlenos < count($camposDireccion)) {
                $alertas['error'][] = 'Si decides llenar tu dirección, todos los campos son requeridos.';
            }
    
            if (empty($alertas)) {
                // Guardar usuario
                $resultado = $usuario->guardar();
    
                if($resultado){
                    // Actualizar la imagen en la sesión para que se refleje de inmediato
                    $_SESSION['imagen'] = $usuario->imagen;
                }
    
                // Guardar o actualizar dirección si está completa
                if ($camposLlenos === count($camposDireccion)) {
                    $direccionResidencial->usuarioId = $usuario->id;
                    $direccionResidencial->guardar();
                } else {
                    if ($direccionResidencial->id) {
                        $direccionResidencial->eliminar();
                    }
                }
    
                // Guardar o actualizar preferencias
                if ($preferencias) {
                    $preferencias->categorias = json_encode($categoriasSeleccionadas);
                    $preferencias->guardar();
                } else {
                    $nuevaPreferencia = new PreferenciaUsuario([
                        'usuarioId' => $usuario->id, 
                        'categorias' => json_encode($categoriasSeleccionadas)
                    ]);
                    $nuevaPreferencia->guardar();
                }   
                
                Usuario::setAlerta('exito', 'Perfil actualizado correctamente');
                header('Location: /comprador/perfil/editar');
                exit();
            }
        }

       // Obtener productos no interesados para mostrarlos en el perfil
        $productosNoInteresados = ProductoNoInteresado::whereField('usuarioId', $usuario->id);
        $idsNoInteresados = array_column($productosNoInteresados, 'productoId');
        
        $productosExcluidos = [];
        if(!empty($idsNoInteresados)) {
            $idsString = implode(',', $idsNoInteresados);
            $productosExcluidos = Producto::consultarSQL("SELECT id, nombre FROM productos WHERE id IN ($idsString)");
            
            // **AÑADIR ESTO:** Obtener la imagen para cada producto excluido
            foreach($productosExcluidos as $producto) {
                $imagenPrincipal = ImagenProducto::obtenerPrincipalPorProductoId($producto->id);
                $producto->imagen_principal = $imagenPrincipal ? $imagenPrincipal->url : null;
            }
        }
    
        $router->render('marketplace/perfil/editar', [
            'titulo' => 'Editar Mi Perfil',
            'usuario' => $usuario,
            'alertas' => $alertas,
            'direcciones' => $direcciones,
            'categorias' => $categorias,
            'categoriasSeleccionadas' => $categoriasSeleccionadas,
            'productosExcluidos' => $productosExcluidos,
            'fecha_hoy' => date('Y-m-d')
        ]);
    }


    public static function cambiarPassword(Router $router) {
        if(!is_auth('comprador')) {
            header('Location: /login');
            exit();
        }

        $alertas = [];
        $usuario = Usuario::find($_SESSION['id']);

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevoPassword();

            if(empty($alertas)) {
                if($usuario->comprobar_password()) {
                    $usuario->pass = $usuario->password_nuevo;
                    $usuario->hashPassword();
                    $resultado = $usuario->guardar();

                    if($resultado) {
                        Usuario::setAlerta('exito', 'Contraseña actualizada correctamente.');
                        $alertas = Usuario::getAlertas();
                        
                        // Enviar email de notificación
                        $email = new Email($usuario->email, $usuario->nombre, ''); // El token no es necesario aquí
                        $email->enviarNotificacionContraseña();
                    }
                } else {
                    Usuario::setAlerta('error', 'La contraseña actual es incorrecta.');
                    $alertas = Usuario::getAlertas();
                }
            }
        }

        $router->render('marketplace/perfil/cambiar-password', [
            'titulo' => 'Cambiar Contraseña',
            'alertas' => $alertas
        ]);
    }


    public static function valoraciones(Router $router) {
        if (!is_auth()) {
            header('Location: /login');
            return;
        }

        $usuarioId = $_SESSION['id'];
        
        // 1. Calificaciones que el usuario HA HECHO (lógica existente)
        $valoracionesEmitidas = Valoracion::whereArray(['calificadorId' => $usuarioId]);
        foreach ($valoracionesEmitidas as $valoracion) {
            $valoracion->producto = Producto::find($valoracion->productoId);
        }
        
        // --- INICIO: NUEVA LÓGICA PARA CALIFICACIONES RECIBIDAS ---
        
        // 2. Calificaciones que el usuario HA RECIBIDO
        $valoracionesRecibidas = Valoracion::whereArray(['calificadoId' => $usuarioId, 'moderado' => 1]);
        
        // 3. Calcular estadísticas para las calificaciones recibidas
        $totalCalificacionesRecibidas = 0;
        $totalEstrellasRecibidas = 0;
        $desgloseEstrellasRecibidas = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

        foreach($valoracionesRecibidas as $valoracion) {
            if ($valoracion->estrellas !== null) {
                $totalCalificacionesRecibidas++;
                $totalEstrellasRecibidas += $valoracion->estrellas;
                if (isset($desgloseEstrellasRecibidas[$valoracion->estrellas])) {
                    $desgloseEstrellasRecibidas[$valoracion->estrellas]++;
                }
            }
            // Cargar datos del producto y del usuario que calificó
            $valoracion->calificador = Usuario::find($valoracion->calificadorId);
            $valoracion->producto = Producto::find($valoracion->productoId);
        }

        $promedioEstrellasRecibidas = $totalCalificacionesRecibidas > 0 ? round($totalEstrellasRecibidas / $totalCalificacionesRecibidas, 1) : 0;
        // --- FIN: NUEVA LÓGICA ---

        $router->render('marketplace/perfil/valoraciones', [
            'titulo' => 'Mis Calificaciones',
            'valoraciones' => $valoracionesEmitidas, // Mantenemos el nombre original para la primera pestaña
            'valoracionesRecibidas' => $valoracionesRecibidas, // Nuevo
            'totalCalificacionesRecibidas' => $totalCalificacionesRecibidas, // Nuevo
            'promedioEstrellasRecibidas' => $promedioEstrellasRecibidas, // Nuevo
            'desgloseEstrellasRecibidas' => $desgloseEstrellasRecibidas // Nuevo
        ]);
    }

    public static function marcarNoInteresa() {
        header('Content-Type: application/json');
        if (!is_auth()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Debes iniciar sesión.']);
            return;
        }

        $datos = json_decode(file_get_contents('php://input'), true);
        $productoId = filter_var($datos['productoId'] ?? null, FILTER_VALIDATE_INT);
        $usuarioId = $_SESSION['id'];

        if (!$productoId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Producto no válido.']);
            return;
        }

        $existente = ProductoNoInteresado::whereArray(['usuarioId' => $usuarioId, 'productoId' => $productoId]);
        if ($existente) {
            echo json_encode(['success' => true, 'message' => 'Preferencia ya registrada.']);
            return;
        }

        $preferencia = new ProductoNoInteresado(['usuarioId' => $usuarioId, 'productoId' => $productoId]);
        $resultado = $preferencia->guardar();

        if ($resultado) {
            // Registrar la interacción
            $interaccion = new HistorialInteraccion([
                'tipo' => 'no_interesa',
                'usuarioId' => $usuarioId,
                'productoId' => $productoId
            ]);
            $interaccion->guardar();
            echo json_encode(['success' => true, 'message' => 'Producto marcado como no interesante.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'No se pudo guardar la preferencia.']);
        }
    }

    public static function eliminarPreferenciaNoInteresa() {
        header('Content-Type: application/json');
        if (!is_auth()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Debes iniciar sesión.']);
            return;
        }

        $datos = json_decode(file_get_contents('php://input'), true);
        $productoId = filter_var($datos['productoId'] ?? null, FILTER_VALIDATE_INT);
        $usuarioId = $_SESSION['id'];

        if (!$productoId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Producto no válido.']);
            return;
        }

        // whereArray devuelve un array de objetos.
        $preferencias = ProductoNoInteresado::whereArray(['usuarioId' => $usuarioId, 'productoId' => $productoId]);

        // 1. Verificamos que el array NO esté vacío.
        if (!empty($preferencias)) {
            // 2. Accedemos al primer (y único) objeto del array.
            $preferenciaAEliminar = $preferencias[0]; 
            $resultado = $preferenciaAEliminar->eliminar();

            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Preferencia eliminada.']);
            } else {
                http_response_code(500); // Internal Server Error
                echo json_encode(['success' => false, 'error' => 'Ocurrió un error al eliminar la preferencia.']);
            }
        } else {
            // Si el array está vacío, significa que la preferencia no se encontró.
            http_response_code(404); // Not Found
            echo json_encode(['success' => false, 'error' => 'Preferencia no encontrada.']);
        }
    }
}