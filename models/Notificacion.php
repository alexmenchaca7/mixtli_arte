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
        $this->mensaje = $args['mensaje'] ?? '';
        $this->url = $args['url'] ?? '';
        $this->leida = $args['leida'] ?? 0;
        $this->creado = $args['creado'] ?? date('Y-m-d H:i:s');
        $this->usuarioId = $args['usuarioId'] ?? '';
    }

    // Cuenta el total de notificaciones no leídas para un usuario.
    public static function contarNoLeidas($usuarioId) {
        $usuarioId = self::$conexion->escape_string($usuarioId);
        $query = "SELECT COUNT(*) as total FROM " . static::$tabla . " WHERE usuarioId = '{$usuarioId}' AND leida = 0";
        $resultado = self::$conexion->query($query);
        if ($resultado) {
            $data = $resultado->fetch_assoc();
            $resultado->free();
            return (int)$data['total'];
        }
        return 0;
    }

    // Marca una notificación específica como leída
    public static function marcarComoLeida($id, $usuarioId) {
        $id = self::$conexion->escape_string($id);
        $usuarioId = self::$conexion->escape_string($usuarioId);
        $query = "UPDATE " . static::$tabla . " SET leida = 1 WHERE id = '{$id}' AND usuarioId = '{$usuarioId}' LIMIT 1";
        return self::$conexion->query($query);
    }

    // Elimina una notificación específica
    public static function eliminarPorId($id, $usuarioId) {
        $id = self::$conexion->escape_string($id);
        $usuarioId = self::$conexion->escape_string($usuarioId);
        $query = "DELETE FROM " . static::$tabla . " WHERE id = '{$id}' AND usuarioId = '{$usuarioId}' LIMIT 1";
        return self::$conexion->query($query);
    }

    // Elimina todas las notificaciones de un usuario
    public static function eliminarPorUsuario($usuarioId) {
        $query = "DELETE FROM " . static::$tabla . " WHERE usuarioId = " . self::$conexion->escape_string($usuarioId);
        $resultado = self::$conexion->query($query);
        return $resultado;
    }
}