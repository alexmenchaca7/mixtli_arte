<?php

namespace Model;

class HistorialInteraccion extends ActiveRecord {
    protected static $tabla = 'historial_interacciones';
    protected static $columnasDB = ['id', 'tipo', 'fecha', 'usuarioId', 'productoId'];

    public $id;
    public $tipo; // Tipo de interaccion (clic, favorito, compra, busqueda, etc)
    public $fecha;
    public $usuarioId;
    public $productoId;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->tipo = $args['tipo'] ?? ''; 
        $this->fecha = $args['fecha'] ?? date('Y-m-d H:i:s');
        $this->usuarioId = $args['usuarioId'] ?? '';
        $this->productoId = $args['productoId'] ?? '';
    }
}