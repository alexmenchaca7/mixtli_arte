<?php

namespace Controllers;

use MVC\Router;
use Model\Mensaje;
use Model\Usuario;
use Model\Producto;

class MensajesController {
    protected static $plantillasMensajes = [
        'saludo_interes' => [
            'id' => 'saludo_interes',
            'nombre' => 'Saludo por Interés',
            'texto' => "¡Hola [nombre_cliente]! Gracias por tu interés en mi producto '[nombre_producto]'. Estoy a tu disposición para cualquier pregunta que tengas.",
            'placeholders' => ['[nombre_cliente]', '[nombre_producto]']
        ],
        'info_disponibilidad' => [
            'id' => 'info_disponibilidad',
            'nombre' => 'Información de Disponibilidad',
            'texto' => "Hola, con respecto a '[nombre_producto]', te confirmo que todavía está disponible. ¿Te gustaría saber algo más específico o coordinar para verlo?",
            'placeholders' => ['[nombre_producto]']
        ],
        'info_producto_detalle' => [
            'id' => 'info_producto_detalle',
            'nombre' => 'Detalles Adicionales del Producto',
            'texto' => "Sobre el producto '[nombre_producto]', puedo añadir que [detalle_adicional_1] y también [detalle_adicional_2]. Si necesitas más información, no dudes en preguntar.",
            'placeholders' => ['[nombre_producto]', '[detalle_adicional_1]', '[detalle_adicional_2]']
        ],
        'coordinar_encuentro' => [
            'id' => 'coordinar_encuentro',
            'nombre' => 'Coordinar Encuentro/Recogida',
            'texto' => "¡Perfecto! Para el producto '[nombre_producto]', podríamos coordinar un encuentro. ¿Qué días y horarios te vendrían bien? Suelo estar disponible por [zona_referencia] o podemos acordar un punto.",
            'placeholders' => ['[nombre_producto]', '[zona_referencia]']
        ],
        'agradecimiento_consulta' => [
            'id' => 'agradecimiento_consulta',
            'nombre' => 'Agradecimiento por Consulta',
            'texto' => "Gracias por tu consulta sobre '[nombre_producto]'. Si decides seguir adelante o tienes más preguntas más adelante, estaré aquí para ayudarte.",
            'placeholders' => ['[nombre_producto]']
        ],
        'respuesta_rapida_ausente' => [
            'id' => 'respuesta_rapida_ausente',
            'nombre' => 'Respuesta Rápida (Ausente)',
            'texto' => "¡Hola! He recibido tu mensaje. En este momento no puedo responder detalladamente, pero lo haré tan pronto como me sea posible. ¡Gracias por tu paciencia!",
            'placeholders' => []
        ]
    ];

    public static function obtenerPlantillasParaVista() {
        return self::$plantillasMensajes;
    }

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

        $datosVista = [
            'titulo' => 'Mensajes',
            'conversaciones' => $conversacionesCompletas,
            'mensajes' => $mensajes,
            'productoChat' => $productoChat,
            'contactoChat' => $contactoChat,
            'vendedor' => $vendedor,
            'direccionComercial' => $direccionComercial
        ];

        if ($rol === 'vendedor') {
            $datosVista['plantillasDefinidas'] = self::$plantillasMensajes;
        }

        $router->render($vista, $datosVista, $layout);
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
            $mensajeTexto = trim($_POST['mensaje'] ?? ''); // Para 'contacto', este es el string JSON
            $productoId = filter_var($_POST['productoId'] ?? '', FILTER_VALIDATE_INT);
            $destinatarioId = filter_var($_POST['destinatarioId'] ?? '', FILTER_VALIDATE_INT);
            $tipo = htmlspecialchars($_POST['tipo'] ?? 'texto', ENT_QUOTES, 'UTF-8');

            $errores = [];

            if ($tipo === 'contacto') {
                // $mensajeTexto es el string JSON que viene del cliente.
                // NO aplicar stripslashes aquí si el cliente envía un JSON bien formado.
                $contactoData = json_decode($mensajeTexto, true); // Decodificar a array asociativo

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $errores[] = 'Formato de datos de contacto inválido (JSON no válido).';
                } else {
                    // Verificar que $contactoData sea un array y tenga las claves esperadas.
                    // La clave 'direccion' DEBE existir. Su valor PUEDE ser null.
                    if (!is_array($contactoData) || !array_key_exists('direccion', $contactoData) || 
                        !array_key_exists('telefono', $contactoData) || !array_key_exists('email', $contactoData)) {
                        $errores[] = 'Estructura de datos de contacto incompleta (faltan claves: direccion, telefono o email).';
                    } else {
                        // Si la clave 'direccion' existe y su valor NO es null, entonces DEBE ser un array.
                        if ($contactoData['direccion'] !== null && !is_array($contactoData['direccion'])) {
                            $errores[] = 'Si se proporciona una dirección, debe tener una estructura válida (ser un objeto/array).';
                        }
                        // Si la dirección es un array (es decir, se intentó enviar una dirección),
                        // y ese array NO está completamente vacío (es decir, se intentó poner algún dato de dirección),
                        // PERO la 'calle' está vacía, entonces es un error.
                        // Esto permite que direccion: null o direccion: {} (objeto vacío) sea válido si no se quiere enviar dirección.
                        if (is_array($contactoData['direccion']) && 
                            !empty(array_filter($contactoData['direccion'])) && // Si hay algún valor en el array de dirección
                            empty(trim($contactoData['direccion']['calle'] ?? ''))) { // Y la calle está vacía
                        $errores[] = 'Si se proporcionan detalles de dirección, la calle es obligatoria.';
                        }

                        // Validar que al menos una forma de contacto esté presente
                        $tieneDireccionValida = is_array($contactoData['direccion']) && !empty(trim($contactoData['direccion']['calle'] ?? ''));
                        $tieneTelefono = !empty(trim($contactoData['telefono']));
                        $tieneEmail = !empty(trim($contactoData['email']));

                        if (!$tieneDireccionValida && !$tieneTelefono && !$tieneEmail) {
                            $errores[] = 'Debe proporcionar al menos una forma de contacto (dirección con calle, teléfono o email).';
                        }
                    }
                }
                // Si hay errores, se manejarán más abajo.
                // Si no hay errores, $mensajeTexto (el string JSON original) se guardará.
            }


            if (empty($mensajeTexto) && $tipo !== 'contacto') { 
                $errores[] = 'El mensaje no puede estar vacío';
            }

            if (empty($productoId) || empty($destinatarioId)) {
                $errores[] = 'Datos de destinatario o producto inválidos';
            }

            if (empty($errores)) {
                $args = [
                    'contenido' => $mensajeTexto, // Para 'contacto', este es el string JSON.
                    'tipo' => $tipo, 
                    'remitenteId' => $usuarioId,
                    'destinatarioId' => $destinatarioId,
                    'productoId' => $productoId
                ];

                $mensaje = new Mensaje($args);
                $resultado = $mensaje->guardar();
                
                if ($resultado) {
                    $mensajeGuardado = Mensaje::find($mensaje->id);
                    
                    echo json_encode([
                        'success' => true,
                        'mensaje' => $mensajeGuardado->toArray() 
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    exit();
                } else {
                    $errores[] = "Error al guardar el mensaje en la base de datos.";
                }
            }    
            
            http_response_code(400); 
            echo json_encode(['success' => false, 'errores' => $errores]);
            exit();
        }
        // El resto del método POST y la respuesta de error 405 si no es POST
        http_response_code(405);
        echo json_encode(['success' => false, 'errores' => ['Método no permitido.']]);
        exit();
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