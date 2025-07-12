<?php
    require_once __DIR__ . '/../includes/app.php'; 
    use Fpdf\Fpdf;

    $documento = $_GET['documento'] ?? '';

    // Función simple para obtener el contenido HTML de la política
    function obtenerContenido($archivo) {
        ob_start();
        include('../views/paginas/politicas/' . $archivo . '.php');
        $html = ob_get_clean();

        // Limpiar el HTML para texto plano
        $texto = strip_tags(preg_replace('/<a[^>]*>.*?<\/a>/i', '', $html));
        return $texto;
    }

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);

    if ($documento === 'privacidad') {
        $pdf->Cell(40, 10, 'Politica de Privacidad - MixtliArte');
        $pdf->Ln(20);
        $pdf->SetFont('Arial', '', 12);
        $contenido = obtenerContenido('privacidad');
        $pdf->MultiCell(0, 10, mb_convert_encoding($contenido, 'ISO-8859-1', 'UTF-8'));
        $pdf->Output('D', 'politica-privacidad-mixtliarte.pdf');

    } elseif ($documento === 'terminos') {
        $pdf->Cell(40, 10, 'Terminos y Condiciones - MixtliArte');
        $pdf->Ln(20);
        $pdf->SetFont('Arial', '', 12);
        $contenido = obtenerContenido('terminos');
        $pdf->MultiCell(0, 10, mb_convert_encoding($contenido, 'ISO-8859-1', 'UTF-8'));
        $pdf->Output('D', 'terminos-condiciones-mixtliarte.pdf');

    } else {
        echo "Documento no válido.";
    }
?>