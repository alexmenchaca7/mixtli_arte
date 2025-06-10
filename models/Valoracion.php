<?php

namespace Model;

class Valoracion extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'comentario', 'estrellas', 'tipo', 'moderado', 'creado', 'calificadorId', 'calificadoId'];
    protected static $tabla = 'resenas';

    public $id;
    public $comentario;
    public $estrellas;
    public $tipo;
    public $moderado;
    public $creado;
    public $calificadorId;
    public $calificadoId;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? NULL;
        $this->comentario = $args['comentario'] ?? '';
        $this->estrellas = $args['estrellas'] ?? '';
        $this->tipo = $args['tipo'] ?? ''; // comprador o vendedor
        $this->moderado = $args['moderado'] ?? date('Y-m-d H:i:s');
        $this->creado = $args['creado'] ?? date('Y-m-d H:i:s');
        $this->calificadorId = $args['calificadorId'] ?? '';
        $this->calificadoId = $args['calificadoId'] ?? '';
    }
}