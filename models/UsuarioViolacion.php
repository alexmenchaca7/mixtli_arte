<?php

namespace Model;

class UsuarioViolacion extends ActiveRecord {
    protected static $tabla = 'usuario_violaciones';
    protected static $columnasDB = ['id', 'usuario_id', 'reporte_producto_id', 'reporte_valoracion_id', 'motivo', 'fecha'];

    public $id;
    public $usuario_id;
    public $reporte_producto_id;
    public $reporte_valoracion_id;
    public $motivo;
    public $fecha;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->usuario_id = $args['usuario_id'] ?? null;
        $this->reporte_producto_id = $args['reporte_producto_id'] ?? null;
        $this->reporte_valoracion_id = $args['repreporte_valoracion_idorte_id'] ?? null;
        $this->motivo = $args['motivo'] ?? '';
        $this->fecha = $args['fecha'] ?? date('Y-m-d H:i:s');
    }

    public static function eliminarPorUsuario($usuarioId) {
        $query = "DELETE FROM " . static::$tabla . " WHERE usuario_id = " . self::$conexion->escape_string($usuarioId);
        $resultado = self::$conexion->query($query);
        return $resultado;
    }
}