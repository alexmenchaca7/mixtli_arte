<?php

namespace Model;

class Mensaje extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'contenido', 'tipo', 'creado', 'remitenteId', 'destinatarioId', 'productoId', 'leido'];
    protected static $tabla = 'mensajes';  


    public $id;
    public $contenido;
    public $tipo;
    public $creado;
    public $remitenteId;
    public $destinatarioId;
    public $productoId;
    public $leido;


    public function __construct($args = [])
    {
        date_default_timezone_set('America/Mexico_City');
        $this->id = $args['id'] ?? NULL;
        $this->contenido = $args['contenido'] ?? '';
        $this->tipo = $args['tipo'] ?? 'texto';
        $this->creado = $args['creado'] ?? date('Y-m-d H:i:s');
        $this->remitenteId = $args['remitenteId'] ?? '';
        $this->destinatarioId = $args['destinatarioId'] ?? '';
        $this->productoId = $args['productoId'] ?? '';
        $this->leido = $args['leido'] ?? 0;
    }

    public static function obtenerMensajesChat($productoId, $usuarioId, $contactoId) {
        $productoId = self::$conexion->escape_string($productoId);
        $usuarioId = self::$conexion->escape_string($usuarioId);
        $contactoId = self::$conexion->escape_string($contactoId);
    
        $query = "SELECT * FROM " . static::getTablaNombre() . "  
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
    
        $query = "SELECT * FROM " . static::getTablaNombre() . " 
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
        $usuarioIdEscaped = self::$conexion->escape_string($usuarioId);

        $tablaMensajes = static::getTablaNombre(); // Nombre de la tabla de mensajes
        $tablaProductos = Producto::getTablaNombre(); // Obtener nombre de la tabla Producto usando el nuevo método

        $query = "
        SELECT m_actual.*
        FROM " . $tablaMensajes . " m_actual
        INNER JOIN (
            SELECT
                MAX(sub_m.id) as max_id
            FROM " . $tablaMensajes . " sub_m
            INNER JOIN " . $tablaProductos . " sub_p ON sub_m.productoId = sub_p.id
            WHERE (
                sub_m.remitenteId = '{$usuarioIdEscaped}'
                OR sub_m.destinatarioId = '{$usuarioIdEscaped}'
                OR sub_p.usuarioId = '{$usuarioIdEscaped}' 
            )
            GROUP BY sub_m.productoId, 
                     LEAST(sub_m.remitenteId, sub_m.destinatarioId), 
                     GREATEST(sub_m.remitenteId, sub_m.destinatarioId)
        ) AS ultimos_mensajes_ids ON m_actual.id = ultimos_mensajes_ids.max_id
        ORDER BY m_actual.creado DESC";

        $mensajesRecientes = self::consultarSQL($query);

        $conversaciones = [];
    
        foreach($mensajesRecientes as $ultimoMensaje) { 
            $contactoId = '';
            if ($ultimoMensaje->remitenteId == $usuarioId) {
                $contactoId = $ultimoMensaje->destinatarioId;
            } else if ($ultimoMensaje->destinatarioId == $usuarioId) {
                $contactoId = $ultimoMensaje->remitenteId;
            } else {
                // Este es el caso donde el usuario actual es el dueño del producto,
                // pero no fue ni remitente ni destinatario del último mensaje.
                // La conversación es entre remitenteId y destinatarioId del $ultimoMensaje.
                // El "contacto" para el dueño del producto será uno de ellos.
                // Debemos asegurarnos que el $contactoId no sea el mismo $usuarioId.
                if ($ultimoMensaje->remitenteId != $usuarioId) {
                    $contactoId = $ultimoMensaje->remitenteId;
                } else if ($ultimoMensaje->destinatarioId != $usuarioId) {
                    $contactoId = $ultimoMensaje->destinatarioId;
                } else {
                    // Ambos son el usuarioId, lo cual es lógicamente imposible en una conversación de dos.
                    // Si llega aquí, hay un problema en la lógica o los datos.
                    // Por seguridad, se omite esta conversación.
                    continue;
                }
            }
            
            if(empty($contactoId) || $contactoId == $usuarioId) { // Segunda condición por si la lógica anterior falló
                // No se pudo determinar un contacto válido o el contacto es el mismo usuario
                continue; 
            }

            $key = $ultimoMensaje->productoId . '-' . $contactoId;
            
            if(!isset($conversaciones[$key])) {
                $conversaciones[$key] = [
                    'productoId' => $ultimoMensaje->productoId,
                    'contactoId' => $contactoId,
                    'ultimoMensaje' => $ultimoMensaje, 
                    'fecha' => $ultimoMensaje->creado 
                ];
            }
        }
        
        return array_values($conversaciones); 
    }


    public static function buscarEnConversaciones($usuarioId, $termino) {
        $usuarioIdEsc = self::$conexion->escape_string($usuarioId);
        $terminoEsc = self::$conexion->escape_string("%$termino%");
        
        $tablaMensajes = static::getTablaNombre();
        $tablaProductos = Producto::getTablaNombre();
        $tablaUsuarios = Usuario::getTablaNombre(); // Asumo que tienes un modelo Usuario con este método

        $query = "SELECT 
                    m.productoId, 
                    CASE 
                        WHEN m.remitenteId = '{$usuarioIdEsc}' THEN m.destinatarioId
                        WHEN m.destinatarioId = '{$usuarioIdEsc}' THEN m.remitenteId
                        ELSE NULL  -- Si el usuario es dueño del producto, el contacto será el otro.
                                   -- Este CASE necesita refinamiento si el dueño no participa directamente.
                    END AS contactoIdOriginal,
                    m.remitenteId,
                    m.destinatarioId,
                    p.usuarioId as productoUsuarioId
                  FROM " . $tablaMensajes . " m
                  INNER JOIN " . $tablaProductos . " p ON m.productoId = p.id
                  INNER JOIN " . $tablaUsuarios . " u_remitente ON m.remitenteId = u_remitente.id
                  INNER JOIN " . $tablaUsuarios . " u_destinatario ON m.destinatarioId = u_destinatario.id
                  WHERE 
                    ( 
                        m.remitenteId = '{$usuarioIdEsc}' 
                        OR m.destinatarioId = '{$usuarioIdEsc}'
                        OR p.usuarioId = '{$usuarioIdEsc}'
                    )
                    AND 
                    ( 
                        m.contenido LIKE '{$terminoEsc}'
                        OR p.nombre LIKE '{$terminoEsc}'
                        OR u_remitente.nombre LIKE '{$terminoEsc}' 
                        OR u_destinatario.nombre LIKE '{$terminoEsc}'
                    )";
                    // Quitar el GROUP BY por ahora para procesar la lógica de contactoId en PHP

        $resultado = self::$conexion->query($query);
        
        $posibles_conversaciones = [];
        if($resultado) {
            while($fila = $resultado->fetch_assoc()) {
                $contactoId = null;
                // Si el usuario actual es el remitente
                if ($fila['remitenteId'] == $usuarioId) {
                    $contactoId = $fila['destinatarioId'];
                // Si el usuario actual es el destinatario
                } elseif ($fila['destinatarioId'] == $usuarioId) {
                    $contactoId = $fila['remitenteId'];
                // Si el usuario actual es el dueño del producto (y no es remitente ni destinatario del mensaje)
                } elseif ($fila['productoUsuarioId'] == $usuarioId) {
                    // El contacto es el que NO es el dueño del producto.
                    // Si el remitente no es el dueño, ese es el contacto.
                    if ($fila['remitenteId'] != $usuarioId) {
                        $contactoId = $fila['remitenteId'];
                    // Si el destinatario no es el dueño, ese es el contacto.
                    } elseif ($fila['destinatarioId'] != $usuarioId) {
                        $contactoId = $fila['destinatarioId'];
                    }
                    // Si ambos son el dueño (mensaje a sí mismo), $contactoId quedará null y se filtrará.
                }

                if ($contactoId && $contactoId != $usuarioId) {
                    $posibles_conversaciones[] = [
                        'productoId' => $fila['productoId'],
                        'contactoId' => $contactoId
                    ];
                }
            }
            $resultado->free();
        }
        
        // Eliminar duplicados
        $conversaciones = [];
        $keys = [];
        foreach ($posibles_conversaciones as $conv) {
            $key = $conv['productoId'] . '-' . $conv['contactoId'];
            if (!in_array($key, $keys)) {
                $conversaciones[] = $conv;
                $keys[] = $key;
            }
        }
        return $conversaciones;
    }

    public static function obtenerUltimoMensajeConversacion($productoId, $usuarioId, $contactoId) {
        $productoId = self::$conexion->escape_string($productoId);
        $usuarioId = self::$conexion->escape_string($usuarioId);
        $contactoId = self::$conexion->escape_string($contactoId);

        $query = "SELECT * FROM " . static::getTablaNombre() . " 
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
        if($this->id == NULL && $resultado) { 
            $this->id = self::$conexion->insert_id;
        }
        return $resultado;
    }

    public static function marcarComoLeido($productoId, $usuarioIdActual, $otroUsuarioId) {
        if (empty($productoId) || empty($usuarioIdActual) || empty($otroUsuarioId)) {
            return false;
        }
        $productoIdEscaped = self::$conexion->escape_string($productoId);
        $usuarioIdActualEscaped = self::$conexion->escape_string($usuarioIdActual);
        $otroUsuarioIdEscaped = self::$conexion->escape_string($otroUsuarioId);

        $query = "UPDATE " . static::$tabla . " SET leido = 1 
                WHERE productoId = '{$productoIdEscaped}' 
                AND remitenteId = '{$otroUsuarioIdEscaped}' 
                AND destinatarioId = '{$usuarioIdActualEscaped}' 
                AND leido = 0";

        $resultado = self::$conexion->query($query);
        return $resultado;
    }

    public static function contarNoLeidos($usuarioId) {
        $usuarioId = self::$conexion->escape_string($usuarioId);
        $query = "SELECT COUNT(*) as total FROM " . static::$tabla . " WHERE destinatarioId = '$usuarioId' AND leido = 0";
        $resultado = self::$conexion->query($query);
        if ($resultado) {
            $data = $resultado->fetch_assoc();
            $resultado->free(); // Liberar el resultado
            return (int)$data['total'];
        }
        return 0; // Devolver 0 en caso de error
    }

    public static function obtenerConteosNoLeidosPorConversacion($usuarioId) {
        $usuarioId = self::$conexion->escape_string($usuarioId);
        $query = "SELECT remitenteId, productoId, COUNT(*) as total 
                  FROM " . static::$tabla . " 
                  WHERE destinatarioId = '{$usuarioId}' AND leido = 0 
                  GROUP BY remitenteId, productoId";

        $resultado = self::$conexion->query($query);
        $conteos = [];
        if($resultado) {
            while($fila = $resultado->fetch_assoc()) {
                // Creamos una clave única para cada conversación
                $key = $fila['productoId'] . '-' . $fila['remitenteId'];
                $conteos[$key] = (int)$fila['total'];
            }
            $resultado->free();
        }
        return $conteos;
    }

    public static function obtenerActualizacionesLeido($productoId, $remitenteId, $destinatarioId) {
        $productoId = self::$conexion->escape_string($productoId);
        $remitenteId = self::$conexion->escape_string($remitenteId); // Quien envió el mensaje original
        $destinatarioId = self::$conexion->escape_string($destinatarioId); // Quien ahora está leyendo

        // Buscamos mensajes enviados por $remitenteId al $destinatarioId que ahora están leídos
        $query = "SELECT id FROM " . static::$tabla . " 
                WHERE productoId = '{$productoId}'
                AND remitenteId = '{$remitenteId}'
                AND destinatarioId = '{$destinatarioId}'
                AND leido = 1";
                // Podrías añadir una condición para no traer siempre todos los leídos,
                // por ejemplo, `AND actualizado > 'ultima_vez_que_se_checo'`
                // pero para empezar, esto funciona.

        $resultado = self::$conexion->query($query);
        $ids = [];
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $ids[] = $fila['id'];
            }
            $resultado->free();
        }
        return $ids;
    }

    public static function obtenerIdsLeidosPorContacto($productoId, $remitenteId, $lectorId) {
        $productoIdEsc = self::$conexion->escape_string($productoId);
        $remitenteIdEsc = self::$conexion->escape_string($remitenteId); // El usuario que envió originalmente
        $lectorIdEsc = self::$conexion->escape_string($lectorId);     // El usuario que leyó los mensajes

        $query = "SELECT id FROM " . static::$tabla . " 
                WHERE productoId = '{$productoIdEsc}'
                AND remitenteId = '{$remitenteIdEsc}'
                AND destinatarioId = '{$lectorIdEsc}'
                AND leido = 1";

        $resultado = self::$conexion->query($query);
        $ids = [];
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $ids[] = $fila['id'];
            }
            $resultado->free();
        }
        return $ids;
    }

    public static function eliminarConversacionPorProductoYUsuarios($productoId, $usuario1Id, $usuario2Id) {
        $productoId = self::$conexion->escape_string($productoId);
        $usuario1Id = self::$conexion->escape_string($usuario1Id);
        $usuario2Id = self::$conexion->escape_string($usuario2Id);

        // Delete messages where (remitente is u1 AND destinatario is u2) OR (remitente is u2 AND destinatario is u1)
        $query = "DELETE FROM " . static::$tabla . "
                WHERE productoId = '{$productoId}'
                AND (
                    (remitenteId = '{$usuario1Id}' AND destinatarioId = '{$usuario2Id}')
                    OR
                    (remitenteId = '{$usuario2Id}' AND destinatarioId = '{$usuario1Id}')
                )";
        return self::$conexion->query($query);
    }

    public static function eliminarPorProductoId($productoId) {
        $productoId_sanitizado = self::$conexion->escape_string($productoId);
        $query = "DELETE FROM " . static::$tabla . " WHERE productoId = '{$productoId_sanitizado}'";
        $resultado = self::$conexion->query($query);
        return $resultado;
    }

    public static function eliminarPorUsuario($usuarioId) {
        $usuarioIdEsc = self::$conexion->escape_string($usuarioId);
        $query = "DELETE FROM " . static::$tabla . " WHERE remitenteId = '{$usuarioIdEsc}' OR destinatarioId = '{$usuarioIdEsc}'";
        $resultado = self::$conexion->query($query);
        return $resultado;
    }
}