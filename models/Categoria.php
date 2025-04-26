<?php

namespace Model;

class Categoria extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'nombre', 'descripcion'];
    protected static $tabla = 'categorias';

    // Propiedad con las columnas a buscar
    protected static $buscarColumns = ['nombre', 'descripcion'];


    public $id;
    public $nombre;
    public $descripcion;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? NULL;
        $this->nombre = $args['nombre'] ?? '';
        $this->descripcion = $args['descripcion'] ?? '';
    }

    // Validar formulario de categorias
    public function validar() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre de la categoria es obligatorio';
        }
        return self::$alertas;
    }

    // Busca categorias por término en nombre o descripcion
    public static function buscar($termino) {
        if(empty($termino)) return [];

        $termino = self::$conexion->escape_string($termino);
        return [
            "(nombre LIKE '%$termino%' OR 
              descripcion LIKE '%$termino%')"
        ];
    }
}