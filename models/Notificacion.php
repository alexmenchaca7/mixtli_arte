<?php

namespace Model;

class Notificacion extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'tipo', 'mensaje', 'url', 'leida', 'creado', 'usuarioId'];
    protected static $tabla = 'notificaciones';  


    public $id;
    public $tipo;
    public $mensaje;
    public $url;
    public $leida;
    public $creado;
    public $usuarioId;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? NULL;
        $this->tipo = $args['tipo'] ?? '';
        $this->mensaje = $args['mensaje'];
        $this->url = $args['url'];
        $this->leida = $args['leida'] ?? 0;
        $this->creado = $args['creado'] ?? date('Y-m-d H:i:s');
        $this->usuarioId = $args['usuarioId'] ?? '';
    }
}