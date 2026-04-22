<?php

namespace Controllers;

use MVC\Router;
use Model\Solicitudes;
use Model\Vehiculos;

class SolicitudesController
{
    // ── API: crear solicitud (COMTE_PTN) ──────────────────────────────────────
    public static function crearAPI(): void
    {
        isAuthApi();
        getHeadersApi();

        $tipo  = $_POST['tipo']  ?? '';
        $placa = strtoupper(trim($_POST['placa'] ?? ''));
        $datos = $_POST['datos_cambio'] ?? null;

        if (!$tipo || !$placa) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Datos incompletos']);
            exit;
        }

        if (!in_array($tipo, ['modificacion', 'eliminacion'])) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Tipo inválido']);
            exit;
        }

        // ── Obtener valores actuales PRIMERO ──────────────────────────────────────
        $vehiculo = \Model\Vehiculos::find($placa);
        $datosConAnterior = null;

        if ($datos && $vehiculo) {
            $datosDecodificados = json_decode($datos, true);
            $datosConAnterior = [];
            foreach ($datosDecodificados as $campo => $valorNuevo) {
                if ($campo === 'tipo_solicitud') {
                    $datosConAnterior[$campo] = $valorNuevo;
                    continue;
                }
                // El JS ya manda {antes, ahora} — solo extraemos el ahora
                $valorFinal = is_array($valorNuevo) ? ($valorNuevo['ahora'] ?? $valorNuevo) : $valorNuevo;
                $datosConAnterior[$campo] = [
                    'antes' => mb_convert_encoding($vehiculo->$campo ?? '—', 'UTF-8', 'UTF-8'),
                    'ahora' => mb_convert_encoding(is_array($valorFinal) ? json_encode($valorFinal) : $valorFinal, 'UTF-8', 'UTF-8')
                ];
            }
        }

        // ── Validar campos duplicados ─────────────────────────────────────────────
        if ($datosConAnterior) {
            $camposSolicitados = array_keys(array_filter(
                $datosConAnterior,
                fn($k) => $k !== 'tipo_solicitud',
                ARRAY_FILTER_USE_KEY
            ));

            foreach ($camposSolicitados as $campo) {
                $yaExiste = Solicitudes::fetchArray("
                SELECT id FROM solicitudes 
                WHERE placa = '{$placa}' 
                AND estado = 'pendiente'
                AND datos_cambio LIKE '%\"{$campo}\"%'
                LIMIT 1
            ");
                if (!empty($yaExiste)) {
                    echo json_encode([
                        'codigo'  => 0,
                        'mensaje' => "Ya existe una solicitud pendiente para el campo '{$campo}'"
                    ]);
                    exit;
                }
            }
        }
        // ── Insertar solicitud ────────────────────────────────────────────────────
        $db = \Model\ActiveRecord::getDB();
        $stmt = $db->prepare("
        INSERT INTO solicitudes (tipo, placa, catalogo_solicitante, datos_cambio, fecha_solicitud)
        VALUES (?, ?, ?, ?, NOW())
    ");
        $stmt->execute([
            $tipo,
            $placa,
            $_SESSION['auth_user'],
            $datosConAnterior ? json_encode($datosConAnterior) : null
        ]);

        echo json_encode([
            'codigo'  => 1,
            'mensaje' => 'Solicitud enviada correctamente. El Comandante de Cía será notificado.'
        ]);
    }

    // ── API: listar solicitudes pendientes (COMTE_CIA) ────────────────────────
    public static function pendientesAPI(): void
    {
        isAuthApi();
        getHeadersApi();

        $rol = $_SESSION['auth_rol'] ?? '';
        if (!in_array($rol, ['COMTE_CIA', 'SUPERUSUARIO'])) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Sin permisos']);
            exit;
        }

        $pendientes = Solicitudes::pendientes();
        echo json_encode(['codigo' => 1, 'datos' => $pendientes], JSON_UNESCAPED_UNICODE);
    }

    // ── API: resolver solicitud (COMTE_CIA aprueba o rechaza) ─────────────────
    public static function resolverAPI(): void
    {
        isAuthApi();
        getHeadersApi();

        $rol = $_SESSION['auth_rol'] ?? '';
        if (!in_array($rol, ['COMTE_CIA', 'SUPERUSUARIO'])) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Sin permisos']);
            exit;
        }

        $id     = (int)($_POST['id']    ?? 0);
        $accion = $_POST['accion']      ?? '';
        $motivo = trim($_POST['motivo'] ?? '');

        if (!$id || !in_array($accion, ['aprobar', 'rechazar'])) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Datos incompletos']);
            exit;
        }

        if ($accion === 'rechazar' && !$motivo) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Debes indicar el motivo del rechazo']);
            exit;
        }

        $resultado = Solicitudes::fetchArray("
        SELECT * FROM solicitudes WHERE id = {$id} AND estado = 'pendiente' LIMIT 1
    ");

        if (empty($resultado)) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Solicitud no encontrada o ya resuelta']);
            exit;
        }

        $solicitud = $resultado[0];
        $db = \Model\ActiveRecord::getDB();

        // Campos que NO son columnas de la BD
        $camposExcluidos = ['tipo_solicitud', 'nombre_unidad', 'descripcion'];

        if ($accion === 'aprobar') {
            try {
                $db->beginTransaction();

                if ($solicitud['tipo'] === 'eliminacion') {
                    $stmt = $db->prepare("UPDATE vehiculos SET situacion = 'eliminado' WHERE placa = ?");
                    $stmt->execute([$solicitud['placa']]);
                } elseif ($solicitud['tipo'] === 'modificacion' && $solicitud['datos_cambio']) {
                    $datos = json_decode($solicitud['datos_cambio'], true);

                    if ($datos) {
                        $tipoSolicitud = $datos['tipo_solicitud'] ?? 'texto';

                        if ($tipoSolicitud === 'archivo') {
                            // Solicitud de archivo — no hay UPDATE automático
                            // El CIA deberá subir el archivo manualmente desde el formulario
                            // Solo marcamos como aprobada

                        } else {
                            // texto o unidad — ejecutar UPDATE
                            $campos = [];
                            $valores = [];

                            foreach ($datos as $campo => $info) {
                                // Saltar campos que no son columnas de BD
                                if (in_array($campo, $camposExcluidos)) continue;

                                $valor = is_array($info) ? ($info['ahora'] ?? '') : $info;
                                $campos[] = "{$campo} = ?";
                                $valores[] = $valor;
                            }

                            if (!empty($campos)) {
                                $valores[] = $solicitud['placa'];
                                $sql = "UPDATE vehiculos SET " . implode(', ', $campos) . " WHERE placa = ?";
                                $stmt = $db->prepare($sql);
                                $stmt->execute($valores);
                            }
                        }
                    }
                }

                // Marcar solicitud como aprobada
                $stmt = $db->prepare("
                UPDATE solicitudes SET
                    estado = 'aprobada',
                    catalogo_revisor = ?,
                    motivo_resolucion = ?,
                    fecha_resolucion = NOW(),
                    leida_solicitante = 0
                WHERE id = ?
            ");
                $stmt->execute([$_SESSION['auth_user'], $motivo ?: 'Aprobado', $id]);

                $db->commit();
                echo json_encode(['codigo' => 1, 'mensaje' => 'Solicitud aprobada y cambio ejecutado']);
            } catch (\Exception $e) {
                $db->rollBack();
                echo json_encode(['codigo' => 0, 'mensaje' => 'Error al ejecutar el cambio: ' . $e->getMessage()]);
            }
        } else {
            $stmt = $db->prepare("
            UPDATE solicitudes SET
                estado = 'rechazada',
                catalogo_revisor = ?,
                motivo_resolucion = ?,
                fecha_resolucion = NOW(),
                leida_solicitante = 0
            WHERE id = ?
        ");
            $stmt->execute([$_SESSION['auth_user'], $motivo, $id]);

            echo json_encode(['codigo' => 1, 'mensaje' => 'Solicitud rechazada']);
        }
    }

    // ── API: contar notificaciones (campanita) ────────────────────────────────
    public static function contarAPI(): void
    {
        isAuthApi();
        getHeadersApi();

        $rol      = $_SESSION['auth_rol']  ?? '';
        $catalogo = $_SESSION['auth_user'] ?? '';

        if (in_array($rol, ['COMTE_CIA', 'SUPERUSUARIO'])) {
            $count = Solicitudes::contarPendientes();
        } else {
            $count = Solicitudes::contarNoLeidas($catalogo);
        }

        echo json_encode(['codigo' => 1, 'total' => $count]);
    }

    // ── API: mis notificaciones (COMTE_PTN) ───────────────────────────────────
    public static function misNotificacionesAPI(): void
    {
        isAuthApi();
        getHeadersApi();

        $catalogo = $_SESSION['auth_user'] ?? '';
        $notifs   = Solicitudes::notificacionesSolicitante($catalogo);

        // Marcar como leídas
        $db = \Model\ActiveRecord::getDB();
        $stmt = $db->prepare("
            UPDATE solicitudes SET leida_solicitante = 1 
            WHERE catalogo_solicitante = ? AND estado IN ('aprobada','rechazada')
        ");
        $stmt->execute([$catalogo]);

        echo json_encode(['codigo' => 1, 'datos' => $notifs], JSON_UNESCAPED_UNICODE);
    }

    // ── API: marcar notificaciones del revisor como leídas ────────────────────
    public static function marcarLeidasRevisorAPI(): void
    {
        isAuthApi();
        getHeadersApi();

        $db = \Model\ActiveRecord::getDB();
        $stmt = $db->prepare("UPDATE solicitudes SET leida_revisor = 1 WHERE estado = 'pendiente'");
        $stmt->execute();

        echo json_encode(['codigo' => 1, 'mensaje' => 'Marcadas como leídas']);
    }
}
