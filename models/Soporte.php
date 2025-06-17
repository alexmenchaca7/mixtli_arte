<?php

namespace Model;

class Soporte extends ActiveRecord {

    protected static $tabla = 'consultas_soporte';
    protected static $columnasDB = ['id', 'email', 'asunto', 'mensaje', 'numero_caso', 'estado', 'creado', 'actualizado', 'fecha_resolucion'];

    public $id;
    public $email;
    public $asunto;
    public $mensaje;
    public $numero_caso;
    public $estado;
    public $creado;
    public $actualizado;
    public $fecha_resolucion; // Nueva propiedad

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->email = $args['email'] ?? '';
        $this->asunto = $args['asunto'] ?? '';
        $this->mensaje = $args['mensaje'] ?? '';
        $this->numero_caso = $args['numero_caso'] ?? '';
        $this->estado = $args['estado'] ?? 'pendiente';
        $this->creado = $args['creado'] ?? date('Y-m-d H:i:s');
        $this->actualizado = $args['actualizado'] ?? date('Y-m-d H:i:s');
        $this->fecha_resolucion = $args['fecha_resolucion'] ?? null; // Inicializar a null
    }

    public function validar() {
        if (!$this->email) {
            self::$alertas['error'][] = 'El correo electrónico es obligatorio.';
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'El correo electrónico no es válido.';
        }
        if (!$this->asunto) {
            self::$alertas['error'][] = 'El asunto es obligatorio.';
        }
        if (!$this->mensaje) {
            self::$alertas['error'][] = 'La descripción del problema es obligatoria.';
        }
        return self::$alertas;
    }

    public function generarNumeroCaso() {
        $this->numero_caso = uniqid('SOPORTE-');
    }
}