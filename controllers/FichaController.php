<?php

namespace Controllers;

use Exception;
use Model\Vehiculos;
use Model\OrdenesServicio;
use Model\OrdenServicioItems;
use Model\Reparaciones;
use Model\TiposServicio;
use Model\TiposReparacion;
use Model\Seguros;
use Model\ActiveRecord;
use Model\Accidentes;
use MVC\Router;

class FichaController
{
    // ── FICHA COMPLETA ────────────────────────────────────────────────────────
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

            // ── Motivo del taller ─────────────────────────────────────────
            $motivoTaller = null;
            if ($vehiculo['estado'] === 'Taller') {
                if (OrdenesServicio::existeEnProceso($placa)) {
                    $motivoTaller = 'Servicio';
                } elseif (Reparaciones::contarEnProceso($placa) > 0) {
                    $motivoTaller = 'Reparación';
                }
            }

            $urlBase = rtrim($_ENV['SFTP_PUBLIC_URL'] ?? '', '/');
            $vehiculo['foto_url']            = $vehiculo['foto_frente']     ? "{$urlBase}/{$vehiculo['foto_frente']}"     : null;
            $vehiculo['foto_lateral_url']    = $vehiculo['foto_lateral']    ? "{$urlBase}/{$vehiculo['foto_lateral']}"    : null;
            $vehiculo['foto_trasera_url']    = $vehiculo['foto_trasera']    ? "{$urlBase}/{$vehiculo['foto_trasera']}"    : null;
            $vehiculo['pdf_url']             = $vehiculo['tarjeta_pdf']     ? "{$urlBase}/{$vehiculo['tarjeta_pdf']}"     : null;
            $vehiculo['cert_inventario_url'] = $vehiculo['cert_inventario'] ? "{$urlBase}/{$vehiculo['cert_inventario']}" : null;
            $vehiculo['cert_sicoin_url']     = $vehiculo['cert_sicoin']     ? "{$urlBase}/{$vehiculo['cert_sicoin']}"     : null;

            $ordenes        = OrdenesServicio::traerPorPlaca($placa);
            $ordenEnProceso = OrdenesServicio::traerEnProceso($placa);

            $proximoServicio = OrdenesServicio::traerProximoServicio($placa);
            $alertaKm        = false;
            $alertaAmarilla  = false;

            if ($proximoServicio && !empty($proximoServicio['km_proximo'])) {
                $kmActual   = (int)$vehiculo['km_actuales'];
                $kmProximo  = (int)$proximoServicio['km_proximo'];
                $diferencia = $kmProximo - $kmActual;

                if ($diferencia <= 0) {
                    $alertaKm = true;
                } elseif ($diferencia <= 500) {
                    $alertaAmarilla = true;
                }
            }

            $reparaciones = Reparaciones::traerPorPlaca($placa);

            Seguros::actualizarEstadosVencidos($placa);
            $seguros = Seguros::traerPorPlaca($placa);
            foreach ($seguros as &$s) {
                $s['pdf_poliza_url'] = $s['archivo_poliza']
                    ? "{$urlBase}/{$s['archivo_poliza']}"
                    : null;
            }
            unset($s);

            $accidentes = Accidentes::traerPorPlaca($placa);
            foreach ($accidentes as &$a) {
                $a['foto_1_url']       = $a['archivo_foto_1']  ? "{$urlBase}/{$a['archivo_foto_1']}"  : null;
                $a['foto_2_url']       = $a['archivo_foto_2']  ? "{$urlBase}/{$a['archivo_foto_2']}"  : null;
                $a['foto_3_url']       = $a['archivo_foto_3']  ? "{$urlBase}/{$a['archivo_foto_3']}"  : null;
                $a['foto_4_url']       = $a['archivo_foto_4']  ? "{$urlBase}/{$a['archivo_foto_4']}"  : null;
                $a['informe_url']      = $a['archivo_informe'] ? "{$urlBase}/{$a['archivo_informe']}" : null;
                $a['costo_danos']      = $a['costo_estimado']    ?? null;
                $a['costo_reparacion'] = $a['costo_real']        ?? null;
                $a['no_expediente']    = $a['numero_expediente'] ?? null;
                $a['estado']           = $a['estado_caso']       ?? null;
            }
            unset($a);

