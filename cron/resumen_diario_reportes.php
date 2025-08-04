<?php
// Asegura que el script se ejecute desde la línea de comandos
if (php_sapi_name() !== 'cli') {
    exit;
}

require __DIR__ . '/../includes/app.php';

use Model\Notificacion;
use Model\Producto;
use Model\ReporteProducto;
use Model\Usuario;
use Classes\Email; // Importar la clase Email

echo "Iniciando cron de resumen diario de reportes..." . PHP_EOL;

// 1. Obtener reportes de las últimas 24 horas
$query = "SELECT * FROM " . ReporteProducto::getTablaNombre() . " WHERE creado >= NOW() - INTERVAL 24 HOUR";
$reportes = ReporteProducto::consultarSQL($query);

if (empty($reportes)) {
    echo "No hay reportes nuevos en las últimas 24 horas. Saliendo." . PHP_EOL;
    exit;
}

// 2. Preparar el contenido del resumen
$totalReportes = count($reportes);
$resumenTexto = "Resumen de reportes del día: {$totalReportes} en total." . PHP_EOL . PHP_EOL;
$resumenHtml = "<h1>Resumen de reportes del día: {$totalReportes} en total.</h1><hr>";

foreach ($reportes as $reporte) {
    $producto = Producto::find($reporte->productoId);
    if (!$producto) continue;

    $totalReportesProducto = ReporteProducto::contarTotalReportes($producto->id);
    $indicador = $totalReportesProducto > 5 ? ' **(Reportes Múltiples: ' . $totalReportesProducto . ')**' : '';

    // Para texto plano (notificación en sistema)
    $resumenTexto .= "- Producto ID: {$producto->id} ('{$producto->nombre}') {$indicador}" . PHP_EOL;
    $resumenTexto .= "  Motivo: {$reporte->motivo}" . PHP_EOL;
    if(!empty($reporte->comentarios)){
        $resumenTexto .= "  Comentario: {$reporte->comentarios}" . PHP_EOL;
    }
    $resumenTexto .= "------------------------------------" . PHP_EOL;

    // Para HTML (email)
    $resumenHtml .= "<p><b>Producto ID:</b> {$producto->id} (<a href='{$_ENV['HOST']}/marketplace/producto?id={$producto->id}'>'{$producto->nombre}'</a>){$indicador}</p>";
    $resumenHtml .= "<ul>";
    $resumenHtml .= "<li><b>Motivo:</b> {$reporte->motivo}</li>";
    if(!empty($reporte->comentarios)){
        $resumenHtml .= "<li><b>Comentario:</b> {$reporte->comentarios}</li>";
    }
    $resumenHtml .= "</ul><hr>";
}

// 3. Enviar notificaciones a todos los administradores según sus preferencias
$admins = Usuario::findAdmins();
if (empty($admins)) {
    echo "No se encontraron administradores para notificar. Saliendo." . PHP_EOL;
    exit;
}

foreach ($admins as $admin) {
    $prefs = json_decode($admin->preferencias_notificaciones ?? '{}', true);

    // Notificación vía sistema
    if ($prefs['notif_resumen_diario_sistema'] ?? true) { // Activado por defecto
        $notificacionResumen = new Notificacion([
            'tipo' => 'resumen_diario_reportes',
            'mensaje' => "Resumen Diario: {$totalReportes} Nuevos Reportes de Productos",
            'descripcion' => $resumenTexto, // Usamos la descripción para el detalle
            'url' => '/admin/reportes',
            'usuarioId' => $admin->id
        ]);
        $notificacionResumen->guardar();
        echo "Notificación de sistema enviada a {$admin->email}" . PHP_EOL;
    }

    // Notificación vía correo electrónico
    if ($prefs['notif_resumen_diario_email'] ?? true) { // Activado por defecto
        $email = new Email($admin->email, $admin->nombre, '');
        $email->enviarResumenDiarioReportes($resumenHtml);
        echo "Email de resumen enviado a {$admin->email}" . PHP_EOL;
    }
}

echo "Proceso de resumen diario de reportes finalizado." . PHP_EOL;
?>