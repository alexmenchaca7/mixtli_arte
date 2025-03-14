<?php

namespace Model;

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
}