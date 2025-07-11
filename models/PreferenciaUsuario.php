<?php

namespace Model;

class PreferenciaUsuario extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'categorias', 'usuarioId'];
    protected static $tabla = 'preferencias_usuarios';  


    public $id;
    public $categorias;
    public $usuarioId;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? NULL;
        $this->categorias = $args['categorias'] ?? '[]'; // json de categorias que le interesan al usuario
        $this->usuarioId = $args['usuarioId'] ?? null;
    }

    public static function eliminarPorUsuario($usuarioId) {
        $query = "DELETE FROM " . static::$tabla . " WHERE usuarioId = " . self::$conexion->escape_string($usuarioId);
        $resultado = self::$conexion->query($query);
        return $resultado;
    }
}