<?php
// Script para agrupar y enviar notificaciones de nuevos productos cada 5 minutos

define('IS_CRON_JOB', true);
require __DIR__ . '/../includes/app.php';

use Model\NotificacionPendiente;
use Model\Notificacion;
use Model\Usuario;
use Model\Follow;
use Model\Producto;
use Model\ImagenProducto;
use Classes\Email;

echo "Iniciando Cron Job: Procesar Notificaciones de Nuevos Productos...\n";

// 1. Obtener TODAS las notificaciones pendientes, ordenadas por vendedor y fecha.
$pendientes = NotificacionPendiente::whereField('procesado', 0);

if (empty($pendientes)) {
    echo "No hay notificaciones pendientes para procesar.\n";
    exit;
}

// 2. Agrupar las notificaciones por vendedor en un array.
$lotesPorVendedor = [];
foreach ($pendientes as $pendiente) {
    $lotesPorVendedor[$pendiente->vendedorId][] = $pendiente;
}

echo "Se encontraron lotes pendientes de " . count($lotesPorVendedor) . " vendedores. Evaluando...\n";

// 3. Iterar sobre cada lote de vendedor para decidir si se procesa.
foreach ($lotesPorVendedor as $vendedorId => $loteDeNotificaciones) {

    // Obtener la última notificación del lote actual.
    $ultimaNotificacionDelLote = end($loteDeNotificaciones);
    $fechaUltimaPublicacion = strtotime($ultimaNotificacionDelLote->fecha_creacion);

    // Umbral de espera: 5 minutos. Si la última publicación fue hace más de 5 minutos,
    // asumimos que el vendedor terminó de subir productos y procesamos el lote.
    $umbralEsperaSegundos = 5 * 60; 
    $segundosDesdeUltimaPub = time() - $fechaUltimaPublicacion;

    if ($segundosDesdeUltimaPub < $umbralEsperaSegundos) {
        // Si no ha pasado suficiente tiempo desde la última publicación de este vendedor,
        // saltamos este lote y lo re-evaluamos en la siguiente ejecución del cron.
        echo "Lote del vendedor {$vendedorId} en espera (última publicación hace {$segundosDesdeUltimaPub}s).\n";
        continue;
    }
    
    echo "Procesando lote del vendedor {$vendedorId}...\n";

    $vendedor = Usuario::find($vendedorId);
    if (!$vendedor) continue;

    $seguidores = Follow::whereField('seguidoId', $vendedorId);
    if (empty($seguidores)) continue;

    $cantidadProductos = count($loteDeNotificaciones);
    $nombreVendedor = $vendedor->nombre . ' ' . $vendedor->apellido;

    if ($cantidadProductos === 1) {
        // --- CASO 1: Notificación y Email para un solo producto ---
        $producto = Producto::find($loteDeNotificaciones[0]->productoId);
        if (!$producto) continue;
        
        $mensaje = "Tu artesano seguido, {$nombreVendedor}, ha publicado un nuevo producto: {$producto->nombre}.";
        $urlProducto = "/marketplace/producto?id={$producto->id}";
        
        // Obtener la imagen principal del producto
        $imagenPrincipal = ImagenProducto::obtenerPrincipalPorProductoId($producto->id);
        $urlImagen = $imagenPrincipal ? $_ENV['HOST'] . '/img/productos/' . $imagenPrincipal->url . '.webp' : $_ENV['HOST'] . '/img/productos/placeholder.jpg';

        foreach ($seguidores as $follow) {
            $seguidor = Usuario::find($follow->seguidorId);
            if ($seguidor) {
                // Verificar preferencias
                $prefsSeguidor = json_decode($seguidor->preferencias_notificaciones ?? '{}', true);
                $quiereNotifSistema = $prefsSeguidor['notif_producto_nuevo_sistema'] ?? true; // Por defecto activado
                $quiereNotifEmail = $prefsSeguidor['notif_producto_nuevo_email'] ?? true; // Por defecto activado

                // Enviar notificación por el sistema si el usuario lo desea
                if ($quiereNotifSistema) {
                    $notificacion = new Notificacion([
                        'usuarioId' => $seguidor->id, 
                        'tipo' => 'nuevo_producto', 
                        'mensaje' => $mensaje, 
                        'url' => $urlProducto
                    ]);
                    $notificacion->guardar();
                }

                // Enviar notificación por email si el usuario lo desea
                if ($quiereNotifEmail) {
                    $email = new Email($seguidor->email, $seguidor->nombre, '');
                    $email->enviarNotificacionNuevoProducto(
                        $nombreVendedor, 
                        $producto->nombre, 
                        $producto->precio, 
                        $urlImagen, 
                        $urlProducto
                    );
                }
            }
        }

    } else {
        // --- CASO 2: Notificación y Email para productos agrupados ---
        $mensaje = "¡Novedades! Tu artesano seguido, {$nombreVendedor}, ha publicado {$cantidadProductos} nuevos productos.";
        $urlPerfilVendedor = "/perfil?id={$vendedorId}";

        // Recolectar datos de los productos para el email
        $productosSugeridos = [];
        foreach($loteDeNotificaciones  as $notif) {
            $producto = Producto::find($notif->productoId);
            if($producto) {
                // Obtener la imagen principal de los productos
                $imagenPrincipal = ImagenProducto::obtenerPrincipalPorProductoId($producto->id);
                $urlImagen = $imagenPrincipal ? $_ENV['HOST'] . '/img/productos/' . $imagenPrincipal->url . '.webp' : $_ENV['HOST'] . '/img/productos/placeholder.jpg';

                $productosSugeridos[] = [
                    'nombre' => $producto->nombre,
                    'precio' => $producto->precio,
                    'urlProducto' => "/marketplace/producto?id={$producto->id}",
                    'urlImagen' => $urlImagen
                ];
            }
        }
        
        if (empty($productosSugeridos)) continue;

        foreach ($seguidores as $follow) {
            $seguidor = Usuario::find($follow->seguidorId);
            if ($seguidor) {
                // Verificar preferencias
                $prefsSeguidor = json_decode($seguidor->preferencias_notificaciones ?? '{}', true);
                $quiereNotifSistema = $prefsSeguidor['notif_producto_nuevo_sistema'] ?? true; // Por defecto activado
                $quiereNotifEmail = $prefsSeguidor['notif_producto_nuevo_email'] ?? true; // Por defecto activado

                // Enviar notificación por el sistema si el usuario lo desea
                if ($quiereNotifSistema) {
                    $notificacion = new Notificacion([
                        'usuarioId' => $seguidor->id, 
                        'tipo' => 'nuevo_producto', 
                        'mensaje' => $mensaje, 
                        'url' => $urlPerfilVendedor
                    ]);
                    $notificacion->guardar();
                }

                // Enviar notificación por correo si el usuario lo desea
                if ($quiereNotifEmail) {
                    $email = new Email($seguidor->email, $seguidor->nombre, '');
                    $email->enviarNotificacionNuevosProductosAgrupados(
                        $nombreVendedor, 
                        $cantidadProductos, 
                        $productosSugeridos, 
                        $urlPerfilVendedor
                    );
                }
            }
        }
    }

    // 4. Marcar las notificaciones de ESTE LOTE como procesadas para no volver a enviarlas.
    foreach ($loteDeNotificaciones as $notificacionProcesada) {
        $notificacionProcesada->procesado = 1;
        $notificacionProcesada->guardar();
    }
    echo "Lote del vendedor {$vendedorId} procesado y enviado.\n";
}

echo "Proceso finalizado.\n";