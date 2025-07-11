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

    public function validar() {
        if (!$this->tipo) {
            self::$alertas['error'][] = 'El tipo de interacción es obligatorio.';
        }

        // Definimos los tipos de interacción que sí requieren un productoId
        $tiposQueRequierenProducto = [
            'clic',
            'favorito',
            'compra',
            'autocompletado_producto',
            'tiempo_en_pagina'
        ];

        // Solo validamos el productoId si el tipo de interacción está en la lista anterior
        if (in_array($this->tipo, $tiposQueRequierenProducto) && !$this->productoId) {
            self::$alertas['error'][] = 'El producto es obligatorio para este tipo de interacción.';
        }
        
        return self::$alertas;
    }

    public static function eliminarPorUsuario($usuarioId) {
        $query = "DELETE FROM " . static::$tabla . " WHERE usuarioId = " . self::$conexion->escape_string($usuarioId);
        $resultado = self::$conexion->query($query);
        return $resultado;
    }
}