<?php

namespace Controllers;

use MVC\Router;
use Model\Mensaje;
use Model\Usuario;
use Model\Producto;

class MensajesController {
    public static function index(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
            exit();
        }

        $usuarioId = $_SESSION['id'];
        $rol = $_SESSION['rol'] ?? '';

        $mensajes = [];
        $productoChat = null;
        $contactoChat = null;
    
        // Si hay parámetros de chat en la URL
        if(isset($_GET['productoId']) && isset($_GET['contactoId'])) {
            $productoId = $_GET['productoId'];
            $contactoId = $_GET['contactoId'];
            
            // Obtener conversación usando el modelo
            $conversacion = Mensaje::obtenerConversacionActual(
                $productoId,
                $usuarioId,
                $contactoId
            );
            
            if($conversacion) {
                $productoChat = $conversacion['producto'];
                $contactoChat = $conversacion['contacto'];
                $mensajes = $conversacion['mensajes'];
            }
        }
    
        // Obtener conversaciones para el sidebar
        $conversacionesRaw = Mensaje::obtenerConversaciones($usuarioId);
        $conversacionesCompletas = [];
    
        foreach ($conversacionesRaw as $conv) {
            $contacto = Usuario::find($conv['contactoId']);
            $producto = Producto::find($conv['productoId']);

            // Determinar el contacto real
            if($producto->usuarioId == $usuarioId) {
                // Si es vendedor, el contacto es el comprador (siempre el otro participante)
                $contacto = Usuario::find($conv['contactoId'] == $usuarioId ? $producto->usuarioId : $conv['contactoId']);
            } else {
                // Si es comprador, el contacto es el vendedor del producto
                $vendedor = Usuario::find($producto->usuarioId);
                $contacto = $vendedor ?? $contacto;
            }
                
            if($contacto && $producto) {
                $conversacionesCompletas[] = [
                    'contacto' => $contacto,
                    'producto' => $producto,
                    'ultimoMensaje' => $conv['ultimoMensaje'],
                    'fecha' => $conv['fecha']
                ];
            }
        }

