<?php

namespace Controllers;

use Exception;
use Model\Vehiculos;
use Model\Servicios;
use Model\Reparaciones;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use MVC\Router;

class ExpedienteController
{
    // ─────────────────────────────────────────────────────────────────────────
    //  ENTRY POINT  →  GET /vehiculos/expediente?placa=O-816-BBC
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

            // ── 2. Bajar foto desde SFTP como base64 ──────────────────────────
            $fotoBase64 = '';
            if (!empty($vehiculo['foto_frente'])) {
                $fotoBase64 = self::obtenerFotoBase64($vehiculo['foto_frente']);
            }

            // ── 3. Separar servicios por tipo para las hojas especializadas ───
            $cambiosAceite  = array_filter(
                $servicios,
                fn($s) => stripos($s['tipo_nombre'], 'aceite') !== false
            );
            $cambiosLlantas = array_filter(
                $servicios,
                fn($s) => stripos($s['tipo_nombre'], 'llanta') !== false
            );
            $cambiosAcumulador = array_filter(
                $servicios,
                fn($s) =>
                stripos($s['tipo_nombre'], 'acumulador') !== false ||
                    stripos($s['tipo_nombre'], 'batería')    !== false ||
                    stripos($s['tipo_nombre'], 'bateria')    !== false
            );

            // ── 4. Configurar mPDF ────────────────────────────────────────────
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

