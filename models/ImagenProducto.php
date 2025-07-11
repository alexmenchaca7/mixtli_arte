<?php

namespace Model;

class ImagenProducto extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'url', 'creado', 'productoId'];
    protected static $tabla = 'imagenes_producto';  


    public $id;
    public $url;
    public $creado;
    public $productoId;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? NULL;
        $this->url = $args['url'] ?? '';
        $this->creado = $args['creado'] ?? date('Y-m-d H:i:s');
        $this->productoId = $args['productoId'] ?? '';
    }

    public static function buscarPorProducto($productoId) {
        $query = "SELECT * FROM " . static::$tabla . " WHERE productoId = {$productoId}";
        return self::consultarSQL($query);
    }

    public static function obtenerPrincipalPorProductoId(int $productoId) {
        // Busca la primera imagen asociada a ese productoId, ordenada por ID
        $query = "SELECT * FROM " . self::$tabla . " WHERE productoId = " . self::$conexion->escape_string($productoId) . " ORDER BY id ASC LIMIT 1";
        $resultado = self::consultarSQL($query); // Usa el método heredado de ActiveRecord
        return array_shift($resultado); // Devuelve el objeto ImagenProducto o null si no hay
    }

    public static function eliminarPorUsuario($usuarioId) {
        $query = "DELETE FROM " . static::$tabla . " WHERE usuarioId = " . self::$conexion->escape_string($usuarioId);
        $resultado = self::$conexion->query($query);
        return $resultado;
    }
}