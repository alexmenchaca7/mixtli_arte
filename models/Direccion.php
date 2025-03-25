<?php

namespace Model;

class Direccion extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'tipo', 'calle', 'colonia', 'ciudad', 'estado', 'codigo_postal', 'usuarioId'];
    protected static $tabla = 'direcciones';  


    public $id;
    public $tipo;
    public $calle;
    public $colonia;
    public $ciudad;
    public $estado;
    public $codigo_postal;
    public $usuarioId;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? NULL;
        $this->tipo = $args['tipo'] ?? '';
        $this->calle = $args['calle'] ?? '';
        $this->colonia = $args['colonia'] ?? '';
        $this->ciudad = $args['ciudad'] ?? '';
        $this->estado = $args['estado'] ?? '';
        $this->codigo_postal = $args['codigo_postal'] ?? '';
        $this->usuarioId = $args['usuarioId'] ?? '';
    }

    public static function eliminarPorUsuario($usuarioId) {
        $query = "DELETE FROM " . static::$tabla . " WHERE usuarioId = " . self::$conexion->escape_string($usuarioId);
        $resultado = self::$conexion->query($query);
        return $resultado;
    }
}