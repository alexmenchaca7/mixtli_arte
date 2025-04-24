<?php

namespace Controllers;

use MVC\Router;
use Model\Producto;
use Model\ImagenProducto;
use Intervention\Image\ImageManagerStatic as Image;

class ProductosController {
    public static function index(Router $router) {
        if(!is_auth('vendedor')) {
            header('Location: /login');
            exit();
        }

        $router->render('vendedor/productos/index', [
            'titulo' => 'Productos'
        ], 'vendedor-layout');
    }

    public static function crear(Router $router) {
        if(!is_auth('vendedor')) {
            header('Location: /login');
            exit();
        }

        // Creando una nueva instancia de Producto
        $producto = new Producto;

        // Manejo de alertas
        $alertas = [];
        $imagenes = [];

        // Ejecutar el código después de que el usuario envíe el formulario
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_auth('vendedor')) {
                header('Location: /login');
                exit();
            }

            // Sincronizar datos del formulario
            $producto->sincronizar($_POST);

            // Validar los datos del producto
            $alertas = $producto->validar();

            // Validar imágenes
            $imagenes = $_FILES['imagenes'] ?? [];
            $totalImagenes = count($imagenes['name'] ?? 0);
            
            if ($totalImagenes > 5) {
                Producto::setAlerta('error', 'Máximo 5 imágenes permitidas');
                $alertas = Producto::getAlertas();
            }

            if (empty($alertas)) {
                $resultado = $producto->guardar();
                
                if ($resultado) {
                    // Procesar imágenes
                    for ($i = 0; $i < $totalImagenes; $i++) {
                        if ($imagenes['error'][$i] === 0) {
                            $nombre_imagen = md5(uniqid(rand(), true));
                            $carpeta_imagenes = '../public/img/productos';
                            
                            // Crear versiones
                            $imagen_png = Image::make($imagenes['tmp_name'][$i])->fit(800, 800)->encode('png', 80);
                            $imagen_webp = Image::make($imagenes['tmp_name'][$i])->fit(800, 800)->encode('webp', 80);
                            
                            // Guardar imágenes
                            $imagen_png->save("$carpeta_imagenes/$nombre_imagen.png");
                            $imagen_webp->save("$carpeta_imagenes/$nombre_imagen.webp");
                            
                            // Guardar en BD
                            (new ImagenProducto([
                                'url' => $nombre_imagen,
                                'productoId' => $producto->id
                            ]))->guardar();
                        }
                    }
                    
                    header('Location: /vendedor/productos');
                    exit;
                }
            }
        }

        // Renderizar la vista de creación de producto
        $router->render('vendedor/productos/crear', [
            'titulo' => 'Registrar Producto',
            'alertas' => $alertas,
            'producto' => $producto,
            'imagenes' => $imagenes
        ], 'vendedor-layout');
    }

    public static function editar(Router $router) {
        if(!is_auth('vendedor')) {
            header('Location: /login');
            exit();
        }

        $id = $_GET['id'];
        $producto = Producto::find($id);
        $alertas = [];
        
        // Obtener imágenes existentes
        $imagenes_existentes = ImagenProducto::where('productoId', $producto->id);
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $producto->sincronizar($_POST);
            $alertas = $producto->validar();
            
            // Validar límite de imágenes
            $nuevas_imagenes = count($_FILES['imagenes']['name'] ?? []);
            $imagenes_a_eliminar = count($_POST['eliminar_imagenes'] ?? []);
            $total_final = (count($imagenes_existentes) - $imagenes_a_eliminar) + $nuevas_imagenes;
            
            if($total_final > 5) {
                Producto::setAlerta('error', 'El número total de imágenes no puede exceder 5');
                $alertas = Producto::getAlertas();
            }

            if(empty($alertas)) {
                // Procesar eliminación de imágenes
                if(!empty($_POST['eliminar_imagenes'])) {
                    foreach($_POST['eliminar_imagenes'] as $imagenId) {
                        $imagen = ImagenProducto::find($imagenId);
                        if($imagen) {
                            // Eliminar archivos
                            $carpeta = '../public/img/productos';
                            if(file_exists("$carpeta/{$imagen->url}.png")) unlink("$carpeta/{$imagen->url}.png");
                            if(file_exists("$carpeta/{$imagen->url}.webp")) unlink("$carpeta/{$imagen->url}.webp");
                            $imagen->eliminar();
                        }
                    }
                }
                
                // Procesar nuevas imágenes
                if(!empty($_FILES['imagenes']['tmp_name'][0])) {
                    foreach($_FILES['imagenes']['tmp_name'] as $key => $tmp_name) {
                        $nombre_imagen = md5(uniqid(rand(), true));
                        $carpeta = '../public/img/productos';
                        
                        // Crear versiones
                        $imagen_png = Image::make($tmp_name)->fit(800, 800)->encode('png', 80);
                        $imagen_webp = Image::make($tmp_name)->fit(800, 800)->encode('webp', 80);
                        
                        // Guardar
                        $imagen_png->save("$carpeta/$nombre_imagen.png");
                        $imagen_webp->save("$carpeta/$nombre_imagen.webp");
                        
                        // Registrar en BD
                        (new ImagenProducto([
                            'url' => $nombre_imagen,
                            'productoId' => $producto->id
                        ]))->guardar();
                    }
                }
                
                // Guardar cambios en el producto
                $producto->guardar();
                header('Location: /vendedor/productos');
            }
        }

        $router->render('vendedor/productos/editar', [
            'titulo' => 'Editar Producto',
            'producto' => $producto,
            'alertas' => $alertas,
            'imagenes_existentes' => $imagenes_existentes
        ], 'vendedor-layout');
    }
}