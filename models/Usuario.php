<?php

namespace Model;

use DateTime;

class Usuario extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'nombre', 'apellido', 'email', 'pass', 'telefono', 'fecha_nacimiento', 'sexo', 'rol', 'verificado', 'token', 'creado', 'imagen', 'biografia', 'last_active'];
    protected static $tabla = 'usuarios';

    // Propiedad con las columnas a buscar
    protected static $buscarColumns = ['nombre', 'apellido', 'email', 'telefono', 'rol'];


    public $id;
    public $nombre;
    public $apellido;
    public $email;
    public $pass;
    public $pass2;
    public $telefono;
    public $fecha_nacimiento;
    public $sexo;
    public $rol;
    public $verificado;
    public $token;
    public $creado;
    public $imagen;
    public $biografia;
    public $last_active ;

    public $password_actual;
    public $password_nuevo; 


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? NULL;
        $this->nombre = $args['nombre'] ?? '';
        $this->apellido = $args['apellido'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->pass = $args['pass'] ?? '';
        $this->pass2 = $args['pass2'] ?? '';
        $this->telefono = $args['telefono'] ?? '';
        $this->fecha_nacimiento = $args['fecha_nacimiento'] ?? NULL;
        $this->sexo = $args['sexo'] ?? '';
        $this->rol = $args['rol'] ?? '';
        $this->verificado = $args['verificado'] ?? 0;
        $this->token = $args['token'] ?? '';
        $this->creado = $args['creado'] ?? date('Y-m-d H:i:s');
        $this->imagen = $args['imagen'] ?? '';
        $this->biografia = $args['biografia'] ?? '';
        $this->last_active  = $args['last_active '] ?? date('Y-m-d H:i:s');
    }


    // Validar el Login de Usuarios
    public function validarLogin() {
        if(!$this->email) {
            self::$alertas['error'][] = 'El email del usuario es obligatorio';
        } else if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'Email no válido';
        }
        if(!$this->pass) {
            self::$alertas['error'][] = 'El Password no puede ir vacio';
        }
        return self::$alertas;
    }


    // Validación para cuentas nuevas
    public function validar_cuenta() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre es obligatorio';
        }
        if(!$this->apellido) {
            self::$alertas['error'][] = 'El apellido es obligatorio';
        }
        if(!$this->fecha_nacimiento) {
            self::$alertas['error'][] = 'La fecha de nacimiento es obligatoria';
        } else {
            // Validar que la edad sea mayor o igual a 18 años
            $fecha_nacimiento = new \DateTime($this->fecha_nacimiento);
            $hoy = new \DateTime();
            $edad = $hoy->diff($fecha_nacimiento)->y;
    
            if ($edad < 18) {
                self::$alertas['error'][] = 'Debes tener al menos 18 años para registrarte';
            }
        }
        if(!$this->sexo) {
            self::$alertas['error'][] = 'El sexo es obligatorio';
        }
        if(!$this->rol) {
            self::$alertas['error'][] = 'El rol es obligatorio';
        }
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        }
        
        if(!$this->pass) {
            self::$alertas['error'][] = 'El password no puede ir vacío';
        } else {
            if(strlen($this->pass) < 8) {
                self::$alertas['error'][] = 'El password debe contener al menos 8 caracteres';
            }
            if (!preg_match('/[A-Z]/', $this->pass)) {
                self::$alertas['error'][] = 'El password debe contener al menos una letra mayúscula';
            }
            if (!preg_match('/[a-z]/', $this->pass)) {
                self::$alertas['error'][] = 'El password debe contener al menos una letra minúscula';
            }
            if (!preg_match('/[0-9]/', $this->pass)) {
                self::$alertas['error'][] = 'El password debe contener al menos un número';
            }
            if (!preg_match('/[^A-Za-z0-9]/', $this->pass)) {
                self::$alertas['error'][] = 'El password debe contener al menos un carácter especial';
            }
        }

        if($this->pass !== $this->pass2) {
            self::$alertas['error'][] = 'Los passwords no coinciden';
        }
        return self::$alertas;
    }

    // Validación para cuentas nuevas desde el dashboard
    public function validar_cuenta_dashboard() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre es obligatorio';
        }
        if(!$this->apellido) {
            self::$alertas['error'][] = 'El apellido es obligatorio';
        }
        if(!$this->fecha_nacimiento) {
            self::$alertas['error'][] = 'La fecha de nacimiento es obligatoria';
        } else {
            // Validar que la edad sea mayor o igual a 18 años
            $fecha_nacimiento = new \DateTime($this->fecha_nacimiento);
            $hoy = new \DateTime();
            $edad = $hoy->diff($fecha_nacimiento)->y;
    
            if ($edad < 18) {
                self::$alertas['error'][] = 'Debes tener al menos 18 años para registrarte';
            }
        }
        if(!$this->sexo) {
            self::$alertas['error'][] = 'El sexo es obligatorio';
        }
        if(!$this->rol) {
            self::$alertas['error'][] = 'El rol es obligatorio';
        }
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        }
        return self::$alertas;
    }

    
    // Valida un email
    public function validarEmail() {
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        } else if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'Email no válido';
        }
        return self::$alertas;
    }

    // Valida el Password 
    public function validarPassword() {
        if(!$this->pass) {
            self::$alertas['error'][] = 'El password no puede ir vacio';
        } else if(strlen($this->pass) < 6) {
            self::$alertas['error'][] = 'El password debe contener al menos 6 caracteres';
        }
        return self::$alertas;
    }

    public function validarNuevoPassword() {
        if(!$this->password_actual) {
            self::$alertas['error'][] = 'El password actual no puede ir vacío';
        }
        if(!$this->password_nuevo) {
            self::$alertas['error'][] = 'El password nuevo no puede ir vacío';
        }
        if(strlen($this->password_nuevo) < 8) {
            self::$alertas['error'][] = 'El password nuevo debe contener al menos 8 caracteres';
        }
        if (!preg_match('/[A-Z]/', $this->password_nuevo)) {
            self::$alertas['error'][] = 'El password debe contener al menos una letra mayúscula';
        }
        if (!preg_match('/[a-z]/', $this->password_nuevo)) {
            self::$alertas['error'][] = 'El password debe contener al menos una letra minúscula';
        }
        if (!preg_match('/[0-9]/', $this->password_nuevo)) {
            self::$alertas['error'][] = 'El password debe contener al menos un número';
        }
        if (!preg_match('/[^A-Za-z0-9]/', $this->password_nuevo)) {
            self::$alertas['error'][] = 'El password debe contener al menos un carácter especial';
        }
        if($this->password_actual === $this->password_nuevo) {
            self::$alertas['error'][] = 'El nuevo password no puede ser igual al actual';
        }
        return self::$alertas;
    }

    // Comprobar el password
    public function comprobar_password() : bool {
        return password_verify($this->password_actual, $this->pass );
    }

    // Hashea el password
    public function hashPassword() : void {
        $this->pass = password_hash($this->pass, PASSWORD_BCRYPT);
    }

    // Generar un Token
    public function crearToken() : void {
        $this->token = uniqid();
    }

    // Busca usuarios por término en nombre, apellido, email o teléfono
    public static function buscar($termino) {
        if(empty($termino)) return [];

        $termino = self::$conexion->escape_string($termino);
        return [
            "(CONCAT(nombre, ' ', apellido) LIKE '%$termino%' OR 
             email LIKE '%$termino%' OR 
             telefono LIKE '%$termino%' OR
             rol LIKE '%$termino%')"
        ];
    }

    public function obtenerDireccionComercial() {
        return Direccion::whereArray([
            'usuarioId' => $this->id,
            'tipo' => 'comercial'
        ]);
    }    
}