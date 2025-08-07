<?php

namespace Model;

class Producto extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'nombre', 'descripcion', 'precio', 'stock', 'estado', 'tipo_original', 'creado', 'modificado', 'usuarioId', 'categoriaId'];
    protected static $tabla = 'productos';

    // Propiedad con las columnas a buscar
    protected static $buscarColumns = ['nombre', 'descripcion', 'estado'];

    public $id;
    public $nombre;
    public $descripcion;
    public $precio;
    public $stock;
    public $estado;
    public $tipo_original;
    public $creado;
    public $modificado;
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
        $this->tipo_original = $args['tipo_original'] ?? '';
        $this->creado = $args['creado'] ?? date('Y-m-d H:i:s');
        $this->modificado = $args['modificado'] ?? null;
        $this->usuarioId = $args['usuarioId'] ?? '';
        $this->categoriaId = $args['categoriaId'] ?? '';
    }


    public function validar() {
        // Reiniciamos el array de alertas para evitar duplicados en cada llamada
        self::$alertas = []; 

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
        // --- 1. Eliminar Imágenes Asociadas ---
        $imagenes = ImagenProducto::whereField('productoId', $this->id);
        foreach($imagenes as $imagen) {
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

        // --- 2. Eliminar Valoraciones y Puntos Fuertes ---
        Valoracion::eliminarPorProductoId($this->id);

        // --- 3. Eliminar Favoritos ---
        Favorito::eliminarPorProductoId($this->id);

        // --- 4. Eliminar Mensajes ---
        Mensaje::eliminarPorProductoId($this->id);

        // --- 5. Eliminar Reportes de Producto ---
        $reportes = ReporteProducto::whereField('productoId', $this->id);
        foreach($reportes as $reporte) {
            $reporte->eliminar();
        }
        
        // --- 6. Finalmente, eliminar el producto ---
        $resultado = parent::eliminar();
        
        return $resultado;
    }


    public static function searchByTerm(string $termino) {
        $terminoSeguro = self::$conexion->escape_string('%' . $termino . '%'); 
        $query = "SELECT * FROM " . static::$tabla . " WHERE nombre LIKE " . $terminoSeguro;
        return self::consultarSQL($query); 
    }

    public static function eliminarPorUsuario($usuarioId) {
        // Find all products for the given user
        $productos = self::whereField('usuarioId', $usuarioId);

        // Iterate through each product and delete it
        foreach ($productos as $producto) {
            // The eliminar() instance method handles deleting associated data
            $producto->eliminar();
        }

        return true;
    }
}