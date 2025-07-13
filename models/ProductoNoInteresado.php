<?php

namespace Model;

class ProductoNoInteresado extends ActiveRecord {
    protected static $tabla = 'productos_no_interesados';
    protected static $columnasDB = ['id', 'usuarioId', 'productoId', 'creado'];

    public $id;
    public $usuarioId;
    public $productoId;
    public $creado;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->usuarioId = $args['usuarioId'] ?? null;
        $this->productoId = $args['productoId'] ?? null;
        $this->creado = $args['creado'] ?? date('Y-m-d H:i:s');
    }

    public static function eliminarPorUsuario($usuarioId) {
        $query = "DELETE FROM " . static::$tabla . " WHERE usuarioId = " . self::$conexion->escape_string($usuarioId);
        $resultado = self::$conexion->query($query);
        return $resultado;
    }
}