<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\Follow;
use Classes\Email;
use Model\Mensaje;
use Model\Usuario;
use Model\Favorito;
use Model\Producto;
use Model\Categoria;
use Model\Valoracion;
use Classes\Paginacion;
use Model\ImagenProducto;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProductosController {
    public static function index(Router $router) {
        if(!is_auth('vendedor')) {
            header('Location: /login');
            exit();
        }

        // Obtener el ID del vendedor autenticado
        $usuarioId = $_SESSION['id']; 

        // Obtener término de búsqueda si existe
        $busqueda = $_GET['busqueda'] ?? '';
        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;

        if($pagina_actual < 1) {
            header('Location: /vendedor/productos?page=1');
            exit();
        }

        $registros_por_pagina = 10;

        // Paginación para Productos Activos
        $pagina_actual_activos = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
        if($pagina_actual_activos < 1) {
            header('Location: /vendedor/productos?page=1');
            exit();
        }
        
        // Paginación para el Historial
        $pagina_actual_historial = filter_var($_GET['page_historial'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
        if($pagina_actual_historial < 1) {
            header('Location: /vendedor/productos?page_historial=1');
            exit();
        }

        // 1. Consulta y Paginación de Productos Activos
        $condiciones_activos = ["usuarioId = '$usuarioId'", "(estado = 'disponible' OR estado = 'unico')"];
        if(!empty($busqueda)) {
            $searchConditions = Producto::buscar($busqueda);
            if (!empty($searchConditions)) {
                $condiciones_activos[] = "(" . implode(' AND ', $searchConditions) . ")";
            }
        }
        $total_activos = Producto::totalCondiciones($condiciones_activos);

        $paginacion_activos = new Paginacion($pagina_actual_activos, $registros_por_pagina, $total_activos, 'page');
        
        $productos_activos = Producto::metodoSQL([
            'condiciones' => $condiciones_activos,
            'orden' => 'modificado DESC',
            'limite' => $registros_por_pagina,
            'offset' => $paginacion_activos->offset()
        ]);
        foreach($productos_activos as $producto) {
            $imagenPrincipal = ImagenProducto::obtenerPrincipalPorProductoId($producto->id);
            $producto->imagen_principal = $imagenPrincipal ? $imagenPrincipal->url : null;
        }

        // 2. Consulta y Paginación del Historial
        $condiciones_historial = ["usuarioId = '$usuarioId'", "estado = 'agotado'"];
        $total_historial = Producto::totalCondiciones($condiciones_historial);

        $paginacion_historial = new Paginacion($pagina_actual_historial, $registros_por_pagina, $total_historial, 'page_historial');

        $productos_historial = Producto::metodoSQL([
            'condiciones' => $condiciones_historial,
            'orden' => 'modificado DESC',
            'limite' => $registros_por_pagina,
            'offset' => $paginacion_historial->offset()
        ]);
        foreach($productos_historial as $producto) {
            $imagenPrincipal = ImagenProducto::obtenerPrincipalPorProductoId($producto->id);
            $producto->imagen_principal = $imagenPrincipal ? $imagenPrincipal->url : null;
        }

        // Pasar los productos a la vista
        $router->render('vendedor/productos/index', [
            'titulo' => 'Productos',
            'productos_activos' => $productos_activos,
            'productos_historial' => $productos_historial,
            'paginacion_activos' => $paginacion_activos->paginacion(),
            'paginacion_historial' => $paginacion_historial->paginacion(),
            'busqueda' => $busqueda
        ], 'vendedor-layout');
    }

    public static function crear(Router $router) {
        if(!is_auth('vendedor')) {
            header('Location: /login');
            exit();
        }

        $producto = new Producto;
        $alertas = [];
        $imagenes = [];
        $manager = new ImageManager(new Driver());

        // Obtener categorias disponibles
        $categorias = Categoria::all();

        // Ejecutar el código después de que el usuario envíe el formulario
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_auth('vendedor')) {
                header('Location: /login');
                exit();
            }

            // Limpiamos las alertas de la sesión ANTES de hacer cualquier otra cosa.
            // Esto evita que se acumulen errores de intentos anteriores.
            $_SESSION['alertas'] = [];

            // Sincronizar datos del formulario
            $producto->sincronizar($_POST);

            // Forzar stock a 1 si es artículo único
            if ($producto->estado === 'unico') {
                $producto->tipo_original = 'unico'; // Guardamos su naturaleza original
            } else {
                $producto->tipo_original = 'disponible';
            }

            $producto->usuarioId = $_SESSION['id']; 
            $alertas = $producto->validar();

            // Procesar imágenes nuevas
            $imagenes_subidas = [];
            if(!empty($_FILES['nuevas_imagenes']['tmp_name'][0])) {
                foreach($_FILES['nuevas_imagenes']['tmp_name'] as $key => $tmp_name) {
                    if($_FILES['nuevas_imagenes']['error'][$key] === UPLOAD_ERR_OK) {
                        $imagen = new \stdClass();
                        $imagen->tmp = $tmp_name;
                        $imagen->name = $_FILES['nuevas_imagenes']['name'][$key];
                        $imagenes_subidas[] = $imagen;
                    }
                }
            }
            
            // Validar imágenes
            $total_imagenes = count($imagenes_subidas);
            if($total_imagenes > 5) {
                $alertas['error'][] = "Máximo 5 imágenes permitidas";
            }

            if(!empty($alertas['error'])) {
                // Usamos la función setAlerta para cada error, en lugar de usar implode.
                foreach($alertas['error'] as $error) {
                    Usuario::setAlerta('error', $error);
                }
            } else {
                $resultado = $producto->guardar();
                
                if($resultado) {
                    // Guardar imágenes
                    $carpeta = '../public/img/productos';
                    if(!is_dir($carpeta)) mkdir($carpeta, 0755, true);

                    foreach($imagenes_subidas as $imagen_data) {
                        try {
                            $nombre_unico = md5(uniqid(rand(), true));
                            $imagen = $manager->read($imagen_data->tmp);

                            // Redimensionar la imagen manteniendo el aspecto
                            $imagen->resize(800, 800, function ($constraint) {
                                $constraint->aspectRatio(); // Mantener la proporción
                                $constraint->upsize(); // Evitar que se escale hacia arriba si la imagen es más pequeña
                            });
                            
                            // Guardar formatos
                            $imagen->toWebp(85)->save("{$carpeta}/{$nombre_unico}.webp");
                            $imagen->toPng()->save("{$carpeta}/{$nombre_unico}.png");
                            
                            // Registrar en BD
                            $imagen_producto = new ImagenProducto([
                                'url' => $nombre_unico,
                                'productoId' => $producto->id
                            ]);
                            $imagen_producto->guardar();
                            
                        } catch(Exception $e) {
                            error_log("Error procesando imagen: " . $e->getMessage());
                            $alertas['error'][] = 'Error al procesar una imagen';
                        }
                    }

                    // Find all followers of this vendor
                    $seguidores = Follow::whereField('seguidoId', $producto->usuarioId);
                    if (!empty($seguidores)) {
                        $vendedor = Usuario::find($producto->usuarioId);
                        $urlProducto = "/marketplace/producto?id={$producto->id}";

                        foreach ($seguidores as $follow) {
                            $seguidor = Usuario::find($follow->seguidorId);
                            if ($seguidor) {
                                // Create on-site notification
                                $notificacion = new \Model\Notificacion([
                                    'usuarioId' => $seguidor->id,
                                    'tipo' => 'nuevo_producto',
                                    'mensaje' => "Tu artesano seguido, {$vendedor->nombre}, ha publicado un nuevo producto: {$producto->nombre}.",
                                    'url' => $urlProducto
                                ]);
                                $notificacion->guardar();

                                // Send email notification
                                $email = new Email($seguidor->email, $seguidor->nombre, '');
                                $email->enviarNotificacionNuevoProducto($vendedor->nombre, $producto->nombre, $urlProducto);
                            }
                        }
                    }
                    
                    Usuario::setAlerta('exito', 'Producto Creado Correctamente');
                    header('Location: /vendedor/productos');
                    exit();
                }
            }
        }

        // Renderizar la vista de creación de producto
        $router->render('vendedor/productos/crear', [
            'titulo' => 'Registrar Producto',
            'producto' => $producto,
            'imagenes' => $imagenes,
            'categorias' => $categorias
         ], 'vendedor-layout');
    }

    public static function editar(Router $router) {
        if (!is_auth('vendedor')) {
            header('Location: /login');
            exit();
        }

        $id = $_GET['id'];
        $producto = Producto::find($id);
        $producto_estado_actual = $producto->estado; 
        $alertas = [];
        $manager = new ImageManager(new Driver());

        // Obtener categorias disponibles
        $categorias = Categoria::all();

        if (!$producto || $producto->usuarioId !== $_SESSION['id']) { // Validate ownership
            header('Location: /vendedor/productos');
            exit();
        }

        // Obtener imágenes existentes
        $imagenes_existentes = ImagenProducto::whereField('productoId', $producto->id);
        if (!is_array($imagenes_existentes)) {
            $imagenes_existentes = []; // Aseguramos que sea un arreglo
        }

        // Pasar imágenes existentes a la vista
        $imagenes = $imagenes_existentes;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $producto->sincronizar($_POST);

            // Reglas de negocio
            if ($producto->tipo_original === 'unico') {
                $producto->stock = ($producto->estado === 'agotado') ? 0 : 1;
            }

            if ((int)$producto->stock === 0 && $producto->tipo_original !== 'unico') {
                $producto->estado = 'agotado';
            }

            $alertas = $producto->validar();

            // Procesar imágenes eliminadas
            $imagenes_eliminadas = $_POST['imagenes_eliminadas'] ?? [];

            // Procesar nuevas imágenes
            $nuevas_imagenes = [];
            if (!empty($_FILES['nuevas_imagenes']['tmp_name'][0])) {
                foreach ($_FILES['nuevas_imagenes']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['nuevas_imagenes']['error'][$key] === UPLOAD_ERR_OK) {
                        $imagen = new \stdClass();
                        $imagen->tmp = $tmp_name;
                        $imagen->name = $_FILES['nuevas_imagenes']['name'][$key];
                        $nuevas_imagenes[] = $imagen;
                    }
                }
            }

            // Validar total
            $total_final = (count($imagenes_existentes) - count($imagenes_eliminadas) + count($nuevas_imagenes));
            if ($total_final > 5) {
                $alertas['error'][] = "Máximo 5 imágenes permitidas";
            }

            if(!empty($alertas['error'])) {
                // Usamos la función setAlerta para cada error, en lugar de usar implode.
                foreach($alertas['error'] as $error) {
                    Usuario::setAlerta('error', $error);
                }
            } else {
                // Eliminar imágenes marcadas
                foreach ($imagenes_eliminadas as $id_imagen) {
                    $imagen = ImagenProducto::find($id_imagen);
                    if ($imagen) {
                        $carpeta = '../public/img/productos';
                        if (file_exists("{$carpeta}/{$imagen->url}.png")) {
                            unlink("{$carpeta}/{$imagen->url}.png");
                        }
                        if (file_exists("{$carpeta}/{$imagen->url}.webp")) {
                            unlink("{$carpeta}/{$imagen->url}.webp");
                        }
                        $imagen->eliminar();
                    }
                }

                // Procesar nuevas imágenes
                if (!empty($nuevas_imagenes)) {
                    $carpeta = '../public/img/productos';

                    foreach ($nuevas_imagenes as $imagen_data) {
                        try {
                            $nombre_unico = md5(uniqid(rand(), true));
                            $imagen = $manager->read($imagen_data->tmp);

                            // Redimensionar la imagen manteniendo proporciones
                            $imagen->resize(800, 800, function ($constraint) {
                                $constraint->aspectRatio(); // Mantener la proporción
                                $constraint->upsize(); // Evitar que se escale hacia arriba si la imagen es más pequeña
                            });

                            $imagen->toWebp(90)->save("{$carpeta}/{$nombre_unico}.webp");
                            $imagen->toPng()->save("{$carpeta}/{$nombre_unico}.png");

                            $imagen_producto = new ImagenProducto([
                                'url' => $nombre_unico,
                                'productoId' => $producto->id
                            ]);
                            $imagen_producto->guardar();
                        } catch (Exception $e) {
                            error_log("Error procesando imagen: " . $e->getMessage());
                            $alertas['error'][] = 'Error al procesar una imagen';
                        }
                    }
                }

                $producto->guardar();
                Usuario::setAlerta('exito', 'Producto Actualizado Correctamente');
                header('Location: /vendedor/productos');
                exit();
            }
        }

        $router->render('vendedor/productos/editar', [
            'titulo' => 'Editar Producto',
            'producto' => $producto,
            'categorias' => $categorias,
            'imagenes_existentes' => $imagenes_existentes,
            'imagenes' => $imagenes,
            'edicion' => true,
            'producto_estado_actual' => $producto_estado_actual
        ], 'vendedor-layout');
    }

    public static function eliminar() {  
        if (!is_auth('vendedor')) {
            header('Location: /login');
            exit();
        }
    
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!is_auth('vendedor')) {
                header('Location: /login');
                exit();
            }
            
            $id = $_POST['id'];
            $producto = Producto::find($id);
    
            if(!$producto || $producto->usuarioId !== $_SESSION['id']) { // Validate ownership
                header('Location: /vendedor/productos');
                exit;
            }
            
            // --- INICIO DE ELIMINACIÓN EN CASCADA ---

            // 1. Eliminar imágenes del producto (físicas y de la BD)
            $imagenes = ImagenProducto::whereField('productoId', $producto->id);
            foreach($imagenes as $imagen) {
                if(file_exists("../public/img/productos/{$imagen->url}.png")) {
                    unlink("../public/img/productos/{$imagen->url}.png");
                }
                if(file_exists("../public/img/productos/{$imagen->url}.webp")) {
                    unlink("../public/img/productos/{$imagen->url}.webp");
                }
                $imagen->eliminar();
            }

            // 2. Eliminar Puntos Fuertes (a través del modelo Valoracion) y luego las Valoraciones
            Valoracion::eliminarPorProductoId($producto->id);

            // 3. Eliminar Favoritos
            Favorito::eliminarPorProductoId($producto->id);

            // 4. Eliminar Mensajes
            Mensaje::eliminarPorProductoId($producto->id);
            
            // 5. Finalmente, eliminar el producto
            $resultado = $producto->eliminar();
    
            if($resultado) {
                Usuario::setAlerta('exito', 'Producto Eliminado Correctamente');
                header('Location: /vendedor/productos');
                exit;
            }
        }
    }
}