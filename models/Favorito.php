<?php

namespace Model;

class Favorito extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'creado', 'productoId', 'usuarioId'];
    protected static $tabla = 'favoritos';

    public $id;
    public $creado;
    public $productoId;
    public $usuarioId;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? NULL;
        $this->creado = $args['creado'] ?? '';
        $this->productoId = $args['productoId'] ?? '';
        $this->usuarioId = $args['usuarioId'] ?? '';
    }
}