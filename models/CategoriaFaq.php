<?php

namespace Model;

class CategoriaFaq extends ActiveRecord {
    protected static $columnasDB = ['id', 'nombre', 'descripcion'];
    protected static $tabla = 'categorias_faq'; // Asignar la nueva tabla

    public $id;
    public $nombre;
    public $descripcion;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? NULL;
        $this->nombre = $args['nombre'] ?? '';
        $this->descripcion = $args['descripcion'] ?? '';
    }

    public function validar() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre de la categoría de FAQ es obligatorio';
        }
        return self::$alertas;
    }
}