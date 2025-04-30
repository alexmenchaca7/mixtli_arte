<?php

namespace Model;

class Mensaje extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'contenido', 'tipo', 'creado', 'remitenteId', 'destinatarioId', 'productoId'];
    protected static $tabla = 'mensajes';  


    public $id;
    public $contenido;
    public $tipo;
    public $creado;
    public $remitenteId;
    public $destinatarioId;
    public $productoId;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? NULL;
        $this->contenido = $args['contenido'] ?? '';
        $this->tipo = $args['tipo'] ?? '';
        $this->creado = $args['creado'] ?? date('Y-m-d H:i:s');
        $this->remitenteId = $args['remitenteId'] ?? '';
        $this->destinatarioId = $args['destinatarioId'] ?? '';
        $this->productoId = $args['productoId'] ?? '';
    }
}