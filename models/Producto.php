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
        $imagenes = ImagenProducto::all();
        $imagenes_del_producto = array_filter($imagenes, fn($img) => $img->productoId === $this->id);

        foreach ($imagenes_del_producto as $imagen) {
            $ruta_imagen = UPLOAD_PATH . '/' . $imagen->imagen;
            if (file_exists($ruta_imagen)) {
                unlink($ruta_imagen);
            }
            $imagen->eliminar();
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
        // Llama al método original de ActiveRecord para borrar el registro del producto.
        $resultado = parent::eliminar();
        
        return $resultado;
    }
}