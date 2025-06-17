<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\Producto;
use Model\Categoria;
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
        $usuarioId = $_SESSION['id']; // Get the authenticated user's ID

        // Obtener término de búsqueda si existe
        $busqueda = $_GET['busqueda'] ?? '';
        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;

        if($pagina_actual < 1) {
            header('Location: /vendedor/productos?page=1');
            exit();
        }

        $registros_por_pagina = 10;
        $condiciones = [];

        // Add condition to filter by the current seller's ID
        $condiciones[] = "usuarioId = '$usuarioId'"; // Filter products by the current user's ID

        if(!empty($busqueda)) {
            // Apply search conditions in addition to the user ID filter
            $searchConditions = Producto::buscar($busqueda);
            if (!empty($searchConditions)) {
                $condiciones[] = "(" . implode(' AND ', $searchConditions) . ")";
            }
        }

        // Obtener total de registros con las nuevas condiciones
        $total = Producto::totalCondiciones($condiciones);
        
        // Crear instancia de paginación
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);
        
        if ($paginacion->total_paginas() < $pagina_actual && $pagina_actual > 1) {
            header('Location: /vendedor/productos?page=1');
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

        // Pasar los productos a la vista
        $router->render('vendedor/productos/index', [
            'titulo' => 'Productos',
            'productos' => $productos,
            'paginacion' => $paginacion->paginacion(),
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

            // Sincronizar datos del formulario
            $producto->sincronizar($_POST);

            // Forzar stock a 1 si es artículo único
            if ($producto->estado === 'unico') {
                $producto->stock = 1;
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

            if(empty($alertas['error'])) {
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
                    
                    header('Location: /vendedor/productos');
                    exit();
                }
            }
        }

        // Renderizar la vista de creación de producto
        $router->render('vendedor/productos/crear', [
            'titulo' => 'Registrar Producto',
            'alertas' => $alertas,
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

            // Forzar stock a 1 si es artículo único
            if ($producto->estado === 'unico') {
                $producto->stock = 1;
            } else {
                $producto->stock = (int)$_POST['stock'] ?? 0;
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

            if (empty($alertas['error'])) {
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
                header('Location: /vendedor/productos');
                exit();
            }
        }

        $router->render('vendedor/productos/editar', [
            'titulo' => 'Editar Producto',
            'producto' => $producto,
            'alertas' => $alertas,
            'categorias' => $categorias,
            'imagenes_existentes' => $imagenes_existentes,
            'imagenes' => $imagenes,
            'edicion' => true
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
            
            // Eliminar imágenes
            $imagenes = ImagenProducto::whereField('productoId', $producto->id);
            foreach($imagenes as $imagen) {
                // Eliminar archivos físicos
                if(file_exists("../public/img/productos/{$imagen->url}.png")) {
                    unlink("../public/img/productos/{$imagen->url}.png");
                    unlink("../public/img/productos/{$imagen->url}.webp");
                }
                $imagen->eliminar();
            }
            
            // Eliminar producto
            $resultado = $producto->eliminar();
    
            if($resultado) {
                header('Location: /vendedor/productos');
                exit;
            }
        }
    }
}