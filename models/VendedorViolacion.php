<?php

namespace Model;

class VendedorViolacion extends ActiveRecord {
    protected static $tabla = 'vendedor_violaciones';
    protected static $columnasDB = ['id', 'vendedor_id', 'reporte_id', 'motivo', 'fecha'];

    public $id;
    public $vendedor_id;
    public $reporte_id;
    public $motivo;
    public $fecha;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->vendedor_id = $args['vendedor_id'] ?? null;
        $this->reporte_id = $args['reporte_id'] ?? null;
        $this->motivo = $args['motivo'] ?? '';
        $this->fecha = $args['fecha'] ?? date('Y-m-d H:i:s');
    }

    public static function eliminarPorUsuario($usuarioId) {
        $query = "DELETE FROM " . static::$tabla . " WHERE vendedor_id = " . self::$conexion->escape_string($usuarioId);
        $resultado = self::$conexion->query($query);
        return $resultado;
    }
}