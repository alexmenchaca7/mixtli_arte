<?php

namespace Model;

class Mensaje extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'contenido', 'tipo', 'creado', 'remitenteId', 'destinatarioId', 'productoId'];
    protected static $tabla = 'mensajes';  


    public $id;
    public $contenido;
    public $tipo;
    public $creado;
    public $remitenteId;
    public $destinatarioId;
    public $productoId;


    public function __construct($args = [])
    {
        date_default_timezone_set('America/Mexico_City'); // Establecer zona horaria de México
        $this->id = $args['id'] ?? NULL;
        $this->contenido = $args['contenido'] ?? '';
        $this->tipo = $args['tipo'] ?? '';
        $this->creado = $args['creado'] ?? date('Y-m-d H:i:s');
        $this->remitenteId = $args['remitenteId'] ?? '';
        $this->destinatarioId = $args['destinatarioId'] ?? '';
        $this->productoId = $args['productoId'] ?? '';
    }

    public static function obtenerMensajesChat($productoId, $usuarioId, $contactoId) {
        $productoId = self::$conexion->escape_string($productoId);
        $usuarioId = self::$conexion->escape_string($usuarioId);
        $contactoId = self::$conexion->escape_string($contactoId);
    
        $query = "SELECT * FROM mensajes 
                  WHERE productoId = '$productoId' 
                  AND (
                      (remitenteId = '$usuarioId' AND destinatarioId = '$contactoId') 
                      OR 
                      (remitenteId = '$contactoId' AND destinatarioId = '$usuarioId')
                  )
                  ORDER BY creado ASC, id ASC"; 
    
        return self::consultarSQL($query);
    }
    
    public static function obtenerConversacionActual($productoId, $usuarioId, $contactoId) {
        $producto = Producto::find($productoId);
        $contacto = Usuario::find($contactoId);
        
        if(!$producto || !$contacto) {
            return null;
        }
        
        return [
            'producto' => $producto,
            'contacto' => $contacto,
            'mensajes' => self::obtenerMensajesChat($productoId, $usuarioId, $contactoId)
        ];
    }
    
    public static function obtenerConversaciones($usuarioId) {
        $usuarioId = self::$conexion->escape_string($usuarioId);
    
        $query = "SELECT m1.*,
                  CONVERT_TZ(m1.creado, '+00:00', '-06:00') as creado_mexico 
                  FROM mensajes m1
                  WHERE m1.id = (
                      SELECT MAX(m2.id)
                      FROM mensajes m2
                      WHERE m2.productoId = m1.productoId
                      AND (
                          (m2.remitenteId = '$usuarioId' AND m2.destinatarioId = m1.destinatarioId) OR
                          (m2.remitenteId = m1.destinatarioId AND m2.destinatarioId = '$usuarioId')
                      )
                  )
                  AND (m1.remitenteId = '$usuarioId' OR m1.destinatarioId = '$usuarioId')
                  ORDER BY m1.creado DESC";
    
        $mensajes = self::consultarSQL($query);
    
        // Procesar para agrupar conversaciones
        $conversaciones = [];
    
        foreach($mensajes as $mensaje) {
            $key = $mensaje->productoId . '-' . 
                  ($mensaje->remitenteId == $usuarioId ? $mensaje->destinatarioId : $mensaje->remitenteId);
            
            if(!isset($conversaciones[$key])) {
                $conversaciones[$key] = [
                    'productoId' => $mensaje->productoId,
                    'contactoId' => $mensaje->remitenteId == $usuarioId ? $mensaje->destinatarioId : $mensaje->remitenteId,
                    'ultimoMensaje' => $mensaje,
                    'fecha' => $mensaje->creado
                ];
            }
        }
        
        return array_values($conversaciones);
    }
    
    public function guardar() {
        $resultado = parent::guardar();
        if($resultado) {
            $this->id = self::$conexion->insert_id;
        }
        return $resultado;
    }
}