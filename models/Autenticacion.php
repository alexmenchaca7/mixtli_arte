<?php

namespace Model;

class Autenticacion extends ActiveRecord {
    
    protected static $columnasDB = ['id', 'codigo', 'expiracion', 'usuarioId'];
    protected static $tabla = 'autenticacion_2fa';  

    public $id;
    public $codigo;
    public $expiracion;
    public $usuarioId;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? NULL;
        $this->codigo = $args['codigo'] ?? '';
        $this->expiracion = $args['expiracion'] ?? '';
        $this->usuarioId = $args['usuarioId'] ?? '';
    }

    public function generarCodigo() {
        $this->codigo = rand(100000, 999999);
        $this->expiracion = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    }

    public function validarCodigo($codigo) {
        $ahora = date('Y-m-d H:i:s');
        return $this->codigo === $codigo && $this->expiracion > $ahora;
    }
}