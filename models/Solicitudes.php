<?php

namespace Model;

class Solicitudes extends ActiveRecord
{
    protected static $tabla = 'solicitudes';
    protected static $columnasDB = [
        'id',
        'tipo',
        'placa',
        'catalogo_solicitante',
        'datos_cambio',
        'estado',
        'catalogo_revisor',
        'motivo_resolucion',
        'fecha_solicitud',
        'fecha_resolucion',
        'leida_solicitante',
        'leida_revisor'
    ];

    public $id;
    public $tipo;
    public $placa;
    public $catalogo_solicitante;
    public $datos_cambio;
    public $estado = 'pendiente';
    public $catalogo_revisor;
    public $motivo_resolucion;
    public $fecha_solicitud;
    public $fecha_resolucion;
    public $leida_solicitante = 0;
    public $leida_revisor = 0;

    public function __construct($args = [])
    {
        $this->tipo                 = $args['tipo']                 ?? '';
        $this->placa                = $args['placa']                ?? '';
        $this->catalogo_solicitante = $args['catalogo_solicitante'] ?? '';
        $this->datos_cambio         = $args['datos_cambio']         ?? null;
        $this->estado               = $args['estado']               ?? 'pendiente';
        $this->catalogo_revisor     = $args['catalogo_revisor']     ?? null;
        $this->motivo_resolucion    = $args['motivo_resolucion']    ?? null;
        $this->leida_solicitante    = $args['leida_solicitante']    ?? 0;
        $this->leida_revisor        = $args['leida_revisor']        ?? 0;
    }

    // ── Pendientes para el revisor (COMTE_CIA) ────────────────────────────────
    public static function pendientes(): array
    {
        return self::fetchArray("
        SELECT s.*, 
               v.marca, v.modelo, v.tipo as tipo_vehiculo,
               v.foto_frente,
               u.grado, u.nombre_completo, u.plaza
        FROM solicitudes s
        JOIN vehiculos v ON s.placa = v.placa
        JOIN usuarios u ON s.catalogo_solicitante = u.catalogo
        WHERE s.estado = 'pendiente'
        ORDER BY s.fecha_solicitud DESC
    ");
    }

    // ── Notificaciones para el solicitante (COMTE_PTN) ────────────────────────
    public static function notificacionesSolicitante(string $catalogo): array
    {
        return self::fetchArray("
        SELECT s.*, v.marca, v.modelo, v.tipo as tipo_vehiculo
        FROM solicitudes s
        JOIN vehiculos v ON s.placa = v.placa
        WHERE s.catalogo_solicitante = '{$catalogo}'
        AND s.estado IN ('aprobada', 'rechazada')
        AND s.leida_solicitante = 0
        ORDER BY s.fecha_resolucion DESC
    ");
    }

    // ── Contar pendientes sin leer para campanita ─────────────────────────────
    public static function contarPendientes(): int
    {
        $resultado = self::fetchArray("
            SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'pendiente'
        ");
        return (int)($resultado[0]['total'] ?? 0);
    }

    public static function contarNoLeidas(string $catalogo): int
    {
        $resultado = self::fetchArray("
            SELECT COUNT(*) as total FROM solicitudes 
            WHERE catalogo_solicitante = '{$catalogo}'
            AND estado IN ('aprobada','rechazada')
            AND leida_solicitante = 0
        ");
        return (int)($resultado[0]['total'] ?? 0);
    }
}
