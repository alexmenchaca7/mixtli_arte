<?php

namespace Model;

class ReporteProducto extends ActiveRecord {
    protected static $tabla = 'reportes_productos';
    protected static $columnasDB = ['id', 'productoId', 'usuarioId', 'motivo', 'comentarios', 'estado', 'creado'];

    public $id;
    public $productoId;
    public $usuarioId;
    public $motivo;
    public $comentarios;
    public $estado;
    public $creado;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->productoId = $args['productoId'] ?? null;
        $this->usuarioId = $args['usuarioId'] ?? null;
        $this->motivo = $args['motivo'] ?? '';
        $this->comentarios = $args['comentarios'] ?? '';
        $this->estado = $args['estado'] ?? 'pendiente';
        $this->creado = $args['creado'] ?? date('Y-m-d H:i:s');
    }

    public function validar() {
        if (!$this->motivo) {
            self::$alertas['error'][] = 'El motivo del reporte es obligatorio.';
        }
        if (!$this->productoId || !$this->usuarioId) {
            self::$alertas['error'][] = 'Faltan datos para procesar el reporte.';
        }
        return self::$alertas;
    }

    // Cuenta los reportes recientes para un producto específico dentro de un intervalo de tiempo.
    public static function contarReportesRecientes($productoId, $intervalo) {
        $query = "SELECT COUNT(*) FROM " . self::$tabla . " WHERE productoId = " . self::$conexion->escape_string($productoId) . " AND creado >= NOW() - INTERVAL " . $intervalo;
        $resultado = self::$conexion->query($query);
        $fila = $resultado->fetch_array();
        return (int)$fila[0];
    }

    // Cuenta el total de reportes para un producto.
    public static function contarTotalReportes($productoId) {
        $query = "SELECT COUNT(*) FROM " . self::$tabla . " WHERE productoId = " . self::$conexion->escape_string($productoId);
        $resultado = self::$conexion->query($query);
        $fila = $resultado->fetch_array();
        return (int)$fila[0];
    }
}