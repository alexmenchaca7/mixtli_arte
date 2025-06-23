<?php
// This script should run daily (e.g., 7 days after the notification, or 30 days after sale_completed_at)
// Example cron: 0 0 * * * php /path/to/your/project/cron/eliminar_conversaciones_vendidas.php

require __DIR__ . '/../includes/app.php'; // Adjust path as needed

use Model\Valoracion;
use Model\Mensaje;
use Model\Usuario;
use Model\Producto;

echo "Iniciando script de eliminación de conversaciones de productos vendidos...\n";

// Define the period after sale_completed_at when conversations should be deleted.
// This should be greater than the notification period (e.g., 30 days after sale_completed_at)
$daysForDeletion = 30; // Cambia esto para pruebas, luego vuelve a 30 o el valor deseado
$dateToMatch = date('Y-m-d', strtotime("-{$daysForDeletion} days")); // Renamed for clarity: this is the date we are matching in the DB

// Find valoraciones where sale_completed_at is exactly $daysForDeletion days ago
$query = "SELECT * FROM valoraciones WHERE sale_completed_at IS NOT NULL AND DATE(sale_completed_at) = '{$dateToMatch}'"; // Use $dateToMatch here

$ventasParaEliminarConversacion = Valoracion::consultarSQL($query);

if (empty($ventasParaEliminarConversacion)) {
    echo "No hay conversaciones elegibles para eliminación automática hoy.\n";
    exit;
}

echo "Se encontraron " . count($ventasParaEliminarConversacion) . " ventas para eliminar conversaciones.\n";

foreach ($ventasParaEliminarConversacion as $valoracion) {
    // To delete the conversation, we need the product ID and the two user IDs involved in that specific sale.
    // A valoracion directly links a calificador (buyer/seller) to a calificado (seller/buyer) for a product.
    // We can use these IDs to identify the specific conversation.
    $productoId = $valoracion->productoId;
    $user1Id = $valoracion->calificadorId; // One party in the sale
    $user2Id = $valoracion->calificadoId;  // The other party in the sale

    // Double check that the product and users still exist
    $product = Producto::find($productoId);
    $user1 = Usuario::find($user1Id);
    $user2 = Usuario::find($user2Id);

    if (!$product || !$user1 || !$user2) {
        echo "Skipping conversation deletion for valoracion ID {$valoracion->id} due to missing product or user data.\n";
        continue;
    }

    // Delete the conversation messages
    $deleted = Mensaje::eliminarConversacionPorProductoYUsuarios($productoId, $user1Id, $user2Id);

    if ($deleted) {
        echo "Eliminada conversación del producto ID {$productoId} entre usuarios {$user1Id} y {$user2Id}.\n";
    } else {
        echo "Error al intentar eliminar conversación del producto ID {$productoId} entre usuarios {$user1Id} y {$user2Id}.\n";
    }

    // OPTIONAL: You might want to mark the valoracion record itself to indicate that its conversation has been deleted,
    // or prevent future cron runs from trying to delete it again.
    // For example, add a `conversation_deleted` boolean column to `valoraciones` table and set it to true here.
    // $valoracion->conversation_deleted = 1;
    // $valoracion->guardar();
}

echo "Script de eliminación de conversaciones de productos vendidos finalizado.\n";