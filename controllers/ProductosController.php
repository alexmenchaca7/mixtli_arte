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
            if (count($_FILES['imagenes']['name']) > 5) {
                Producto::setAlerta('error', 'No puedes subir más de 5 imágenes.');
                $alertas = Producto::getAlertas();
            } else {
                // Procesar las imágenes (máximo 5)
                for ($i = 0; $i < count($_FILES['imagenes']['tmp_name']); $i++) {
                    if ($_FILES['imagenes']['error'][$i] === 0) {
                        // Procesar imagen usando Intervention
                        $imagen_temp = $_FILES['imagenes']['tmp_name'][$i];
                        $nombre_imagen = md5(uniqid(rand(), true));

                        // Redimensionar y guardar imagen en diferentes formatos
                        $imagen_png = Image::make($imagen_temp)->fit(800, 800)->encode('png', 80);
                        $imagen_webp = Image::make($imagen_temp)->fit(800, 800)->encode('webp', 80);

                        // Guardar las imágenes en el servidor
                        $carpeta_imagenes = '../public/img/productos';
                        if (!is_dir($carpeta_imagenes)) {
                            mkdir($carpeta_imagenes, 0775, true);
                        }
                        $imagen_png->save($carpeta_imagenes . '/' . $nombre_imagen . '.png');
                        $imagen_webp->save($carpeta_imagenes . '/' . $nombre_imagen . '.webp');

                        // Guardar las imágenes en el arreglo
                        $imagenes[] = $nombre_imagen;
                    }
                }
            }

            // Si no hay errores de validación
            if (empty($alertas)) {
                // Guardar el producto en la base de datos
                $resultado = $producto->guardar();

                if ($resultado) {
                    // Guardar las imágenes en la base de datos
                    foreach ($imagenes as $imagen) {
                        $imagen_producto = new ImagenProducto([
                            'url' => $imagen,
                            'productoId' => $producto->id // Relacionamos la imagen con el producto
                        ]);
                        $imagen_producto->guardar();
                    }

                    // Redirigir a la lista de productos
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
}