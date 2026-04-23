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
    public static function generarPDF(Router $router): void
    {
        $placa = strtoupper(trim($_GET['placa'] ?? ''));

        if (!$placa) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Placa requerida']);
            return;
        }

        ini_set('memory_limit', '256M');

        try {
            $vehiculo     = Vehiculos::traerConDetalle($placa);
            $servicios    = Servicios::traerPorPlaca($placa);
            $reparaciones = Reparaciones::traerPorPlaca($placa);

            if (!$vehiculo) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Vehículo no encontrado']);
                return;
            }

            array_walk($vehiculo, function (&$val) {
                if (is_array($val)) $val = implode(', ', $val);
                $val = (string)($val ?? '');
            });

            $fotoBase64  = !empty($vehiculo['foto_frente'])
                ? self::obtenerFotoBase64($vehiculo['foto_frente']) : '';
            $fotoLateral = !empty($vehiculo['foto_lateral'])
                ? self::obtenerFotoBase64($vehiculo['foto_lateral']) : '';
            $fotoTrasera = !empty($vehiculo['foto_trasera'])
                ? self::obtenerFotoBase64($vehiculo['foto_trasera']) : '';

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

            $chequeo = \Model\Chequeos::traerUltimoCompletado($placa);

            $tmpTarjeta    = self::descargarPdfTmp($vehiculo['tarjeta_pdf']     ?? '');
            $tmpInventario = self::descargarPdfTmp($vehiculo['cert_inventario'] ?? '');
            $tmpSicoin     = self::descargarPdfTmp($vehiculo['cert_sicoin']     ?? '');

            $mpdf = new Mpdf([
                'mode'              => 'utf-8',
                'format'            => 'Letter',
                'margin_top'        => 14,
                'margin_bottom'     => 14,
                'margin_left'       => 16,
                'margin_right'      => 16,
                'margin_header'     => 8,
                'margin_footer'     => 8,
                'default_font_size' => 10,
                'default_font'      => 'dejavusans',
                'img_dpi'           => 96,
                'tempDir'           => sys_get_temp_dir(),
            ]);

            $mpdf->SetTitle("Expediente Vehículo {$placa}");
            $mpdf->SetAuthor('Brigada Humanitaria y de Rescate – Ejército de Guatemala');
            $mpdf->WriteHTML(self::estilosGlobales(), \Mpdf\HTMLParserMode::HEADER_CSS);

            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaCaratula($vehiculo, $fotoBase64));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaIndice($vehiculo));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('FOTOGRAFÍA DEL VEHÍCULO'));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaFoto($vehiculo, $fotoBase64, $fotoLateral, $fotoTrasera));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('INFORMACIÓN DEL VEHÍCULO'));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaInfoVehiculo($vehiculo));

            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('COPIA DE TARJETA DE CIRCULACIÓN'));
            if (!$tmpTarjeta) {
                $mpdf->AddPage();
                $mpdf->WriteHTML(self::paginaSinPDF($vehiculo, '05', 'COPIA DE TARJETA DE CIRCULACIÓN', 'tarjeta de circulación'));
            }

            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('CERTIFICACIÓN INVENTARIO'));
            if (!$tmpInventario) {
                $mpdf->AddPage();
                $mpdf->WriteHTML(self::paginaSinPDF($vehiculo, '06', 'CERTIFICACIÓN INVENTARIO', 'certificación de inventario'));
            }

            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('CERTIFICACIÓN SICOIN WEB'));
            if (!$tmpSicoin) {
                $mpdf->AddPage();
                $mpdf->WriteHTML(self::paginaSinPDF($vehiculo, '07', 'CERTIFICACIÓN SICOIN WEB', 'certificación SICOIN'));
            }

            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('HISTORIAL DE SERVICIOS'));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaHistorialServicios($vehiculo, $servicios));

            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('HISTORIAL DE CAMBIO DE LLANTAS'));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaHistorialFiltrado($vehiculo, $cambiosLlantas, 'HISTORIAL DE CAMBIO DE LLANTAS', 'llantas'));

            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('HISTORIAL DE CAMBIO DE ACUMULADOR'));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaHistorialFiltrado($vehiculo, $cambiosAcumulador, 'HISTORIAL DE CAMBIO DE ACUMULADOR', 'acumulador'));

            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('REPARACIONES'));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaReparaciones($vehiculo, $reparaciones));

            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('HOJA INDIVIDUAL DE CHEQUEO DE VEHÍCULOS'));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaHojaChequeo($vehiculo, $chequeo));

            $tmpMpdf = tempnam(sys_get_temp_dir(), 'exp_') . '.pdf';
            $mpdf->Output($tmpMpdf, \Mpdf\Output\Destination::FILE);

            $nombreArchivo = "Expediente_{$placa}_" . date('Ymd') . '.pdf';
            self::mergePDF($tmpMpdf, $tmpTarjeta, $tmpInventario, $tmpSicoin, $nombreArchivo);

            @unlink($tmpMpdf);
            if ($tmpTarjeta)    @unlink($tmpTarjeta);
            if ($tmpInventario) @unlink($tmpInventario);
            if ($tmpSicoin)     @unlink($tmpSicoin);

            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al generar expediente', 'detalle' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── MERGE ─────────────────────────────────────────────────────────────────
    private static function mergePDF(string $archivoPrincipal, ?string $tmpTarjeta, ?string $tmpInventario, ?string $tmpSicoin, string $nombreSalida): void
    {
        $fpdi = new \setasign\Fpdi\Fpdi();

        $importarPdf = function (string $archivo) use ($fpdi) {
            try {
                $total = $fpdi->setSourceFile($archivo);
                for ($i = 1; $i <= $total; $i++) {
                    $tpl  = $fpdi->importPage($i);
                    $size = $fpdi->getTemplateSize($tpl);
                    $fpdi->AddPage(($size['width'] > $size['height']) ? 'L' : 'P', [$size['width'], $size['height']]);
                    $fpdi->useTemplate($tpl, 0, 0, $size['width'], $size['height']);
                }
            } catch (\Throwable $e) {
            }
        };

        $totalPrincipal      = $fpdi->setSourceFile($archivoPrincipal);
        $paginaSepTarjeta    = 7;
        $paginaRefTarjeta    = $paginaSepTarjeta + 1;
        $paginaSepInventario = $paginaSepTarjeta + 1 + ($tmpTarjeta    ? 0 : 1);
        $paginaRefInventario = $paginaSepInventario + 1;
        $paginaSepSicoin     = $paginaSepInventario + 1 + ($tmpInventario ? 0 : 1);
        $paginaRefSicoin     = $paginaSepSicoin + 1;
        $paginaResto         = $paginaSepSicoin + 1 + ($tmpSicoin     ? 0 : 1);

        $fpdi->setSourceFile($archivoPrincipal);
        for ($i = 1; $i <= $paginaSepTarjeta; $i++) {
            $tpl = $fpdi->importPage($i);
            $size = $fpdi->getTemplateSize($tpl);
            $fpdi->AddPage('P', [$size['width'], $size['height']]);
            $fpdi->useTemplate($tpl, 0, 0, $size['width'], $size['height']);
        }

        if ($tmpTarjeta) {
            $importarPdf($tmpTarjeta);
        } else {
            $fpdi->setSourceFile($archivoPrincipal);
            $tpl = $fpdi->importPage($paginaRefTarjeta);
            $size = $fpdi->getTemplateSize($tpl);
            $fpdi->AddPage('P', [$size['width'], $size['height']]);
            $fpdi->useTemplate($tpl, 0, 0, $size['width'], $size['height']);
        }

        $fpdi->setSourceFile($archivoPrincipal);
        $tpl = $fpdi->importPage($paginaSepInventario);
        $size = $fpdi->getTemplateSize($tpl);
        $fpdi->AddPage('P', [$size['width'], $size['height']]);
        $fpdi->useTemplate($tpl, 0, 0, $size['width'], $size['height']);

        if ($tmpInventario) {
            $importarPdf($tmpInventario);
        } else {
            $fpdi->setSourceFile($archivoPrincipal);
            $tpl = $fpdi->importPage($paginaRefInventario);
            $size = $fpdi->getTemplateSize($tpl);
            $fpdi->AddPage('P', [$size['width'], $size['height']]);
            $fpdi->useTemplate($tpl, 0, 0, $size['width'], $size['height']);
        }

        $fpdi->setSourceFile($archivoPrincipal);
        $tpl = $fpdi->importPage($paginaSepSicoin);
        $size = $fpdi->getTemplateSize($tpl);
        $fpdi->AddPage('P', [$size['width'], $size['height']]);
        $fpdi->useTemplate($tpl, 0, 0, $size['width'], $size['height']);

        if ($tmpSicoin) {
            $importarPdf($tmpSicoin);
        } else {
            $fpdi->setSourceFile($archivoPrincipal);
            $tpl = $fpdi->importPage($paginaRefSicoin);
            $size = $fpdi->getTemplateSize($tpl);
            $fpdi->AddPage('P', [$size['width'], $size['height']]);
            $fpdi->useTemplate($tpl, 0, 0, $size['width'], $size['height']);
        }

        $fpdi->setSourceFile($archivoPrincipal);
        for ($i = $paginaResto; $i <= $totalPrincipal; $i++) {
            $tpl = $fpdi->importPage($i);
            $size = $fpdi->getTemplateSize($tpl);
            $fpdi->AddPage('P', [$size['width'], $size['height']]);
            $fpdi->useTemplate($tpl, 0, 0, $size['width'], $size['height']);
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $nombreSalida . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        $fpdi->Output($nombreSalida, 'I');
    }

    // ── HELPERS ───────────────────────────────────────────────────────────────
    private static function descargarPdfTmp(string $nombreArchivo): ?string
    {
        if (empty($nombreArchivo)) return null;
        try {
            $url       = 'http://localhost/bhr_functions/API/vehiculos/foto?archivo=/' . urlencode($nombreArchivo);
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

    private static function obtenerFotoBase64(string $nombreArchivo): string
    {
        if (empty($nombreArchivo)) return '';
        $nombreLimpio = ltrim($nombreArchivo, '/');
        return 'http://localhost/bhr_functions/API/vehiculos/foto?archivo=/' . $nombreLimpio;
    }

    private static function logoBase64(): string
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/bhr_functions/public/images/triangule.png';
        if (!file_exists($path)) return '';
        $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }

    // ── ENCABEZADO ────────────────────────────────────────────────────────────
    private static function encabezado(string $titulo = ''): string
    {
        return '
        <table width="100%" style="border-collapse:collapse;margin-bottom:0;">
            <tr>
                <td style="padding:5px 0;border-bottom:3pt solid #C75B00;">
                    <div style="font-size:7.5pt;color:#888;font-weight:bold;text-transform:uppercase;letter-spacing:.5pt;">
                        República de Guatemala &nbsp;·&nbsp; Ejército de Guatemala
                    </div>
                </td>
                <td style="padding:5px 0;border-bottom:3pt solid #C75B00;text-align:right;">
                    <div style="font-size:8pt;color:#C75B00;font-weight:bold;text-transform:uppercase;letter-spacing:.5pt;">
                        Brigada Humanitaria y de Rescate
                    </div>
                </td>
            </tr>
        </table>
        ' . ($titulo ? '
        <div style="margin:10px 0 12px 0;padding:7px 14px;background:#C75B00;border-radius:2pt;">
            <div style="font-size:11pt;font-weight:bold;color:#ffffff;text-transform:uppercase;letter-spacing:2pt;text-align:center;">
                ' . $titulo . '
            </div>
        </div>' : '<div style="margin-bottom:10px;"></div>');
    }

    // ── PIE ───────────────────────────────────────────────────────────────────
    private static function pie(string $placa, string $seccion): string
    {
        return '
        <div class="pie-pagina">
            Vehículo: <strong>' . $placa . '</strong>
            &nbsp;·&nbsp; Sección: <strong>' . $seccion . '</strong>
            &nbsp;·&nbsp; ' . date('d/m/Y H:i') . '
            &nbsp;·&nbsp; MDN-BHR-SAGE
        </div>';
    }

    // ── SEPARADOR ─────────────────────────────────────────────────────────────
    private static function separador(string $titulo): string
    {
        $logo    = self::logoBase64();
        $logoTag = $logo
            ? '<img src="' . $logo . '" style="width:300px;height:auto;display:block;margin:0 auto;">'
            : '';

        return '
        <div style="width:100%;height:680px;background:#ffffff;
            display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;">
            <div style="border-top:3pt solid #C75B00;border-bottom:3pt solid #C75B00;
                padding:14px 50px;margin:0 40px 30px 40px;">
                <div style="font-size:20pt;font-weight:bold;color:#1a1a1a;
                    text-transform:uppercase;letter-spacing:3pt;line-height:1.3;">
                    ' . $titulo . '
                </div>
            </div>
            ' . $logoTag . '
            <div style="margin-top:24px;font-size:9pt;color:#777;letter-spacing:1.5pt;line-height:1.8;">
                BRIGADA HUMANITARIA Y DE RESCATE<br>EJÉRCITO DE GUATEMALA
            </div>
            <div style="margin-top:16px;font-size:7.5pt;color:#bbb;letter-spacing:2pt;">MDN · BHR · SAGE</div>
        </div>';
    }

    // ── FICHA IDENTIFICACIÓN (reutilizable) ───────────────────────────────────
    private static function fichaIdentificacion(array $v): string
    {
        return '
        <table width="100%" style="border-collapse:collapse;margin-bottom:14px;">
            <tr>
                <td style="width:20%;background:#C75B00;padding:5px 8px;border:1pt solid #a34800;">
                    <div style="font-size:7pt;color:#ffe0b2;text-transform:uppercase;letter-spacing:.5pt;">Catálogo</div>
                    <div style="font-size:12pt;font-weight:bold;color:#ffffff;">' . htmlspecialchars($v['placa']) . '</div>
                </td>
                <td style="width:28%;background:#fff8f2;padding:5px 8px;border:1pt solid #e8c89a;">
                    <div style="font-size:7pt;color:#C75B00;text-transform:uppercase;letter-spacing:.5pt;">Marca / Modelo</div>
                    <div style="font-size:10pt;font-weight:bold;color:#1a1a1a;">' . htmlspecialchars($v['marca'] . ' ' . $v['modelo']) . '</div>
                </td>
                <td style="width:14%;background:#fff8f2;padding:5px 8px;border:1pt solid #e8c89a;">
                    <div style="font-size:7pt;color:#C75B00;text-transform:uppercase;letter-spacing:.5pt;">Año</div>
                    <div style="font-size:10pt;font-weight:bold;color:#1a1a1a;">' . htmlspecialchars($v['anio']) . '</div>
                </td>
                <td style="width:18%;background:#fff8f2;padding:5px 8px;border:1pt solid #e8c89a;">
                    <div style="font-size:7pt;color:#C75B00;text-transform:uppercase;letter-spacing:.5pt;">Tipo</div>
                    <div style="font-size:10pt;font-weight:bold;color:#1a1a1a;">' . htmlspecialchars($v['tipo']) . '</div>
                </td>
                <td style="width:20%;background:#fff8f2;padding:5px 8px;border:1pt solid #e8c89a;">
                    <div style="font-size:7pt;color:#C75B00;text-transform:uppercase;letter-spacing:.5pt;">KM Actuales</div>
                    <div style="font-size:10pt;font-weight:bold;color:#1a1a1a;">' . number_format((int)$v['km_actuales']) . ' km</div>
                </td>
            </tr>
        </table>';
    }

    // ── PÁGINA SIN PDF ────────────────────────────────────────────────────────
    private static function paginaSinPDF(array $v, string $seccion, string $titulo, string $nombreDoc): string
    {
        return '
        ' . self::encabezado($titulo) . '
        ' . self::fichaIdentificacion($v) . '
        <div style="border:1pt dashed #C75B00;border-radius:4pt;padding:50px 30px;
            text-align:center;margin:20px 0;background:#fffaf5;">
            <div style="font-size:22pt;color:#C75B00;margin-bottom:10px;">📄</div>
            <div style="font-size:10pt;color:#888;margin-bottom:6px;">
                No se ha digitalizado la <strong>' . $nombreDoc . '</strong>.
            </div>
            <div style="font-size:9pt;color:#aaa;">Adjuntar copia física en esta sección.</div>
        </div>
        <table width="100%" style="border-collapse:collapse;margin-top:40px;">
            <tr>
                <td style="text-align:center;padding:8px 16px;">
                    <div style="border-top:1pt solid #C75B00;margin:0 20px;padding-top:5px;
                        font-size:7.5pt;color:#666;text-transform:uppercase;letter-spacing:.5pt;">
                        Firma y Sello del Responsable
                    </div>
                </td>
                <td style="text-align:center;padding:8px 16px;">
                    <div style="border-top:1pt solid #C75B00;margin:0 20px;padding-top:5px;
                        font-size:7.5pt;color:#666;text-transform:uppercase;letter-spacing:.5pt;">
                        Fecha de Verificación
                    </div>
                </td>
            </tr>
        </table>
        ' . self::pie($v['placa'], $seccion . ' – ' . $titulo);
    }

    // ── CSS GLOBAL ────────────────────────────────────────────────────────────
    private static function estilosGlobales(): string
    {
        return '
        <style>
            body { font-family: dejavusans, sans-serif; font-size: 10pt; color: #1a1a1a; margin: 0; padding: 0; }

            .titulo-sub {
                font-size: 9pt; font-weight: bold; color: #ffffff;
                background: #C75B00; text-transform: uppercase;
                letter-spacing: 1pt; padding: 4px 10px;
                margin: 12px 0 0 0; border-radius: 2pt;
            }

            .tabla-datos { width: 100%; border-collapse: collapse; font-size: 9.5pt; margin-bottom: 0; }
            .tabla-datos td { padding: 6px 10px; border: 1pt solid #e8c89a; vertical-align: middle; }
            .tabla-datos .td-label { background: #fff8f2; font-weight: bold; color: #C75B00; font-size: 7.5pt; text-transform: uppercase; letter-spacing: .5pt; width: 22%; }
            .tabla-datos .td-valor { font-weight: bold; color: #1a1a1a; font-size: 10pt; background: #ffffff; }

            .tabla-control { width: 100%; border-collapse: collapse; font-size: 8.5pt; margin-bottom: 0; }
            .tabla-control th { background: #C75B00; color: #ffffff; padding: 6px 8px; border: 1pt solid #a34800; text-align: center; font-size: 7.5pt; text-transform: uppercase; letter-spacing: .5pt; }
            .tabla-control td { padding: 5px 8px; border: 1pt solid #e8c89a; text-align: center; vertical-align: middle; }
            .tabla-control tr:nth-child(even) td { background: #fff8f2; }
            .tabla-control tr:nth-child(odd)  td { background: #ffffff; }

            .badge-alta   { background:#e6f4ea; color:#2e7d32; padding:2px 8px; border-radius:3px; font-size:8pt; font-weight:bold; border:0.5pt solid #2e7d32; }
            .badge-baja   { background:#fce8e8; color:#c62828; padding:2px 8px; border-radius:3px; font-size:8pt; font-weight:bold; border:0.5pt solid #c62828; }
            .badge-taller { background:#fff3e0; color:#e65100; padding:2px 8px; border-radius:3px; font-size:8pt; font-weight:bold; border:0.5pt solid #e65100; }

            .indice-tabla { width: 100%; border-collapse: collapse; font-size: 10pt; }
            .indice-tabla tr { border-bottom: 0.5pt solid #f0e0cc; }
            .indice-tabla tr:nth-child(even) td { background: #fff8f2; }
            .indice-num    { width: 44px; text-align: center; font-weight: bold; color: #ffffff; background: #C75B00; font-size: 11pt; padding: 7px 10px; }
            .indice-tabla tr:nth-child(even) .indice-num { background: #ffffff; color: #C75B00; border: 1pt solid #e8c89a; }
            .indice-titulo { font-weight: bold; color: #1a1a1a; padding: 7px 10px; }
            .indice-puntos { color: #ddd; text-align: right; font-size: 8pt; padding: 7px 10px; }

            .caja-vacia { border: 1pt dashed #C75B00; border-radius: 4pt; padding: 20px; text-align: center; color: #aaa; font-size: 9pt; margin: 10px 0; background: #fffaf5; }

            .pie-pagina { position: fixed; bottom: -10px; left: 0; right: 0; font-size: 6.5pt; color: #aaa; border-top: 0.5pt solid #f0e0cc; padding-top: 3px; text-align: center; background: #ffffff; }
        </style>';
    }

    // ── PÁGINAS ───────────────────────────────────────────────────────────────

    private static function paginaCaratula(array $v, string $foto): string
    {
        $fotoTag = !empty($foto)
            ? '<img src="' . $foto . '" style="max-width:440px;max-height:340px;border:1.5pt solid #e8c89a;display:block;margin:0 auto;border-radius:3pt;">'
            : '<div style="border:1.5pt dashed #e8c89a;padding:60px 40px;color:#ccc;font-size:10pt;text-align:center;background:#fffaf5;border-radius:4pt;">Sin fotografía registrada</div>';


        $estadoBadge = match ($v['estado'] ?? '') {
            'Alta'   => '<span class="badge-alta"   style="font-size:10pt;padding:5px 18px;">● OPERATIVO – ALTA</span>',
            'Baja'   => '<span class="badge-baja"   style="font-size:10pt;padding:5px 18px;">● FUERA DE SERVICIO – BAJA</span>',
            'Taller' => '<span class="badge-taller" style="font-size:10pt;padding:5px 18px;">● EN TALLER</span>',
            default  => ''
        };

        $unidad     = $v['unidad_nombre']       ?? '—';
        $dest       = $v['destacamento_nombre'] ?? '';
        $depto      = $v['destacamento_depto']  ?? '';
        $asignacion = $dest ? "{$unidad} · {$dest}, {$depto}" : $unidad;

        return '
        ' . self::encabezado() . '
        <div style="text-align:center;margin:8px 0 16px 0;">
            <div style="font-size:14pt;font-weight:bold;color:#C75B00;letter-spacing:3pt;text-transform:uppercase;">BRIGADA HUMANITARIA Y DE RESCATE</div>
            <div style="font-size:9pt;letter-spacing:2pt;color:#888;margin-top:3px;">EJÉRCITO DE GUATEMALA</div>
        </div>
        <div style="text-align:center;margin-bottom:16px;">' . $fotoTag . '</div>
        <div style="text-align:center;margin-bottom:14px;">
            <div style="font-size:8pt;color:#888;text-transform:uppercase;letter-spacing:2pt;margin-bottom:5px;">Expediente de Vehículo — Catálogo / Placa</div>
            <div style="display:inline-block;background:#1a1a1a;padding:10px 50px;border-radius:4pt;">
                <span style="font-size:34pt;font-weight:bold;letter-spacing:8pt;color:#e8b84b;">' . htmlspecialchars($v['placa']) . '</span>
            </div>
        </div>
        <div style="text-align:center;margin-bottom:16px;">' . $estadoBadge . '</div>
        <table width="100%" style="border-collapse:collapse;margin-bottom:10px;">
            <tr>
                <td style="padding:7px 10px;border:1pt solid #e8c89a;background:#fff8f2;width:22%;">
                    <div style="font-size:7pt;color:#C75B00;text-transform:uppercase;letter-spacing:.5pt;margin-bottom:2px;">Marca</div>
                    <div style="font-size:12pt;font-weight:bold;color:#1a1a1a;">' . htmlspecialchars($v['marca']) . '</div>
                </td>
                <td style="padding:7px 10px;border:1pt solid #e8c89a;background:#fff8f2;width:22%;">
                    <div style="font-size:7pt;color:#C75B00;text-transform:uppercase;letter-spacing:.5pt;margin-bottom:2px;">Modelo</div>
                    <div style="font-size:12pt;font-weight:bold;color:#1a1a1a;">' . htmlspecialchars($v['modelo']) . '</div>
                </td>
                <td style="padding:7px 10px;border:1pt solid #e8c89a;background:#fff8f2;width:14%;">
                    <div style="font-size:7pt;color:#C75B00;text-transform:uppercase;letter-spacing:.5pt;margin-bottom:2px;">Año</div>
                    <div style="font-size:12pt;font-weight:bold;color:#1a1a1a;">' . htmlspecialchars($v['anio']) . '</div>
                </td>
                <td style="padding:7px 10px;border:1pt solid #e8c89a;background:#fff8f2;width:18%;">
                    <div style="font-size:7pt;color:#C75B00;text-transform:uppercase;letter-spacing:.5pt;margin-bottom:2px;">Color</div>
                    <div style="font-size:12pt;font-weight:bold;color:#1a1a1a;">' . htmlspecialchars($v['color']) . '</div>
                </td>
                <td style="padding:7px 10px;border:1pt solid #e8c89a;background:#fff8f2;width:24%;">
                    <div style="font-size:7pt;color:#C75B00;text-transform:uppercase;letter-spacing:.5pt;margin-bottom:2px;">Tipo</div>
                    <div style="font-size:12pt;font-weight:bold;color:#1a1a1a;">' . htmlspecialchars($v['tipo']) . '</div>
                </td>
            </tr>
        </table>
        <table width="100%" style="border-collapse:collapse;margin-bottom:14px;">
            <tr>
                <td style="padding:7px 10px;border:1pt solid #e8c89a;background:#ffffff;width:35%;">
                    <div style="font-size:7pt;color:#C75B00;text-transform:uppercase;letter-spacing:.5pt;margin-bottom:2px;">Número de Serie / Chasis</div>
                    <div style="font-size:11pt;font-weight:bold;color:#1a1a1a;">' . htmlspecialchars($v['numero_serie']) . '</div>
                </td>
                <td style="padding:7px 10px;border:1pt solid #e8c89a;background:#ffffff;width:65%;">
                    <div style="font-size:7pt;color:#C75B00;text-transform:uppercase;letter-spacing:.5pt;margin-bottom:2px;">Unidad Asignada</div>
                    <div style="font-size:10pt;font-weight:bold;color:#1a1a1a;">' . htmlspecialchars($asignacion) . '</div>
                </td>
            </tr>
            <tr>
                <td style="padding:7px 10px;border:1pt solid #e8c89a;background:#ffffff;">
                    <div style="font-size:7pt;color:#C75B00;text-transform:uppercase;letter-spacing:.5pt;margin-bottom:2px;">Kilometraje Actual</div>
                    <div style="font-size:11pt;font-weight:bold;color:#1a1a1a;">' . number_format((int)$v['km_actuales']) . ' km</div>
                </td>
                <td style="padding:7px 10px;border:1pt solid #e8c89a;background:#ffffff;">
                    <div style="font-size:7pt;color:#C75B00;text-transform:uppercase;letter-spacing:.5pt;margin-bottom:2px;">Fecha de Ingreso</div>
                    <div style="font-size:11pt;font-weight:bold;color:#1a1a1a;">' . htmlspecialchars($v['fecha_ingreso']) . '</div>
                </td>
            </tr>
        </table>
        <div style="text-align:center;font-size:7.5pt;color:#bbb;border-top:0.5pt solid #f0e0cc;padding-top:6px;">
            Generado el ' . date('d \d\e F \d\e Y \a \l\a\s H:i') . ' &nbsp;·&nbsp; MDN-BHR-SAGE
        </div>
        ' . self::pie($v['placa'], '01 – CARÁTULA');
    }

    private static function paginaIndice(array $v): string
    {
        $secciones = [
            ['01', 'CARÁTULA'],
            ['02', 'ÍNDICE'],
            ['03', 'FOTOGRAFÍAS DEL VEHÍCULO'],
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
            $filas .= '
            <tr>
                <td class="indice-num">' . $s[0] . '</td>
                <td class="indice-titulo">' . $s[1] . '</td>
                <td class="indice-puntos">· · · · · · · · · · ·</td>
            </tr>';
        }

        return '
        ' . self::encabezado('ÍNDICE') . '
        <table class="indice-tabla">' . $filas . '</table>
        ' . self::pie($v['placa'], '02 – ÍNDICE');
    }

    private static function paginaFoto(array $v, string $foto, string $fotoLateral = '', string $fotoTrasera = ''): string
    {
        $fotoTag = !empty($foto)
            ? '<img src="' . $foto . '" style="max-width:520px;max-height:380px;border:2pt solid #e8c89a;border-radius:4pt;display:block;margin:0 auto;">'
            : '<div class="caja-vacia" style="padding:40px;">Sin fotografía frontal registrada</div>';

        $lateralTag = !empty($fotoLateral)
            ? '<img src="' . $fotoLateral . '" style="max-width:100%;max-height:200px;border:1.5pt solid #e8c89a;border-radius:4pt;display:block;margin:0 auto;">'
            : '<div class="caja-vacia" style="padding:20px;font-size:8pt;">Sin foto lateral registrada</div>';

        $traseraTag = !empty($fotoTrasera)
            ? '<img src="' . $fotoTrasera . '" style="max-width:100%;max-height:200px;border:1.5pt solid #e8c89a;border-radius:4pt;display:block;margin:0 auto;">'
            : '<div class="caja-vacia" style="padding:20px;font-size:8pt;">Sin foto trasera registrada</div>';

        $fotosExtra = '
    <table width="100%" style="border-collapse:collapse;margin-top:14px;">
        <tr>
            <td style="width:50%;text-align:center;padding:8px 6px;">
                ' . $lateralTag . '
                <div style="font-size:7.5pt;color:#C75B00;margin-top:6px;
                    text-transform:uppercase;letter-spacing:1pt;font-weight:bold;">
                    Vista Lateral
                </div>
            </td>
            <td style="width:50%;text-align:center;padding:8px 6px;">
                ' . $traseraTag . '
                <div style="font-size:7.5pt;color:#C75B00;margin-top:6px;
                    text-transform:uppercase;letter-spacing:1pt;font-weight:bold;">
                    Vista Trasera
                </div>
            </td>
        </tr>
    </table>';

        return '
    ' . self::encabezado('FOTOGRAFÍA DEL VEHÍCULO') . '
    ' . self::fichaIdentificacion($v) . '
    <div style="text-align:center;margin-top:14px;">
        ' . $fotoTag . '
        <div style="font-size:7.5pt;color:#C75B00;margin-top:8px;
            text-transform:uppercase;letter-spacing:1.5pt;font-weight:bold;">
            Vista Frontal &nbsp;·&nbsp; ' . htmlspecialchars($v['placa']) . '
        </div>
    </div>
    ' . $fotosExtra . '
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
        ' . self::encabezado('INFORMACIÓN DEL VEHÍCULO') . '
        <div class="titulo-sub">Datos de Identificación</div>
        <table class="tabla-datos">
            <tr>
                <td class="td-label">Catálogo / Placa</td><td class="td-valor">' . htmlspecialchars($v['placa']) . '</td>
                <td class="td-label">Número de Serie</td><td class="td-valor">' . htmlspecialchars($v['numero_serie']) . '</td>
            </tr>
            <tr>
                <td class="td-label">Marca</td><td class="td-valor">' . htmlspecialchars($v['marca']) . '</td>
                <td class="td-label">Modelo</td><td class="td-valor">' . htmlspecialchars($v['modelo']) . '</td>
            </tr>
            <tr>
                <td class="td-label">Año</td><td class="td-valor">' . htmlspecialchars($v['anio']) . '</td>
                <td class="td-label">Color</td><td class="td-valor">' . htmlspecialchars($v['color']) . '</td>
            </tr>
            <tr>
                <td class="td-label">Tipo de Vehículo</td><td class="td-valor">' . htmlspecialchars($v['tipo']) . '</td>
                <td class="td-label">Estado Operacional</td><td class="td-valor">' . $estadoBadge . '</td>
            </tr>
        </table>
        <div class="titulo-sub">Datos Técnicos</div>
        <table class="tabla-datos">
            <tr>
                <td class="td-label">Combustible</td><td class="td-valor">—</td>
                <td class="td-label">Kilometraje Actual</td><td class="td-valor">' . number_format((int)$v['km_actuales']) . ' km</td>
            </tr>
            <tr>
                <td class="td-label">Fecha de Ingreso</td><td class="td-valor">' . htmlspecialchars($v['fecha_ingreso']) . '</td>
                <td class="td-label">N° Motor</td><td class="td-valor">—</td>
            </tr>
        </table>
        <div class="titulo-sub">Asignación</div>
        <table class="tabla-datos">
            <tr><td class="td-label">Unidad</td><td class="td-valor" colspan="3">' . htmlspecialchars($unidad) . '</td></tr>
            <tr><td class="td-label">Destacamento</td><td class="td-valor" colspan="3">' . htmlspecialchars($dest) . '</td></tr>
        </table>
        <div class="titulo-sub">Observaciones</div>
        <div style="border:1pt solid #e8c89a;border-left:4pt solid #C75B00;padding:10px 12px;
            min-height:40px;font-size:9.5pt;background:#fffaf5;border-radius:0 3pt 3pt 0;">
            ' . (empty($v['observaciones']) ? '<span style="color:#bbb;">Sin observaciones registradas.</span>' : htmlspecialchars($v['observaciones'])) . '
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
                $filas .= '
                <tr>
                    <td>' . date('d/m/Y', strtotime($s['fecha_realizado'])) . '</td>
                    <td style="text-align:left;">' . htmlspecialchars($s['tipo_nombre']) . '</td>
                    <td>' . number_format((int)$s['km_al_servicio']) . ' km</td>
                    <td>' . $kmP . '</td>
                    <td style="text-align:left;">' . htmlspecialchars($s['responsable'] ?? '—') . '</td>
                    <td style="text-align:left;">' . htmlspecialchars($s['observaciones'] ?? '—') . '</td>
                </tr>';
            }
            $tabla = '<table class="tabla-control"><thead><tr>
                <th>Fecha</th><th>Servicio Realizado</th><th>Kilometraje</th>
                <th>Próximo KM</th><th>Responsable</th><th>Observaciones</th>
            </tr></thead><tbody>' . $filas . '</tbody></table>';
        }

        return '
        ' . self::encabezado('HISTORIAL DE SERVICIOS') . '
        ' . self::fichaIdentificacion($v) . '
        <div class="titulo-sub">Servicios Registrados</div>
        ' . $tabla . '
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
                $filas .= '
                <tr>
                    <td>' . date('d/m/Y', strtotime($s['fecha_realizado'])) . '</td>
                    <td>' . number_format((int)$s['km_al_servicio']) . ' km</td>
                    <td>' . $kmP . '</td>
                    <td style="text-align:left;">' . htmlspecialchars($s['responsable'] ?? '—') . '</td>
                    <td style="text-align:left;">' . htmlspecialchars($s['observaciones'] ?? '—') . '</td>
                </tr>';
            }
            $tabla = '<table class="tabla-control"><thead><tr>
                <th>Fecha</th><th>KM al Cambio</th><th>Próximo Cambio</th>
                <th>Responsable</th><th>Observaciones</th>
            </tr></thead><tbody>' . $filas . '</tbody></table>';
        }

        return '
        ' . self::encabezado($titulo) . '
        ' . self::fichaIdentificacion($v) . '
        <div class="titulo-sub">Registros Encontrados</div>
        ' . $tabla . '
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
                $filas .= '
                <tr>
                    <td>' . date('d/m/Y', strtotime($r['fecha_inicio'])) . '</td>
                    <td>' . ($r['fecha_fin'] ? date('d/m/Y', strtotime($r['fecha_fin'])) : '—') . '</td>
                    <td style="text-align:left;">' . htmlspecialchars($r['tipo_nombre']) . '</td>
                    <td style="text-align:left;">' . htmlspecialchars($r['descripcion']) . '</td>
                    <td>' . number_format((int)$r['km_al_momento']) . ' km</td>
                    <td>' . ($r['costo'] ? 'Q ' . number_format((float)$r['costo'], 2) : '—') . '</td>
                    <td><span class="' . $estadoClass . '">' . htmlspecialchars($r['estado']) . '</span></td>
                </tr>';
            }
            $tabla = '<table class="tabla-control"><thead><tr>
                <th>F. Inicio</th><th>F. Fin</th><th>Tipo</th>
                <th>Descripción</th><th>KM</th><th>Costo</th><th>Estado</th>
            </tr></thead><tbody>' . $filas . '</tbody></table>';
        }

        return '
        ' . self::encabezado('REPARACIONES') . '
        ' . self::fichaIdentificacion($v) . '
        <div class="titulo-sub">Reparaciones Registradas</div>
        ' . $tabla . '
        ' . self::pie($v['placa'], '11 – REPARACIONES');
    }

    private static function paginaHojaChequeo(array $v, ?array $chequeo = null): string
    {
        $tipoVehiculo = strtolower(trim($v['tipo'] ?? ''));

        // ── Definición con exclusiones por tipo ───────────────────────────────────
        $itemsDef = [
            1  => [
                'desc' => 'Tren delantero',
                'tipos' => null
            ],
            2  => [
                'desc' => 'Tapicería',
                'tipos' => ['automóvil', 'pickup', 'camión', 'microbús', 'blindado', 'camioneta', 'otro']
            ],
            3  => [
                'desc' => 'Carrocería',
                'tipos' => ['automóvil', 'pickup', 'camión', 'microbús', 'blindado', 'camioneta', 'otro']
            ],
            4  => [
                'desc' => 'Pintura en general',
                'tipos' => null
            ],
            5  => [
                'desc' => 'Siglas que identifican a los vehículos pintados en color naranja fluorescente y en el lugar correspondiente',
                'tipos' => null
            ],
            6  => [
                'desc' => 'Lona del camión',
                'tipos' => ['camión']
            ],
            7  => [
                'desc' => 'Luces y pide vías',
                'tipos' => null
            ],
            8  => [
                'desc' => 'Sistema eléctrico',
                'tipos' => null
            ],
            9  => [
                'desc' => 'Herramienta extra para reparación de vehículos',
                'tipos' => ['automóvil', 'pickup', 'camión', 'microbús', 'blindado', 'camioneta', 'otro']
            ],
            10 => [
                'desc' => 'Herramienta básica (Tricket, llave de chuchos, palanca o tubo, trozo, cable o cadena, señalizaciones etc.)',
                'tipos' => ['automóvil', 'pickup', 'camión', 'microbús', 'blindado', 'camioneta', 'otro']
            ],
            11 => [
                'desc' => 'Herramienta de emergencia (llave de ½, Nos. 12, 13, 14, alicate, llave ajustable, juego de desatornilladores)',
                'tipos' => ['automóvil', 'pickup', 'camión', 'microbús', 'blindado', 'camioneta', 'otro']
            ],
            12 => [
                'desc' => 'Repuestos necesarios de emergencias',
                'tipos' => ['automóvil', 'pickup', 'camión', 'microbús', 'blindado', 'camioneta', 'otro']
            ],
            13 => [
                'desc' => 'Neumático de repuesto',
                'tipos' => ['automóvil', 'pickup', 'camión', 'microbús', 'blindado', 'camioneta', 'otro']
            ],
            14 => [
                'desc' => 'Acumulador o batería',
                'tipos' => null
            ],
            15 => [
                'desc' => 'Neumáticos',
                'tipos' => null
            ],
            16 => [
                'desc' => 'Lubricante',
                'tipos' => null
            ],
            17 => [
                'desc' => 'Odómetro',
                'tipos' => ['automóvil', 'pickup', 'camión', 'microbús', 'blindado', 'camioneta', 'otro', 'motocicleta']
            ],
        ];

        // ── Filtrar según tipo ────────────────────────────────────────────────────
        $items = array_filter(
            $itemsDef,
            fn($item) => $item['tipos'] === null || in_array($tipoVehiculo, $item['tipos'])
        );

        $resultados = [];
        if ($chequeo && !empty($chequeo['items'])) {
            foreach ($chequeo['items'] as $item) {
                $resultados[(int)($item['numero_item'] ?? 0)] = [
                    'resultado'   => $item['resultado']   ?? '',
                    'observacion' => $item['observacion'] ?? '',
                ];
            }
        }

        $colores = ['BE' => '#2e7d32', 'ME' => '#e8b84b', 'MEI' => '#e05252', 'NT' => '#7c8398'];
        $filas   = '';
        $contador = 1;
        foreach ($items as $num => $item) {
            $desc   = $item['desc'];
            $res    = $resultados[$num]['resultado']   ?? '';
            $obs    = $resultados[$num]['observacion'] ?? '';
            $celdas = '';
            foreach (['BE', 'ME', 'MEI', 'NT'] as $op) {
                $color   = $colores[$op];
                $circulo = ($res === $op)
                    ? '<svg width="14" height="14" xmlns="http://www.w3.org/2000/svg"><circle cx="7" cy="7" r="6" fill="' . $color . '" stroke="' . $color . '" stroke-width="1"/></svg>'
                    : '<svg width="14" height="14" xmlns="http://www.w3.org/2000/svg"><circle cx="7" cy="7" r="6" fill="none" stroke="#cccccc" stroke-width="1.5"/></svg>';
                $celdas .= '<td style="text-align:center;padding:3px 4px;width:38px;">' . $circulo . '</td>';
            }
            $bg = ($contador % 2 === 0) ? '#fff8f2' : '#ffffff';
            $filas .= '
        <tr style="background:' . $bg . ';border-bottom:0.5pt solid #f0e0cc;">
            <td style="text-align:center;font-weight:bold;color:#C75B00;font-size:7.5pt;padding:4px 5px;width:28px;">' . str_pad($contador, 2, '0', STR_PAD_LEFT) . '</td>
            <td style="text-align:left;font-size:8pt;padding:4px 6px;color:#1a1a1a;">' . htmlspecialchars($desc) . '</td>
            ' . $celdas . '
            <td style="font-size:7.5pt;color:#888;padding:4px 6px;">' . htmlspecialchars($obs) . '</td>
        </tr>';
            $contador++;
        }

        $fecha       = $chequeo ? $chequeo['fecha_chequeo']  : '—';
        $km          = $chequeo ? number_format((int)$chequeo['km_al_chequeo']) . ' km' : '—';
        $responsable = $chequeo ? ($chequeo['realizado_por'] ?: '—') : '—';
        $obsGen      = $chequeo ? ($chequeo['observaciones_gen'] ?? '') : '';

        $estadoBadge = $chequeo
            ? '<span style="background:#e6f4ea;color:#2e7d32;border:0.5pt solid #2e7d32;padding:1px 8px;border-radius:3px;font-size:7pt;font-weight:bold;">✓ COMPLETADO</span>'
            : '<span style="background:#fce8e8;color:#c62828;border:0.5pt solid #c62828;padding:1px 8px;border-radius:3px;font-size:7pt;font-weight:bold;">⚠ PENDIENTE</span>';

        return '
    ' . self::encabezado('HOJA INDIVIDUAL DE CHEQUEO DE VEHÍCULOS') . '
    <table width="100%" style="border-collapse:collapse;margin-bottom:10px;">
        <tr>
            <td style="background:#C75B00;padding:5px 8px;border:1pt solid #a34800;width:18%;">
                <div style="font-size:7pt;color:#ffe0b2;text-transform:uppercase;">Catálogo</div>
                <div style="font-size:12pt;font-weight:bold;color:#ffffff;">' . htmlspecialchars($v['placa']) . '</div>
            </td>
            <td style="background:#fff8f2;padding:5px 8px;border:1pt solid #e8c89a;width:26%;">
                <div style="font-size:7pt;color:#C75B00;text-transform:uppercase;">Marca / Modelo</div>
                <div style="font-size:10pt;font-weight:bold;">' . htmlspecialchars($v['marca'] . ' ' . $v['modelo']) . '</div>
            </td>
            <td style="background:#fff8f2;padding:5px 8px;border:1pt solid #e8c89a;width:20%;">
                <div style="font-size:7pt;color:#C75B00;text-transform:uppercase;">Fecha</div>
                <div style="font-size:9.5pt;font-weight:bold;">' . $fecha . ' &nbsp;' . $estadoBadge . '</div>
            </td>
            <td style="background:#fff8f2;padding:5px 8px;border:1pt solid #e8c89a;width:18%;">
                <div style="font-size:7pt;color:#C75B00;text-transform:uppercase;">KM al Momento</div>
                <div style="font-size:9.5pt;font-weight:bold;">' . $km . '</div>
            </td>
            <td style="background:#fff8f2;padding:5px 8px;border:1pt solid #e8c89a;width:18%;">
                <div style="font-size:7pt;color:#C75B00;text-transform:uppercase;">Realizado Por</div>
                <div style="font-size:9.5pt;font-weight:bold;">' . htmlspecialchars($responsable) . '</div>
            </td>
        </tr>
    </table>
    <table width="100%" style="border-collapse:collapse;font-size:8.5pt;">
        <thead>
            <tr style="background:#1a1a1a;color:#ffffff;">
                <th style="padding:5px;text-align:center;width:28px;font-size:7pt;">No.</th>
                <th style="padding:5px;text-align:left;font-size:7pt;">Descripción del Ítem</th>
                <th style="padding:5px;text-align:center;width:38px;color:#4caf7d;font-size:7pt;">BE</th>
                <th style="padding:5px;text-align:center;width:38px;color:#e05252;font-size:7pt;">ME</th>
                <th style="padding:5px;text-align:center;width:38px;color:#e8b84b;font-size:7pt;">MEI</th>
                <th style="padding:5px;text-align:center;width:38px;color:#aaa;font-size:7pt;">NT</th>
                <th style="padding:5px;text-align:left;font-size:7pt;">Observación</th>
            </tr>
        </thead>
        <tbody>' . $filas . '</tbody>
    </table>
    <div style="margin-top:5px;font-size:7pt;color:#aaa;">
        <strong style="color:#C75B00;">BE</strong> = Buen Estado &nbsp;·&nbsp;
        <strong style="color:#C75B00;">ME</strong> = Mal Estado &nbsp;·&nbsp;
        <strong style="color:#C75B00;">MEI</strong> = Mal Estado Irreparable &nbsp;·&nbsp;
        <strong style="color:#C75B00;">NT</strong> = No Tiene
    </div>
    ' . (!empty($obsGen) ? '
    <div style="margin-top:8px;border:1pt solid #e8c89a;border-left:4pt solid #C75B00;
        padding:6px 10px;font-size:8pt;background:#fffaf5;border-radius:0 3pt 3pt 0;">
        <strong style="color:#C75B00;">Observaciones:</strong> ' . htmlspecialchars($obsGen) . '
    </div>' : '') . '
    <div style="margin-top:16px;">
        <table width="100%" style="border-collapse:collapse;">
            <tr>
                <td style="text-align:center;padding:10px 16px;">
                    <div style="border-top:1pt solid #C75B00;margin:0 16px;padding-top:5px;
                        font-size:7.5pt;color:#666;text-transform:uppercase;letter-spacing:.5pt;">
                        Firma del Responsable
                    </div>
                </td>
                <td style="text-align:center;padding:10px 16px;">
                    <div style="border-top:1pt solid #C75B00;margin:0 16px;padding-top:5px;
                        font-size:7.5pt;color:#666;text-transform:uppercase;letter-spacing:.5pt;">
                        Firma del Oficial de Turno
                    </div>
                </td>
                <td style="text-align:center;padding:10px 16px;">
                    <div style="border-top:1pt solid #C75B00;margin:0 16px;padding-top:5px;
                        font-size:7.5pt;color:#666;text-transform:uppercase;letter-spacing:.5pt;">
                        Sello Unidad
                    </div>
                </td>
            </tr>
        </table>
    </div>
    ' . self::pie($v['placa'], '16 – HOJA DE CHEQUEO');
    }
}
