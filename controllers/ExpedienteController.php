<?php

namespace Controllers;

use Exception;
use Model\Vehiculos;
use Model\Servicios;
use Model\Reparaciones;
use Mpdf\Mpdf;
use MVC\Router;

class ExpedienteController
{
    // ─────────────────────────────────────────────────────────────────────────
    //  ENTRY POINT
    // ─────────────────────────────────────────────────────────────────────────
    public static function generarPDF(Router $router): void
    {
        $placa = strtoupper(trim($_GET['placa'] ?? ''));

        if (!$placa) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Placa requerida']);
            return;
        }

        try {
            // ── 1. Obtener datos ──────────────────────────────────────────────
            $vehiculo     = Vehiculos::traerConDetalle($placa);
            $servicios    = Servicios::traerPorPlaca($placa);
            $reparaciones = Reparaciones::traerPorPlaca($placa);

            if (!$vehiculo) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Vehículo no encontrado']);
                return;
            }

            // Normalizar campos
            array_walk($vehiculo, function (&$val) {
                if (is_array($val)) $val = implode(', ', $val);
                $val = (string)($val ?? '');
            });

            // ── 2. Fotos base64 ───────────────────────────────────────────────
            $fotoBase64 = !empty($vehiculo['foto_frente'])
                ? self::obtenerFotoBase64($vehiculo['foto_frente']) : '';

            // ── 3. Separar servicios ──────────────────────────────────────────
            $cambiosLlantas = array_values(array_filter(
                $servicios,
                fn($s) => stripos($s['tipo_nombre'], 'llanta') !== false
            ));
            $cambiosAcumulador = array_values(array_filter(
                $servicios,
                fn($s) =>
                stripos($s['tipo_nombre'], 'acumulador') !== false ||
                    stripos($s['tipo_nombre'], 'batería')    !== false ||
                    stripos($s['tipo_nombre'], 'bateria')    !== false
            ));

            // ── 4. Chequeo ────────────────────────────────────────────────────
            $chequeo = \Model\Chequeos::traerUltimoCompletado($placa);

            // ── 5. Descargar PDFs externos ────────────────────────────────────
            $tmpTarjeta    = self::descargarPdfTmp($vehiculo['tarjeta_pdf']    ?? '');
            $tmpInventario = self::descargarPdfTmp($vehiculo['cert_inventario'] ?? '');
            $tmpSicoin     = self::descargarPdfTmp($vehiculo['cert_sicoin']    ?? '');

            // ── 6. Generar mPDF principal ─────────────────────────────────────
            $mpdf = new Mpdf([
                'mode'              => 'utf-8',
                'format'            => 'Letter',
                'margin_top'        => 15,
                'margin_bottom'     => 15,
                'margin_left'       => 18,
                'margin_right'      => 18,
                'margin_header'     => 8,
                'margin_footer'     => 8,
                'default_font_size' => 10,
                'default_font'      => 'dejavusans',
            ]);
            $mpdf->SetTitle("Expediente Vehículo {$placa}");
            $mpdf->SetAuthor('Brigada Humanitaria y de Rescate – Ejército de Guatemala');
            $mpdf->WriteHTML(self::estilosGlobales(), \Mpdf\HTMLParserMode::HEADER_CSS);

