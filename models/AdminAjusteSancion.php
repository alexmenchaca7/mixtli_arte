<?php

namespace Model;

class AdminAjusteSancion extends ActiveRecord {
    protected static $tabla = 'admin_ajustes_sanciones';
    protected static $columnasDB = ['id', 'admin_id', 'vendedor_id', 'sancion_anterior', 'sancion_nueva', 'comentario', 'fecha_ajuste'];

    public $id;
    public $admin_id;
    public $vendedor_id;
    public $sancion_anterior;
    public $sancion_nueva;
    public $comentario;
    public $fecha_ajuste;
    public $vendedor_nombre;
    public $vendedor_apellido;
    public $admin_nombre;
    public $admin_apellido;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->admin_id = $args['admin_id'] ?? null;
        $this->vendedor_id = $args['vendedor_id'] ?? null;
        $this->sancion_anterior = $args['sancion_anterior'] ?? 0;
        $this->sancion_nueva = $args['sancion_nueva'] ?? 0;
        $this->comentario = $args['comentario'] ?? '';
        $this->fecha_ajuste = $args['fecha_ajuste'] ?? date('Y-m-d H:i:s');
        $this->vendedor_nombre = $args['vendedor_nombre'] ?? '';
        $this->vendedor_apellido = $args['vendedor_apellido'] ?? '';
        $this->admin_nombre = $args['admin_nombre'] ?? '';
        $this->admin_apellido = $args['admin_apellido'] ?? '';
    }

    public function validar() {
        if (!$this->admin_id) {
            self::$alertas['error'][] = 'El administrador es obligatorio.';
        }
        if (!$this->vendedor_id) {
            self::$alertas['error'][] = 'El vendedor es obligatorio.';
        }
        if (!is_numeric($this->sancion_nueva)) {
            self::$alertas['error'][] = 'El nuevo número de sanciones debe ser un número.';
        }
        if (empty($this->comentario)) {
            self::$alertas['error'][] = 'El comentario es obligatorio para realizar un ajuste.';
        }
        return self::$alertas;
    }
}