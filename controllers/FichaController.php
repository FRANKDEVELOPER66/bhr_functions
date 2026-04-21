<?php

namespace Controllers;

use Exception;
use Model\Vehiculos;
use Model\Servicios;
use Model\Reparaciones;
use Model\TiposServicio;
use Model\TiposReparacion;
use Model\Seguros;
use Model\Accidentes;
use MVC\Router;


class FichaController
{
    // ── FICHA COMPLETA ───────────────────────────────────────────────────────
    public static function fichaAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');

        $placa = strtoupper(trim($_GET['placa'] ?? ''));

        if (!$placa) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Placa requerida'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $vehiculo = Vehiculos::traerConDetalle($placa);

            if (!$vehiculo) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Vehículo no encontrado'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // URLs de archivos
            $urlBase = rtrim($_ENV['SFTP_PUBLIC_URL'] ?? '', '/');
            $vehiculo['foto_url']            = $vehiculo['foto_frente']     ? "{$urlBase}/{$vehiculo['foto_frente']}"     : null;
            $vehiculo['foto_lateral_url']    = $vehiculo['foto_lateral']    ? "{$urlBase}/{$vehiculo['foto_lateral']}"    : null;
            $vehiculo['foto_trasera_url']    = $vehiculo['foto_trasera']    ? "{$urlBase}/{$vehiculo['foto_trasera']}"    : null;
            $vehiculo['pdf_url']             = $vehiculo['tarjeta_pdf']     ? "{$urlBase}/{$vehiculo['tarjeta_pdf']}"     : null;
            $vehiculo['cert_inventario_url'] = $vehiculo['cert_inventario'] ? "{$urlBase}/{$vehiculo['cert_inventario']}" : null;
            $vehiculo['cert_sicoin_url']     = $vehiculo['cert_sicoin']     ? "{$urlBase}/{$vehiculo['cert_sicoin']}"     : null;

            // Historial servicios y reparaciones
            $servicios    = Servicios::traerPorPlaca($placa);
            $reparaciones = Reparaciones::traerPorPlaca($placa);

            // ── SEGUROS ───────────────────────────────────────────────────────
            Seguros::actualizarEstadosVencidos($placa);
            $seguros = Seguros::traerPorPlaca($placa);
            foreach ($seguros as &$s) {
                $s['pdf_poliza_url'] = $s['archivo_poliza']
                    ? "{$urlBase}/{$s['archivo_poliza']}"
                    : null;
            }
            unset($s);

            // ── ACCIDENTES ────────────────────────────────────────────────────
            $accidentes = Accidentes::traerPorPlaca($placa);
            foreach ($accidentes as &$a) {
                $a['fotos_url']   = $a['archivo_fotos']   ? "{$urlBase}/{$a['archivo_fotos']}"   : null;
                $a['foto_1_url']  = $a['archivo_foto_1']  ? "{$urlBase}/{$a['archivo_foto_1']}"  : null;
                $a['foto_2_url']  = $a['archivo_foto_2']  ? "{$urlBase}/{$a['archivo_foto_2']}"  : null;
                $a['foto_3_url']  = $a['archivo_foto_3']  ? "{$urlBase}/{$a['archivo_foto_3']}"  : null;
                $a['foto_4_url']  = $a['archivo_foto_4']  ? "{$urlBase}/{$a['archivo_foto_4']}"  : null;
                $a['informe_url'] = $a['archivo_informe'] ? "{$urlBase}/{$a['archivo_informe']}" : null;
                // Alias para que el JS pueda leer costo_danos y costo_reparacion
                // (el JS renderTablaAccidentes usa esos nombres)
                $a['costo_danos']      = $a['costo_estimado'] ?? null;
                $a['costo_reparacion'] = $a['costo_real']     ?? null;
                $a['no_expediente']    = $a['numero_expediente'] ?? null;
                $a['estado']           = $a['estado_caso']    ?? null;
            }
            unset($a);

