<?php

// Este script se debe ejecutar una vez al día con un Cron Job
require __DIR__ . '/../includes/app.php';

use Model\Valoracion;
use Model\Notificacion;
use Model\Usuario;
use Model\Producto;
use Classes\Email;

echo "Iniciando script de recordatorios...\n";

// Buscar valoraciones pendientes que fueron creadas hace exactamente 7 días
$fechaHace7Dias = date('Y-m-d', strtotime('-7 days'));
$query = "SELECT * FROM valoraciones WHERE estrellas IS NULL AND DATE(creado) = '{$fechaHace7Dias}'";

$valoracionesPendientes = Valoracion::consultarSQL($query);

if (empty($valoracionesPendientes)) {
    echo "No hay valoraciones pendientes para enviar recordatorios.\n";
    exit;
}

echo "Se encontraron " . count($valoracionesPendientes) . " valoraciones pendientes.\n";

foreach ($valoracionesPendientes as $valoracion) {
    $calificador = Usuario::find($valoracion->calificadorId);
    $calificado = Usuario::find($valoracion->calificadoId);
    $producto = Producto::find($valoracion->productoId);

    if (!$calificador || !$calificado || !$producto) continue;

    // Determinar la URL correcta según el rol del usuario que debe calificar
    $url = ($calificador->rol === 'vendedor') ? 
        "/vendedor/mensajes?productoId={$producto->id}&contactoId={$calificado->id}" : 
        "/mensajes?productoId={$producto->id}&contactoId={$calificado->id}";

    // Crear notificación interna con el tipo 'recordatorio_valoracion'
    $notificacion = new Notificacion([
        'usuarioId' => $calificador->id,
        'tipo' => 'recordatorio_valoracion',
        'mensaje' => "Recordatorio: No olvides calificar a {$calificado->nombre} por el producto {$producto->nombre}.",
        'url' => $url
    ]);
    $notificacion->guardar();

    // Enviar email de recordatorio
    $email = new Email($calificador->email, $calificador->nombre, "Recordatorio para calificar tu transacción");
    $email->enviarNotificacionCalificacion($calificado->nombre, $producto->nombre, $url);
    
    echo "Recordatorio enviado a: " . $calificador->email . "\n";
}

echo "Script de recordatorios finalizado.\n";