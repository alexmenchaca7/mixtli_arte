<?php

namespace Model;

class ReporteValoracion extends ActiveRecord {
    protected static $tabla = 'reportes_valoraciones';
    protected static $columnasDB = ['id', 'valoracionId', 'reportadorId', 'motivo', 'comentarios', 'estado', 'creado'];

    public $id;
    public $valoracionId;
    public $reportadorId;
    public $motivo;
    public $comentarios;
    public $estado;
    public $creado;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->valoracionId = $args['valoracionId'] ?? null;
        $this->reportadorId = $args['reportadorId'] ?? null;
        $this->motivo = $args['motivo'] ?? '';
        $this->comentarios = $args['comentarios'] ?? '';
        $this->estado = $args['estado'] ?? 'pendiente'; // Estados: pendiente, resuelto, descartado
        $this->creado = $args['creado'] ?? date('Y-m-d H:i:s');
    }

    public function validar() {
        if (!$this->valoracionId) self::$alertas['error'][] = 'La valoración es obligatoria.';
        if (!$this->reportadorId) self::$alertas['error'][] = 'El usuario que reporta es obligatorio.';
        if (!$this->motivo) self::$alertas['error'][] = 'El motivo del reporte es obligatorio.';
        return self::$alertas;
    }
}