        // Determinar qué vista y layout usar
        if ($rol === 'comprador') {
            $router->render('marketplace/mensajes', [
                'titulo' => 'Mensajes',
                'conversaciones' => $conversacionesCompletas,
                'mensajes' => $mensajes,
                'productoChat' => $productoChat,
                'contactoChat' => $contactoChat
            ], 'layout');
        } else if ($rol === 'vendedor') {
            $router->render('vendedor/mensajes', [
                'titulo' => 'Mensajes',
                'conversaciones' => $conversacionesCompletas,
                'mensajes' => $mensajes,
                'productoChat' => $productoChat,
                'contactoChat' => $contactoChat
            ], 'vendedor-layout');
        }
    }

    public static function chat(Router $router) {
        if (!is_auth()) {
            http_response_code(401);
            exit(json_encode(['error' => 'No autenticado']));
        }
    
        $usuarioId = $_SESSION['id'];
        $productoId = $_GET['productoId'] ?? '';
        $contactoId = $_GET['contactoId'] ?? '';
    
        // Obtener datos usando el modelo
        $conversacion = Mensaje::obtenerConversacionActual($productoId, $usuarioId, $contactoId);
    
        if (!$conversacion) {
            http_response_code(404);
            exit(json_encode(['error' => 'Conversación no encontrada']));
        }
    
        // Variables para la vista
        $productoChat = $conversacion['producto'];
        $contactoChat = $conversacion['contacto'];
        $mensajes = $conversacion['mensajes']; 
    
        // Renderizar solo la parte del chat
        ob_start();
        include '../views/marketplace/partials/chat.php'; // Asegúrate de que esta vista renderiza los mensajes correctamente
        $html = ob_get_clean();
    
        $ultimoId = !empty($mensajes) ? end($mensajes)->id : 0;
    
        echo json_encode([
            'html' => $html,
            'ultimoId' => $ultimoId
        ]);
        exit();
    }
    

    public static function enviar(Router $router) {
        date_default_timezone_set('America/Mexico_City');
    
        if (!is_auth()) {
            http_response_code(401);
            exit(json_encode(['error' => 'No autenticado']));
        }
    
        $usuarioId = $_SESSION['id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mensajeTexto = trim($_POST['mensaje'] ?? '');
            $productoId = $_POST['productoId'] ?? '';
            $destinatarioId = $_POST['destinatarioId'] ?? '';
    
            // Validar datos
            $errores = [];
            
            if (empty($mensajeTexto)) {
                $errores[] = 'El mensaje no puede estar vacío';
            }
    
            if (empty($productoId) || !is_numeric($productoId) || empty($destinatarioId) || !is_numeric($destinatarioId)) {
                $errores[] = 'Datos de contacto inválidos';
            }
    
            if (empty($errores)) {
                $args = [
                    'contenido' => $mensajeTexto,
                    'tipo' => 'texto',
                    'remitenteId' => $usuarioId,
                    'destinatarioId' => $destinatarioId,
                    'productoId' => $productoId
                ];
                
                $mensaje = new Mensaje($args);
                $resultado = $mensaje->guardar();
                
                if ($resultado) {
                    // Obtener el mensaje recién creado desde la base de datos
                    $mensajeGuardado = Mensaje::find($mensaje->id);
    
                    // Devolver el mensaje completo con todos los datos
                    echo json_encode([
                        'success' => true,
                        'mensaje' => [
                            'id' => $mensajeGuardado->id,
                            'contenido' => $mensajeGuardado->contenido,
                            'tipo' => $mensajeGuardado->tipo,
                            'creado' => $mensajeGuardado->creado,
                            'remitenteId' => $mensajeGuardado->remitenteId,
                            'destinatarioId' => $mensajeGuardado->destinatarioId,
                            'productoId' => $mensajeGuardado->productoId
                        ]
                    ]);
                    exit();
                }
            }
            
            echo json_encode([
                'success' => false, 
                'errores' => $errores
            ]);
            exit();
        }
    }

    public static function subirArchivo(Router $router) {
        if (!is_auth()) {
            http_response_code(401);
            exit(json_encode(['error' => 'No autenticado']));
        }

        $usuarioId = $_SESSION['id'];
        $productoId = $_POST['productoId'] ?? '';
        $destinatarioId = $_POST['destinatarioId'] ?? '';
        
        // Validar archivo
        $archivo = $_FILES['archivo'] ?? null;
        $errores = [];
        $tipoPermitidos = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        
        if(!$archivo || $archivo['error'] !== UPLOAD_ERR_OK) {
            $errores[] = 'Error al subir el archivo';
        }
        
        if(!in_array($archivo['type'], $tipoPermitidos)) {
            $errores[] = 'Tipo de archivo no permitido';
        }
        
        if($archivo['size'] > 5000000) { // 5MB
            $errores[] = 'El archivo es demasiado grande';
        }

        if(empty($errores)) {
            // Crear estructura de carpetas
            $carpetaBase = $_SERVER['DOCUMENT_ROOT'] . '/mensajes/';
            $subcarpeta = $archivo['type'] === 'application/pdf' ? 'pdf' : 'img';

            // Crear directorios si no existen
            if (!is_dir($carpetaBase . $subcarpeta)) {
                mkdir($carpetaBase . $subcarpeta, 0755, true);
            }

            // Generar nombre único
            $nombreArchivo = md5(uniqid(rand(), true)) . '.' . pathinfo($archivo['name'], PATHINFO_EXTENSION);
            $ruta = $nombreArchivo;

            move_uploaded_file($archivo['tmp_name'], $carpetaBase . $subcarpeta . '/' . $nombreArchivo);

            // Crear mensaje
            $mensaje = new Mensaje([
                'contenido' => $ruta,
                'tipo' => $archivo['type'] === 'application/pdf' ? 'documento' : 'imagen',
                'remitenteId' => $usuarioId,
                'destinatarioId' => $destinatarioId,
                'productoId' => $productoId
            ]);
            
            if($mensaje->guardar()) {
                echo json_encode([
                    'success' => true,
                    'mensaje' => [
                        'id' => $mensaje->id,
                        'contenido' => $mensaje->contenido,
                        'tipo' => $mensaje->tipo,
                        'creado' => $mensaje->creado,
                        'remitenteId' => $mensaje->remitenteId,
                        'destinatarioId' => $mensaje->destinatarioId,
                        'productoId' => $mensaje->productoId
                    ]
                ]);
                exit();
            }
        }
        
        echo json_encode(['success' => false, 'errores' => $errores]);
        exit();
    }

    public static function obtenerNuevosMensajes(Router $router) {
        if (!is_auth()) {
            http_response_code(401);
            exit(json_encode(['error' => 'No autenticado']));
        }
    
        $usuarioId = $_SESSION['id'];
        $productoId = $_GET['productoId'] ?? '';
        $contactoId = $_GET['contactoId'] ?? '';
        $ultimoId = $_GET['ultimoId'] ?? 0;
    
        $mensajes = Mensaje::obtenerMensajesNuevos($productoId, $usuarioId, $contactoId, $ultimoId);

        echo json_encode([
            'success' => true,
            'mensajes' => array_map(function($m) {
                return $m->toArray();
            }, $mensajes)
        ]);
        exit();
    }

    public static function buscarConversaciones(Router $router) {
        if (!is_auth()) {
            http_response_code(401);
            exit(json_encode(['error' => 'No autenticado']));
        }
    
        $termino = $_GET['term'] ?? '';
        $usuarioId = $_SESSION['id'];
    
        $conversacionesIds = Mensaje::buscarEnConversaciones($usuarioId, $termino);
        
        $conversacionesCompletas = [];
        foreach ($conversacionesIds as $conv) {
            // Validar IDs antes de buscar
            if(empty($conv['contactoId']) || empty($conv['productoId'])) {
                continue; // Saltar entradas inválidas
            }

            $contacto = Usuario::find($conv['contactoId']);
            $producto = Producto::find($conv['productoId']);

            if (!$contacto || !$producto) {
                continue; // Saltar si no existen
            }
            
            if ($contacto && $producto) {
                $ultimoMensaje = Mensaje::obtenerUltimoMensajeConversacion(
                    $conv['productoId'],
                    $usuarioId,
                    $conv['contactoId']
                );
                
                $conversacionesCompletas[] = [
                    'contacto' => $contacto,
                    'producto' => $producto,
                    'ultimoMensaje' => $ultimoMensaje,
                    'fecha' => $ultimoMensaje->creado ?? date('Y-m-d H:i:s')
                ];
            }
        }
    
        echo json_encode(['conversaciones' => $conversacionesCompletas]);
        exit();
    }
}