            echo json_encode([
                'codigo'           => 1,
                'vehiculo'         => $vehiculo,
                'ordenes'          => $ordenes,
                'orden_en_proceso' => $ordenEnProceso,
                'reparaciones'     => $reparaciones,
                'seguros'          => $seguros,
                'accidentes'       => $accidentes,
                'proximo_servicio' => $proximoServicio,
                'alerta_km'        => $alertaKm,
                'alerta_amarilla'  => $alertaAmarilla,
                'motivo_taller'    => $motivoTaller,
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al obtener ficha',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── TIPOS DE SERVICIO ─────────────────────────────────────────────────────
    public static function tiposServicioAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');

        $tipoVehiculo = strtolower(trim($_GET['tipo_vehiculo'] ?? ''));

        try {
            $sql = "SELECT 
                    ts.id_tipo_servicio,
                    ts.nombre,
                    ts.descripcion,
                    ts.intervalo_km AS intervalo_km_base,
                    COALESCE(
                        (SELECT tsi.intervalo_km
                         FROM tipos_servicio_intervalo tsi
                         WHERE tsi.id_tipo_servicio = ts.id_tipo_servicio
                           AND LOWER(tsi.tipo_vehiculo) = ?
                         LIMIT 1),
                        ts.intervalo_km
                    ) AS intervalo_km
                FROM tipos_servicio ts
                ORDER BY ts.nombre ASC";

            $tipos = \Model\OrdenesServicio::fetchArray($sql, [$tipoVehiculo]);

            echo json_encode([
                'codigo' => 1,
                'tipos'  => $tipos
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── CREAR ORDEN ───────────────────────────────────────────────────────────
    public static function crearOrdenAPI(): void
    {
        $placa       = trim($_POST['placa']         ?? '');
        $fecha       = trim($_POST['fecha_ingreso'] ?? '');
        $km          = intval($_POST['km_al_ingreso'] ?? 0);
        $responsable = trim($_POST['responsable']   ?? '');
        $obs         = trim($_POST['observaciones'] ?? '');

        if (!$placa || !$fecha || !$km) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Datos incompletos']);
            return;
        }

        if (OrdenesServicio::existeEnProceso($placa)) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Ya existe una orden en proceso para este vehículo']);
            return;
        }

        // ── Guardar KM anterior antes de actualizar ───────────────────────
        $vehiculoAntes = Vehiculos::find($placa);
        if (!$vehiculoAntes) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Vehículo no encontrado']);
            return;
        }
        $kmAnterior = (int)$vehiculoAntes->km_actuales;

        $orden = new OrdenesServicio();
        $orden->placa         = $placa;
        $orden->fecha_ingreso = $fecha;
        $orden->km_al_ingreso = $km;
        $orden->km_anterior   = $kmAnterior;
        $orden->responsable   = $responsable;
        $orden->observaciones = $obs;
        $orden->estado        = 'En proceso';

        $resultado = $orden->guardar();
        if (!$resultado) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al crear la orden']);
            return;
        }

        // ── Cambiar estado y actualizar KM del vehículo ───────────────────
        Vehiculos::consultarSQL("UPDATE vehiculos SET estado = 'Taller', km_actuales = {$km} WHERE placa = '{$placa}'");

        // Obtener la orden recién creada
        $ordenCreada = OrdenesServicio::traerEnProceso($placa);
        if (!$ordenCreada) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al recuperar la orden creada']);
            return;
        }

        $idOrden = (int)$ordenCreada['id_orden'];

        echo json_encode([
            'codigo'   => 1,
            'mensaje'  => 'Orden creada correctamente',
            'id_orden' => $idOrden,
            'orden'    => [
                'id_orden'      => $idOrden,
                'placa'         => $placa,
                'fecha_ingreso' => $ordenCreada['fecha_ingreso'],
                'km_al_ingreso' => $ordenCreada['km_al_ingreso'],
                'km_anterior'   => $kmAnterior,
                'responsable'   => $ordenCreada['responsable'],
                'observaciones' => $ordenCreada['observaciones'],
                'estado'        => 'En proceso',
                'total_items'   => 0,
                'items'         => []
            ]
        ]);
    }

