<?php

namespace Model;

use DateTime;

class Producto extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'nombre', 'descripcion', 'precio', 'stock', 'estado', 'creado', 'usuarioId', 'categoriaId'];
    protected static $tabla = 'usuarios';  


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
}