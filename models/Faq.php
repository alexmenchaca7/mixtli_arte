<?php

namespace Model;

class Faq extends ActiveRecord {
    protected static $tabla = 'faqs';
    // Cambiar categoriaId para que se relacione con la nueva tabla
    protected static $columnasDB = ['id', 'pregunta', 'respuesta', 'categoriaFaqId', 'creado', 'actualizado'];

    public $id;
    public $pregunta;
    public $respuesta;
    public $categoriaFaqId; // Cambiado de categoriaId a categoriaFaqId
    public $creado;
    public $actualizado;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->pregunta = $args['pregunta'] ?? '';
        $this->respuesta = $args['respuesta'] ?? '';
        $this->categoriaFaqId = $args['categoriaFaqId'] ?? null; // Cambiado
        $this->creado = $args['creado'] ?? date('Y-m-d H:i:s');
        $this->actualizado = $args['actualizado'] ?? date('Y-m-d H:i:s');
    }

    public function validar() {
        if (!$this->pregunta) {
            self::$alertas['error'][] = 'La pregunta es obligatoria';
        }
        if (!$this->respuesta) {
            self::$alertas['error'][] = 'La respuesta es obligatoria';
        }
        if (!$this->categoriaFaqId) { // Cambiado
            self::$alertas['error'][] = 'La categoría es obligatoria';
        }
        return self::$alertas;
    }
}