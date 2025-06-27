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
$productosConMultiplesReportes = [];

foreach ($reportes as $reporte) {
    $producto = Producto::find($reporte->productoId);
    if (!$producto) continue;

    $totalReportesProducto = ReporteProducto::contarTotalReportes($producto->id);

    // Indicador especial para productos con múltiples reportes
    $indicador = '';
    if ($totalReportesProducto > 5) { // Umbral para considerar "múltiples reportes" en el resumen
        $indicador = ' **(Reportes Múltiples: ' . $totalReportesProducto . ')**';
    }

    $resumenTexto .= "- Producto ID: {$producto->id} ('{$producto->nombre}') {$indicador}" . PHP_EOL;
    $resumenTexto .= "  Motivo: {$reporte->motivo}" . PHP_EOL;
    if(!empty($reporte->comentarios)){
        $resumenTexto .= "  Comentario: {$reporte->comentarios}" . PHP_EOL;
    }
    $resumenTexto .= "------------------------------------" . PHP_EOL;
}

// 3. Enviar notificación a todos los administradores
$admins = Usuario::findAdmins();
if (empty($admins)) {
    echo "No se encontraron administradores para notificar. Saliendo." . PHP_EOL;
    exit;
}

foreach ($admins as $admin) {
    $notificacionResumen = new Notificacion([
        'tipo' => 'resumen_diario_reportes',
        'descripcion' => $resumenTexto,
        'mensaje' => "Resumen Diario: {$totalReportes} Reportes", 
        'url' => '/admin/dashboard', 
        'usuarioId' => $admin->id
    ]);
    $notificacionResumen->guardar();
}

echo "Resumen diario de reportes enviado a " . count($admins) . " administradores." . PHP_EOL;
?>