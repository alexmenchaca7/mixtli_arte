<?php

namespace Model;

class PalabraClave extends ActiveRecord {
    protected static $tabla = 'palabras_clave';
    protected static $columnasDB = ['id', 'palabra'];

    // Propiedad con las columnas a buscar
    protected static $buscarColumns = ['palabra'];

    public $id;
    public $palabra;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->palabra = $args['palabra'] ?? '';
    }

    public function validar() {
        if(!$this->palabra) {
            self::$alertas['error'][] = 'La palabra clave es obligatoria';
        }
        return self::$alertas;
    }
}