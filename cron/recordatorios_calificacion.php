<?php
// Este script se debe ejecutar una vez al día con un Cron Job (0 0 * * *)

require __DIR__ . '/../includes/app.php';

use Model\Valoracion;
use Model\Notificacion;
use Model\Usuario;
use Model\Producto;
use Classes\Email;

echo "Iniciando script de recordatorios...\n";

// Buscamos valoraciones pendientes (sin estrellas) donde la venta se completó hace exactamente 27 días.
$fechaHace27Dias = date('Y-m-d', strtotime('-27 days'));
$query = "SELECT * FROM valoraciones WHERE estrellas IS NULL AND sale_completed_at IS NOT NULL AND DATE(sale_completed_at) = '{$fechaHace27Dias}'";

$valoracionesParaRecordar = Valoracion::consultarSQL($query);

if (empty($valoracionesParaRecordar)) {
    echo "No hay valoraciones para enviar recordatorios de expiración hoy.\n";
    exit;
}

echo "Se encontraron " . count($valoracionesParaRecordar) . " valoraciones para recordar.\n";

foreach ($valoracionesParaRecordar as $valoracion) {
    $calificador = Usuario::find($valoracion->calificadorId);
    $calificado = Usuario::find($valoracion->calificadoId);
    $producto = Producto::find($valoracion->productoId);

    if (!$calificador || !$calificado || !$producto) {
        echo "Omitiendo recordatorio para valoración ID {$valoracion->id} por datos incompletos.\n";
        continue;
    }

    // Determinar la URL correcta según el rol del usuario que debe calificar
    $url = ($calificador->rol === 'vendedor') ? 
        "/vendedor/mensajes?productoId={$producto->id}&contactoId={$calificado->id}" : 
        "/mensajes?productoId={$producto->id}&contactoId={$calificado->id}";

    // Crear notificación interna
    $notificacion = new Notificacion([
        'usuarioId' => $calificador->id,
        'tipo' => 'recordatorio_valoracion_expira',
        'mensaje' => "¡Última oportunidad! Te quedan 3 días para calificar a {$calificado->nombre} por el producto {$producto->nombre}.",
        'url' => $url
    ]);
    $notificacion->guardar();

    // Enviar email de recordatorio
    $email = new Email($calificador->email, $calificador->nombre, '');
    $email->enviarNotificacionCalificacion(
        $calificado->nombre, 
        $producto->nombre, 
        $url, 
        'expira_pronto' // Contexto para el nuevo texto
    );
    
    echo "Recordatorio de expiración enviado a: " . $calificador->email . "\n";
}

echo "Script de recordatorios finalizado.\n";