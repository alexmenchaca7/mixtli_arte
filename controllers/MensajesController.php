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
        $vendedor = null;
        $direccionComercial = [];

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

                // Obtener datos del vendedor relacionado al producto
                $vendedor = Usuario::find($productoChat->usuarioId);
                
                // Obtener dirección comercial completa
                if($vendedor) {
                    $direcciones = $vendedor->obtenerDireccionComercial();
                    $direccionComercial = $direcciones[0] ?? [];
                    
                    // Asegurar estructura completa
                    $direccionComercial = array_merge([
                        'calle' => '',
                        'colonia' => '',
                        'ciudad' => '',
                        'estado' => '',
                        'codigo_postal' => ''
                    ], (array)$direccionComercial);
                }

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
                $contacto = Usuario::find($conv['contactoId'] == $usuarioId ? $producto->usuarioId : $conv['contactoId']);
            } else {
                $vendedorProducto = Usuario::find($producto->usuarioId);
                $contacto = $vendedorProducto ?? $contacto;
            }
                
            if($contacto && $producto) {
                $ultimoMensaje = $conv['ultimoMensaje'];
                
                // Procesar mensajes de contacto para vista previa
                if($ultimoMensaje && $ultimoMensaje->tipo === 'contacto') {
                    $contenido = json_decode(stripslashes($ultimoMensaje->contenido), true);
                    
                    if(json_last_error() === JSON_ERROR_NONE && isset($contenido['direccion'])) {
                        $preview = $contenido['direccion']['calle'];
                        if(!empty($contenido['direccion']['colonia'])) {
                            $preview .= ', ' . $contenido['direccion']['colonia'];
                        }
                        $ultimoMensaje->contenido = $preview;
                    }
                }
    
                $conversacionesCompletas[] = [
                    'contacto' => $contacto,
                    'producto' => $producto,
                    'ultimoMensaje' => $ultimoMensaje,
                    'fecha' => $conv['fecha']
                ];
            }
        }

        // Renderizar vista según rol
        $vista = $rol === 'comprador' ? 'marketplace/mensajes' : 'vendedor/mensajes';
        $layout = $rol === 'comprador' ? 'layout' : 'vendedor-layout';

        $router->render($vista, [
            'titulo' => 'Mensajes',
            'conversaciones' => $conversacionesCompletas,
            'mensajes' => $mensajes,
            'productoChat' => $productoChat,
            'contactoChat' => $contactoChat,
            'vendedor' => $vendedor,
            'direccionComercial' => $direccionComercial
        ], $layout);
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

        // Obtener el vendedor (dueño del producto)
        $vendedor = Usuario::find($productoChat->usuarioId);
        $direccionComercial = $vendedor->obtenerDireccionComercial();
        
        $mensajes = $conversacion['mensajes']; 
    
        // Renderizar solo la parte del chat
        ob_start();
        extract([
            'productoChat' => $productoChat,
            'contactoChat' => $contactoChat,
            'vendedor' => $vendedor,
            'direccionComercial' => $direccionComercial,
            'mensajes' => $mensajes
        ]);
        
        if ($_SESSION['rol'] === 'comprador') {
            include '../views/marketplace/partials/chat.php'; // Vista para compradores
        } else if ($_SESSION['rol'] === 'vendedor') {
            include '../views/vendedor/partials/chat.php'; // Vista para vendedores
        }
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
            $tipo = $_POST['tipo'] ?? 'texto';
    
            // Validar datos
            $errores = [];

            // Validación simplificada para contacto
            if ($tipo === 'contacto') {
                // Eliminar escapes dobles del JSON
                $mensajeTexto = stripslashes($mensajeTexto);
                $mensajeTexto = filter_var($mensajeTexto, FILTER_UNSAFE_RAW);
                
                // Validar estructura básica
                $contactoData = json_decode($mensajeTexto, true);
                if (json_last_error() !== JSON_ERROR_NONE || !isset($contactoData['direccion'])) {
                    $errores[] = 'Estructura de contacto inválida';
                }
            }

            if (empty($mensajeTexto) && !isset($_FILES['archivo'])) {
                $errores[] = 'El mensaje no puede estar vacío';
            }
    
            if (empty($productoId) || !is_numeric($productoId) || empty($destinatarioId) || !is_numeric($destinatarioId)) {
                $errores[] = 'Datos de contacto inválidos';
            }
    
            if (empty($errores)) {
                $args = [
                    'contenido' => $mensajeTexto,
                    'tipo' => $tipo, 
                    'remitenteId' => $usuarioId,
                    'destinatarioId' => $destinatarioId,
                    'productoId' => $productoId
                ];
    
                // Procesamiento especial para contactos
                if ($tipo === 'contacto') {
                    $contactoData = json_decode($mensajeTexto, true);
                    
                    // Limpiar y validar estructura
                    $contactoData['direccion'] = array_filter($contactoData['direccion'] ?? []);
                    $contactoData = array_filter($contactoData);
                    
                    // Reconstruir contenido limpio
                    $args['contenido'] = json_encode($contactoData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
    
                $mensaje = new Mensaje($args);
                $resultado = $mensaje->guardar();
                
                if ($resultado) {
                    $mensajeGuardado = Mensaje::find($mensaje->id);
                    $mensajeGuardado->contenido = $args['contenido']; // Forzar contenido limpio
                    
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
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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
        
        $archivo = $_FILES['archivo'] ?? null;
        $errores = [];
        $tipoPermitidos = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        
        if(!$archivo || $archivo['error'] !== UPLOAD_ERR_OK) {
            $errores[] = 'Error al subir el archivo: ' . ($archivo['error'] ?? 'Desconocido');
        } else { // Solo continuar si no hay error de subida inicial
            if(!in_array($archivo['type'], $tipoPermitidos)) {
                $errores[] = 'Tipo de archivo no permitido';
            }
            
            if($archivo['size'] > 5000000) { // 5MB
                $errores[] = 'El archivo es demasiado grande (máx 5MB)';
            }
        }


        if(empty($errores)) {
            // Crear estructura de carpetas
            $carpetaArchivos = 'chat_adjuntos/'; // Nuevo nombre de la carpeta base para adjuntos
            $carpetaBaseServidor = $_SERVER['DOCUMENT_ROOT'] . '/' . $carpetaArchivos; // Ruta completa en el servidor

            $subcarpeta = $archivo['type'] === 'application/pdf' ? 'pdf/' : 'img/'; // Subcarpetas img/ o pdf/

            // Crear directorios si no existen
            if (!is_dir($carpetaBaseServidor . $subcarpeta)) {
                if (!mkdir($carpetaBaseServidor . $subcarpeta, 0755, true)) {
                    $errores[] = 'Error al crear el directorio de subida.';
                    echo json_encode(['success' => false, 'errores' => $errores]);
                    exit();
                }
            }

            // Generar nombre único
            $nombreOriginal = pathinfo($archivo['name'], PATHINFO_FILENAME); // Obtiene el nombre sin extensión
            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);    // Obtiene la extensión original (ej. "pdf", "png")

            // Asegurarse de que la extensión sea minúscula y válida
            $extension = strtolower($extension);
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'pdf'])) {
                $errores[] = 'Extensión de archivo no válida después de la verificación de tipo MIME.';
                echo json_encode(['success' => false, 'errores' => $errores]);
                exit();
            }
            
            $nombreArchivoUnico = md5(uniqid(rand(), true) . $nombreOriginal) . '.' . $extension; // Nombre único CON extensión original
        
            $rutaParaBD = $carpetaArchivos . $subcarpeta . $nombreArchivoUnico; 
            $rutaDestinoServidor = $carpetaBaseServidor . $subcarpeta . $nombreArchivoUnico;

            if (move_uploaded_file($archivo['tmp_name'], $rutaDestinoServidor)) {
                // Crear mensaje
                $mensaje = new Mensaje([
                    'contenido' => $rutaParaBD, 
                    'tipo' => ($extension === 'pdf') ? 'documento' : 'imagen', 
                    'remitenteId' => $usuarioId,
                    'destinatarioId' => $destinatarioId,
                    'productoId' => $productoId
                ]);
                
                if($mensaje->guardar()) {
                    // Devolver el objeto mensaje completo (ya lo hace el modelo con toArray())
                    echo json_encode([
                        'success' => true,
                        'mensaje' => $mensaje->toArray() // Usar el toArray() del modelo
                    ]);
                    exit();
                } else {
                    $errores[] = 'Error al guardar el mensaje en la base de datos.';
                    // Si falla guardar en BD, idealmente se debería borrar el archivo subido
                    if (file_exists($rutaDestinoServidor)) {
                        unlink($rutaDestinoServidor);
                    }
                }
            } else {
                $errores[] = 'Error al mover el archivo subido.';
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

    // OBTENER LA LISTA DE CONVERSACIONES PARA EL SIDEBAR
    public static function obtenerListaConversaciones(Router $router) {
        if (!is_auth()) {
            http_response_code(401);
            exit(json_encode(['error' => 'No autenticado']));
        }

        $usuarioId = $_SESSION['id'];
        // Llamamos a Mensaje::obtenerConversaciones que ya debería devolver los últimos mensajes
        // de cada conversación para el usuario.
        $conversacionesRaw = Mensaje::obtenerConversaciones($usuarioId);
        $conversacionesCompletas = [];

        foreach ($conversacionesRaw as $convData) { // $convData es el array ['productoId', 'contactoId', 'ultimoMensaje', 'fecha']
            // Validar IDs antes de buscar
            if (empty($convData['contactoId']) || empty($convData['productoId'])) {
                continue; // Saltar entradas inválidas
            }

            $contacto = Usuario::find($convData['contactoId']);
            $producto = Producto::find($convData['productoId']);
            $ultimoMensaje = $convData['ultimoMensaje']; // Esto ya es un objeto Mensaje

            if (!$contacto || !$producto) {
                // Podría pasar si un usuario o producto fue eliminado.
                // Considera si quieres mostrar estas conversaciones o filtrarlas.
                continue;
            }

            $conversacionesCompletas[] = [
                'contacto' => $contacto->toArray(), // Convertir a array para JSON
                'producto' => $producto->toArray(), // Convertir a array para JSON
                'ultimoMensaje' => $ultimoMensaje ? $ultimoMensaje->toArray() : null, // Convertir a array
                'fecha' => $convData['fecha']
            ];
        }

        // Ordenar por fecha descendente (más reciente primero)
        // La consulta en Mensaje::obtenerConversaciones ya debería ordenarlas, pero una doble verificación no hace daño.
        usort($conversacionesCompletas, function($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });

        echo json_encode(['conversaciones' => $conversacionesCompletas]);
        exit();
    }


    public static function buscarConversaciones(Router $router) {
        if (!is_auth()) {
            http_response_code(401);
            exit(json_encode(['error' => 'No autenticado']));
        }
    
        $termino = $_GET['term'] ?? '';
        $usuarioId = $_SESSION['id'];
    
        // Si el término está vacío, podríamos llamar a obtenerListaConversaciones
        // o simplemente dejar que la búsqueda con término vacío devuelva todo.
        // Por ahora, mantendremos la lógica de búsqueda específica aquí.
        $conversacionesIds = Mensaje::buscarEnConversaciones($usuarioId, $termino);
        
        $conversacionesCompletas = [];
        foreach ($conversacionesIds as $conv) {
            if(empty($conv['contactoId']) || empty($conv['productoId'])) {
                continue; 
            }

            $contacto = Usuario::find($conv['contactoId']);
            $producto = Producto::find($conv['productoId']);

            if (!$contacto || !$producto) {
                continue; 
            }
            
            // Para la búsqueda, necesitamos obtener el último mensaje específico de esta conversación.
            $ultimoMensaje = Mensaje::obtenerUltimoMensajeConversacion(
                $conv['productoId'],
                $usuarioId,
                $conv['contactoId']
            );
            
            $conversacionesCompletas[] = [
                'contacto' => $contacto->toArray(),
                'producto' => $producto->toArray(),
                'ultimoMensaje' => $ultimoMensaje ? $ultimoMensaje->toArray() : null,
                'fecha' => $ultimoMensaje->creado ?? $producto->creado // Fallback a la fecha de creación del producto si no hay mensajes
            ];
        }

        // Ordenar por fecha para que la búsqueda también muestre los más recientes primero
         usort($conversacionesCompletas, function($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });
    
        echo json_encode(['conversaciones' => $conversacionesCompletas]);
        exit();
    }
}