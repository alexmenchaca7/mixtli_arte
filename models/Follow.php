<?php

namespace Model;

class Follow extends ActiveRecord {
    protected static $tabla = 'follows';
    protected static $columnasDB = ['id', 'seguidorId', 'seguidoId', 'creado'];

    public $id;
    public $seguidorId;
    public $seguidoId;
    public $creado;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->seguidorId = $args['seguidorId'] ?? ''; // El que sigue (comprador)
        $this->seguidoId = $args['seguidoId'] ?? '';  // El que es seguido (vendedor)
        $this->creado = $args['creado'] ?? date('Y-m-d H:i:s');
    }

    // Valida que los IDs necesarios para la relación de seguimiento existan.
    public function validar() {
        if (!$this->seguidorId) {
            self::$alertas['error'][] = 'El ID del seguidor es obligatorio.';
        }
        if (!$this->seguidoId) {
            self::$alertas['error'][] = 'El ID del usuario a seguir es obligatorio.';
        }
        return self::$alertas;
    }
}