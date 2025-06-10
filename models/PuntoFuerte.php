<?php

namespace Model;

class PuntoFuerte extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'punto', 'valoracionId'];
    protected static $tabla = 'puntos_fuertes_valoraciones';


    public $id;
    public $punto;
    public $valoracionId;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? NULL;
        $this->punto = $args['punto'] ?? ''; // 'Negociación justa','Puntualidad','Honestidad','Comunicación efectiva','Pago oportuno','Buena comunicación'
        $this->valoracionId = $args['valoracionId'] ?? '';
    }
}