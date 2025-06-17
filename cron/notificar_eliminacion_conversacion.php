<?php
// This script should run daily
// Example cron: 0 0 * * * php /path/to/your/project/cron/notificar_eliminacion_conversacion.php

require __DIR__ . '/../includes/app.php'; // Adjust path as needed

use Model\Valoracion;
use Model\Notificacion;
use Model\Usuario;
use Model\Producto;
use Classes\Email;

echo "Iniciando script de notificación de eliminación de conversaciones...\n";

// Find valoraciones where sale_completed_at is X days ago (e.g., 23 days ago, to give 7 days notice for a 30-day window)
$daysForNotification = 0; // Cambia esto para pruebas, luego vuelve a 23 o el valor deseado
$notificationDate = date('Y-m-d', strtotime("-{$daysForNotification} days"));

$query = "SELECT * FROM valoraciones WHERE sale_completed_at IS NOT NULL AND DATE(sale_completed_at) = '{$notificationDate}' AND (estrellas IS NULL OR estrellas IS NOT NULL)"; // Check all valoraciones where sale was completed

$ventasCompletadas = Valoracion::consultarSQL($query);

if (empty($ventasCompletadas)) {
    echo "No hay ventas completadas para enviar notificaciones de eliminación de conversación.\n";
    exit;
}

echo "Se encontraron " . count($ventasCompletadas) . " ventas completadas para notificación de eliminación.\n";

foreach ($ventasCompletadas as $valoracion) {
    $buyer = Usuario::find($valoracion->calificadorId); // Assuming calificador is buyer in this context
    $seller = Usuario::find($valoracion->calificadoId); // Assuming calificado is seller in this context
    $product = Producto::find($valoracion->productoId);

    if (!$buyer || !$seller || !$product) {
        echo "Skipping valoracion ID {$valoracion->id} due to missing user or product data.\n";
        continue;
    }

    // Determine who the buyer is and who the seller is from the valoracion perspective
    // One valoracion links buyer to seller, the other links seller to buyer for rating.
    // We need to identify both parties in the sale.
    $actualBuyer = ($valoracion->tipo === 'comprador') ? Usuario::find($valoracion->calificadorId) : Usuario::find($valoracion->calificadoId);
    $actualSeller = ($valoracion->tipo === 'vendedor') ? Usuario::find($valoracion->calificadorId) : Usuario::find($valoracion->calificadoId);

    if (!$actualBuyer || !$actualSeller || !$product) {
        echo "Skipping valoracion ID {$valoracion->id} due to missing user or product data after role check.\n";
        continue;
    }

    $commonUrl = "/mensajes?productoId={$product->id}&contactoId="; // Base URL

    // Notify Buyer
    $urlForBuyer = $commonUrl . $actualSeller->id;
    $notificacionBuyer = new Notificacion([
        'usuarioId' => $actualBuyer->id,
        'tipo' => 'eliminacion_conversacion',
        'mensaje' => "Aviso: La conversación sobre '{$product->nombre}' con {$actualSeller->nombre} será eliminada en 7 días debido a que la venta ha finalizado. Guarda la información relevante si la necesitas.",
        'url' => $urlForBuyer
    ]);
    $notificacionBuyer->guardar();
    $emailBuyer = new Email($actualBuyer->email, $actualBuyer->nombre, '');
    $emailBuyer->enviarAvisoEliminacionConversacion($actualSeller->nombre, $product->nombre, $urlForBuyer, 7);
    echo "Notificación de eliminación enviada al comprador {$actualBuyer->email} para producto {$product->nombre}.\n";


    // Notify Seller
    $urlForSeller = $commonUrl . $actualBuyer->id;
    $notificacionSeller = new Notificacion([
        'usuarioId' => $actualSeller->id,
        'tipo' => 'eliminacion_conversacion',
        'mensaje' => "Aviso: La conversación sobre '{$product->nombre}' con {$actualBuyer->nombre} será eliminada en 7 días debido a que la venta ha finalizado. Guarda la información relevante si la necesitas.",
        'url' => $urlForSeller
    ]);
    $notificacionSeller->guardar();
    $emailSeller = new Email($actualSeller->email, $actualSeller->nombre, '');
    $emailSeller->enviarAvisoEliminacionConversacion($actualBuyer->nombre, $product->nombre, $urlForSeller, 7);
    echo "Notificación de eliminación enviada al vendedor {$actualSeller->email} para producto {$product->nombre}.\n";
}

echo "Script de notificación de eliminación de conversaciones finalizado.\n";
?>