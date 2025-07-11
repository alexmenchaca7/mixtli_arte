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

    public static function eliminarPorProductoId($productoId) {
        $productoId_sanitizado = self::$conexion->escape_string($productoId);
        $query = "DELETE FROM " . static::$tabla . " WHERE productoId = '{$productoId_sanitizado}'";
        $resultado = self::$conexion->query($query);
        return $resultado;
    }

    public static function eliminarPorUsuario($usuarioId) {
        $query = "DELETE FROM " . static::$tabla . " WHERE usuarioId = " . self::$conexion->escape_string($usuarioId);
        $resultado = self::$conexion->query($query);
        return $resultado;
    }
}