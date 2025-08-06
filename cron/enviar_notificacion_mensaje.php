<?php
// Este script se ejecuta automáticamente en segundo plano cada vez que un usuario envía un mensaje.
// Su propósito es dar una respuesta inmediata en el chat sin esperar a que el correo se envíe.

// Define que este script es una tarea de sistema (cron job)
define('IS_CRON_JOB', true);

require __DIR__ . '/../includes/app.php';

use Model\Usuario;
use Model\Producto;
use Classes\Email;

// Validamos que se reciban los argumentos necesarios desde la línea de comandos
if ($argc < 5) {
    exit("Uso: php enviar_notificacion_mensaje.php <destinatarioId> <remitenteId> <productoId> <mensaje>\n");
}

// Asignamos los argumentos a variables
$destinatarioId = $argv[1];
$remitenteId = $argv[2];
$productoId = $argv[3];
$mensajeTexto = $argv[4];

// Buscamos la información necesaria en la base de datos
$destinatarioInfo = Usuario::find($destinatarioId);
$remitenteInfo = Usuario::find($remitenteId);
$productoInfo = Producto::find($productoId);

// Si todos los datos son válidos, procedemos a enviar el correo
if ($destinatarioInfo && $remitenteInfo && $productoInfo) {

    // Verificar las preferencias del destinatario
    $prefs = json_decode($destinatarioInfo->preferencias_notificaciones ?? '{}', true);
    $quiereEmail = $prefs['notif_mensaje_email'] ?? true; 

    if (!$quiereEmail) {
        // Si el usuario desactivó esta notificación, terminamos el script.
        exit("El destinatario ha desactivado las notificaciones por correo para mensajes.\n");
    }

    // Comprobar si el usuario está activo
    $isOnline = false;
    if ($destinatarioInfo->last_active) {
        $lastActiveTimestamp = strtotime($destinatarioInfo->last_active);
        $currentTime = time();
        // Si la última actividad fue hace menos de 3 minutos, consideramos que está online
        if (($currentTime - $lastActiveTimestamp) < (3 * 60)) {
            $isOnline = true;
        }
    }

    // Si el usuario está online, no enviamos el correo.
    if ($isOnline) {
        exit("El destinatario está activo en la plataforma. No se enviará correo.\n");
    }

    // Si el usuario quiere email y no está activo, procedemos a enviar el correo
    $mensajeCortoPreview = substr(stripslashes($mensajeTexto), 0, 70);
    $urlConversacion = $_ENV['HOST'] . "/mensajes?productoId={$productoId}&contactoId={$remitenteId}";
    
    $email = new Email($destinatarioInfo->email, $destinatarioInfo->nombre, '');
    $email->enviarNotificacionNuevoMensaje(
        $destinatarioInfo->email,
        $destinatarioInfo->nombre . ' ' . $destinatarioInfo->apellido,
        $remitenteInfo->nombre,
        $productoInfo->nombre,
        $mensajeCortoPreview,
        $urlConversacion
    );
}