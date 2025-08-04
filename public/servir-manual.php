<?php
require_once __DIR__ . '/../includes/app.php';

if (!is_auth()) {
    http_response_code(403);
    echo "Acceso denegado.";
    exit;
}

$tipo = $_GET['tipo'] ?? 'comprador';
$rol = $_SESSION['rol'] ?? '';
$pdfFile = '';

// Verificación de seguridad adicional
if ($rol === 'comprador' && $tipo !== 'comprador') {
    http_response_code(403);
    echo "Acceso no autorizado.";
    exit;
}

if ($tipo === 'vendedor' && ($rol === 'vendedor' || $rol === 'admin')) {
    $pdfFile = __DIR__ . '/../manuales/manual_vendedor.pdf';
} elseif ($tipo === 'comprador') {
    $pdfFile = __DIR__ . '/../manuales/manual_comprador.pdf';
} else {
    http_response_code(403);
    echo "Acceso no autorizado.";
    exit;
}

if (file_exists($pdfFile)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($pdfFile) . '"');
    header('Content-Length: ' . filesize($pdfFile));
    readfile($pdfFile);
    exit;
} else {
    http_response_code(404);
    echo "Manual no encontrado.";
    exit;
}