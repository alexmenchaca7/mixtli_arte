<?php

namespace Model;

class Autenticacion extends ActiveRecord {
    
    protected static $columnasDB = ['id', 'auth_secret', 'auth_enabled', 'backup_codes', 'creado', 'actualizado', 'usuarioId'];
    protected static $tabla = 'autenticacion_2fa';  

    public $id;
    public $auth_secret;
    public $auth_enabled = 0;
    public $backup_codes;
    public $creado;
    public $actualizado;
    public $usuarioId;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? NULL;
        $this->auth_secret = $args['auth_secret'] ?? '';
        $this->auth_enabled = $args['auth_enabled'] ?? 0;
        $this->backup_codes = $args['backup_codes'] ?? '[]';
        $this->creado = $args['creado'] ?? date('Y-m-d H:i:s');
        $this->actualizado = $args['actualizado'] ?? date('Y-m-d H:i:s');
        $this->usuarioId = $args['usuarioId'] ?? '';
    }

    // Validaciones para la configuración inicial del 2FA
    public function validarConfiguracionInicial() {
        if(empty($this->auth_secret)) {
            self::$alertas['error'][] = 'El secreto de autenticación es requerido';
        }
        
        if(empty($this->usuarioId)) {
            self::$alertas['error'][] = 'Debe estar asociado a un usuario';
        }
        
        return self::$alertas;
    }

    // Validación para códigos de verificación
    public function validarCodigoVerificacion($codigo) {
        if(empty($codigo)) {
            self::$alertas['error'][] = 'El código de verificación es requerido';
        } elseif(!is_numeric($codigo)) {
            self::$alertas['error'][] = 'El código debe ser numérico';
        } elseif(strlen($codigo) !== 6) {
            self::$alertas['error'][] = 'El código debe tener exactamente 6 dígitos';
        }
        
        return self::$alertas;
    }

    // Validación para códigos de respaldo
    public function validarCodigoRespaldo($codigo) {
        if(empty($codigo)) {
            self::$alertas['error'][] = 'El código de respaldo es requerido';
        } elseif(strlen($codigo) !== 8) {
            self::$alertas['error'][] = 'El código de respaldo debe tener 8 caracteres';
        }
        
        return self::$alertas;
    }

    // Validación para activación/desactivación
    public function validarEstado() {
        if(!is_numeric($this->auth_enabled)) {
            self::$alertas['error'][] = 'El estado de autenticación es inválido';
        } elseif($this->auth_enabled != 0 && $this->auth_enabled != 1) {
            self::$alertas['error'][] = 'El estado de autenticación debe ser 0 o 1';
        }
        
        return self::$alertas;
    }

    public static function findByUsuarioId($usuarioId) {
        return static::where('usuarioId', $usuarioId);
    }

    public function generarBackupCodes() {
        $codes = [];
        for ($i = 0; $i < 5; $i++) {
            $codes[] = bin2hex(random_bytes(4)); // Genera códigos de 8 caracteres
        }
        $this->backup_codes = json_encode($codes) ?: '[]'; // Asegura valor válido
        return $codes;
    }

    public function verificarBackupCode($code) {
        $alertas = $this->validarCodigoRespaldo($code);
        if(!empty($alertas['error'])) return false;

        $codes = json_decode($this->backup_codes, true) ?? [];
        $index = array_search($code, $codes);
        
        if ($index !== false) {
            unset($codes[$index]);
            $this->backup_codes = json_encode(array_values($codes));
            return true;
        }
        
        self::$alertas['error'][] = 'Código de respaldo no válido';
        return false;
    }

    // Método para verificar códigos TOTP
    public function verificarCodigo($codigo) {
        $alertas = $this->validarCodigoVerificacion($codigo);
        if(!empty($alertas['error'])) return false;

        $g = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();
        return $g->checkCode($this->auth_secret, $codigo);
    }

    public static function eliminarPorUsuario($usuarioId) {
        $query = "DELETE FROM " . static::$tabla . " WHERE usuarioId = " . self::$conexion->escape_string($usuarioId);
        $resultado = self::$conexion->query($query);
        return $resultado;
    }
}