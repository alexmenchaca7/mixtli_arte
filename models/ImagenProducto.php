<?php

namespace Model;

class ImagenProducto extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'url', 'creado', 'productoId'];
    protected static $tabla = 'imagenes_producto';  


    public $id;
    public $url;
    public $creado;
    public $productoId;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? NULL;
        $this->url = $args['url'] ?? '';
        $this->creado = $args['creado'] ?? date('Y-m-d H:i:s');
        $this->productoId = $args['productoId'] ?? '';
    }
}