            // ── 5. Estilos globales ───────────────────────────────────────────
            $css = self::estilosGlobales();
            $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);

            // ═══════════════════════════════════════════════════════════════════
            //  SECCIÓN 1 – CARÁTULA
            // ═══════════════════════════════════════════════════════════════════
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaCaratula($vehiculo, $fotoBase64));

            // ═══════════════════════════════════════════════════════════════════
            //  SECCIÓN 2 – ÍNDICE
            // ═══════════════════════════════════════════════════════════════════
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaIndice($vehiculo));

            // ═══════════════════════════════════════════════════════════════════
            //  SECCIÓN 3 – FOTOGRAFÍA DEL VEHÍCULO
            // ═══════════════════════════════════════════════════════════════════
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('03', 'FOTOGRAFÍA DEL VEHÍCULO'));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaFoto($vehiculo, $fotoBase64));

            // ═══════════════════════════════════════════════════════════════════
            //  SECCIÓN 4 – INFORMACIÓN DEL VEHÍCULO
            // ═══════════════════════════════════════════════════════════════════
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('04', 'INFORMACIÓN DEL VEHÍCULO'));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaInfoVehiculo($vehiculo));

            // ═══════════════════════════════════════════════════════════════════
            //  SECCIÓN 5 – TARJETA DE CIRCULACIÓN
            // ═══════════════════════════════════════════════════════════════════
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('05', 'COPIA DE TARJETA DE CIRCULACIÓN'));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaTarjeta($vehiculo));

            // ═══════════════════════════════════════════════════════════════════
            //  SECCIÓN 8 – HISTORIAL DE SERVICIOS (todos)
            // ═══════════════════════════════════════════════════════════════════
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('08', 'HISTORIAL DE SERVICIOS'));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaHistorialServicios($vehiculo, $servicios));

            // ═══════════════════════════════════════════════════════════════════
            //  SECCIÓN 9 – HISTORIAL CAMBIO DE LLANTAS
            // ═══════════════════════════════════════════════════════════════════
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('09', 'HISTORIAL DE CAMBIO DE LLANTAS'));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaHistorialFiltrado(
                $vehiculo,
                array_values($cambiosLlantas),
                'HISTORIAL DE CAMBIO DE LLANTAS',
                'llantas'
            ));

            // ═══════════════════════════════════════════════════════════════════
            //  SECCIÓN 10 – HISTORIAL CAMBIO DE ACUMULADOR
            // ═══════════════════════════════════════════════════════════════════
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('10', 'HISTORIAL DE CAMBIO DE ACUMULADOR'));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaHistorialFiltrado(
                $vehiculo,
                array_values($cambiosAcumulador),
                'HISTORIAL DE CAMBIO DE ACUMULADOR',
                'acumulador'
            ));

            // ═══════════════════════════════════════════════════════════════════
            //  SECCIÓN 11 – REPARACIONES
            // ═══════════════════════════════════════════════════════════════════
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('11', 'REPARACIONES'));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaReparaciones($vehiculo, $reparaciones));

            // ═══════════════════════════════════════════════════════════════════
            //  SECCIÓN 16 – HOJA DE CHEQUEO DIARIO
            // ═══════════════════════════════════════════════════════════════════
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::separador('16', 'HOJA INDIVIDUAL DE CHEQUEO DE VEHÍCULOS'));
            $mpdf->AddPage();
            $mpdf->WriteHTML(self::paginaHojaChequeo($vehiculo));

            // ── 6. Enviar al navegador ────────────────────────────────────────
            $nombreArchivo = "Expediente_{$placa}_" . date('Ymd') . '.pdf';
            $mpdf->Output($nombreArchivo, \Mpdf\Output\Destination::INLINE);
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
    //  HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /** Descarga la imagen del SFTP y la convierte a data URI base64 */
    private static function obtenerFotoBase64(string $nombreArchivo): string
    {
        try {
            $sftp = new \phpseclib3\Net\SFTP(
                $_ENV['SFTP_HOST'],
                (int)($_ENV['SFTP_PORT'] ?? 22)
            );
            $sftp->login($_ENV['SFTP_USER'], $_ENV['SFTP_PASS']);

            $rutaBase  = rtrim($_ENV['SFTP_PATH'] ?? '/vehiculos', '/');
            $contenido = $sftp->get("{$rutaBase}/fotos/{$nombreArchivo}");

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

    /** Encabezado de página con línea institucional */
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

    /** Pie de página estándar */
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

    /** Página separadora fondo blanco estilo BHR */
    private static function separador(string $numero, string $titulo): string
    {
        // Ruta absoluta al logo PNG (ya sin fondo negro)
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/bhr_functions/public/images/triangule.png';
        $logoTag  = '';

        if (file_exists($logoPath)) {
            $ext     = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
            $mime    = match ($ext) {
                'png'        => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                default      => 'image/png'
            };
            $logoB64 = base64_encode(file_get_contents($logoPath));
            $logoTag = '<img src="data:' . $mime . ';base64,' . $logoB64 . '"
                         style="width:220px;height:auto;display:block;margin:0 auto;">';
        }

        return '
        <div class="separador-pagina">
            <div class="sep-numero">' . $numero . '</div>
            <div class="sep-titulo">' . $titulo . '</div>
            <div class="sep-linea"></div>
            ' . $logoTag . '
            <div class="sep-institucion" style="margin-top:18px;">
                BRIGADA HUMANITARIA Y DE RESCATE<br>
                EJÉRCITO DE GUATEMALA
            </div>
        </div>';
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  CSS GLOBAL
    // ─────────────────────────────────────────────────────────────────────────
    private static function estilosGlobales(): string
    {
        return '
        <style>
            body {
                font-family: dejavusans, sans-serif;
                font-size: 10pt;
                color: #1a1a1a;
                margin: 0;
                padding: 0;
            }

            /* ── ENCABEZADO ── */
            .encabezado-tabla {
                border-bottom: 2.5pt solid #C75B00;
                margin-bottom: 10px;
                padding-bottom: 4px;
            }
            .enc-izq { font-size: 8pt; color: #444; }
            .enc-der { font-size: 8pt; text-align: right; color: #C75B00; font-weight: bold; }
            .enc-republica { font-weight: bold; }
            .enc-sep { color: #999; }

            /* ── TÍTULOS ── */
            .titulo-seccion {
                text-align: center;
                font-size: 13pt;
                font-weight: bold;
                letter-spacing: 2pt;
                text-transform: uppercase;
                border-bottom: 1pt solid #C75B00;
                padding-bottom: 5px;
                margin-bottom: 14px;
                color: #1a1a1a;
            }
            .titulo-sub {
                font-size: 10pt;
                font-weight: bold;
                color: #C75B00;
                text-transform: uppercase;
                letter-spacing: 1pt;
                border-left: 3pt solid #C75B00;
                padding-left: 6px;
                margin: 10px 0 6px 0;
            }

            /* ── SEPARADOR DE SECCIÓN ── */
            .separador-pagina {
                background-color: #ffffff;
                width: 100%;
                height: 680px;
                text-align: center;
                padding-top: 120px;
            }
            .sep-numero {
                font-size: 80pt;
                font-weight: bold;
                color: rgba(199,91,0,0.12);
                line-height: 1;
                margin-bottom: 0px;
            }
            .sep-titulo {
                font-size: 18pt;
                font-weight: bold;
                color: #1a1a1a;
                text-transform: uppercase;
                letter-spacing: 3pt;
                border-top: 2pt solid #C75B00;
                border-bottom: 2pt solid #C75B00;
                padding: 12px 40px;
                margin: 0 60px;
            }
            .sep-linea {
                height: 1pt;
                background: #e0e0e0;
                margin: 18px 80px;
            }
            .sep-institucion {
                font-size: 9pt;
                color: #555555;
                letter-spacing: 1pt;
                line-height: 1.6;
            }

            /* ── TABLAS DE DATOS ── */
            .tabla-datos {
                width: 100%;
                border-collapse: collapse;
                font-size: 9.5pt;
                margin-bottom: 12px;
            }
            .tabla-datos th {
                background-color: #C75B00;
                color: #ffffff;
                font-weight: bold;
                text-align: center;
                padding: 5px 7px;
                border: 0.5pt solid #a34800;
                font-size: 8.5pt;
                text-transform: uppercase;
                letter-spacing: 0.5pt;
            }
            .tabla-datos td {
                padding: 4px 7px;
                border: 0.5pt solid #d0d0d0;
                vertical-align: middle;
            }
            .tabla-datos tr:nth-child(even) td {
                background-color: #fdf4ee;
            }
            .tabla-datos tr:nth-child(odd) td {
                background-color: #ffffff;
            }
            .tabla-datos .td-label {
                background-color: #f0f0f0 !important;
                font-weight: bold;
                color: #444;
                font-size: 8pt;
                text-transform: uppercase;
                width: 28%;
            }
            .tabla-datos .td-valor {
                font-weight: bold;
                color: #1a1a1a;
            }

            /* ── FICHA INFO VEHÍCULO ── */
            .grid-info {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 14px;
            }
            .grid-info td {
                padding: 5px 8px;
                border: 0.5pt solid #d0d0d0;
                font-size: 9.5pt;
                vertical-align: top;
            }
            .grid-info .lbl {
                background: #f5f5f5;
                font-size: 7.5pt;
                font-weight: bold;
                color: #888;
                text-transform: uppercase;
                letter-spacing: 0.5pt;
                padding: 3px 8px 1px 8px;
                border-bottom: none;
            }
            .grid-info .val {
                font-size: 10pt;
                font-weight: bold;
                color: #1a1a1a;
                border-top: none;
                padding-top: 2px;
            }

            /* ── ESTADO BADGES ── */
            .badge-alta   { background:#e6f4ea; color:#2e7d32; padding:2px 8px; border-radius:3px; font-size:8.5pt; font-weight:bold; }
            .badge-baja   { background:#fce8e8; color:#c62828; padding:2px 8px; border-radius:3px; font-size:8.5pt; font-weight:bold; }
            .badge-taller { background:#fff3e0; color:#e65100; padding:2px 8px; border-radius:3px; font-size:8.5pt; font-weight:bold; }

            /* ── CONTROL DE SERVICIOS ── */
            .tabla-control {
                width: 100%;
                border-collapse: collapse;
                font-size: 9pt;
                margin-bottom: 8px;
            }
            .tabla-control th {
                background-color: #1a1a1a;
                color: #ffffff;
                padding: 5px 7px;
                border: 0.5pt solid #333;
                text-align: center;
                font-size: 8pt;
                text-transform: uppercase;
                letter-spacing: 0.5pt;
            }
            .tabla-control td {
                padding: 4px 7px;
                border: 0.5pt solid #ccc;
                text-align: center;
            }
            .tabla-control tr:nth-child(even) td { background: #f9f9f9; }

            /* ── PIE DE PÁGINA ── */
            .pie-pagina {
                position: fixed;
                bottom: -12px;
                left: 0;
                right: 0;
                font-size: 7pt;
                color: #888;
                border-top: 0.5pt solid #ddd;
                padding-top: 3px;
                text-align: center;
            }

            /* ── CARÁTULA ── */
            .caratula-box {
                border: 2pt solid #C75B00;
                padding: 24px 30px;
                margin: 10px 0;
            }
            .caratula-institucion {
                text-align: center;
                font-size: 11pt;
                font-weight: bold;
                color: #C75B00;
                letter-spacing: 2pt;
                text-transform: uppercase;
                margin-bottom: 20px;
            }
            .caratula-placa {
                text-align: center;
                font-size: 32pt;
                font-weight: bold;
                color: #1a1a1a;
                letter-spacing: 4pt;
                margin: 12px 0;
                border: 1.5pt solid #1a1a1a;
                padding: 8px;
                display: inline-block;
            }
            .caratula-foto {
                text-align: center;
                margin: 16px 0;
            }
            .caratula-foto img {
                max-width: 280px;
                max-height: 200px;
                border: 1pt solid #ccc;
            }

            /* ── HOJA CHEQUEO ── */
            .chequeo-tabla {
                width: 100%;
                border-collapse: collapse;
                font-size: 9pt;
            }
            .chequeo-tabla th {
                background: #1a1a1a;
                color: white;
                padding: 4px 6px;
                border: 0.5pt solid #333;
                text-align: center;
                font-size: 8pt;
            }
            .chequeo-tabla td {
                padding: 5px 7px;
                border: 0.5pt solid #bbb;
                text-align: center;
            }
            .chequeo-tabla .item { text-align: left; }
            .casilla {
                width: 16px; height: 16px;
                border: 1pt solid #555;
                display: inline-block;
                margin: 0 2px;
            }

            /* ── ÍNDICE ── */
            .indice-tabla {
                width: 100%;
                border-collapse: collapse;
                font-size: 10pt;
            }
            .indice-tabla td {
                padding: 7px 10px;
                border-bottom: 0.5pt solid #e0e0e0;
            }
            .indice-tabla .num {
                width: 40px;
                text-align: center;
                font-weight: bold;
                color: #C75B00;
                font-size: 12pt;
            }
            .indice-tabla .titulo-item {
                font-weight: bold;
                color: #1a1a1a;
            }
            .indice-tabla .puntos {
                color: #ccc;
                text-align: right;
                font-size: 8pt;
            }

            /* ── CAJA VACÍA (sin datos) ── */
            .caja-vacia {
                border: 1pt dashed #ccc;
                padding: 20px;
                text-align: center;
                color: #aaa;
                font-size: 9pt;
                margin: 14px 0;
            }
        </style>';
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  PÁGINAS DEL EXPEDIENTE
    // ─────────────────────────────────────────────────────────────────────────

    /** 1 – CARÁTULA */
    private static function paginaCaratula(array $v, string $foto): string
    {
        $fotoTag = $foto
            ? '<img src="' . $foto . '" style="max-width:260px;max-height:190px;border:1pt solid #ccc;">'
            : '<div style="border:1pt dashed #ccc;padding:30px;color:#aaa;font-size:9pt;display:inline-block;">Sin fotografía</div>';

        $estadoBadge = match ($v['estado'] ?? '') {
            'Alta'   => '<span class="badge-alta">OPERATIVO – ALTA</span>',
            'Baja'   => '<span class="badge-baja">FUERA DE SERVICIO – BAJA</span>',
            'Taller' => '<span class="badge-taller">EN TALLER</span>',
            default  => ''
        };

        $unidad     = $v['unidad_nombre']      ?? '—';
        $dest       = $v['destacamento_nombre'] ?? '';
        $depto      = $v['destacamento_depto']  ?? '';
        $asignacion = $dest ? "{$unidad} · {$dest}, {$depto}" : $unidad;

        return '
        ' . self::encabezado() . '
        <div class="caratula-box">
            <div class="caratula-institucion">
                BRIGADA HUMANITARIA Y DE RESCATE
            </div>
            <div style="text-align:center;font-size:10pt;font-weight:bold;letter-spacing:1pt;margin-bottom:18px;">
                EXPEDIENTE DE VEHÍCULO
            </div>

            <div style="text-align:center;margin-bottom:14px;">
                ' . $fotoTag . '
            </div>

            <div style="text-align:center;margin-bottom:16px;">
                <div style="font-size:8pt;color:#888;text-transform:uppercase;letter-spacing:1pt;">Catálogo / Placa</div>
                <div style="font-size:28pt;font-weight:bold;letter-spacing:4pt;border:1.5pt solid #1a1a1a;padding:6px 24px;display:inline-block;">
                    ' . htmlspecialchars($v['placa']) . '
                </div>
            </div>

            <table width="100%" style="border-collapse:collapse;margin-bottom:14px;">
                <tr>
                    <td style="padding:4px;border:0.5pt solid #ddd;background:#f9f9f9;">
                        <div style="font-size:7.5pt;color:#888;text-transform:uppercase;">Marca / Modelo</div>
                        <div style="font-size:11pt;font-weight:bold;">' . htmlspecialchars($v['marca'] . ' ' . $v['modelo']) . '</div>
                    </td>
                    <td style="padding:4px;border:0.5pt solid #ddd;background:#f9f9f9;">
                        <div style="font-size:7.5pt;color:#888;text-transform:uppercase;">Año</div>
                        <div style="font-size:11pt;font-weight:bold;">' . htmlspecialchars($v['anio']) . '</div>
                    </td>
                    <td style="padding:4px;border:0.5pt solid #ddd;background:#f9f9f9;">
                        <div style="font-size:7.5pt;color:#888;text-transform:uppercase;">Color</div>
                        <div style="font-size:11pt;font-weight:bold;">' . htmlspecialchars($v['color']) . '</div>
                    </td>
                    <td style="padding:4px;border:0.5pt solid #ddd;background:#f9f9f9;">
                        <div style="font-size:7.5pt;color:#888;text-transform:uppercase;">Estado</div>
                        <div style="margin-top:3px;">' . $estadoBadge . '</div>
                    </td>
                </tr>
            </table>

            <table width="100%" style="border-collapse:collapse;">
                <tr>
                    <td style="padding:4px;border:0.5pt solid #ddd;">
                        <span style="font-size:7.5pt;color:#888;text-transform:uppercase;">Número de Serie / Chasis</span><br>
                        <strong>' . htmlspecialchars($v['numero_serie']) . '</strong>
                    </td>
                    <td style="padding:4px;border:0.5pt solid #ddd;">
                        <span style="font-size:7.5pt;color:#888;text-transform:uppercase;">Tipo</span><br>
                        <strong>' . htmlspecialchars($v['tipo']) . '</strong>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding:4px;border:0.5pt solid #ddd;">
                        <span style="font-size:7.5pt;color:#888;text-transform:uppercase;">Unidad Asignada</span><br>
                        <strong>' . htmlspecialchars($asignacion) . '</strong>
                    </td>
                </tr>
            </table>

            <div style="text-align:center;margin-top:20px;font-size:8pt;color:#888;">
                Generado el ' . date('d \d\e F \d\e Y') . ' &nbsp;·&nbsp;
                KM actuales: <strong>' . number_format((int)$v['km_actuales']) . ' km</strong>
            </div>
        </div>
        ' . self::pie($v['placa'], '01 – CARÁTULA');
    }

    /** 2 – ÍNDICE */
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
            $filas .= '
            <tr>
                <td class="num">' . $s[0] . '</td>
                <td class="titulo-item">' . $s[1] . '</td>
                <td class="puntos">..................</td>
            </tr>';
        }

        return '
        ' . self::encabezado('02', 'ÍNDICE') . '
        <table class="indice-tabla">
            ' . $filas . '
        </table>
        ' . self::pie($v['placa'], '02 – ÍNDICE');
    }

    /** 3 – FOTO DEL VEHÍCULO */
    private static function paginaFoto(array $v, string $foto): string
    {
        $contenido = $foto
            ? '<div style="text-align:center;margin-top:30px;">
                 <img src="' . $foto . '" style="max-width:450px;max-height:350px;border:1.5pt solid #ccc;">
                 <div style="font-size:8pt;color:#888;margin-top:8px;text-transform:uppercase;letter-spacing:1pt;">
                   Vista frontal – ' . htmlspecialchars($v['placa']) . '
                 </div>
               </div>'
            : '<div class="caja-vacia">Sin fotografía registrada en el sistema</div>';

        return '
        ' . self::encabezado('03', 'FOTOGRAFÍA DEL VEHÍCULO') . '
        <table width="100%" style="border-collapse:collapse;margin-bottom:12px;">
            <tr>
                <td class="td-label" style="border:0.5pt solid #ddd;background:#f5f5f5;padding:4px 8px;font-size:8pt;font-weight:bold;">TIPO DE VEHÍCULO</td>
                <td style="border:0.5pt solid #ddd;padding:4px 8px;"><strong>' . htmlspecialchars($v['tipo']) . ' – ' . htmlspecialchars($v['marca'] . ' ' . $v['modelo']) . '</strong></td>
                <td class="td-label" style="border:0.5pt solid #ddd;background:#f5f5f5;padding:4px 8px;font-size:8pt;font-weight:bold;">CATÁLOGO</td>
                <td style="border:0.5pt solid #ddd;padding:4px 8px;"><strong>' . htmlspecialchars($v['placa']) . '</strong></td>
            </tr>
        </table>
        ' . $contenido . '
        ' . self::pie($v['placa'], '03 – FOTOGRAFÍA');
    }

    /** 4 – INFORMACIÓN DEL VEHÍCULO */
    private static function paginaInfoVehiculo(array $v): string
    {
        $unidad = $v['unidad_nombre'] ?? '—';
        $dest   = isset($v['destacamento_nombre']) && $v['destacamento_nombre']
            ? $v['destacamento_nombre'] . ', ' . ($v['destacamento_depto'] ?? '')
            : '—';

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
            <tr>
                <td class="td-label">Catálogo / Placa</td>
                <td class="td-valor">' . htmlspecialchars($v['placa']) . '</td>
                <td class="td-label">Número de Serie</td>
                <td class="td-valor">' . htmlspecialchars($v['numero_serie']) . '</td>
            </tr>
            <tr>
                <td class="td-label">Marca</td>
                <td class="td-valor">' . htmlspecialchars($v['marca']) . '</td>
                <td class="td-label">Modelo</td>
                <td class="td-valor">' . htmlspecialchars($v['modelo']) . '</td>
            </tr>
            <tr>
                <td class="td-label">Año</td>
                <td class="td-valor">' . htmlspecialchars($v['anio']) . '</td>
                <td class="td-label">Color</td>
                <td class="td-valor">' . htmlspecialchars($v['color']) . '</td>
            </tr>
            <tr>
                <td class="td-label">Tipo de Vehículo</td>
                <td class="td-valor">' . htmlspecialchars($v['tipo']) . '</td>
                <td class="td-label">Estado Operacional</td>
                <td class="td-valor">' . $estadoBadge . '</td>
            </tr>
        </table>

        <div class="titulo-sub">Datos Técnicos</div>
        <table class="tabla-datos">
            <tr>
                <td class="td-label">Combustible</td>
                <td class="td-valor">—</td>
                <td class="td-label">Kilometraje Actual</td>
                <td class="td-valor">' . number_format((int)$v['km_actuales']) . ' km</td>
            </tr>
            <tr>
                <td class="td-label">Fecha de Ingreso</td>
                <td class="td-valor">' . htmlspecialchars($v['fecha_ingreso']) . '</td>
                <td class="td-label">N° Motor</td>
                <td class="td-valor">—</td>
            </tr>
        </table>

        <div class="titulo-sub">Asignación</div>
        <table class="tabla-datos">
            <tr>
                <td class="td-label">Unidad</td>
                <td class="td-valor" colspan="3">' . htmlspecialchars($unidad) . '</td>
            </tr>
            <tr>
                <td class="td-label">Destacamento</td>
                <td class="td-valor" colspan="3">' . htmlspecialchars($dest) . '</td>
            </tr>
        </table>

        <div class="titulo-sub">Observaciones</div>
        <div style="border:0.5pt solid #ddd;padding:10px;min-height:50px;font-size:9.5pt;">
            ' . (empty($v['observaciones'])
            ? '<span style="color:#aaa;">Sin observaciones registradas.</span>'
            : htmlspecialchars($v['observaciones'])) . '
        </div>
        ' . self::pie($v['placa'], '04 – INFORMACIÓN');
    }

    /** 5 – TARJETA DE CIRCULACIÓN */
    private static function paginaTarjeta(array $v): string
    {
        $nota = !empty($v['tarjeta_pdf'])
            ? '<div style="text-align:center;padding:20px;font-size:9pt;">
                 <div style="font-size:11pt;font-weight:bold;color:#C75B00;margin-bottom:8px;">
                   ✓ Tarjeta de Circulación digitalizada
                 </div>
                 Archivo: <strong>' . htmlspecialchars(basename($v['tarjeta_pdf'])) . '</strong><br>
                 <span style="font-size:8pt;color:#888;">
                   Disponible en el sistema. Imprimir por separado desde el módulo de vehículos.
                 </span>
               </div>'
            : '<div class="caja-vacia">
                 No se ha digitalizado la tarjeta de circulación.<br>
                 Adjuntar copia física en esta sección.
               </div>';

        return '
        ' . self::encabezado('05', 'COPIA DE TARJETA DE CIRCULACIÓN') . '
        <table class="tabla-datos" style="margin-bottom:16px;">
            <tr>
                <td class="td-label">Placa</td>
                <td class="td-valor">' . htmlspecialchars($v['placa']) . '</td>
                <td class="td-label">Marca / Modelo</td>
                <td class="td-valor">' . htmlspecialchars($v['marca'] . ' ' . $v['modelo']) . '</td>
            </tr>
        </table>
        ' . $nota . '
        <div style="margin-top:30px;border-top:1pt dashed #ccc;padding-top:14px;">
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
        ' . self::pie($v['placa'], '05 – TARJETA DE CIRCULACIÓN');
    }

    /** 8 – HISTORIAL COMPLETO DE SERVICIOS */
    private static function paginaHistorialServicios(array $v, array $servicios): string
    {
        if (empty($servicios)) {
            $tabla = '<div class="caja-vacia">No hay servicios registrados para este vehículo.</div>';
        } else {
            $filas = '';
            foreach ($servicios as $s) {
                $kmP = $s['km_proximo_servicio']
                    ? number_format((int)$s['km_proximo_servicio']) . ' km'
                    : '—';
                $filas .= '
                <tr>
                    <td>' . date('d/m/Y', strtotime($s['fecha_realizado'])) . '</td>
                    <td>' . htmlspecialchars($s['tipo_nombre']) . '</td>
                    <td>' . number_format((int)$s['km_al_servicio']) . ' km</td>
                    <td>' . $kmP . '</td>
                    <td>' . htmlspecialchars($s['responsable'] ?? '—') . '</td>
                    <td>' . htmlspecialchars($s['observaciones'] ?? '—') . '</td>
                </tr>';
            }
            $tabla = '
            <table class="tabla-control">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Servicio Realizado</th>
                        <th>Kilometraje</th>
                        <th>Próximo Servicio</th>
                        <th>Responsable</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>' . $filas . '</tbody>
            </table>';
        }

        $vacias = '<table class="tabla-control"><thead><tr>
            <th>Fecha</th><th>Servicio Realizado</th>
            <th>Kilometraje</th><th>Próximo Servicio</th>
            <th>Responsable</th><th>Observaciones</th>
            </tr></thead><tbody>';
        for ($i = 0; $i < 8; $i++) {
            $vacias .= '<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td></tr>';
        }
        $vacias .= '</tbody></table>';

        return '
        ' . self::encabezado('08', 'HISTORIAL DE SERVICIOS') . '
        <table class="tabla-datos" style="margin-bottom:12px;">
            <tr>
                <td class="td-label">Tipo de Vehículo</td>
                <td class="td-valor">' . htmlspecialchars($v['tipo']) . '</td>
                <td class="td-label">Catálogo</td>
                <td class="td-valor">' . htmlspecialchars($v['placa']) . '</td>
            </tr>
            <tr>
                <td class="td-label">Chasis N°</td>
                <td class="td-valor">' . htmlspecialchars($v['numero_serie']) . '</td>
                <td class="td-label">Combustible</td>
                <td class="td-valor">—</td>
            </tr>
            <tr>
                <td class="td-label">Color</td>
                <td class="td-valor">' . htmlspecialchars($v['color']) . '</td>
                <td class="td-label">KM Actuales</td>
                <td class="td-valor">' . number_format((int)$v['km_actuales']) . ' km</td>
            </tr>
        </table>

        <div class="titulo-sub">Servicios Registrados</div>
        ' . $tabla . '

        <div class="titulo-sub" style="margin-top:14px;">Espacios para Registro Manual</div>
        ' . $vacias . '
        ' . self::pie($v['placa'], '08 – HISTORIAL DE SERVICIOS');
    }

    /** 9 / 10 – HISTORIAL FILTRADO (LLANTAS / ACUMULADOR) */
    private static function paginaHistorialFiltrado(
        array  $v,
        array  $servicios,
        string $titulo,
        string $tipo
    ): string {

        if (empty($servicios)) {
            $tabla = '<div class="caja-vacia">No hay registros de ' . strtolower($tipo) . ' para este vehículo.</div>';
        } else {
            $filas = '';
            foreach ($servicios as $s) {
                $kmP = $s['km_proximo_servicio']
                    ? number_format((int)$s['km_proximo_servicio']) . ' km'
                    : '—';
                $filas .= '
                <tr>
                    <td>' . date('d/m/Y', strtotime($s['fecha_realizado'])) . '</td>
                    <td>' . number_format((int)$s['km_al_servicio']) . ' km</td>
                    <td>' . $kmP . '</td>
                    <td>' . htmlspecialchars($s['responsable'] ?? '—') . '</td>
                    <td>' . htmlspecialchars($s['observaciones'] ?? '—') . '</td>
                </tr>';
            }
            $tabla = '
            <table class="tabla-control">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>KM al Cambio</th>
                        <th>Próximo Cambio</th>
                        <th>Responsable</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>' . $filas . '</tbody>
            </table>';
        }

        $vacias = '<table class="tabla-control"><thead><tr>
            <th>Fecha</th><th>KM al Cambio</th>
            <th>Próximo Cambio</th><th>Responsable</th><th>Observaciones</th>
            </tr></thead><tbody>';
        for ($i = 0; $i < 10; $i++) {
            $vacias .= '<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>';
        }
        $vacias .= '</tbody></table>';

        return '
        ' . self::encabezado('', $titulo) . '
        <table class="tabla-datos" style="margin-bottom:12px;">
            <tr>
                <td class="td-label">Catálogo</td>
                <td class="td-valor">' . htmlspecialchars($v['placa']) . '</td>
                <td class="td-label">Marca / Modelo</td>
                <td class="td-valor">' . htmlspecialchars($v['marca'] . ' ' . $v['modelo']) . '</td>
                <td class="td-label">KM Actuales</td>
                <td class="td-valor">' . number_format((int)$v['km_actuales']) . ' km</td>
            </tr>
        </table>

        <div class="titulo-sub">Registros encontrados</div>
        ' . $tabla . '

        <div class="titulo-sub" style="margin-top:14px;">Espacios para Registro Manual</div>
        ' . $vacias . '
        ' . self::pie($v['placa'], $titulo);
    }

    /** 11 – REPARACIONES */
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
                    <td>' . htmlspecialchars($r['tipo_nombre']) . '</td>
                    <td>' . htmlspecialchars($r['descripcion']) . '</td>
                    <td>' . number_format((int)$r['km_al_momento']) . ' km</td>
                    <td>' . ($r['costo'] ? 'Q ' . number_format((float)$r['costo'], 2) : '—') . '</td>
                    <td><span class="' . $estadoClass . '">' . htmlspecialchars($r['estado']) . '</span></td>
                </tr>';
            }
            $tabla = '
            <table class="tabla-control">
                <thead>
                    <tr>
                        <th>F. Inicio</th>
                        <th>F. Fin</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>KM</th>
                        <th>Costo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>' . $filas . '</tbody>
            </table>';
        }

        $vacias = '<table class="tabla-control"><thead><tr>
            <th>F. Inicio</th><th>F. Fin</th><th>Tipo</th>
            <th>Descripción</th><th>KM</th><th>Costo</th><th>Estado</th>
            </tr></thead><tbody>';
        for ($i = 0; $i < 6; $i++) {
            $vacias .= '<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
        }
        $vacias .= '</tbody></table>';

        return '
        ' . self::encabezado('11', 'REPARACIONES') . '
        <table class="tabla-datos" style="margin-bottom:12px;">
            <tr>
                <td class="td-label">Catálogo</td>
                <td class="td-valor">' . htmlspecialchars($v['placa']) . '</td>
                <td class="td-label">KM Actuales</td>
                <td class="td-valor">' . number_format((int)$v['km_actuales']) . ' km</td>
            </tr>
        </table>

        <div class="titulo-sub">Reparaciones Registradas</div>
        ' . $tabla . '

        <div class="titulo-sub" style="margin-top:14px;">Espacios para Registro Manual</div>
        ' . $vacias . '
        ' . self::pie($v['placa'], '11 – REPARACIONES');
    }

    /** 16 – HOJA DE CHEQUEO DIARIO */
    private static function paginaHojaChequeo(array $v): string
    {
        $items = [
            'Motor'                => ['Aceite de motor', 'Temperatura', 'Correas', 'Mangueras', 'Filtro de aire', 'Nivel de combustible'],
            'Sistema de frenos'    => ['Líquido de frenos', 'Freno de mano', 'Pedal de freno'],
            'Sistema eléctrico'    => ['Luces delanteras', 'Luces traseras', 'Direccionales', 'Batería/Acumulador', 'Bocina'],
            'Neumáticos'           => ['Llanta delantera izquierda', 'Llanta delantera derecha', 'Llanta trasera izquierda', 'Llanta trasera derecha', 'Llanta de refacción'],
            'Exterior / Carrocería' => ['Espejos laterales', 'Espejo retrovisor', 'Limpiaparabrisas', 'Cinturones de seguridad'],
            'Documentación'        => ['Tarjeta de circulación', 'Licencia del conductor', 'Sticker de revisión'],
        ];

        $filas = '';
        foreach ($items as $categoria => $subitems) {
            $filas .= '<tr>
                <td colspan="5" style="background:#C75B00;color:#fff;font-weight:bold;
                    font-size:8pt;text-transform:uppercase;padding:4px 8px;letter-spacing:0.5pt;">
                    ' . $categoria . '
                </td>
            </tr>';
            foreach ($subitems as $item) {
                $filas .= '
                <tr>
                    <td class="item">' . $item . '</td>
                    <td style="text-align:center;color:#2e7d32;font-weight:bold;">✓&nbsp;BIEN</td>
                    <td style="text-align:center;color:#e65100;font-weight:bold;">✗&nbsp;MAL</td>
                    <td style="text-align:center;color:#999;">N/A</td>
                    <td style="min-width:100px;">&nbsp;</td>
                </tr>';
            }
        }

        return '
        ' . self::encabezado('16', 'HOJA INDIVIDUAL DE CHEQUEO DE VEHÍCULOS') . '

        <table class="tabla-datos" style="margin-bottom:10px;">
            <tr>
                <td class="td-label">Catálogo / Placa</td>
                <td class="td-valor">' . htmlspecialchars($v['placa']) . '</td>
                <td class="td-label">Marca / Modelo</td>
                <td class="td-valor">' . htmlspecialchars($v['marca'] . ' ' . $v['modelo']) . '</td>
                <td class="td-label">Fecha</td>
                <td class="td-valor">___/___/______</td>
            </tr>
            <tr>
                <td class="td-label">KM al momento</td>
                <td class="td-valor">_______________</td>
                <td class="td-label">Conductor</td>
                <td class="td-valor" colspan="3">_____________________________</td>
            </tr>
        </table>

        <table class="chequeo-tabla">
            <thead>
                <tr>
                    <th style="text-align:left;width:35%;">Ítem de Revisión</th>
                    <th style="width:15%;">BIEN</th>
                    <th style="width:15%;">MAL</th>
                    <th style="width:12%;">N/A</th>
                    <th style="width:23%;text-align:left;">Observación</th>
                </tr>
            </thead>
            <tbody>
                ' . $filas . '
            </tbody>
        </table>

        <div style="margin-top:18px;">
            <table width="100%" style="border-collapse:collapse;">
                <tr>
                    <td style="text-align:center;padding:8px 16px;">
                        <div style="border-top:0.5pt solid #333;margin:0 20px;padding-top:4px;font-size:8pt;color:#555;">
                            FIRMA DEL CONDUCTOR
                        </div>
                    </td>
                    <td style="text-align:center;padding:8px 16px;">
                        <div style="border-top:0.5pt solid #333;margin:0 20px;padding-top:4px;font-size:8pt;color:#555;">
                            FIRMA DEL OFICIAL RESPONSABLE
                        </div>
                    </td>
                    <td style="text-align:center;padding:8px 16px;">
                        <div style="border-top:0.5pt solid #333;margin:0 20px;padding-top:4px;font-size:8pt;color:#555;">
                            SELLO UNIDAD
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        ' . self::pie($v['placa'], '16 – HOJA DE CHEQUEO');
    }
}