            // Sección 1 — Carátula
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaCaratula($vehiculo, $fotoBase64));

            // Sección 2 — Índice
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaIndice($vehiculo));

            // Sección 3 — Fotografía
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('', 'FOTOGRAFÍA DEL VEHÍCULO'));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaFoto($vehiculo, $fotoBase64));

            // Sección 4 — Información
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('', 'INFORMACIÓN DEL VEHÍCULO'));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaInfoVehiculo($vehiculo));

            // Sección 5 — Separador Tarjeta
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('', 'COPIA DE TARJETA DE CIRCULACIÓN'));
            if (!$tmpTarjeta) {
                $mpdf->AddPage();
                $mpdf->WriteHTML(self::paginaSinPDF($vehiculo, '05', 'COPIA DE TARJETA DE CIRCULACIÓN', 'tarjeta de circulación'));
            }

            // Sección 6 — Separador Cert Inventario
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('', 'CERTIFICACIÓN INVENTARIO'));
            if (!$tmpInventario) {
                $mpdf->AddPage();
                $mpdf->WriteHTML(self::paginaSinPDF($vehiculo, '06', 'CERTIFICACIÓN INVENTARIO', 'certificación de inventario'));
            }

            // Sección 7 — Separador Cert SICOIN
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('', 'CERTIFICACIÓN SICOIN WEB'));
            if (!$tmpSicoin) {
                $mpdf->AddPage();
                $mpdf->WriteHTML(self::paginaSinPDF($vehiculo, '07', 'CERTIFICACIÓN SICOIN WEB', 'certificación SICOIN'));
            }

            // Sección 8 — Servicios
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('', 'HISTORIAL DE SERVICIOS'));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaHistorialServicios($vehiculo, $servicios));

            // Sección 9 — Llantas
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('', 'HISTORIAL DE CAMBIO DE LLANTAS'));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaHistorialFiltrado($vehiculo, $cambiosLlantas, 'HISTORIAL DE CAMBIO DE LLANTAS', 'llantas'));

            // Sección 10 — Acumulador
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('', 'HISTORIAL DE CAMBIO DE ACUMULADOR'));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaHistorialFiltrado($vehiculo, $cambiosAcumulador, 'HISTORIAL DE CAMBIO DE ACUMULADOR', 'acumulador'));

            // Sección 11 — Reparaciones
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('', 'REPARACIONES'));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaReparaciones($vehiculo, $reparaciones));

            // Sección 16 — Chequeo
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('', 'HOJA INDIVIDUAL DE CHEQUEO DE VEHÍCULOS'));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaHojaChequeo($vehiculo, $chequeo));

            // ── 7. Guardar mPDF en tmp ────────────────────────────────────────
            $tmpMpdf = tempnam(sys_get_temp_dir(), 'exp_') . '.pdf';
            $mpdf->Output($tmpMpdf, \Mpdf\Output\Destination::FILE);

            // ── 8. Merge con FPDI ─────────────────────────────────────────────
            $nombreArchivo = "Expediente_{$placa}_" . date('Ymd') . '.pdf';
            self::mergePDF(
                $tmpMpdf,
                $tmpTarjeta,
                $tmpInventario,
                $tmpSicoin,
                $nombreArchivo
            );

            // Limpiar temporales
            @unlink($tmpMpdf);
            if ($tmpTarjeta)    @unlink($tmpTarjeta);
            if ($tmpInventario) @unlink($tmpInventario);
            if ($tmpSicoin)     @unlink($tmpSicoin);

            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al generar expediente',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  MERGE PDF con FPDI
    // ─────────────────────────────────────────────────────────────────────────
    private static function mergePDF(
        string  $archivoPrincipal,
        ?string $tmpTarjeta,
        ?string $tmpInventario,
        ?string $tmpSicoin,
        string  $nombreSalida
    ): void {
        $fpdi = new \setasign\Fpdi\Fpdi();

        // Helper: importar todas las páginas de un PDF externo
        $importarPdf = function (string $archivo) use ($fpdi) {
            try {
                $total = $fpdi->setSourceFile($archivo);
                for ($i = 1; $i <= $total; $i++) {
                    $tpl    = $fpdi->importPage($i);
                    $size   = $fpdi->getTemplateSize($tpl);
                    $orient = ($size['width'] > $size['height']) ? 'L' : 'P';
                    $fpdi->AddPage($orient, [$size['width'], $size['height']]);
                    $fpdi->useTemplate($tpl, 0, 0, $size['width'], $size['height']);
                }
            } catch (\Throwable $e) {
                // Si falla importar el PDF externo, ignorar silenciosamente
            }
        };

        // Helper: importar una página del PDF principal
        $importarPagina = function (int $pagina) use ($fpdi, $archivoPrincipal) {
            $fpdi->setSourceFile($archivoPrincipal);
            // Necesitamos navegar hasta la página correcta
            for ($i = 1; $i <= $pagina; $i++) {
                $tpl = $fpdi->importPage($i);
            }
            $size   = $fpdi->getTemplateSize($tpl);
            $orient = ($size['width'] > $size['height']) ? 'L' : 'P';
            $fpdi->AddPage($orient, [$size['width'], $size['height']]);
            $fpdi->useTemplate($tpl, 0, 0, $size['width'], $size['height']);
        };

        // Leer el PDF principal completo para saber total de páginas
        $totalPrincipal = $fpdi->setSourceFile($archivoPrincipal);

        // ── Calcular posición de cada sección en el PDF principal ─────────────
        // Página 1: Carátula
        // Página 2: Índice
        // Página 3: Sep Foto
        // Página 4: Foto
        // Página 5: Sep Info
        // Página 6: Info
        // Página 7: Sep Tarjeta
        // Página 8: [Ref Tarjeta si no hay PDF] o → PDF externo
        // Página 8 o 9: Sep Inventario
        // Página 9 o 10: [Ref Inventario si no hay PDF] o → PDF externo
        // ...etc

        $paginaSepTarjeta    = 7;
        $paginaRefTarjeta    = $paginaSepTarjeta + 1; // solo existe si no hay PDF
        $paginaSepInventario = $paginaSepTarjeta + 1 + ($tmpTarjeta ? 0 : 1);
        $paginaRefInventario = $paginaSepInventario + 1;
        $paginaSepSicoin     = $paginaSepInventario + 1 + ($tmpInventario ? 0 : 1);
        $paginaRefSicoin     = $paginaSepSicoin + 1;
        $paginaResto         = $paginaSepSicoin + 1 + ($tmpSicoin ? 0 : 1);

        // ── Páginas 1 a 7 (Carátula → Separador Tarjeta) ──────────────────────
        $fpdi->setSourceFile($archivoPrincipal);
        for ($i = 1; $i <= $paginaSepTarjeta; $i++) {
            $tpl  = $fpdi->importPage($i);
            $size = $fpdi->getTemplateSize($tpl);
            $fpdi->AddPage('P', [$size['width'], $size['height']]);
            $fpdi->useTemplate($tpl, 0, 0, $size['width'], $size['height']);
        }

        // ── Tarjeta PDF externo o página de referencia ────────────────────────
        if ($tmpTarjeta) {
            $importarPdf($tmpTarjeta);
        } else {
            $fpdi->setSourceFile($archivoPrincipal);
            $tpl  = $fpdi->importPage($paginaRefTarjeta);
            $size = $fpdi->getTemplateSize($tpl);
            $fpdi->AddPage('P', [$size['width'], $size['height']]);
            $fpdi->useTemplate($tpl, 0, 0, $size['width'], $size['height']);
        }

        // ── Separador Inventario ──────────────────────────────────────────────
        $fpdi->setSourceFile($archivoPrincipal);
        $tpl  = $fpdi->importPage($paginaSepInventario);
        $size = $fpdi->getTemplateSize($tpl);
        $fpdi->AddPage('P', [$size['width'], $size['height']]);
        $fpdi->useTemplate($tpl, 0, 0, $size['width'], $size['height']);

        // ── Cert Inventario PDF externo o referencia ──────────────────────────
        if ($tmpInventario) {
            $importarPdf($tmpInventario);
        } else {
            $fpdi->setSourceFile($archivoPrincipal);
            $tpl  = $fpdi->importPage($paginaRefInventario);
            $size = $fpdi->getTemplateSize($tpl);
            $fpdi->AddPage('P', [$size['width'], $size['height']]);
            $fpdi->useTemplate($tpl, 0, 0, $size['width'], $size['height']);
        }

        // ── Separador SICOIN ──────────────────────────────────────────────────
        $fpdi->setSourceFile($archivoPrincipal);
        $tpl  = $fpdi->importPage($paginaSepSicoin);
        $size = $fpdi->getTemplateSize($tpl);
        $fpdi->AddPage('P', [$size['width'], $size['height']]);
        $fpdi->useTemplate($tpl, 0, 0, $size['width'], $size['height']);

        // ── Cert SICOIN PDF externo o referencia ──────────────────────────────
        if ($tmpSicoin) {
            $importarPdf($tmpSicoin);
        } else {
            $fpdi->setSourceFile($archivoPrincipal);
            $tpl  = $fpdi->importPage($paginaRefSicoin);
            $size = $fpdi->getTemplateSize($tpl);
            $fpdi->AddPage('P', [$size['width'], $size['height']]);
            $fpdi->useTemplate($tpl, 0, 0, $size['width'], $size['height']);
        }

        // ── Resto del expediente (Servicios → Chequeo) ────────────────────────
        $fpdi->setSourceFile($archivoPrincipal);
        for ($i = $paginaResto; $i <= $totalPrincipal; $i++) {
            $tpl  = $fpdi->importPage($i);
            $size = $fpdi->getTemplateSize($tpl);
            $fpdi->AddPage('P', [$size['width'], $size['height']]);
            $fpdi->useTemplate($tpl, 0, 0, $size['width'], $size['height']);
        }

        // ── Enviar al browser ─────────────────────────────────────────────────
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $nombreSalida . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        $fpdi->Output($nombreSalida, 'I');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  DESCARGAR PDF EXTERNO A /tmp
    // ─────────────────────────────────────────────────────────────────────────
    private static function descargarPdfTmp(string $nombreArchivo): ?string
    {
        if (empty($nombreArchivo)) return null;

        try {
            $url = 'http://localhost/bhr_functions/API/vehiculos/foto?archivo=/' . urlencode($nombreArchivo);
            $contenido = @file_get_contents($url);

            if (!$contenido || strlen($contenido) < 100) return null;
            if (substr($contenido, 0, 4) !== '%PDF') return null;

            $tmp = tempnam(sys_get_temp_dir(), 'pdf_') . '.pdf';
            file_put_contents($tmp, $contenido);
            return $tmp;
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  HELPERS
    // ─────────────────────────────────────────────────────────────────────────
    private static function obtenerFotoBase64(string $nombreArchivo): string
    {
        try {
            $url = 'http://localhost/bhr_functions/API/vehiculos/foto?archivo=/' . urlencode($nombreArchivo);
            $contenido = @file_get_contents($url);
            if (!$contenido) return '';

            $ext  = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png'         => 'image/png',
                'webp'        => 'image/webp',
                default       => 'image/jpeg'
            };
            return "data:{$mime};base64," . base64_encode($contenido);
        } catch (\Throwable $e) {
            return '';
        }
    }

    private static function encabezado(string $seccion = '', string $titulo = ''): string
    {
        return '
        <table class="encabezado-tabla" width="100%">
            <tr>
                <td class="enc-izq">
                    <span class="enc-republica">REPÚBLICA DE GUATEMALA</span>
                    <span class="enc-sep"> &nbsp;·&nbsp; </span>
                    <span class="enc-ejército">EJÉRCITO DE GUATEMALA</span>
                </td>
                <td class="enc-der">
                    <strong>BRIGADA HUMANITARIA Y DE RESCATE</strong>
                </td>
            </tr>
        </table>
        ' . ($titulo ? '<div class="titulo-seccion">' . $titulo . '</div>' : '');
    }

    private static function pie(string $placa, string $seccion): string
    {
        return '
        <div class="pie-pagina">
            Vehículo: <strong>' . $placa . '</strong>
            &nbsp;|&nbsp; Sección: <strong>' . $seccion . '</strong>
            &nbsp;|&nbsp; Generado: ' . date('d/m/Y H:i') . '
            &nbsp;|&nbsp; MDN-BHR-SAGE
        </div>';
    }

    private static function separador(string $numero, string $titulo): string
    {
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/bhr_functions/public/images/triangule.png';
        $logoTag  = '';

        if (file_exists($logoPath)) {
            $ext     = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
            $mime    = match ($ext) {
                'png'         => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                default       => 'image/png'
            };
            $logoB64 = base64_encode(file_get_contents($logoPath));
            $logoTag = '<img src="data:' . $mime . ';base64,' . $logoB64 . '"
                         style="width:220px;height:auto;display:block;margin:0 auto;">';
        }

        return '
        <div class="separador-pagina">
            <div class="sep-titulo">' . $titulo . '</div>
            <div class="sep-linea"></div>
            ' . $logoTag . '
            <div class="sep-institucion" style="margin-top:18px;">
                BRIGADA HUMANITARIA Y DE RESCATE<br>
                EJÉRCITO DE GUATEMALA
            </div>
        </div>';
    }

    /** Página de referencia cuando no hay PDF subido */
    private static function paginaSinPDF(array $v, string $seccion, string $titulo, string $nombreDoc): string
    {
        return '
        ' . self::encabezado($seccion, $titulo) . '
        <table class="tabla-datos" style="margin-bottom:16px;">
            <tr>
                <td class="td-label">Placa</td>
                <td class="td-valor">' . htmlspecialchars($v['placa']) . '</td>
                <td class="td-label">Marca / Modelo</td>
                <td class="td-valor">' . htmlspecialchars($v['marca'] . ' ' . $v['modelo']) . '</td>
            </tr>
        </table>
        <div class="caja-vacia" style="min-height:300px;padding:60px;">
            <div style="font-size:14pt;margin-bottom:12px;">📄</div>
            No se ha digitalizado la ' . $nombreDoc . '.<br>
            <strong>Adjuntar copia física en esta sección.</strong>
        </div>
        <div style="margin-top:20px;border-top:1pt dashed #ccc;padding-top:14px;">
            <table width="100%">
                <tr>
                    <td style="text-align:center;padding:10px;">
                        <div style="border-top:0.5pt solid #333;margin:0 20px;padding-top:4px;font-size:8pt;color:#555;">
                            FIRMA Y SELLO DEL RESPONSABLE
                        </div>
                    </td>
                    <td style="text-align:center;padding:10px;">
                        <div style="border-top:0.5pt solid #333;margin:0 20px;padding-top:4px;font-size:8pt;color:#555;">
                            FECHA DE VERIFICACIÓN
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        ' . self::pie($v['placa'], $seccion . ' – ' . $titulo);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  CSS GLOBAL
    // ─────────────────────────────────────────────────────────────────────────
    private static function estilosGlobales(): string
    {
        return '
        <style>
            body { font-family: dejavusans, sans-serif; font-size: 10pt; color: #1a1a1a; margin: 0; padding: 0; }
            .encabezado-tabla { border-bottom: 2.5pt solid #C75B00; margin-bottom: 10px; padding-bottom: 4px; }
            .enc-izq { font-size: 8pt; color: #444; }
            .enc-der { font-size: 8pt; text-align: right; color: #C75B00; font-weight: bold; }
            .enc-republica { font-weight: bold; }
            .enc-sep { color: #999; }
            .titulo-seccion { text-align: center; font-size: 13pt; font-weight: bold; letter-spacing: 2pt; text-transform: uppercase; border-bottom: 1pt solid #C75B00; padding-bottom: 5px; margin-bottom: 14px; color: #1a1a1a; }
            .titulo-sub { font-size: 10pt; font-weight: bold; color: #C75B00; text-transform: uppercase; letter-spacing: 1pt; border-left: 3pt solid #C75B00; padding-left: 6px; margin: 10px 0 6px 0; }
            .separador-pagina { background-color: #ffffff; width: 100%; height: 680px; text-align: center; padding-top: 120px; }
            .sep-titulo { font-size: 18pt; font-weight: bold; color: #1a1a1a; text-transform: uppercase; letter-spacing: 3pt; border-top: 2pt solid #C75B00; border-bottom: 2pt solid #C75B00; padding: 12px 40px; margin: 0 60px; }
            .sep-linea { height: 1pt; background: #e0e0e0; margin: 18px 80px; }
            .sep-institucion { font-size: 9pt; color: #555555; letter-spacing: 1pt; line-height: 1.6; }
            .tabla-datos { width: 100%; border-collapse: collapse; font-size: 9.5pt; margin-bottom: 12px; }
            .tabla-datos th { background-color: #C75B00; color: #ffffff; font-weight: bold; text-align: center; padding: 5px 7px; border: 0.5pt solid #a34800; font-size: 8.5pt; text-transform: uppercase; letter-spacing: 0.5pt; }
            .tabla-datos td { padding: 4px 7px; border: 0.5pt solid #d0d0d0; vertical-align: middle; }
            .tabla-datos tr:nth-child(even) td { background-color: #fdf4ee; }
            .tabla-datos tr:nth-child(odd) td { background-color: #ffffff; }
            .tabla-datos .td-label { background-color: #f0f0f0 !important; font-weight: bold; color: #444; font-size: 8pt; text-transform: uppercase; width: 28%; }
            .tabla-datos .td-valor { font-weight: bold; color: #1a1a1a; }
            .grid-info { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
            .grid-info td { padding: 5px 8px; border: 0.5pt solid #d0d0d0; font-size: 9.5pt; vertical-align: top; }
            .grid-info .lbl { background: #f5f5f5; font-size: 7.5pt; font-weight: bold; color: #888; text-transform: uppercase; letter-spacing: 0.5pt; padding: 3px 8px 1px 8px; border-bottom: none; }
            .grid-info .val { font-size: 10pt; font-weight: bold; color: #1a1a1a; border-top: none; padding-top: 2px; }
            .badge-alta   { background:#e6f4ea; color:#2e7d32; padding:2px 8px; border-radius:3px; font-size:8.5pt; font-weight:bold; }
            .badge-baja   { background:#fce8e8; color:#c62828; padding:2px 8px; border-radius:3px; font-size:8.5pt; font-weight:bold; }
            .badge-taller { background:#fff3e0; color:#e65100; padding:2px 8px; border-radius:3px; font-size:8.5pt; font-weight:bold; }
            .tabla-control { width: 100%; border-collapse: collapse; font-size: 9pt; margin-bottom: 8px; }
            .tabla-control th { background-color: #1a1a1a; color: #ffffff; padding: 5px 7px; border: 0.5pt solid #333; text-align: center; font-size: 8pt; text-transform: uppercase; letter-spacing: 0.5pt; }
            .tabla-control td { padding: 4px 7px; border: 0.5pt solid #ccc; text-align: center; }
            .tabla-control tr:nth-child(even) td { background: #f9f9f9; }
            .pie-pagina { position: fixed; bottom: -12px; left: 0; right: 0; font-size: 7pt; color: #888; border-top: 0.5pt solid #ddd; padding-top: 3px; text-align: center; }
            .indice-tabla { width: 100%; border-collapse: collapse; font-size: 10pt; }
            .indice-tabla td { padding: 7px 10px; border-bottom: 0.5pt solid #e0e0e0; }
            .indice-tabla .num { width: 40px; text-align: center; font-weight: bold; color: #C75B00; font-size: 12pt; }
            .indice-tabla .titulo-item { font-weight: bold; color: #1a1a1a; }
            .indice-tabla .puntos { color: #ccc; text-align: right; font-size: 8pt; }
            .caja-vacia { border: 1pt dashed #ccc; padding: 20px; text-align: center; color: #aaa; font-size: 9pt; margin: 14px 0; }
        </style>';
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  PÁGINAS DEL EXPEDIENTE
    // ─────────────────────────────────────────────────────────────────────────

    private static function paginaCaratula(array $v, string $foto): string
    {
        $fotoTag = $foto
            ? '<img src="' . $foto . '" style="max-width:420px;max-height:320px;border:1.5pt solid #ccc;display:block;margin:0 auto;">'
            : '<div style="border:1.5pt dashed #ccc;padding:60px 40px;color:#aaa;font-size:10pt;text-align:center;display:inline-block;width:300px;">Sin fotografía registrada</div>';

        $estadoBadge = match ($v['estado'] ?? '') {
            'Alta'   => '<span class="badge-alta" style="font-size:11pt;padding:4px 16px;">● OPERATIVO – ALTA</span>',
            'Baja'   => '<span class="badge-baja" style="font-size:11pt;padding:4px 16px;">● FUERA DE SERVICIO – BAJA</span>',
            'Taller' => '<span class="badge-taller" style="font-size:11pt;padding:4px 16px;">● EN TALLER</span>',
            default  => ''
        };

        $unidad     = $v['unidad_nombre']       ?? '—';
        $dest       = $v['destacamento_nombre'] ?? '';
        $depto      = $v['destacamento_depto']  ?? '';
        $asignacion = $dest ? "{$unidad} · {$dest}, {$depto}" : $unidad;

        return '
        ' . self::encabezado() . '
        <div style="text-align:center;margin:6px 0 18px 0;">
            <div style="font-size:13pt;font-weight:bold;color:#C75B00;letter-spacing:3pt;text-transform:uppercase;">BRIGADA HUMANITARIA Y DE RESCATE</div>
            <div style="font-size:10pt;letter-spacing:2pt;color:#555;margin-top:4px;">EJÉRCITO DE GUATEMALA</div>
        </div>
        <div style="text-align:center;margin-bottom:20px;">' . $fotoTag . '</div>
        <div style="text-align:center;margin-bottom:20px;">
            <div style="font-size:9pt;color:#888;text-transform:uppercase;letter-spacing:2pt;margin-bottom:6px;">EXPEDIENTE DE VEHÍCULO — CATÁLOGO / PLACA</div>
            <div style="display:inline-block;border:3pt solid #1a1a1a;padding:10px 40px;">
                <span style="font-size:36pt;font-weight:bold;letter-spacing:6pt;">' . htmlspecialchars($v['placa']) . '</span>
            </div>
        </div>
        <div style="text-align:center;margin-bottom:20px;">' . $estadoBadge . '</div>
        <table width="100%" style="border-collapse:collapse;margin-bottom:16px;">
            <tr>
                <td style="padding:8px 10px;border:0.5pt solid #ddd;background:#f9f9f9;width:25%;"><div style="font-size:7.5pt;color:#888;text-transform:uppercase;">Marca</div><div style="font-size:13pt;font-weight:bold;">' . htmlspecialchars($v['marca']) . '</div></td>
                <td style="padding:8px 10px;border:0.5pt solid #ddd;background:#f9f9f9;width:25%;"><div style="font-size:7.5pt;color:#888;text-transform:uppercase;">Modelo</div><div style="font-size:13pt;font-weight:bold;">' . htmlspecialchars($v['modelo']) . '</div></td>
                <td style="padding:8px 10px;border:0.5pt solid #ddd;background:#f9f9f9;width:15%;"><div style="font-size:7.5pt;color:#888;text-transform:uppercase;">Año</div><div style="font-size:13pt;font-weight:bold;">' . htmlspecialchars($v['anio']) . '</div></td>
                <td style="padding:8px 10px;border:0.5pt solid #ddd;background:#f9f9f9;width:15%;"><div style="font-size:7.5pt;color:#888;text-transform:uppercase;">Color</div><div style="font-size:13pt;font-weight:bold;">' . htmlspecialchars($v['color']) . '</div></td>
                <td style="padding:8px 10px;border:0.5pt solid #ddd;background:#f9f9f9;width:20%;"><div style="font-size:7.5pt;color:#888;text-transform:uppercase;">Tipo</div><div style="font-size:13pt;font-weight:bold;">' . htmlspecialchars($v['tipo']) . '</div></td>
            </tr>
        </table>
        <table width="100%" style="border-collapse:collapse;margin-bottom:16px;">
            <tr>
                <td style="padding:8px 10px;border:0.5pt solid #ddd;width:40%;"><div style="font-size:7.5pt;color:#888;text-transform:uppercase;">Número de Serie / Chasis</div><div style="font-size:12pt;font-weight:bold;">' . htmlspecialchars($v['numero_serie']) . '</div></td>
                <td style="padding:8px 10px;border:0.5pt solid #ddd;width:60%;"><div style="font-size:7.5pt;color:#888;text-transform:uppercase;">Unidad Asignada</div><div style="font-size:11pt;font-weight:bold;">' . htmlspecialchars($asignacion) . '</div></td>
            </tr>
            <tr>
                <td style="padding:8px 10px;border:0.5pt solid #ddd;"><div style="font-size:7.5pt;color:#888;text-transform:uppercase;">Kilometraje Actual</div><div style="font-size:12pt;font-weight:bold;">' . number_format((int)$v['km_actuales']) . ' km</div></td>
                <td style="padding:8px 10px;border:0.5pt solid #ddd;"><div style="font-size:7.5pt;color:#888;text-transform:uppercase;">Fecha de Ingreso</div><div style="font-size:12pt;font-weight:bold;">' . htmlspecialchars($v['fecha_ingreso']) . '</div></td>
            </tr>
        </table>
        <div style="text-align:center;font-size:8pt;color:#aaa;border-top:0.5pt solid #eee;padding-top:8px;">
            Generado el ' . date('d \d\e F \d\e Y \a \l\a\s H:i') . ' · MDN-BHR-SAGE
        </div>
        ' . self::pie($v['placa'], '01 – CARÁTULA');
    }

    private static function paginaIndice(array $v): string
    {
        $secciones = [
            ['01', 'CARÁTULA'],
            ['02', 'ÍNDICE'],
            ['03', 'FOTOGRAFÍA DEL VEHÍCULO'],
            ['04', 'INFORMACIÓN DEL VEHÍCULO'],
            ['05', 'COPIA DE TARJETA DE CIRCULACIÓN'],
            ['06', 'CERTIFICACIÓN INVENTARIO'],
            ['07', 'CERTIFICACIÓN SICOIN WEB'],
            ['08', 'HISTORIAL DE SERVICIOS'],
            ['09', 'HISTORIAL DE CAMBIO DE LLANTAS'],
            ['10', 'HISTORIAL DE CAMBIO DE ACUMULADOR'],
            ['11', 'REPARACIONES (SMG)'],
            ['12', 'PROCEDIMIENTOS LEGALES – CAMBIOS (MOTOR, CHASIS, CARROCERÍA, COLOR, PLACAS)'],
            ['13', 'PROCEDIMIENTOS LEGALES – ACCIDENTES'],
            ['14', 'COPIA DE PAGOS DE MULTAS'],
            ['15', 'REGISTRO Y PROGRAMA DE MANTENIMIENTO MDN-SMG-SAGE-125'],
            ['16', 'HOJA INDIVIDUAL DE CHEQUEO DE VEHÍCULOS'],
        ];

        $filas = '';
        foreach ($secciones as $s) {
            $filas .= '<tr><td class="num">' . $s[0] . '</td><td class="titulo-item">' . $s[1] . '</td><td class="puntos">..................</td></tr>';
        }

        return '
        ' . self::encabezado('02', 'ÍNDICE') . '
        <table class="indice-tabla">' . $filas . '</table>
        ' . self::pie($v['placa'], '02 – ÍNDICE');
    }

    private static function paginaFoto(array $v, string $foto): string
    {
        $contenido = $foto
            ? '<div style="text-align:center;margin-top:20px;"><img src="' . $foto . '" style="max-width:500px;max-height:480px;border:1.5pt solid #ccc;"><div style="font-size:8pt;color:#888;margin-top:8px;text-transform:uppercase;letter-spacing:1pt;">Vista frontal – ' . htmlspecialchars($v['placa']) . '</div></div>'
            : '<div class="caja-vacia">Sin fotografía registrada en el sistema</div>';

        return '
        ' . self::encabezado('03', 'FOTOGRAFÍA DEL VEHÍCULO') . '
        <table width="100%" style="border-collapse:collapse;margin-bottom:12px;">
            <tr>
                <td class="td-label" style="border:0.5pt solid #ddd;background:#f5f5f5;padding:4px 8px;font-size:8pt;font-weight:bold;">TIPO DE VEHÍCULO</td>
                <td style="border:0.5pt solid #ddd;padding:4px 8px;"><strong>' . htmlspecialchars($v['tipo'] . ' – ' . $v['marca'] . ' ' . $v['modelo']) . '</strong></td>
                <td class="td-label" style="border:0.5pt solid #ddd;background:#f5f5f5;padding:4px 8px;font-size:8pt;font-weight:bold;">CATÁLOGO</td>
                <td style="border:0.5pt solid #ddd;padding:4px 8px;"><strong>' . htmlspecialchars($v['placa']) . '</strong></td>
            </tr>
        </table>
        ' . $contenido . '
        ' . self::pie($v['placa'], '03 – FOTOGRAFÍA');
    }

    private static function paginaInfoVehiculo(array $v): string
    {
        $unidad = $v['unidad_nombre'] ?? '—';
        $dest   = isset($v['destacamento_nombre']) && $v['destacamento_nombre']
            ? $v['destacamento_nombre'] . ', ' . ($v['destacamento_depto'] ?? '') : '—';

        $estadoBadge = match ($v['estado'] ?? '') {
            'Alta'   => '<span class="badge-alta">ALTA – OPERATIVO</span>',
            'Baja'   => '<span class="badge-baja">BAJA – FUERA DE SERVICIO</span>',
            'Taller' => '<span class="badge-taller">TALLER – EN REPARACIÓN</span>',
            default  => htmlspecialchars($v['estado'] ?? '')
        };

        return '
        ' . self::encabezado('04', 'INFORMACIÓN DEL VEHÍCULO') . '
        <div class="titulo-sub">Datos de Identificación</div>
        <table class="tabla-datos">
            <tr><td class="td-label">Catálogo / Placa</td><td class="td-valor">' . htmlspecialchars($v['placa']) . '</td><td class="td-label">Número de Serie</td><td class="td-valor">' . htmlspecialchars($v['numero_serie']) . '</td></tr>
            <tr><td class="td-label">Marca</td><td class="td-valor">' . htmlspecialchars($v['marca']) . '</td><td class="td-label">Modelo</td><td class="td-valor">' . htmlspecialchars($v['modelo']) . '</td></tr>
            <tr><td class="td-label">Año</td><td class="td-valor">' . htmlspecialchars($v['anio']) . '</td><td class="td-label">Color</td><td class="td-valor">' . htmlspecialchars($v['color']) . '</td></tr>
            <tr><td class="td-label">Tipo de Vehículo</td><td class="td-valor">' . htmlspecialchars($v['tipo']) . '</td><td class="td-label">Estado Operacional</td><td class="td-valor">' . $estadoBadge . '</td></tr>
        </table>
        <div class="titulo-sub">Datos Técnicos</div>
        <table class="tabla-datos">
            <tr><td class="td-label">Combustible</td><td class="td-valor">—</td><td class="td-label">Kilometraje Actual</td><td class="td-valor">' . number_format((int)$v['km_actuales']) . ' km</td></tr>
            <tr><td class="td-label">Fecha de Ingreso</td><td class="td-valor">' . htmlspecialchars($v['fecha_ingreso']) . '</td><td class="td-label">N° Motor</td><td class="td-valor">—</td></tr>
        </table>
        <div class="titulo-sub">Asignación</div>
        <table class="tabla-datos">
            <tr><td class="td-label">Unidad</td><td class="td-valor" colspan="3">' . htmlspecialchars($unidad) . '</td></tr>
            <tr><td class="td-label">Destacamento</td><td class="td-valor" colspan="3">' . htmlspecialchars($dest) . '</td></tr>
        </table>
        <div class="titulo-sub">Observaciones</div>
        <div style="border:0.5pt solid #ddd;padding:10px;min-height:50px;font-size:9.5pt;">
            ' . (empty($v['observaciones']) ? '<span style="color:#aaa;">Sin observaciones registradas.</span>' : htmlspecialchars($v['observaciones'])) . '
        </div>
        ' . self::pie($v['placa'], '04 – INFORMACIÓN');
    }

    private static function paginaHistorialServicios(array $v, array $servicios): string
    {
        if (empty($servicios)) {
            $tabla = '<div class="caja-vacia">No hay servicios registrados para este vehículo.</div>';
        } else {
            $filas = '';
            foreach ($servicios as $s) {
                $kmP = $s['km_proximo_servicio'] ? number_format((int)$s['km_proximo_servicio']) . ' km' : '—';
                $filas .= '<tr><td>' . date('d/m/Y', strtotime($s['fecha_realizado'])) . '</td><td>' . htmlspecialchars($s['tipo_nombre']) . '</td><td>' . number_format((int)$s['km_al_servicio']) . ' km</td><td>' . $kmP . '</td><td>' . htmlspecialchars($s['responsable'] ?? '—') . '</td><td>' . htmlspecialchars($s['observaciones'] ?? '—') . '</td></tr>';
            }
            $tabla = '<table class="tabla-control"><thead><tr><th>Fecha</th><th>Servicio Realizado</th><th>Kilometraje</th><th>Próximo Servicio</th><th>Responsable</th><th>Observaciones</th></tr></thead><tbody>' . $filas . '</tbody></table>';
        }

        $vacias = '<table class="tabla-control"><thead><tr><th>Fecha</th><th>Servicio Realizado</th><th>Kilometraje</th><th>Próximo Servicio</th><th>Responsable</th><th>Observaciones</th></tr></thead><tbody>';
        for ($i = 0; $i < 8; $i++) $vacias .= '<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td></tr>';
        $vacias .= '</tbody></table>';

        return '
        ' . self::encabezado('08', 'HISTORIAL DE SERVICIOS') . '
        <table class="tabla-datos" style="margin-bottom:12px;">
            <tr><td class="td-label">Tipo de Vehículo</td><td class="td-valor">' . htmlspecialchars($v['tipo']) . '</td><td class="td-label">Catálogo</td><td class="td-valor">' . htmlspecialchars($v['placa']) . '</td></tr>
            <tr><td class="td-label">Chasis N°</td><td class="td-valor">' . htmlspecialchars($v['numero_serie']) . '</td><td class="td-label">Combustible</td><td class="td-valor">—</td></tr>
            <tr><td class="td-label">Color</td><td class="td-valor">' . htmlspecialchars($v['color']) . '</td><td class="td-label">KM Actuales</td><td class="td-valor">' . number_format((int)$v['km_actuales']) . ' km</td></tr>
        </table>
        <div class="titulo-sub">Servicios Registrados</div>' . $tabla . '
        <div class="titulo-sub" style="margin-top:14px;">Espacios para Registro Manual</div>' . $vacias . '
        ' . self::pie($v['placa'], '08 – HISTORIAL DE SERVICIOS');
    }

    private static function paginaHistorialFiltrado(array $v, array $servicios, string $titulo, string $tipo): string
    {
        if (empty($servicios)) {
            $tabla = '<div class="caja-vacia">No hay registros de ' . strtolower($tipo) . ' para este vehículo.</div>';
        } else {
            $filas = '';
            foreach ($servicios as $s) {
                $kmP = $s['km_proximo_servicio'] ? number_format((int)$s['km_proximo_servicio']) . ' km' : '—';
                $filas .= '<tr><td>' . date('d/m/Y', strtotime($s['fecha_realizado'])) . '</td><td>' . number_format((int)$s['km_al_servicio']) . ' km</td><td>' . $kmP . '</td><td>' . htmlspecialchars($s['responsable'] ?? '—') . '</td><td>' . htmlspecialchars($s['observaciones'] ?? '—') . '</td></tr>';
            }
            $tabla = '<table class="tabla-control"><thead><tr><th>Fecha</th><th>KM al Cambio</th><th>Próximo Cambio</th><th>Responsable</th><th>Observaciones</th></tr></thead><tbody>' . $filas . '</tbody></table>';
        }

        $vacias = '<table class="tabla-control"><thead><tr><th>Fecha</th><th>KM al Cambio</th><th>Próximo Cambio</th><th>Responsable</th><th>Observaciones</th></tr></thead><tbody>';
        for ($i = 0; $i < 10; $i++) $vacias .= '<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>';
        $vacias .= '</tbody></table>';

        return '
        ' . self::encabezado('', $titulo) . '
        <table class="tabla-datos" style="margin-bottom:12px;">
            <tr><td class="td-label">Catálogo</td><td class="td-valor">' . htmlspecialchars($v['placa']) . '</td><td class="td-label">Marca / Modelo</td><td class="td-valor">' . htmlspecialchars($v['marca'] . ' ' . $v['modelo']) . '</td><td class="td-label">KM Actuales</td><td class="td-valor">' . number_format((int)$v['km_actuales']) . ' km</td></tr>
        </table>
        <div class="titulo-sub">Registros encontrados</div>' . $tabla . '
        <div class="titulo-sub" style="margin-top:14px;">Espacios para Registro Manual</div>' . $vacias . '
        ' . self::pie($v['placa'], $titulo);
    }

    private static function paginaReparaciones(array $v, array $reparaciones): string
    {
        if (empty($reparaciones)) {
            $tabla = '<div class="caja-vacia">No hay reparaciones registradas para este vehículo.</div>';
        } else {
            $filas = '';
            foreach ($reparaciones as $r) {
                $estadoClass = $r['estado'] === 'En proceso' ? 'badge-taller' : 'badge-alta';
                $filas .= '<tr><td>' . date('d/m/Y', strtotime($r['fecha_inicio'])) . '</td><td>' . ($r['fecha_fin'] ? date('d/m/Y', strtotime($r['fecha_fin'])) : '—') . '</td><td>' . htmlspecialchars($r['tipo_nombre']) . '</td><td>' . htmlspecialchars($r['descripcion']) . '</td><td>' . number_format((int)$r['km_al_momento']) . ' km</td><td>' . ($r['costo'] ? 'Q ' . number_format((float)$r['costo'], 2) : '—') . '</td><td><span class="' . $estadoClass . '">' . htmlspecialchars($r['estado']) . '</span></td></tr>';
            }
            $tabla = '<table class="tabla-control"><thead><tr><th>F. Inicio</th><th>F. Fin</th><th>Tipo</th><th>Descripción</th><th>KM</th><th>Costo</th><th>Estado</th></tr></thead><tbody>' . $filas . '</tbody></table>';
        }

        $vacias = '<table class="tabla-control"><thead><tr><th>F. Inicio</th><th>F. Fin</th><th>Tipo</th><th>Descripción</th><th>KM</th><th>Costo</th><th>Estado</th></tr></thead><tbody>';
        for ($i = 0; $i < 6; $i++) $vacias .= '<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
        $vacias .= '</tbody></table>';

        return '
        ' . self::encabezado('11', 'REPARACIONES') . '
        <table class="tabla-datos" style="margin-bottom:12px;">
            <tr><td class="td-label">Catálogo</td><td class="td-valor">' . htmlspecialchars($v['placa']) . '</td><td class="td-label">KM Actuales</td><td class="td-valor">' . number_format((int)$v['km_actuales']) . ' km</td></tr>
        </table>
        <div class="titulo-sub">Reparaciones Registradas</div>' . $tabla . '
        <div class="titulo-sub" style="margin-top:14px;">Espacios para Registro Manual</div>' . $vacias . '
        ' . self::pie($v['placa'], '11 – REPARACIONES');
    }

    private static function paginaHojaChequeo(array $v, ?array $chequeo = null): string
    {
        $items = [
            1 => 'Tren delantero',
            2 => 'Tapicería',
            3 => 'Carrocería',
            4 => 'Pintura en general',
            5 => 'Siglas que identifican a los vehículos pintados en color naranja fluorescente y en el lugar correspondiente',
            6 => 'Lona del camión',
            7 => 'Luces y pide vías',
            8 => 'Sistema eléctrico',
            9 => 'Herramienta extra para reparación de vehículos',
            10 => 'Herramienta básica (Tricket, llave de chuchos, palanca o tubo, trozo, cable o cadena, señalizaciones etc.)',
            11 => 'Herramienta de emergencia (llave de ½, Nos. 12, 13, 14, alicate, llave ajustable, juego de desatornilladores)',
            12 => 'Repuestos necesarios de emergencias',
            13 => 'Neumático de repuesto',
            14 => 'Acumulador o batería',
            15 => 'Neumáticos',
            16 => 'Lubricante',
            17 => 'Odómetro',
        ];

        $resultados = [];
        if ($chequeo && !empty($chequeo['items'])) {
            foreach ($chequeo['items'] as $item) {
                $resultados[(int)($item['numero_item'] ?? 0)] = [
                    'resultado'   => $item['resultado']   ?? '',
                    'observacion' => $item['observacion'] ?? '',
                ];
            }
        }

        $colores = ['BE' => '#2e7d32', 'ME' => '#e05252', 'MEI' => '#e8b84b', 'NT' => '#7c8398'];
        $filas = '';
        foreach ($items as $num => $desc) {
            $res = $resultados[$num]['resultado']   ?? '';
            $obs = $resultados[$num]['observacion'] ?? '';
            $celdas = '';
            foreach (['BE', 'ME', 'MEI', 'NT'] as $op) {
                $color = $colores[$op];
                if ($res === $op) {
                    $circulo = '<svg width="14" height="14" xmlns="http://www.w3.org/2000/svg"><circle cx="7" cy="7" r="6" fill="' . $color . '" stroke="' . $color . '" stroke-width="1"/></svg>';
                } else {
                    $circulo = '<svg width="14" height="14" xmlns="http://www.w3.org/2000/svg"><circle cx="7" cy="7" r="6" fill="none" stroke="#cccccc" stroke-width="1.5"/></svg>';
                }
                $celdas .= '<td style="text-align:center;padding:3px 4px;width:40px;">' . $circulo . '</td>';
            }
            $bg = ($num % 2 === 0) ? 'background:#fafafa;' : 'background:#ffffff;';
            $filas .= '<tr style="' . $bg . 'border-bottom:0.5pt solid #e0e0e0;"><td style="text-align:center;font-weight:bold;color:#999;font-size:7.5pt;padding:4px 5px;width:28px;">' . str_pad($num, 2, '0', STR_PAD_LEFT) . '</td><td style="text-align:left;font-size:8pt;padding:4px 6px;">' . htmlspecialchars($desc) . '</td>' . $celdas . '<td style="font-size:7.5pt;color:#555;padding:4px 6px;">' . htmlspecialchars($obs) . '</td></tr>';
        }

        $fecha       = $chequeo ? $chequeo['fecha_chequeo']  : '___/___/______';
        $km          = $chequeo ? number_format((int)$chequeo['km_al_chequeo']) . ' km' : '_______________';
        $responsable = $chequeo ? ($chequeo['realizado_por'] ?: '—') : '_____________________________';
        $obsGen      = $chequeo ? ($chequeo['observaciones_gen'] ?? '') : '';
        $notaChequeo = $chequeo
            ? '<div style="font-size:7.5pt;color:#888;margin-bottom:8px;">Chequeo del <strong>' . $fecha . '</strong> registrado en el sistema.</div>'
            : '<div style="font-size:7.5pt;color:#e05252;margin-bottom:8px;">⚠ No hay chequeo completado registrado.</div>';
        $estadoBadge = $chequeo
            ? '<span style="background:#e6f4ea;color:#2e7d32;border:0.5pt solid #2e7d32;padding:1px 6px;border-radius:3px;font-size:7pt;font-weight:bold;">✓ COMPLETADO</span>'
            : '';

        return '
        ' . self::encabezado('16', 'HOJA INDIVIDUAL DE CHEQUEO DE VEHÍCULOS') . '
        ' . $notaChequeo . '
        <table width="100%" style="border-collapse:collapse;margin-bottom:10px;border:0.5pt solid #ddd;">
            <tr>
                <td style="background:#f5f5f5;font-size:7.5pt;font-weight:bold;color:#888;text-transform:uppercase;padding:3px 8px;width:18%;">Catálogo / Placa</td>
                <td style="font-size:10pt;font-weight:bold;padding:3px 8px;width:12%;">' . htmlspecialchars($v['placa']) . '</td>
                <td style="background:#f5f5f5;font-size:7.5pt;font-weight:bold;color:#888;text-transform:uppercase;padding:3px 8px;width:16%;">Marca / Modelo</td>
                <td style="font-size:10pt;font-weight:bold;padding:3px 8px;width:22%;">' . htmlspecialchars($v['marca'] . ' ' . $v['modelo']) . '</td>
                <td style="background:#f5f5f5;font-size:7.5pt;font-weight:bold;color:#888;text-transform:uppercase;padding:3px 8px;width:14%;">Fecha</td>
                <td style="font-size:9pt;font-weight:bold;padding:3px 8px;width:18%;">' . $fecha . ' ' . $estadoBadge . '</td>
            </tr>
            <tr>
                <td style="background:#f5f5f5;font-size:7.5pt;font-weight:bold;color:#888;text-transform:uppercase;padding:3px 8px;">KM al momento</td>
                <td style="font-size:9pt;font-weight:bold;padding:3px 8px;">' . $km . '</td>
                <td style="background:#f5f5f5;font-size:7.5pt;font-weight:bold;color:#888;text-transform:uppercase;padding:3px 8px;">Realizado por</td>
                <td colspan="3" style="font-size:9pt;font-weight:bold;padding:3px 8px;">' . htmlspecialchars($responsable) . '</td>
            </tr>
        </table>
        <table width="100%" style="border-collapse:collapse;font-size:8.5pt;">
            <thead>
                <tr style="background:#1a1a1a;color:#ffffff;">
                    <th style="padding:5px;text-align:center;width:28px;font-size:7.5pt;">No.</th>
                    <th style="padding:5px;text-align:left;font-size:7.5pt;">Descripción del Ítem</th>
                    <th style="padding:5px;text-align:center;width:40px;color:#4caf7d;font-size:7.5pt;">BE</th>
                    <th style="padding:5px;text-align:center;width:40px;color:#e05252;font-size:7.5pt;">ME</th>
                    <th style="padding:5px;text-align:center;width:40px;color:#e8b84b;font-size:7.5pt;">MEI</th>
                    <th style="padding:5px;text-align:center;width:40px;color:#aaaaaa;font-size:7.5pt;">NT</th>
                    <th style="padding:5px;text-align:left;font-size:7.5pt;">Observación</th>
                </tr>
            </thead>
            <tbody>' . $filas . '</tbody>
        </table>
        <div style="margin-top:5px;font-size:7pt;color:#888;">
            <strong>BE</strong> = Buen Estado &nbsp;·&nbsp;
            <strong>ME</strong> = Mal Estado &nbsp;·&nbsp;
            <strong>MEI</strong> = Mal Estado Irreparable &nbsp;·&nbsp;
            <strong>NT</strong> = No Tiene
        </div>
        ' . (!empty($obsGen) ? '<div style="margin-top:8px;border:0.5pt solid #ddd;padding:6px 8px;font-size:8pt;"><strong>Observaciones:</strong> ' . htmlspecialchars($obsGen) . '</div>' : '') . '
        <div style="margin-top:14px;">
            <table width="100%" style="border-collapse:collapse;">
                <tr>
                    <td style="text-align:center;padding:8px 16px;"><div style="border-top:0.5pt solid #333;margin:0 20px;padding-top:4px;font-size:8pt;color:#555;">FIRMA DEL RESPONSABLE</div></td>
                    <td style="text-align:center;padding:8px 16px;"><div style="border-top:0.5pt solid #333;margin:0 20px;padding-top:4px;font-size:8pt;color:#555;">FIRMA DEL OFICIAL DE TURNO</div></td>
                    <td style="text-align:center;padding:8px 16px;"><div style="border-top:0.5pt solid #333;margin:0 20px;padding-top:4px;font-size:8pt;color:#555;">SELLO UNIDAD</div></td>
                </tr>
            </table>
        </div>
        ' . self::pie($v['placa'], '16 – HOJA DE CHEQUEO');
    }
}