            // Próximo servicio (el más cercano por km)
            $proximoServicio = null;
            foreach ($servicios as $s) {
                if ($s['km_proximo_servicio']) {
                    if (
                        !$proximoServicio ||
                        $s['km_proximo_servicio'] < $proximoServicio['km_proximo_servicio']
                    ) {
                        $proximoServicio = $s;
                    }
                }
            }

            // Alerta si km actual ya superó el próximo servicio
            $alertaKm = $proximoServicio &&
                $vehiculo['km_actuales'] >= $proximoServicio['km_proximo_servicio'];

            echo json_encode([
                'codigo'           => 1,
                'vehiculo'         => $vehiculo,
                'servicios'        => $servicios,
                'reparaciones'     => $reparaciones,
                'seguros'          => $seguros,
                'accidentes'       => $accidentes,
                'proximo_servicio' => $proximoServicio,
                'alerta_km'        => $alertaKm
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener ficha',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── TIPOS DE SERVICIO ────────────────────────────────────────────────────
    public static function tiposServicioAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $tipos = TiposServicio::traerTodos();
            echo json_encode(['codigo' => 1, 'datos' => $tipos], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(
                ['codigo' => 0, 'mensaje' => 'Error al obtener tipos de servicio'],
                JSON_UNESCAPED_UNICODE
            );
        }
    }

    // ── GUARDAR SERVICIO ──────────────────────────────────────────────────────
    public static function guardarServicioAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');

        $placa = strtoupper(trim(htmlspecialchars($_POST['placa'] ?? '')));

        if (!$placa) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Placa requerida'], JSON_UNESCAPED_UNICODE);
            return;
        }

