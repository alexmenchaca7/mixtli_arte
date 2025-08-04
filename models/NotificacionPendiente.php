<?php

namespace Model;

class NotificacionPendiente extends ActiveRecord {
    protected static $tabla = 'notificaciones_pendientes';
    protected static $columnasDB = ['id', 'vendedorId', 'productoId', 'fecha_creacion', 'procesado'];

    public $id;
    public $vendedorId;
    public $productoId;
    public $fecha_creacion;
    public $procesado;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->vendedorId = $args['vendedorId'] ?? null;
        $this->productoId = $args['productoId'] ?? null;
        $this->fecha_creacion = $args['fecha_creacion'] ?? date('Y-m-d H:i:s');
        $this->procesado = $args['procesado'] ?? 0;
    }
}