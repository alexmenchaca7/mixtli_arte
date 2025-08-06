<?php

namespace Model;

class Valoracion extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'comentario', 'estrellas', 'tipo', 'moderado', 'calificadorId', 'calificadoId', 'productoId', 'sale_completed_at'];
    protected static $tabla = 'valoraciones';

    public $id;
    public $comentario;
    public $estrellas;
    public $tipo;
    public $moderado;
    public $calificadorId;
    public $calificadoId;
    public $productoId;
    public $sale_completed_at;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? NULL;
        $this->comentario = $args['comentario'] ?? '';
        $this->estrellas = $args['estrellas'] ?? '';
        $this->tipo = $args['tipo'] ?? ''; // comprador o vendedor
        $this->moderado = $args['moderado'] ?? 0; // Se establece 0 como valor por defecto, indicando "pendiente de moderación".
        $this->calificadorId = $args['calificadorId'] ?? '';
        $this->calificadoId = $args['calificadoId'] ?? '';
        $this->productoId = $args['productoId'] ?? '';
        $this->sale_completed_at = $args['sale_completed_at'] ?? date('Y-m-d H:i:s');
    }

    public static function eliminarPorProductoId($productoId) {
        // Primero, encontrar todas las valoraciones para este producto
        $query_select = "SELECT id FROM " . static::$tabla . " WHERE productoId = " . self::$conexion->escape_string($productoId);
        $valoraciones = self::consultarSQL($query_select);

        // Para cada valoración, eliminar sus Puntos Fuertes asociados
        foreach($valoraciones as $valoracion) {
            PuntoFuerte::eliminarPorValoracionId($valoracion->id);
        }

        // Finalmente, eliminar todas las valoraciones del producto
        $query_delete = "DELETE FROM " . static::$tabla . " WHERE productoId = " . self::$conexion->escape_string($productoId);
        $resultado = self::$conexion->query($query_delete);
        return $resultado;
    }

    public static function eliminarPorUsuario($usuarioId) {
        $usuarioIdEsc = self::$conexion->escape_string($usuarioId);
        $query = "DELETE FROM " . static::$tabla . " WHERE calificadorId = '{$usuarioIdEsc}' OR calificadoId = '{$usuarioIdEsc}'";
        $resultado = self::$conexion->query($query);
        return $resultado;
    }
}