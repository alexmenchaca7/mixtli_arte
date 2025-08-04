<?php

namespace Model;

class AdminAjusteSancion extends ActiveRecord {
    protected static $tabla = 'admin_ajustes_sanciones';
    protected static $columnasDB = ['id', 'admin_id', 'usuario_id', 'sancion_anterior', 'sancion_nueva', 'comentario', 'fecha_ajuste'];

    public $id;
    public $admin_id;
    public $usuario_id;
    public $sancion_anterior;
    public $sancion_nueva;
    public $comentario;
    public $fecha_ajuste;
    public $usuario_nombre;
    public $usuario_apellido;
    public $usuario_email;
    public $admin_nombre;
    public $admin_apellido;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->admin_id = $args['admin_id'] ?? null;
        $this->usuario_id = $args['usuario_id'] ?? null;
        $this->sancion_anterior = $args['sancion_anterior'] ?? 0;
        $this->sancion_nueva = $args['sancion_nueva'] ?? 0;
        $this->comentario = $args['comentario'] ?? '';
        $this->fecha_ajuste = $args['fecha_ajuste'] ?? date('Y-m-d H:i:s');
        $this->usuario_nombre = $args['usuario_nombre'] ?? '';
        $this->usuario_apellido = $args['usuario_apellido'] ?? '';
        $this->usuario_email = $args['usuario_email'] ?? '';
        $this->admin_nombre = $args['admin_nombre'] ?? '';
        $this->admin_apellido = $args['admin_apellido'] ?? '';
    }

    public function validar() {
        if (!$this->admin_id) {
            self::$alertas['error'][] = 'El administrador es obligatorio.';
        }
        if (!$this->usuario_id) {
            self::$alertas['error'][] = 'El usuario es obligatorio.';
        }
        if (!is_numeric($this->sancion_nueva)) {
            self::$alertas['error'][] = 'El nuevo número de sanciones debe ser un número.';
        }
        if (empty($this->comentario)) {
            self::$alertas['error'][] = 'El comentario es obligatorio para realizar un ajuste.';
        }
        return self::$alertas;
    }

    public static function eliminarPorUsuario($usuarioId) {
        $usuarioIdEsc = self::$conexion->escape_string($usuarioId);
        $query = "DELETE FROM " . static::$tabla . " WHERE admin_id = '{$usuarioIdEsc}' OR usuario_id = '{$usuarioIdEsc}'";
        $resultado = self::$conexion->query($query);
        return $resultado;
    }
}