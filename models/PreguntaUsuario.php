<?php

namespace Model;

class PreguntaUsuario extends ActiveRecord {
    protected static $tabla = 'preguntas_usuarios';
    // Add 'estado_revision' to columnsDB
    protected static $columnasDB = ['id', 'pregunta', 'categoriaFaqId', 'usuarioId', 'palabras_clave', 'frecuencia', 'marcada_frecuente', 'creado', 'estado_revision'];

    public $id;
    public $pregunta;
    public $categoriaFaqId;
    public $usuarioId;
    public $palabras_clave;
    public $frecuencia;
    public $marcada_frecuente;
    public $creado;
    public $estado_revision; // New property: 'pendiente', 'en_revision', 'faq_creada', 'descartada'

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->pregunta = $args['pregunta'] ?? '';
        $this->categoriaFaqId = $args['categoriaFaqId'] ?? null;
        $this->usuarioId = $args['usuarioId'] ?? null;
        $this->palabras_clave = $args['palabras_clave'] ?? '[]';
        $this->frecuencia = $args['frecuencia'] ?? 1;
        $this->marcada_frecuente = $args['marcada_frecuente'] ?? 0;
        $this->creado = $args['creado'] ?? date('Y-m-d H:i:s');
        $this->estado_revision = $args['estado_revision'] ?? 'pendiente'; // Default status
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
        $preguntas = self::all();
        $similares = [];

        foreach ($preguntas as $p) {
            similar_text(mb_strtolower($pregunta, 'UTF-8'), mb_strtolower($p->pregunta, 'UTF-8'), $percent);
            if ($percent >= ($umbral * 100)) {
                $similares[] = $p;
            }
        }
        return $similares;
    }

    // New method to find frequent questions pending review
    public static function findFrequentPendingReview() {
        return self::whereArray(['marcada_frecuente' => 1, 'estado_revision' => 'pendiente']);
    }
}