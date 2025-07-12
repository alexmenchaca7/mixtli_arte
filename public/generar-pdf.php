<?php
    require_once __DIR__ . '/../includes/app.php'; 
    use Fpdf\Fpdf;

    class PDF extends FPDF {
        protected $B = 0;
        protected $I = 0;
        protected $U = 0;
        protected $HREF = '';
        protected $paddingLeft = 0;

        // Cabecera de página
        function Header() {
            // No añadimos cabecera para mantenerlo limpio
        }

        // Pie de página
        function Footer() {
            // Posición: a 1,5 cm del final
            $this->SetY(-15);
            // Arial italic 8
            $this->SetFont('Arial', 'I', 8);
            // Número de página
            $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        }

        function WriteHTML($html) {
            // Decodificar entidades HTML y convertir a la codificación correcta
            $html = html_entity_decode($html);
            $html = iconv('UTF-8', 'windows-1252', $html);
            
            // Dividir el HTML en etiquetas y texto
            $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
            foreach ($a as $i => $e) {
                if ($i % 2 == 0) {
                    // Es texto, escribirlo
                    $this->Write(5, $e);
                } else {
                    // Es una etiqueta
                    if ($e[0] == '/') {
                        $this->CloseTag(strtoupper(substr($e, 1)));
                    } else {
                        $a2 = explode(' ', $e);
                        $tag = strtoupper(array_shift($a2));
                        $attr = [];
                        foreach ($a2 as $v) {
                            if (preg_match('/([^=]*)=["\']?([^"\']*)/', $v, $a3)) {
                                $attr[strtoupper($a3[1])] = $a3[2];
                            }
                        }
                        $this->OpenTag($tag, $attr);
                    }
                }
            }
        }

        function OpenTag($tag, $attr) {
            switch ($tag) {
                case 'STRONG':
                case 'B':
                    $this->SetFont('', 'B');
                    break;
                case 'H3':
                    $this->Ln(6);
                    $this->SetFont('Arial', 'B', 14);
                    $this->SetTextColor(40, 40, 40);
                    break;
                case 'P':
                    $this->Ln(5);
                    break;
                case 'UL':
                    $this->Ln(5);
                    $this->paddingLeft = $this->GetX(); // Guardar sangría actual
                    break;
                case 'LI':
                    $this->Ln(5);
                    $this->SetX($this->paddingLeft + 5); // Aplicar sangría
                    $this->Cell(5, 5, chr(149), 0, 0); // Viñeta (código para Cp1252)
                    break;
                case 'BR':
                    $this->Ln(5);
                    break;
            }
        }

        function CloseTag($tag) {
            switch ($tag) {
                case 'STRONG':
                case 'B':
                    $this->SetFont('', '');
                    break;
                case 'H3':
                    $this->SetFont('Arial', '', 12);
                    $this->SetTextColor(34, 34, 34);
                    $this->Ln(6);
                    break;
                case 'UL':
                    $this->Ln(5);
                    $this->paddingLeft = 0; // Resetear sangría
                    break;
                case 'LI':
                    // No es necesario un salto de línea extra al cerrar LI
                    break;
            }
        }
    }

    $documento = $_GET['documento'] ?? '';

    // Función para obtener y limpiar el contenido HTML
    function obtenerContenidoLimpio($archivo) {
        ob_start();
        // Incluir la vista de la política
        include('../views/paginas/politicas/' . $archivo . '.php');
        $html = ob_get_clean();

        // 1. Eliminar el botón de descarga
        $html = preg_replace('/<a href="\/generar-pdf.php[^>]*>.*?<\/a>/is', '', $html);
        
        // 2. Eliminar el título h3 inicial para evitar duplicados
        $html = preg_replace('/<h3 class="text-center my-5">.*?<\/h3>/is', '', $html, 1);
        
        // 3. Limpiar espacios en blanco extra entre etiquetas
        $html = preg_replace('/>\s+</', '><', $html);

        return trim($html);
    }

    $pdf = new PDF();
    $pdf->AliasNbPages(); // Habilita el alias para el total de páginas
    $pdf->AddPage();
    $pdf->SetMargins(20, 20, 20); // Márgenes más amplios (izq, arriba, der)
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(34, 34, 34);

    $tituloDoc = '';
    $nombreArchivo = '';

    if ($documento === 'privacidad') {
        $tituloDoc = 'Politica de Privacidad - MixtliArte';
        $nombreArchivo = 'politica-privacidad-mixtliarte.pdf';
        $contenidoHTML = obtenerContenidoLimpio('privacidad');
    } elseif ($documento === 'terminos') {
        $tituloDoc = 'Terminos y Condiciones - MixtliArte';
        $nombreArchivo = 'terminos-condiciones-mixtliarte.pdf';
        $contenidoHTML = obtenerContenidoLimpio('terminos');
    } else {
        echo "Documento no válido.";
        exit;
    }

    // Título Principal del Documento
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, $tituloDoc, 0, 1, 'C');
    $pdf->Ln(10);

    // Restaurar fuente para el contenido
    $pdf->SetFont('Arial', '', 12);
    $pdf->WriteHTML($contenidoHTML);

    $pdf->Output('D', $nombreArchivo);
?>