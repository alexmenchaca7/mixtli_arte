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
        $this->tipo = $args['tipo'] ?? 'texto';
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
                  ORDER BY id ASC"; 
    
        return self::consultarSQL($query);
    }

    public static function obtenerMensajesNuevos($productoId, $usuarioId, $contactoId, $ultimoId) {
        $productoId = self::$conexion->escape_string($productoId);
        $usuarioId = self::$conexion->escape_string($usuarioId);
        $contactoId = self::$conexion->escape_string($contactoId);
        $ultimoId = self::$conexion->escape_string($ultimoId);
    
        $query = "SELECT * FROM mensajes 
                WHERE productoId = '$productoId' 
                AND id > '$ultimoId'
                AND (
                    (remitenteId = '$usuarioId' AND destinatarioId = '$contactoId') 
                    OR 
                    (remitenteId = '$contactoId' AND destinatarioId = '$usuarioId')
                )
                ORDER BY id ASC";
        
        return self::consultarSQL($query);
    }    
    
    public static function obtenerConversacionActual($productoId, $usuarioId, $contactoId) {
        $producto = Producto::find($productoId);
        $contacto = Usuario::find($contactoId);
        $direccionComercial = $contacto->obtenerDireccionComercial();
        
        if(!$producto || !$contacto) {
            return null;
        }
        
        return [
            'producto' => $producto,
            'contacto' => $contacto,
            'direccionComercial' => $direccionComercial,
            'mensajes' => self::obtenerMensajesChat($productoId, $usuarioId, $contactoId)
        ];
    }
    
    public static function obtenerConversaciones($usuarioId) {
        $usuarioId = self::$conexion->escape_string($usuarioId);
    
        $query = "SELECT m1.*
                FROM mensajes m1
                INNER JOIN productos p ON m1.productoId = p.id
                WHERE m1.id = (
                    SELECT MAX(m2.id)
                    FROM mensajes m2
                    WHERE m2.productoId = m1.productoId
                    AND (
                        (m2.remitenteId = m1.remitenteId AND m2.destinatarioId = m1.destinatarioId) OR
                        (m2.remitenteId = m1.destinatarioId AND m2.destinatarioId = m1.remitenteId)
                    )
                )
                AND (
                    m1.remitenteId = '$usuarioId' 
                    OR m1.destinatarioId = '$usuarioId'
                    OR p.usuarioId = '$usuarioId'
                )
                ORDER BY m1.creado DESC";
    
        $mensajes = self::consultarSQL($query);
        
        $conversaciones = [];
    
        foreach($mensajes as $mensaje) {
            // Obtener producto relacionado
            $producto = Producto::find($mensaje->productoId);
            
            // Determinar contacto
            $contactoId = ($mensaje->remitenteId == $usuarioId) 
                ? $mensaje->destinatarioId 
                : $mensaje->remitenteId;
            
            // Si es vendedor del producto
            if($producto->usuarioId == $usuarioId) {
                $contactoId = ($mensaje->remitenteId == $usuarioId) 
                    ? $mensaje->destinatarioId 
                    : $mensaje->remitenteId;
            }
    
            $key = $mensaje->productoId . '-' . $contactoId;
            
            if(!isset($conversaciones[$key])) {
                $conversaciones[$key] = [
                    'productoId' => $mensaje->productoId,
                    'contactoId' => $contactoId,
                    'ultimoMensaje' => $mensaje,
                    'fecha' => $mensaje->creado
                ];
            }
        }
        
        return array_values($conversaciones);
    }

    public static function buscarEnConversaciones($usuarioId, $termino) {
        $usuarioId = self::$conexion->escape_string($usuarioId);
        $termino = self::$conexion->escape_string("%$termino%");
    
        $query = "SELECT DISTINCT m.productoId, 
                    CASE 
                        WHEN m.remitenteId = '$usuarioId' THEN m.destinatarioId
                        ELSE m.remitenteId
                    END AS contactoId
                  FROM mensajes m
                  INNER JOIN productos p ON m.productoId = p.id
                  INNER JOIN usuarios u ON (m.remitenteId = u.id OR m.destinatarioId = u.id)
                  WHERE (m.contenido LIKE '$termino'
                         OR p.nombre LIKE '$termino'
                         OR u.nombre LIKE '$termino')
                    AND (m.remitenteId = '$usuarioId' 
                         OR m.destinatarioId = '$usuarioId'
                         OR p.usuarioId = '$usuarioId')";

        $resultado = self::$conexion->query($query); // Ejecutar directamente
        
        $conversaciones = [];
        if($resultado) {
            while($fila = $resultado->fetch_assoc()) { // Leer como array asociativo
                $conversaciones[] = [
                    'productoId' => $fila['productoId'],
                    'contactoId' => $fila['contactoId']
                ];
            }
            $resultado->free();
        }
        
        return $conversaciones;
    }

    public static function buscarMensajes($usuarioId, $termino) {
        $usuarioId = self::$conexion->escape_string($usuarioId);
        $termino = self::$conexion->escape_string("%$termino%");
    
        $query = "SELECT m.id as mensajeId, m.productoId, 
                    CASE 
                        WHEN m.remitenteId = '$usuarioId' THEN m.destinatarioId
                        ELSE m.remitenteId
                    END AS contactoId
                  FROM mensajes m
                  INNER JOIN productos p ON m.productoId = p.id
                  INNER JOIN usuarios u ON (m.remitenteId = u.id OR m.destinatarioId = u.id)
                  WHERE (m.contenido LIKE '$termino'
                         OR p.nombre LIKE '$termino'
                         OR u.nombre LIKE '$termino')
                    AND (m.remitenteId = '$usuarioId' 
                         OR m.destinatarioId = '$usuarioId'
                         OR p.usuarioId = '$usuarioId')";
    
        $resultado = self::$conexion->query($query);
        
        $resultados = [];
        while($fila = $resultado->fetch_assoc()) {
            $resultados[] = $fila;
        }
        
        return $resultados;
    }

    // Helper
    public static function obtenerUltimoMensajeConversacion($productoId, $usuarioId, $contactoId) {
        $query = "SELECT * FROM mensajes 
                  WHERE productoId = '$productoId' 
                  AND (
                      (remitenteId = '$usuarioId' AND destinatarioId = '$contactoId') 
                      OR 
                      (remitenteId = '$contactoId' AND destinatarioId = '$usuarioId')
                  )
                  ORDER BY id DESC
                  LIMIT 1";
        
        $result = self::consultarSQL($query);
        return $result[0] ?? null;
    }
    
    public function guardar() {
        $resultado = parent::guardar();
        if($resultado) {
            $this->id = self::$conexion->insert_id;
        }
        return $resultado;
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'contenido' => $this->contenido,
            'tipo' => $this->tipo,
            'creado' => $this->creado,
            'remitenteId' => $this->remitenteId,
            'destinatarioId' => $this->destinatarioId,
            'productoId' => $this->productoId
        ];
    }    
}