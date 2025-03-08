<?php

namespace Model;

class Usuario extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'nombre', 'apellido', 'email', 'pass', 'telefono', 'fecha_nacimiento', 'sexo', 'rol', 'verificado', 'codigo_sms', 'creado'];
    protected static $tabla = 'usuarios';  


    public $id;
    public $nombre;
    public $apellido;
    public $email;
    public $pass;
    public $telefono;
    public $fecha_nacimiento;
    public $sexo;
    public $rol;
    public $verificado;
    public $codigo_sms;
    public $creado;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->apellido = $args['apellido'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->pass = $args['pass'] ?? '';
        $this->telefono = $args['telefono'] ?? '';
        $this->fecha_nacimiento = $args['fecha_nacimiento'] ?? '';
        $this->sexo = $args['sexo'] ?? '';
        $this->rol = $args['rol'] ?? '';
        $this->verificado = $args['verificado'] ?? '';
        $this->codigo_sms = $args['codigo_sms'] ?? '';
        $this->creado = $args['creado'] ?? '';
    }

    
    // Validar formulario   
    public function validar() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre es obligatorio';
        }

        if(!$this->apellido) {
            self::$alertas['error'][] = 'El apellido es obligatorio';
        }

        return self::$alertas;
    }
}