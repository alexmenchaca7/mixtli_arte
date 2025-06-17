<?php

namespace Model;

class PreguntaUsuario extends ActiveRecord {
    protected static $tabla = 'preguntas_usuarios';
    // Cambiar categoriaId para que se relacione con la nueva tabla
    protected static $columnasDB = ['id', 'pregunta', 'categoriaFaqId', 'usuarioId', 'palabras_clave', 'frecuencia', 'marcada_frecuente', 'creado'];

    public $id;
    public $pregunta;
    public $categoriaFaqId; // Cambiado de categoriaId a categoriaFaqId
    public $usuarioId;
    public $palabras_clave; // JSON de palabras clave
    public $frecuencia; // Contador de preguntas similares
    public $marcada_frecuente; // Booleano para notificar al soporte
    public $creado;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->pregunta = $args['pregunta'] ?? '';
        $this->categoriaFaqId = $args['categoriaFaqId'] ?? null; // Cambiado
        $this->usuarioId = $args['usuarioId'] ?? null;
        $this->palabras_clave = $args['palabras_clave'] ?? '[]';
        $this->frecuencia = $args['frecuencia'] ?? 1;
        $this->marcada_frecuente = $args['marcada_frecuente'] ?? 0;
        $this->creado = $args['creado'] ?? date('Y-m-d H:i:s');
    }

    public function validar() {
        if (!$this->pregunta) {
            self::$alertas['error'][] = 'La pregunta no puede estar vacía';
        }
        if (!$this->usuarioId) {
            self::$alertas['error'][] = 'El ID de usuario es obligatorio';
        }
        return self::$alertas;
    }

    // Método para buscar preguntas similares
    public static function buscarPreguntasSimilares($pregunta, $umbral = 0.7) {
        $preguntas = self::all(); // Obtener todas las preguntas existentes
        $similares = [];

        foreach ($preguntas as $p) {
            // Calcular similitud entre la nueva pregunta y las existentes
            similar_text(mb_strtolower($pregunta, 'UTF-8'), mb_strtolower($p->pregunta, 'UTF-8'), $percent);
            if ($percent >= ($umbral * 100)) {
                $similares[] = $p;
            }
        }
        return $similares;
    }
}