    // ── AGREGAR ITEM A ORDEN ──────────────────────────────────────────────────
    public static function agregarItemAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');

        $idOrden        = (int)($_POST['id_orden']         ?? 0);
        $idTipoServicio = (int)($_POST['id_tipo_servicio'] ?? 0);

        if (!$idOrden || !$idTipoServicio) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Datos incompletos'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            if (OrdenServicioItems::existeEnOrden($idOrden, $idTipoServicio)) {
                echo json_encode([
                    'codigo'  => 0,
                    'mensaje' => 'Este tipo de servicio ya fue agregado a la orden.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $resultado    = 'Realizado';
            $kmIngreso    = null;
            $fechaProximo = null;

            $tipo  = TiposServicio::find($idTipoServicio);
            $orden = OrdenesServicio::find($idOrden);

            if ($tipo && $orden) {
                // ── Obtener vehículo para saber su tipo ──────────────────
                $vehiculoActual = Vehiculos::find($orden->placa);
                $tipoVehiculo   = $vehiculoActual ? strtolower(trim($vehiculoActual->tipo)) : '';
                $kmBase         = $vehiculoActual
                    ? (int)$vehiculoActual->km_actuales
                    : (int)$orden->km_al_ingreso;

                // ── Buscar intervalo específico para este tipo de vehículo
                // Si no existe, usar el intervalo genérico del tipo de servicio
                $sqlIntervalo = "SELECT COALESCE(
                                (SELECT tsi.intervalo_km
                                 FROM tipos_servicio_intervalo tsi
                                 WHERE tsi.id_tipo_servicio = ?
                                   AND LOWER(tsi.tipo_vehiculo) = ?
                                 LIMIT 1),
                                ts.intervalo_km
                             ) AS intervalo_km
                             FROM tipos_servicio ts
                             WHERE ts.id_tipo_servicio = ?";

                $intervaloRow = OrdenesServicio::fetchFirst(
                    $sqlIntervalo,
                    [$idTipoServicio, $tipoVehiculo, $idTipoServicio]
                );
                $intervaloKm = (int)($intervaloRow['intervalo_km'] ?? 0);

                if ($intervaloKm > 0) {
                    $kmIngreso = $kmBase + $intervaloKm;
                }

                if (!empty($tipo->intervalo_dias)) {
                    $fechaProximo = date(
                        'Y-m-d',
                        strtotime($orden->fecha_ingreso . " +{$tipo->intervalo_dias} days")
                    );
                }
            }

            // ── Respetar km_proximo si el mecánico lo envió manualmente ──
            if (!empty($_POST['km_proximo'])) {
                $kmIngreso = (int)$_POST['km_proximo'];
            }
            if (!empty($_POST['fecha_proximo'])) {
                $fechaProximo = $_POST['fecha_proximo'];
            }

            $item = new OrdenServicioItems([
                'id_orden'         => $idOrden,
                'id_tipo_servicio' => $idTipoServicio,
                'resultado'        => $resultado,
                'km_proximo'       => $kmIngreso,
                'fecha_proximo'    => $fechaProximo,
                'observacion'      => htmlspecialchars($_POST['observacion'] ?? '')
            ]);
            $item->crear();
            $idItemCreado = OrdenServicioItems::ultimoDeOrden($idOrden, $idTipoServicio);

            echo json_encode([
                'codigo'        => 1,
                'mensaje'       => 'Item agregado',
                'id_item'       => $idItemCreado,
                'km_proximo'    => $kmIngreso,
                'fecha_proximo' => $fechaProximo
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al agregar item',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }


    // ── ELIMINAR ITEM DE ORDEN ────────────────────────────────────────────────
    public static function eliminarItemAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');

        $id = (int)($_POST['id_item'] ?? 0);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID inválido'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $item = OrdenServicioItems::find($id);
            if (!$item) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Item no encontrado'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $item->eliminar();
            echo json_encode(['codigo' => 1, 'mensaje' => 'Item eliminado'], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al eliminar item',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── COMPLETAR ORDEN ───────────────────────────────────────────────────────
    public static function completarOrdenAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');

        $idOrden = (int)($_POST['id_orden'] ?? 0);

        if (!$idOrden) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID de orden requerido'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $orden = OrdenesServicio::find($idOrden);

            if (!$orden) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Orden no encontrada'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Verificar que tenga al menos un item
            $ordenConItems = OrdenesServicio::traerConItems($idOrden);
            if (empty($ordenConItems['items'])) {
                echo json_encode([
                    'codigo'  => 0,
                    'mensaje' => 'La orden debe tener al menos un servicio registrado antes de completarse.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $placa = $orden->placa;

            // Completar la orden
            $orden->sincronizar([
                'estado'           => 'Completado',
                'fecha_completado' => date('Y-m-d')
            ]);
            $orden->actualizar();

            // Cambiar estado del vehículo a Alta
            Vehiculos::consultarSQL("UPDATE vehiculos SET estado = 'Alta' WHERE placa = '{$placa}'");

            echo json_encode([
                'codigo'  => 1,
                'mensaje' => 'Orden de servicio completada exitosamente'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al completar orden',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── ELIMINAR ORDEN COMPLETA ───────────────────────────────────────────────
    public static function eliminarOrdenAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');

        $idOrden = (int)($_POST['id_orden'] ?? 0);

        if (!$idOrden) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID de orden requerido'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $orden = OrdenesServicio::find($idOrden);

            if (!$orden) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Orden no encontrada'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $placa      = $orden->placa;
            $kmAnterior = (int)$orden->km_anterior;

            // ── Eliminar items primero ────────────────────────────────────
            Vehiculos::consultarSQL("DELETE FROM orden_servicio_items WHERE id_orden = {$idOrden}");

            // ── Eliminar la orden ─────────────────────────────────────────
            Vehiculos::consultarSQL("DELETE FROM ordenes_servicio WHERE id_orden = {$idOrden}");

            // ── Verificar si quedan otras órdenes en proceso ──────────────
            $otraOrdenEnProceso = OrdenesServicio::existeEnProceso($placa);

            if (!$otraOrdenEnProceso) {
                // ── Revertir KM al valor anterior y volver a Alta ─────────
                Vehiculos::consultarSQL(
                    "UPDATE vehiculos SET estado = 'Alta', km_actuales = {$kmAnterior} WHERE placa = '{$placa}'"
                );
            }

            echo json_encode([
                'codigo'  => 1,
                'mensaje' => 'Orden eliminada correctamente'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al eliminar orden',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── OBTENER ORDEN CON ITEMS ───────────────────────────────────────────────
    public static function obtenerOrdenAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');

        $idOrden = (int)($_GET['id'] ?? 0);

        if (!$idOrden) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID requerido'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $orden = OrdenesServicio::traerConItems($idOrden);

            if (!$orden) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Orden no encontrada'], JSON_UNESCAPED_UNICODE);
                return;
            }

            echo json_encode(['codigo' => 1, 'datos' => $orden], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al obtener orden',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── TIPOS DE REPARACIÓN ───────────────────────────────────────────────────
    public static function tiposReparacionAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');
        try {
            $tipos = TiposReparacion::traerTodos();
            $tipos = TiposReparacion::traerTodos();
            error_log("tipos count: " . count($tipos));
            error_log("primer tipo: " . json_encode($tipos[0] ?? 'vacio'));
            echo json_encode(['codigo' => 1, 'datos' => $tipos], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error'], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── GUARDAR REPARACIÓN ────────────────────────────────────────────────────
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

        $fechaInicio       = $_POST['fecha_inicio']        ?? '';
        $fechaFin          = !empty($_POST['fecha_fin'])    ? $_POST['fecha_fin'] : null;
        $fechaIndefinida   = (int)($_POST['fecha_fin_indefinida'] ?? 0);
        $esExterna         = (int)($_POST['es_externa']     ?? 0);
        $destino           = htmlspecialchars($_POST['destino_externo'] ?? '');
        $costo             = !empty($_POST['costo'])         ? $_POST['costo']        : null;
        $proveedor         = htmlspecialchars($_POST['proveedor']       ?? '');
        $responsable       = htmlspecialchars($_POST['responsable']     ?? '');
        $observaciones     = htmlspecialchars($_POST['observaciones']   ?? '');
        // ── Determinar estado automáticamente ────────────────────────────────────
        if ($esExterna) {
            $estado = 'Externa';
        } elseif (!$fechaIndefinida && $fechaFin && $fechaFin <= date('Y-m-d')) {
            $estado = 'Finalizada';
        } else {
            $estado = 'En proceso';
        }
        $itemsJson         = $_POST['items']                ?? '[]';
        if (!$fechaInicio) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Fecha de inicio requerida'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $items = json_decode($itemsJson, true);
        if (!$items || !count($items)) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Agrega al menos un ítem a la reparación'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $reparacion = new Reparaciones([
                'placa'               => $placa,
                'fecha_inicio'        => $fechaInicio,
                'fecha_fin'           => $fechaFin,
                'fecha_fin_indefinida' => $fechaIndefinida,
                'es_externa'          => $esExterna,
                'destino_externo'     => $destino,
                'costo'               => $costo,
                'proveedor'           => $proveedor,
                'responsable'         => $responsable,
                'observaciones'       => $observaciones,
                'estado'              => $estado,
            ]);
            error_log("objeto reparacion es_externa: " . var_export($reparacion->es_externa, true));
            error_log("objeto reparacion fecha_fin_indefinida: " . var_export($reparacion->fecha_fin_indefinida, true));
            error_log("objeto reparacion estado: " . var_export($reparacion->estado, true));
            $reparacion->guardar();
            // ── LOG TEMPORAL ──────────────────────────────────────────────────────────────
            $verificar = Reparaciones::fetchFirst(
                "SELECT es_externa, fecha_fin_indefinida, estado FROM reparaciones WHERE placa = ? ORDER BY id_reparacion DESC LIMIT 1",
                [$placa]
            );
            error_log("BD después de guardar es_externa: " . var_export($verificar['es_externa'], true));
            error_log("BD después de guardar fecha_fin_indefinida: " . var_export($verificar['fecha_fin_indefinida'], true));
            error_log("BD después de guardar estado: " . var_export($verificar['estado'], true));
            // ─────────────────────────────────────────────────────────────────────────────

            // Obtener el id insertado
            $idReparacion = (int)Reparaciones::fetchFirst(
                "SELECT id_reparacion FROM reparaciones WHERE placa = ? ORDER BY id_reparacion DESC LIMIT 1",
                [$placa]
            )['id_reparacion'];

            // Guardar items
            foreach ($items as $item) {
                $idCategoria = (int)($item['id_categoria'] ?? 0);
                $especifico  = htmlspecialchars($item['especifico'] ?? '');
                $costItem    = !empty($item['costo']) ? $item['costo'] : null;
                if (!$idCategoria || !$especifico) continue;

                $ri = new \Model\ReparacionItems([
                    'id_reparacion' => $idReparacion,
                    'id_categoria'  => $idCategoria,
                    'especifico'    => $especifico,
                    'costo'         => $costItem,
                ]);
                $ri->guardar();
            }

            // Actualizar estado vehículo
            $estadoVehiculo = $esExterna ? 'Baja' : 'Taller';
            Vehiculos::consultarSQL(
                "UPDATE vehiculos SET estado = '{$estadoVehiculo}' WHERE placa = '{$placa}'"
            );

            // Si es externa, actualizar también el motivo en observaciones del vehículo
            if ($esExterna && $destino) {
                $destinoEsc = addslashes($destino);
                Vehiculos::consultarSQL(
                    "UPDATE vehiculos SET estado = 'Baja' WHERE placa = '{$placa}'"
                );
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

    // ── ELIMINAR REPARACIÓN ───────────────────────────────────────────────────
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
            $ordenEnProceso = OrdenesServicio::existeEnProceso($reparacion->placa);

            $vehiculo = Vehiculos::find($reparacion->placa);
            if ($vehiculo && $enProceso === 0 && !$ordenEnProceso) {
                $vehiculo->estado = 'Alta';
                $vehiculo->actualizar();
            }

            echo json_encode(['codigo' => 1, 'mensaje' => 'Reparación eliminada'], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al eliminar',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── MODIFICAR REPARACIÓN ──────────────────────────────────────────────────
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

            $esExterna       = (int)($_POST['es_externa']          ?? 0);
            $fechaIndefinida = (int)($_POST['fecha_fin_indefinida'] ?? 0);
            $itemsJson       = $_POST['items'] ?? '[]';
            $items           = json_decode($itemsJson, true) ?? [];

            $reparacion->sincronizar([
                'fecha_inicio'         => $_POST['fecha_inicio']     ?? $reparacion->fecha_inicio,
                'fecha_fin'            => !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null,
                'fecha_fin_indefinida' => $fechaIndefinida,
                'es_externa'           => $esExterna,
                'destino_externo'      => htmlspecialchars($_POST['destino_externo'] ?? ''),
                'costo'                => !empty($_POST['costo']) ? $_POST['costo'] : null,
                'proveedor'            => htmlspecialchars($_POST['proveedor']     ?? ''),
                'responsable'          => htmlspecialchars($_POST['responsable']   ?? ''),
                'observaciones'        => htmlspecialchars($_POST['observaciones'] ?? ''),
                'estado' => $esExterna
                    ? 'Externa'
                    : (!$fechaIndefinida && !empty($_POST['fecha_fin']) && $_POST['fecha_fin'] <= date('Y-m-d')
                        ? 'Finalizada'
                        : 'En proceso'),
            ]);
            $reparacion->actualizar();

            // Reemplazar items — eliminar los anteriores y guardar los nuevos
            Vehiculos::consultarSQL("DELETE FROM reparacion_items WHERE id_reparacion = {$id}");

            foreach ($items as $item) {
                $idCategoria = (int)($item['id_categoria'] ?? 0);
                $especifico  = htmlspecialchars($item['especifico'] ?? '');
                $costItem    = !empty($item['costo']) ? $item['costo'] : null;
                if (!$idCategoria || !$especifico) continue;

                $ri = new \Model\ReparacionItems([
                    'id_reparacion' => $id,
                    'id_categoria'  => $idCategoria,
                    'especifico'    => $especifico,
                    'costo'         => $costItem,
                ]);
                $ri->guardar();
            }

            // Actualizar estado vehículo
            $placa = $reparacion->placa;
            $enProceso = Reparaciones::contarEnProceso($placa);
            $ordenEnProceso = OrdenesServicio::existeEnProceso($placa);

            if ($esExterna) {
                Vehiculos::consultarSQL("UPDATE vehiculos SET estado = 'Baja' WHERE placa = '{$placa}'");
            } elseif ($enProceso > 0 || $ordenEnProceso) {
                Vehiculos::consultarSQL("UPDATE vehiculos SET estado = 'Taller' WHERE placa = '{$placa}'");
            } else {
                Vehiculos::consultarSQL("UPDATE vehiculos SET estado = 'Alta' WHERE placa = '{$placa}'");
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

    public static function alertasOrdenAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');

        $placa = strtoupper(trim($_GET['placa'] ?? ''));
        if (!$placa) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Placa requerida'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $vehiculo = Vehiculos::find($placa);
            if (!$vehiculo) {
                echo json_encode(['codigo' => 0, 'mensaje' => 'Vehículo no encontrado'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // ── Usar km_override si viene del JS (KM ingresado por el mecánico) ──
            $kmOverride = isset($_GET['km_override']) ? (int)$_GET['km_override'] : 0;
            $kmActual   = $kmOverride > 0 ? $kmOverride : (int)$vehiculo->km_actuales;

            $servicios = OrdenesServicio::traerTodosProximosServicios($placa);

            $alertas = [];
            foreach ($servicios as $s) {
                $kmProximo  = (int)$s['km_proximo'];
                $diferencia = $kmProximo - $kmActual;

                if ($diferencia <= 0) {
                    $nivel = 'rojo';
                    $diff  = abs($diferencia);
                    $texto = "se pasó por {$diff} km";
                } elseif ($diferencia <= 500) {
                    $nivel = 'amarillo';
                    $texto = "faltan {$diferencia} km";
                } else {
                    $nivel = 'verde';
                    $texto = "faltan {$diferencia} km";
                }

                $alertas[] = [
                    'tipo_nombre' => $s['tipo_nombre'],
                    'km_proximo'  => $kmProximo,
                    'diferencia'  => $diferencia,
                    'nivel'       => $nivel,
                    'texto'       => $texto
                ];
            }

            usort($alertas, function ($a, $b) {
                $orden = ['rojo' => 0, 'amarillo' => 1, 'verde' => 2];
                return $orden[$a['nivel']] <=> $orden[$b['nivel']];
            });

            echo json_encode([
                'codigo'      => 1,
                'km_actual'   => $kmActual,
                'alertas'     => $alertas,
                'hay_alertas' => count(array_filter(
                    $alertas,
                    fn($a) => in_array($a['nivel'], ['rojo', 'amarillo'])
                )) > 0
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    public static function hojaVidaAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');

        $placa = strtoupper(trim($_GET['placa'] ?? ''));
        if (!$placa) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Placa requerida'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $grupos = OrdenesServicio::traerHistorialAgrupado($placa);

            // Calcular cumplimiento por grupo
            $resultado = [];
            foreach ($grupos as $tipo => $registros) {
                $items = [];
                $kmProgramadoAnterior = null;

                foreach ($registros as $i => $r) {
                    $kmReal     = (int)$r['km_al_servicio'];
                    $kmProximo  = (int)($r['km_proximo_servicio'] ?? 0);

                    if ($i === 0 || $kmProgramadoAnterior === null) {
                        $cumplimiento = 'primer_registro';
                        $diferencia   = 0;
                    } else {
                        $diferencia   = $kmReal - $kmProgramadoAnterior;
                        if ($diferencia > 0) {
                            $cumplimiento = 'tarde';
                        } elseif ($diferencia < 0) {
                            $cumplimiento = 'antes';
                        } else {
                            $cumplimiento = 'exacto';
                        }
                    }

                    $items[] = [
                        'fecha'         => $r['fecha_realizado'],
                        'km_real'       => $kmReal,
                        'km_proximo'    => $kmProximo > 0 ? $kmProximo : null,
                        'responsable'   => $r['responsable'] ?? '',
                        'observacion'   => $r['obs_item'] ?? $r['observaciones'] ?? '',
                        'cumplimiento'  => $cumplimiento,
                        'diferencia'    => abs($diferencia),
                    ];

                    $kmProgramadoAnterior = $kmProximo > 0 ? $kmProximo : null;
                }

                $resultado[] = [
                    'tipo'   => $tipo,
                    'total'  => count($items),
                    'items'  => $items,
                ];
            }

            echo json_encode([
                'codigo' => 1,
                'grupos' => $resultado
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    public static function hojaVidaReparacionesAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');

        $placa = strtoupper(trim($_GET['placa'] ?? ''));
        if (!$placa) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Placa requerida'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $grupos = Reparaciones::traerHistorialAgrupado($placa);

            $resultado = [];
            foreach ($grupos as $categoria => $registros) {
                $costoTotal = array_sum(array_column($registros, 'costo_item'));
                $resultado[] = [
                    'tipo'        => $categoria,
                    'total'       => count($registros),
                    'costo_total' => $costoTotal,
                    'items'       => array_map(fn($r) => [
                        'fecha_inicio'         => $r['fecha_inicio'],
                        'fecha_fin'            => $r['fecha_fin'] ?? null,
                        'fecha_fin_indefinida' => (int)($r['fecha_fin_indefinida'] ?? 0),
                        'especifico'           => $r['especifico']       ?? '',
                        'costo_item'           => $r['costo_item']       ? (float)$r['costo_item'] : null,
                        'estado'               => $r['estado']           ?? '',
                        'es_externa'           => (int)($r['es_externa'] ?? 0),
                        'destino_externo'      => $r['destino_externo']  ?? '',
                        'proveedor'            => $r['proveedor']        ?? '',
                        'responsable'          => $r['responsable']      ?? '',
                        'observaciones'        => $r['observaciones']    ?? '',
                    ], $registros),
                ];
            }

            echo json_encode(['codigo' => 1, 'grupos' => $resultado], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    public static function hojaVidaAccidentesAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');

        $placa = strtoupper(trim($_GET['placa'] ?? ''));
        if (!$placa) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Placa requerida'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $urlBase = rtrim($_ENV['SFTP_PUBLIC_URL'] ?? '', '/');
            $grupos  = Accidentes::traerHistorialAgrupado($placa);

            $resultado = [];
            foreach ($grupos as $tipo => $registros) {
                $costoTotal = array_sum(array_column($registros, 'costo_real'));
                $resultado[] = [
                    'tipo'        => $tipo,
                    'total'       => count($registros),
                    'costo_total' => $costoTotal,
                    'items'       => array_map(fn($r) => [
                        'fecha_accidente'   => $r['fecha_accidente'],
                        'lugar'             => $r['lugar']                ?? '',
                        'descripcion'       => $r['descripcion']          ?? '',
                        'conductor'         => $r['conductor_responsable'] ?? '',
                        'costo_estimado'    => $r['costo_estimado'] ? (float)$r['costo_estimado'] : null,
                        'costo_real'        => $r['costo_real']     ? (float)$r['costo_real']     : null,
                        'estado'            => $r['estado_caso']          ?? '',
                        'numero_expediente' => $r['numero_expediente']    ?? '',
                        'observaciones'     => $r['observaciones']        ?? '',
                        'foto_1_url'        => !empty($r['archivo_foto_1']) ? "{$urlBase}/{$r['archivo_foto_1']}" : null,
                        'foto_2_url'        => !empty($r['archivo_foto_2']) ? "{$urlBase}/{$r['archivo_foto_2']}" : null,
                        'foto_3_url'        => !empty($r['archivo_foto_3']) ? "{$urlBase}/{$r['archivo_foto_3']}" : null,
                        'foto_4_url'        => !empty($r['archivo_foto_4']) ? "{$urlBase}/{$r['archivo_foto_4']}" : null,
                    ], $registros),
                ];
            }

            echo json_encode([
                'codigo' => 1,
                'grupos' => $resultado
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    public static function guardarCategoriaReparacionAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');

        $nombre = trim(htmlspecialchars($_POST['nombre'] ?? ''));
        if (!$nombre) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Nombre requerido'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            // Si ya existe, devolver el id existente
            $existe = TiposReparacion::fetchFirst(
                "SELECT id_tipo_reparacion FROM tipos_reparacion WHERE nombre = ?",
                [$nombre]
            );
            if ($existe) {
                echo json_encode([
                    'codigo'  => 1,
                    'mensaje' => 'Categoría ya existente',
                    'id'      => (int)$existe['id_tipo_reparacion']
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $nueva = new TiposReparacion(['nombre' => $nombre, 'activo' => 1]);
            $nueva->guardar();

            $id = (int)TiposReparacion::fetchFirst(
                "SELECT id_tipo_reparacion FROM tipos_reparacion WHERE nombre = ? ORDER BY id_tipo_reparacion DESC LIMIT 1",
                [$nombre]
            )['id_tipo_reparacion'];

            echo json_encode([
                'codigo'  => 1,
                'mensaje' => 'Categoría creada correctamente',
                'id'      => $id
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al guardar categoría',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public static function ultimaOrdenCompletadaAPI(Router $router)
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
            $sql = "SELECT 
                    i.id_tipo_servicio,
                    ts.nombre AS tipo_nombre
                FROM ordenes_servicio o
                JOIN orden_servicio_items i ON i.id_orden = o.id_orden
                JOIN tipos_servicio ts ON ts.id_tipo_servicio = i.id_tipo_servicio
                WHERE o.placa = ?
                  AND o.estado = 'Completado'
                  AND i.resultado = 'Realizado'
                  AND o.id_orden = (
                      SELECT MAX(o2.id_orden)
                      FROM ordenes_servicio o2
                      WHERE o2.placa = ?
                        AND o2.estado = 'Completado'
                  )
                ORDER BY ts.nombre ASC";

            $items = OrdenesServicio::fetchArray($sql, [$placa, $placa]);

            if (!$items) {
                echo json_encode(['codigo' => 0, 'mensaje' => 'Sin historial'], JSON_UNESCAPED_UNICODE);
                return;
            }

            echo json_encode([
                'codigo' => 1,
                'items'  => $items
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }
}
