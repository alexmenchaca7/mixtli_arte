<?php

namespace Model;

class HistorialInteraccion extends ActiveRecord {
    protected static $tabla = 'historial_interacciones';
    protected static $columnasDB = ['id', 'tipo', 'fecha', 'usuarioId', 'productoId', 'metadata'];

    public $id;
    public $tipo; // Tipo de interaccion (clic, favorito, compra, busqueda, etc)
    public $fecha;
    public $usuarioId;
    public $productoId;
    public $metadata; // Para guardar información extra como términos de búsqueda o filtros.

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->tipo = $args['tipo'] ?? ''; 
        $this->fecha = $args['fecha'] ?? date('Y-m-d H:i:s');
        $this->usuarioId = $args['usuarioId'] ?? null;
        $this->productoId = $args['productoId'] ?? null;
        $this->metadata = $args['metadata'] ?? null;
    }

    // Puedes agregar este método de validación si no lo tienes
    public function validar() {
        if (!$this->tipo) {
            self::$alertas['error'][] = 'El tipo de interacción es obligatorio.';
        }
        if (!$this->productoId) {
            self::$alertas['error'][] = 'El producto es obligatorio para esta interacción.';
        }
        return self::$alertas;
    }
}