        foreach (['id_tipo_servicio', 'fecha_realizado', 'km_al_servicio'] as $campo) {
            if (empty($_POST[$campo])) {
                http_response_code(400);
                echo json_encode([
                    'codigo'  => 0,
                    'mensaje' => "El campo '{$campo}' es obligatorio"
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
        }

        try {
            $kmAlServicio = (int)$_POST['km_al_servicio'];
            $forzar       = !empty($_POST['forzar']) && $_POST['forzar'] === '1';

            // ── Validación de intervalo ───────────────────────────────────────────
            $ultimoServicio = Servicios::traerUltimoPorTipo($placa, (int)$_POST['id_tipo_servicio']);

            if ($ultimoServicio && !$forzar) {
                $diasDesdeUltimo = (int)((strtotime('now') - strtotime($ultimoServicio['fecha_realizado'])) / 86400);

                if ($diasDesdeUltimo < 15) {
                    echo json_encode([
                        'codigo'       => 0,
                        'mensaje'      => "Este servicio fue realizado hace {$diasDesdeUltimo} día(s). Debe esperar al menos 15 días para registrarlo nuevamente.",
                        'bloqueo_duro' => true
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }

                if ($diasDesdeUltimo < 90) {
                    echo json_encode([
                        'codigo'       => 2,
                        'mensaje'      => "Este servicio fue realizado hace {$diasDesdeUltimo} día(s). ¿Está seguro que desea registrarlo nuevamente?",
                        'dias'         => $diasDesdeUltimo,
                        'ultimo_km'    => $ultimoServicio['km_al_servicio'],
                        'bloqueo_duro' => false
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }
            }

            // ── Calcular próximo servicio ─────────────────────────────────────────
            $tipo         = TiposServicio::find($_POST['id_tipo_servicio']);
            $kmProximo    = null;
            $fechaProximo = null;

            if ($tipo) {
                if (!empty($tipo->intervalo_km)) {
                    $kmProximo = $kmAlServicio + (int)$tipo->intervalo_km;
                }
                if (!empty($tipo->intervalo_dias)) {
                    $fechaProximo = date(
                        'Y-m-d',
                        strtotime($_POST['fecha_realizado'] . " +{$tipo->intervalo_dias} days")
                    );
                }
            }

            // ── Guardar ───────────────────────────────────────────────────────────
            $servicio = new Servicios([
                'placa'               => $placa,
                'id_tipo_servicio'    => (int)$_POST['id_tipo_servicio'],
                'fecha_realizado'     => $_POST['fecha_realizado'],
                'km_al_servicio'      => $kmAlServicio,
                'km_proximo_servicio' => $kmProximo,
                'fecha_proximo'       => $fechaProximo,
                'observaciones'       => htmlspecialchars($_POST['observaciones'] ?? ''),
                'responsable'         => htmlspecialchars($_POST['responsable']   ?? '')
            ]);

            $servicio->crear();

            // Actualizar KM del vehículo si es mayor al actual
            $vehiculo = Vehiculos::find($placa);
            if ($vehiculo && $kmAlServicio > (int)$vehiculo->km_actuales) {
                Vehiculos::actualizarKm($placa, $kmAlServicio);
            }

            echo json_encode([
                'codigo'        => 1,
                'mensaje'       => 'Servicio registrado exitosamente',
                'km_proximo'    => $kmProximo,
                'fecha_proximo' => $fechaProximo
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al guardar servicio',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── ELIMINAR SERVICIO ────────────────────────────────────────────────────
    public static function eliminarServicioAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');

        $id = (int)($_POST['id_servicio'] ?? 0);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID inválido'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $servicio = Servicios::find($id);

            if (!$servicio) {
                http_response_code(404);
                echo json_encode(
                    ['codigo' => 0, 'mensaje' => 'Servicio no encontrado'],
                    JSON_UNESCAPED_UNICODE
                );
                return;
            }

            $servicio->eliminar();

            echo json_encode(
                ['codigo' => 1, 'mensaje' => 'Servicio eliminado'],
                JSON_UNESCAPED_UNICODE
            );
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al eliminar servicio',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── TIPOS DE REPARACIÓN ──────────────────────────────────────────────────
    public static function tiposReparacionAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');
        try {
            $tipos = TiposReparacion::traerTodos();
            echo json_encode(['codigo' => 1, 'datos' => $tipos], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error'], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── GUARDAR REPARACIÓN ───────────────────────────────────────────────────
    public static function guardarReparacionAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');

        $placa = strtoupper(trim(htmlspecialchars($_POST['placa'] ?? '')));

        if (!$placa) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Placa requerida'], JSON_UNESCAPED_UNICODE);
            return;
        }

        foreach (['id_tipo_reparacion', 'descripcion', 'fecha_inicio', 'km_al_momento'] as $campo) {
            if (empty($_POST[$campo])) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "El campo '{$campo}' es obligatorio"], JSON_UNESCAPED_UNICODE);
                return;
            }
        }

        try {
            $idTipo  = (int)$_POST['id_tipo_reparacion'];
            $forzar  = !empty($_POST['forzar']) && $_POST['forzar'] === '1';

            // ── Bloqueo duro: ya existe una en proceso del mismo tipo ─────────────
            if (Reparaciones::existeEnProcesoPorTipo($placa, $idTipo)) {
                echo json_encode([
                    'codigo'       => 0,
                    'mensaje'      => 'Ya existe una reparación del mismo tipo en proceso. Debe finalizarla antes de registrar una nueva.',
                    'bloqueo_duro' => true
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            // ── Advertencia: última reparación finalizada hace menos de 30 días ───
            if (!$forzar) {
                $ultimaRep = Reparaciones::traerUltimaPorTipo($placa, $idTipo);
                if ($ultimaRep) {
                    $diasDesdeUltima = (int)((strtotime('now') - strtotime($ultimaRep['fecha_inicio'])) / 86400);
                    if ($diasDesdeUltima < 30) {
                        echo json_encode([
                            'codigo'       => 2,
                            'mensaje'      => "La última reparación de este tipo fue hace {$diasDesdeUltima} día(s). ¿Está seguro que desea registrar otra?",
                            'dias'         => $diasDesdeUltima,
                            'ultimo_km'    => $ultimaRep['km_al_momento'],
                            'bloqueo_duro' => false
                        ], JSON_UNESCAPED_UNICODE);
                        return;
                    }
                }
            }

            // ── Guardar ───────────────────────────────────────────────────────────
            $reparacion = new Reparaciones([
                'placa'              => $placa,
                'id_tipo_reparacion' => $idTipo,
                'descripcion'        => htmlspecialchars($_POST['descripcion']),
                'fecha_inicio'       => $_POST['fecha_inicio'],
                'fecha_fin'          => $_POST['fecha_fin']       ?? null,
                'km_al_momento'      => (int)$_POST['km_al_momento'],
                'costo'              => $_POST['costo']           ?? null,
                'proveedor'          => htmlspecialchars($_POST['proveedor']    ?? ''),
                'responsable'        => htmlspecialchars($_POST['responsable']  ?? ''),
                'estado'             => $_POST['estado']          ?? 'En proceso',
                'observaciones'      => htmlspecialchars($_POST['observaciones'] ?? '')
            ]);

            $reparacion->crear();

            $vehiculo = Vehiculos::find($placa);
            if ($vehiculo) {
                if ($_POST['estado'] === 'En proceso') {
                    $vehiculo->estado = 'Taller';
                } elseif ($_POST['estado'] === 'Finalizada') {
                    $vehiculo->estado = 'Alta';
                }
                $vehiculo->actualizar();
            }

            echo json_encode(['codigo' => 1, 'mensaje' => 'Reparación registrada exitosamente'], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al guardar reparación',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── ELIMINAR REPARACIÓN ──────────────────────────────────────────────────
    public static function eliminarReparacionAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');

        $id = (int)($_POST['id_reparacion'] ?? 0);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID inválido'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $reparacion = Reparaciones::find($id);

            if (!$reparacion) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Reparación no encontrada'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $reparacion->eliminar();

            $enProceso = Reparaciones::contarEnProceso($reparacion->placa);
            if ($enProceso === 0) {
                $vehiculo = Vehiculos::find($reparacion->placa);
                if ($vehiculo) {
                    $vehiculo->estado = 'Alta';
                    $vehiculo->actualizar();
                }
            }
            echo json_encode(['codigo' => 1, 'mensaje' => 'Reparación eliminada'], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al eliminar', 'detalle' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── MODIFICAR REPARACIÓN ─────────────────────────────────────────────────
    public static function modificarReparacionAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');

        $id = (int)($_POST['id_reparacion'] ?? 0);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID inválido'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $reparacion = Reparaciones::find($id);

            if (!$reparacion) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Reparación no encontrada'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $reparacion->sincronizar([
                'id_tipo_reparacion' => (int)$_POST['id_tipo_reparacion'],
                'descripcion'        => htmlspecialchars($_POST['descripcion']        ?? ''),
                'fecha_inicio'       => $_POST['fecha_inicio']                        ?? $reparacion->fecha_inicio,
                'fecha_fin'          => !empty($_POST['fecha_fin'])  ? $_POST['fecha_fin']   : null,
                'km_al_momento'      => (int)($_POST['km_al_momento']                 ?? 0),
                'costo'              => !empty($_POST['costo'])      ? $_POST['costo']       : null,
                'proveedor'          => htmlspecialchars($_POST['proveedor']          ?? ''),
                'responsable'        => htmlspecialchars($_POST['responsable']        ?? ''),
                'estado'             => $_POST['estado']                              ?? $reparacion->estado,
                'observaciones'      => htmlspecialchars($_POST['observaciones']      ?? ''),
            ]);

            $reparacion->actualizar();

            $vehiculo = Vehiculos::find($reparacion->placa);
            if ($vehiculo) {
                if ($reparacion->estado === 'En proceso') {
                    $vehiculo->estado = 'Taller';
                } else {
                    $enProceso = Reparaciones::contarEnProceso($reparacion->placa);
                    $vehiculo->estado = $enProceso > 0 ? 'Taller' : 'Alta';
                }
                $vehiculo->actualizar();
            }

            echo json_encode(['codigo' => 1, 'mensaje' => 'Reparación actualizada exitosamente'], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al modificar reparación',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
