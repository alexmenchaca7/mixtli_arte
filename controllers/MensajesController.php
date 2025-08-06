<?php

namespace Controllers;

use MVC\Router;
use Classes\Email;
use Model\Mensaje;
use Model\Usuario;
use Model\Favorito;
use Model\Producto;
use Model\Valoracion;
use Model\Notificacion;
use Model\ImagenProducto;
use Model\HistorialInteraccion;

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
        $valoraciones = [];

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

                $sql = "SELECT * FROM " . Valoracion::getTablaNombre() . " WHERE productoId = {$productoId} AND ((calificadorId = {$usuarioId} AND calificadoId = {$contactoId}) OR (calificadorId = {$contactoId} AND calificadoId = {$usuarioId}))";
                $valoraciones = Valoracion::consultarSQL($sql);
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
            'direccionComercial' => $direccionComercial,
            'valoraciones' => $valoraciones
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

        if (!empty($productoId) && !empty($contactoId)) {
            Mensaje::marcarComoLeido($productoId, $usuarioId, $contactoId);
        }

        $conversacion = Mensaje::obtenerConversacionActual($productoId, $usuarioId, $contactoId);

        if (!$conversacion) {
            http_response_code(404);
            exit(json_encode(['error' => 'Conversación no encontrada']));
        }

        $productoChat = $conversacion['producto'];
        $contactoChat = $conversacion['contacto'];
        $vendedorDelProducto = Usuario::find($productoChat->usuarioId); 

        $vendedorParaVista = new \stdClass();
        $direccionComercialData = []; // Inicializar

        if ($vendedorDelProducto) { // Verificar si el vendedor existe
            $vendedorParaVista->id = $vendedorDelProducto->id;
            $vendedorParaVista->telefono = $vendedorDelProducto->telefono ?? ''; // Usar null coalescing para propiedades individuales
            $vendedorParaVista->email = $vendedorDelProducto->email ?? '';
            $direccionComercialData = $vendedorDelProducto->obtenerDireccionComercial();
        } else {
            // Establecer valores por defecto si el vendedor no existe
            $vendedorParaVista->id = null;
            $vendedorParaVista->telefono = '';
            $vendedorParaVista->email = '';
            // $direccionComercialData ya es []
        }

        // Preparar $direccionComercialParaVista
        $direccionComercialParaVista = $direccionComercialData[0] ?? new \stdClass();
        
        // Asegurar que sea un objeto para json_encode en la vista parcial si se usa como $direccionComercial[0]
        if (is_array($direccionComercialParaVista) && empty($direccionComercialParaVista)) {
            $direccionComercialParaVista = new \stdClass();
        } else if (is_array($direccionComercialParaVista) && isset($direccionComercialParaVista[0])) {
            $direccionComercialParaVista = (object) $direccionComercialParaVista[0];
        } else {
            $direccionComercialParaVista = (object) $direccionComercialParaVista;
        }
        
        $mensajes = $conversacion['mensajes']; 

        // --- Obtener valoraciones ---
        $sql = "SELECT * FROM " . Valoracion::getTablaNombre() . " WHERE productoId = {$productoId} AND ((calificadorId = {$usuarioId} AND calificadoId = {$contactoId}) OR (calificadorId = {$contactoId} AND calificadoId = {$usuarioId}))";
        $valoraciones = Valoracion::consultarSQL($sql);

        ob_start();
        extract([
            'productoChat' => $productoChat,
            'contactoChat' => $contactoChat,
            'vendedor' => $vendedorParaVista, // Usar la variable preparada
            'direccionComercial' => $direccionComercialParaVista, // Usar la variable preparada
            'mensajes' => $mensajes,
            'plantillasDefinidas' => ($_SESSION['rol'] === 'vendedor') ? self::obtenerPlantillasParaVista() : [],
            'valoraciones' => $valoraciones
        ]);
        
        $viewPathRoot = dirname(__DIR__) . '/views/'; // Esto apuntará a tu directorio 'mixtli_arte/views/'

        $viewFileToInclude = '';
        if ($_SESSION['rol'] === 'comprador') {
            $viewFileToInclude = $viewPathRoot . 'marketplace/partials/chat.php';
        } else if ($_SESSION['rol'] === 'vendedor') {
            $viewFileToInclude = $viewPathRoot . 'vendedor/partials/chat.php';
        }

        if (!empty($viewFileToInclude) && file_exists($viewFileToInclude)) {
            include $viewFileToInclude;
        } else {
            ob_end_clean(); 
            http_response_code(500);
            $errorMessage = 'Error crítico: La vista parcial del chat no fue encontrada.';
            if (empty($viewFileToInclude)) {
                $errorMessage .= ' Rol de usuario no determinó una vista.';
            } else {
                $errorMessage .= ' Ruta esperada: ' . $viewFileToInclude;
            }
            exit(json_encode(['error' => $errorMessage, 'debug_rol' => $_SESSION['rol'] ?? 'No definido']));
        }
        $html = ob_get_clean();

        $ultimoId = !empty($mensajes) ? end($mensajes)->id : 0;

        // Antes de enviar el JSON, asegúrate que $html no es false
        if ($html === false) {
            http_response_code(500);
            // Esto puede pasar si hay un error fatal dentro de la vista parcial y ob_get_clean() falla
            exit(json_encode(['error' => 'Error generando el HTML del chat. Revisa los logs del servidor.']));
        }

        echo json_encode([
            'html' => $html,
            'ultimoId' => $ultimoId
        ]);
        exit();
    }

    // --- Metodo para marcar como vendido ---
    public static function marcarVendido(Router $router) {
        header('Content-Type: application/json');
        if (!is_auth()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autenticado']);
            exit();
        }
    
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            exit();
        }
        
        $vendedorId = $_SESSION['id'];
        $productoId = filter_var($_POST['productoId'] ?? '', FILTER_VALIDATE_INT);
        $compradorId = filter_var($_POST['compradorId'] ?? '', FILTER_VALIDATE_INT);
    
        if (!$productoId || !$compradorId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
            exit();
        }
    
        $producto = Producto::find($productoId);
        $comprador = Usuario::find($compradorId);
        $vendedor = Usuario::find($vendedorId);
    
        if (!$producto || $producto->usuarioId != $vendedorId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'No autorizado para esta acción']);
            exit();
        }
    
        $existingValoracion = Valoracion::whereArray([
            'productoId' => $productoId,
            'calificadoId' => $compradorId,
            'calificadorId' => $vendedorId
        ]);
    
        if ($existingValoracion) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'Este producto ya fue marcado como vendido a este comprador.']);
            exit();
        }

        // Guardamos el stock ANTES de modificarlo para la comparación
        $stock_anterior = (int)$producto->stock;
    
        if ($producto->estado === 'unico') {
            $producto->estado = 'agotado';
        } elseif ($producto->estado === 'disponible') {
            $producto->stock = max(0, $producto->stock - 1);
            if ($producto->stock == 0) {
                $producto->estado = 'agotado';
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'El producto ya está agotado.']);
            exit();
        }
    
        $resultadoProducto = $producto->guardar();
    
        if (!$resultadoProducto) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al actualizar el producto.']);
            exit();
        }

        // ELIMINAR PRODUCTO DE FAVORITOS DEL COMPRADOR
        $favoritosEncontrados = Favorito::whereArray(['usuarioId' => $compradorId, 'productoId' => $productoId]);
    
        if (!empty($favoritosEncontrados)) {
            // Extraemos el primer (y único) objeto del array.
            $favoritoAEliminar = array_shift($favoritosEncontrados);
            
            // Ahora sí, eliminamos el objeto.
            $favoritoAEliminar->eliminar();
        }


        // LÓGICA DE NOTIFICACIÓN POR BAJO STOCK
        $stock_nuevo = (int)$producto->stock;
        $umbral_stock_bajo = 3; 

        if ($stock_nuevo < $stock_anterior && $stock_nuevo > 0 && $stock_nuevo <= $umbral_stock_bajo) {
            
            $favoritos = Favorito::whereField('productoId', $producto->id);

            if (!empty($favoritos)) {
                $idsUsuarios = array_column($favoritos, 'usuarioId');
                $usuariosParaNotificar = Usuario::consultarSQL("SELECT * FROM usuarios WHERE id IN (" . implode(',', $idsUsuarios) . ")");

                $urlProducto = "/marketplace/producto?id={$producto->id}";
                $imagenPrincipal = ImagenProducto::obtenerPrincipalPorProductoId($producto->id);
                $urlImagen = $imagenPrincipal ? $_ENV['HOST'] . '/img/productos/' . $imagenPrincipal->url . '.webp' : $_ENV['HOST'] . '/img/productos/placeholder.jpg';

                foreach ($usuariosParaNotificar as $usuario) {
                    $prefs = json_decode($usuario->preferencias_notificaciones ?? '{}', true);

                    // Notificación dentro de la plataforma
                    if ($prefs['notif_stock_bajo_sistema'] ?? true) {
                        $notificacion = new Notificacion([
                            'usuarioId' => $usuario->id,
                            'tipo' => 'stock_bajo',
                            'mensaje' => "¡Quedan pocas unidades de '{$producto->nombre}'!",
                            'url' => $urlProducto
                        ]);
                        $notificacion->guardar();
                    }

                    // Notificación por correo electrónico
                    if ($prefs['notif_stock_bajo_email'] ?? true) {
                        $email = new Email($usuario->email, $usuario->nombre, '');
                        $email->enviarNotificacionStockBajo(
                            $producto->nombre,
                            $producto->stock,
                            $urlImagen,
                            $urlProducto
                        );
                    }
                }
            }
        }

        // NOTIFICAR AL VENDEDOR DE STOCK CRÍTICO
        $stock_nuevo_venta = (int)$producto->stock;
        $umbral_critico_vendedor = 3;

        // Comprobamos si el stock llega EXACTAMENTE al umbral crítico a causa de esta venta
        if ($stock_nuevo_venta === $umbral_critico_vendedor) {
            $vendedor = Usuario::find($producto->usuarioId);
            if ($vendedor) {
                $prefsVendedor = json_decode($vendedor->preferencias_notificaciones ?? '{}', true);

                $urlProductoVendedor = "/vendedor/productos/editar?id={$producto->id}";
                $mensajeNotificacion = "¡Stock crítico! A tu producto '{$producto->nombre}' solo le quedan {$stock_nuevo_venta} unidades.";

                // Notificación en la plataforma
                if ($prefsVendedor['notif_stock_critico_sistema'] ?? true) {
                    $notificacion = new Notificacion([
                        'usuarioId' => $vendedor->id,
                        'tipo' => 'stock_critico',
                        'mensaje' => $mensajeNotificacion,
                        'url' => $urlProductoVendedor
                    ]);
                    $notificacion->guardar();
                }

                // Notificación por correo electrónico
                if ($prefsVendedor['notif_stock_critico_email'] ?? true) {
                    $imagenPrincipal = ImagenProducto::obtenerPrincipalPorProductoId($producto->id);
                    $urlImagen = $imagenPrincipal ? $_ENV['HOST'] . '/img/productos/' . $imagenPrincipal->url . '.webp' : $_ENV['HOST'] . '/img/productos/placeholder.jpg';

                    $email = new Email($vendedor->email, $vendedor->nombre, '');
                    $email->enviarNotificacionStockCriticoVendedor(
                        $producto->nombre,
                        $stock_nuevo_venta,
                        $urlImagen,
                        $urlProductoVendedor
                    );
                }
            }
        }
        
    
        // Buyer rates seller
        $valoracionComprador = new Valoracion([
            'calificadorId' => $compradorId,
            'calificadoId' => $vendedorId,
            'productoId' => $productoId,
            'tipo' => 'comprador',
            'sale_completed_at' => date('Y-m-d H:i:s')
        ]);
        $valoracionComprador->guardar();
        
        // Seller rates buyer
        $valoracionVendedor = new Valoracion([
            'calificadorId' => $vendedorId,
            'calificadoId' => $compradorId,
            'productoId' => $productoId,
            'tipo' => 'vendedor',
            'sale_completed_at' => date('Y-m-d H:i:s')
        ]);
        $valoracionVendedor->guardar();

        // 1. Notificación al VENDEDOR
        $urlVendedor = "/vendedor/mensajes?productoId={$producto->id}&contactoId={$comprador->id}";
        $notificacionVendedor = new Notificacion([
            'usuarioId' => $vendedor->id,
            'tipo' => 'valoracion',
            'mensaje' => "¡Ya puedes calificar a {$comprador->nombre} por la venta de {$producto->nombre}!",
            'url' => $urlVendedor
        ]);
        $notificacionVendedor->guardar();

        $emailVendedor = new Email($vendedor->email, $vendedor->nombre, 'Calificación Pendiente');
        $emailVendedor->enviarNotificacionCalificacion($comprador->nombre, $producto->nombre, $urlVendedor);

        // 2. Notificación al COMPRADOR
        $urlComprador = "/mensajes?productoId={$producto->id}&contactoId={$vendedor->id}";
        $notificacionComprador = new Notificacion([
            'usuarioId' => $comprador->id,
            'tipo' => 'valoracion',
            'mensaje' => "¡Transacción completada! Ya puedes calificar a {$vendedor->nombre} por tu compra de {$producto->nombre}.",
            'url' => $urlComprador
        ]);
        $notificacionComprador->guardar();

        $emailComprador = new Email($comprador->email, $comprador->nombre, 'Califica tu compra');
        $emailComprador->enviarNotificacionCalificacion($vendedor->nombre, $producto->nombre, $urlComprador);

        // Se crea un array con los datos que queremos guardar
        $metadataArray = ['vendedorId' => $vendedorId];

        // Se registra la interacción de compra, asegurando que metadata sea un texto JSON
        $interaccionCompra = new HistorialInteraccion([
            'tipo' => 'compra',
            'usuarioId' => $compradorId,
            'productoId' => $productoId,
            'metadata' => json_encode($metadataArray) // Usamos json_encode() para la conversión
        ]);
        $interaccionCompra->guardar(); 
    
        echo json_encode(['success' => true, 'message' => 'Producto marcado como vendido. Sistema de calificación desbloqueado.']);
        exit();
    }

    public static function enviar(Router $router) {
        date_default_timezone_set('America/Mexico_City');

        if (!is_auth()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'errores' => ['No autenticado']]);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'errores' => ['Método no permitido']]);
            exit();
        }
        
        $usuarioId = $_SESSION['id'];
        $mensajeTexto = trim($_POST['mensaje'] ?? '');
        $productoId = filter_var($_POST['productoId'] ?? '', FILTER_VALIDATE_INT);
        $destinatarioId = filter_var($_POST['destinatarioId'] ?? '', FILTER_VALIDATE_INT);
        $tipo = htmlspecialchars($_POST['tipo'] ?? 'texto', ENT_QUOTES, 'UTF-8');

        // Validación de errores
        $errores = [];
        
        // --- INICIO: LÓGICA ANTI-SPAM ---
        $tiempoEspera = 3; // segundos
        $maxMensajesConsecutivos = 3; // mensajes
        
        $claveSesionConversacion = 'last_message_time_' . $productoId . '_' . $destinatarioId;
        $claveSesionContador = 'message_count_' . $productoId . '_' . $destinatarioId;

        // Asegurarse de que la sesión esté iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $ultimoEnvio = $_SESSION[$claveSesionConversacion] ?? 0;
        $conteoMensajes = $_SESSION[$claveSesionContador] ?? 0;
        $tiempoActual = time();

        // Si el último envío fue hace menos del tiempo de espera
        if (($tiempoActual - $ultimoEnvio) < $tiempoEspera) {
            $conteoMensajes++;
            $_SESSION[$claveSesionContador] = $conteoMensajes;

            if ($conteoMensajes > $maxMensajesConsecutivos) {
                $errores[] = 'Has enviado demasiados mensajes en poco tiempo. Por favor, espera antes de enviar otro mensaje.';
            }
        } else {
            // Si ha pasado el tiempo de espera, reiniciar el contador y el tiempo
            $_SESSION[$claveSesionConversacion] = $tiempoActual;
            $_SESSION[$claveSesionContador] = 1;
        }
        // --- FIN: LÓGICA ANTI-SPAM ---

        if (empty($mensajeTexto) && $tipo !== 'contacto' && $tipo !== 'imagen' && $tipo !== 'documento') {
            $errores[] = 'El mensaje no puede estar vacío';
        }
        if (empty($productoId) || empty($destinatarioId)) {
            $errores[] = 'Datos de destinatario o producto inválidos';
        }

        // Excluir mensajes de tipo 'contacto' (ya que son la forma aprobada de compartir contacto)
        if ($tipo === 'texto' || $tipo === 'plantilla_auto') {
            // Patrones para detectar emails, teléfonos (México), y direcciones (simplificado)
            $patrones = [
                'email' => '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/',
                'telefono' => '/\b(?:\d{2,4}[-.\s]?){2}\d{4}\b/', // Adapta según el formato de teléfono mexicano más común
                'direccion' => '/\b(calle|av\.|avenida|num\.|número|colonia|col\.|fracc\.|fraccionamiento|cp|c\.p\.|codigo\s*postal)\b/i'
            ];

            $mensajeLower = strtolower($mensajeTexto);

            foreach ($patrones as $nombre => $patron) {
                if (preg_match($patron, $mensajeLower)) {
                    $errores[] = 'No está permitido compartir información de contacto personal (' . $nombre . ') directamente en el chat. Por favor, utiliza la opción "Compartir Contacto" si está disponible o reformula tu mensaje.';
                    break; // Salir después de la primera coincidencia
                }
            }
        }

        if (!empty($errores)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errores' => $errores]);
            exit();
        }
        
        $mensaje = new Mensaje([
            'contenido' => $mensajeTexto,
            'tipo' => $tipo,
            'remitenteId' => $usuarioId,
            'destinatarioId' => $destinatarioId,
            'productoId' => $productoId,
            'leido' => 0
        ]);

        $resultado = $mensaje->guardar();

        if ($resultado) {
            $mensajeGuardado = Mensaje::find($mensaje->id);
            $respuesta = [
                'success' => true,
                'mensaje' => $mensajeGuardado->toArray()
            ];

            // Cierra la escritura de la sesión para liberar el archivo de sesión.
            // Esto es CRUCIAL para que el comando exec() se ejecute en un segundo
            // plano real y no bloquee la respuesta al navegador.
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }

            // Ignorar si el usuario aborta la conexión y quitar límite de tiempo
            ignore_user_abort(true);
            set_time_limit(0);

            // Iniciar el buffer de salida
            ob_start();

            // Enviar la respuesta JSON al buffer
            echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            // Enviar cabeceras para forzar el cierre de la conexión
            header('Connection: close');
            header('Content-Type: application/json');
            header('Content-Length: ' . ob_get_length());
            
            // Enviar el contenido del buffer (la respuesta) al navegador
            ob_end_flush();
            flush();

            // Si está disponible, usar fastcgi_finish_request para un cierre más limpio
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
            
            $destinatarioInfo = Usuario::find($destinatarioId);
            $isOnline = false;

            if ($destinatarioInfo && $destinatarioInfo->last_active) {
                $lastActiveTimestamp = strtotime($destinatarioInfo->last_active);
                $currentTime = time();
                if (($currentTime - $lastActiveTimestamp) < (3 * 60)) { // 3 minutos
                    $isOnline = true;
                }
            }

            // Solo envía el email si el usuario NO está online
            if (!$isOnline) {
                $phpPath = '/usr/bin/php';
                $scriptPath = __DIR__ . '/../cron/enviar_notificacion_mensaje.php';

                $command = "{$phpPath} {$scriptPath} "
                        . escapeshellarg($destinatarioId) . " "
                        . escapeshellarg($usuarioId) . " "
                        . escapeshellarg($productoId) . " "
                        . escapeshellarg($mensajeTexto)
                        . " > /dev/null 2>&1 &";

                exec($command);
            }

            exit();
        }

        // Si hubo un error guardando en la BD
        http_response_code(500);
        echo json_encode(['success' => false, 'errores' => ['Error al guardar el mensaje.']]);
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

        if (empty($productoId) || empty($contactoId)) {
            http_response_code(400);
            exit(json_encode(['error' => 'Faltan parámetros']));
        }

        // 1. Marcar como leídos los mensajes que este usuario ha recibido de este contacto en este chat.
        //    Esto confirma que el usuario está "viendo" el chat.
        Mensaje::marcarComoLeido($productoId, $usuarioId, $contactoId);

        // 2. Obtener los mensajes nuevos que el contacto le ha enviado a este usuario.
        $mensajesNuevos = Mensaje::obtenerMensajesNuevos($productoId, $usuarioId, $contactoId, $ultimoId);

        // 3. Obtener los IDs de los mensajes que este usuario envió y que el contacto ya leyó.
        //    Esta es la notificación de "visto".
        $idsLeidosPorContacto = Mensaje::obtenerIdsLeidosPorContacto($productoId, $usuarioId, $contactoId);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'mensajes' => array_map(fn($m) => $m->toArray(), $mensajesNuevos),
            'read_updates' => $idsLeidosPorContacto
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
        $conversacionesRaw = Mensaje::obtenerConversaciones($usuarioId);
        $conteosNoLeidos = Mensaje::obtenerConteosNoLeidosPorConversacion($usuarioId);
        $conversacionesCompletas = [];

        foreach ($conversacionesRaw as $convData) { // $convData es el array ['productoId', 'contactoId', 'ultimoMensaje', 'fecha']
            // Validar IDs antes de buscar
            if (empty($convData['contactoId']) || empty($convData['productoId'])) {
                continue; // Saltar entradas inválidas
            }

            $contacto = Usuario::find($convData['contactoId']);
            $producto = Producto::find($convData['productoId']);
            $ultimoMensaje = $convData['ultimoMensaje']; 

            if (!$contacto || !$producto) {
                continue;
            }

            // Crear la clave y buscar el conteo en el mapa
            $keyConteo = $producto->id . '-' . $contacto->id;
            $unread_count = $conteosNoLeidos[$keyConteo] ?? 0;

            $conversacionesCompletas[] = [
                'contacto' => $contacto->toArray(), // Convertir a array para JSON
                'producto' => $producto->toArray(), // Convertir a array para JSON
                'ultimoMensaje' => $ultimoMensaje ? $ultimoMensaje->toArray() : null, // Convertir a array
                'fecha' => $convData['fecha'],
                'unread_count' => $unread_count
            ];
        }

        // Ordenar por fecha descendente (más reciente primero)
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

    public static function getUnreadCount(Router $router) {
        if (!is_auth()) {
            http_response_code(401); // Unauthorized
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }

        $usuarioId = $_SESSION['id'];
        $count = Mensaje::contarNoLeidos($usuarioId);

        header('Content-Type: application/json');
        echo json_encode(['unread_count' => $count]);
        exit;
    }
}