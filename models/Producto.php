<?php

namespace Model;

class Producto extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'nombre', 'descripcion', 'precio', 'stock', 'estado', 'creado', 'usuarioId', 'categoriaId'];
    protected static $tabla = 'productos';

    // Propiedad con las columnas a buscar
    protected static $buscarColumns = ['nombre', 'descripcion', 'estado'];

    public $id;
    public $nombre;
    public $descripcion;
    public $precio;
    public $stock;
    public $estado;
    public $creado;
    public $usuarioId;
    public $categoriaId;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? NULL;
        $this->nombre = $args['nombre'] ?? '';
        $this->descripcion = $args['descripcion'] ?? '';
        $this->precio = $args['precio'] ?? '';
        $this->stock = $args['stock'] ?? '';
        $this->estado = $args['estado'] ?? '';
        $this->creado = $args['creado'] ?? date('Y-m-d H:i:s');
        $this->usuarioId = $args['usuarioId'] ?? '';
        $this->categoriaId = $args['categoriaId'] ?? '';
    }


    public function validar() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El titulo del producto es obligatorio';
        } 
        if(!$this->descripcion) {
            self::$alertas['error'][] = 'La descripcion no puede ir vacia';
        }
        if(!$this->precio) {
            self::$alertas['error'][] = 'El precio es obligatorio';
        }
        if(!$this->estado) {
            self::$alertas['error'][] = 'Seleccionar el tipo de producto es obligatorio';
        }
        if(!$this->categoriaId) {
            self::$alertas['error'][] = 'La categoria es obligatoria';
        }
        
        return self::$alertas;
    }

    public function eliminar() {
        // --- 1. Eliminar Imágenes Asociadas (código anterior) ---
        $imagenes = ImagenProducto::whereField('productoId', $this->id);

        foreach($imagenes as $imagen) {
            // Usamos la propiedad `url` que es la correcta
            if(!empty($imagen->url)) { 
                $ruta_png = "../public/img/productos/{$imagen->url}.png";
                $ruta_webp = "../public/img/productos/{$imagen->url}.webp";
                if(file_exists($ruta_png)) {
                    unlink($ruta_png);
                }
                if(file_exists($ruta_webp)) {
                    unlink($ruta_webp);
                }
            }
            $imagen->eliminar(); // Elimina el registro de la BD
        }

        // --- 2. Eliminar Favoritos Asociados (NUEVO) ---
        $favoritos = Favorito::all();
        $favoritos_del_producto = array_filter($favoritos, fn($fav) => $fav->productoId === $this->id);
        
        foreach ($favoritos_del_producto as $favorito) {
            $favorito->eliminar();
        }

        // --- 3. Eliminar Reportes Asociados (NUEVO Y PREVENTIVO) ---
        $reportes = ReporteProducto::all();
        $reportes_del_producto = array_filter($reportes, fn($rep) => $rep->productoId === $this->id);

        foreach ($reportes_del_producto as $reporte) {
            $reporte->eliminar();
        }
        
        // --- 4. Finalmente, eliminar el producto ---
        $resultado = parent::eliminar();
        
        return $resultado;
    }


    public static function searchByTerm(string $termino) {
        $terminoSeguro = self::$conexion->escape_string('%' . $termino . '%'); 
        $query = "SELECT * FROM " . static::$tabla . " WHERE nombre LIKE " . $terminoSeguro;
        return self::consultarSQL($query); 
    }
}