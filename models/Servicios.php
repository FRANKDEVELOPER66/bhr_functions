<?php

namespace Model;

class Servicios extends ActiveRecord
{
    protected static $tabla   = 'servicios';
    protected static $idTabla = 'id_servicio';
    protected static $columnasDB = [
        'placa',
        'id_tipo_servicio',
        'fecha_realizado',
        'km_al_servicio',
        'km_proximo_servicio',
        'fecha_proximo',
        'observaciones',
        'responsable'
    ];

    public $id_servicio;
    public $placa;
    public $id_tipo_servicio;
    public $fecha_realizado;
    public $km_al_servicio;
    public $km_proximo_servicio;
    public $fecha_proximo;
    public $observaciones;
    public $responsable;

    public function __construct($args = [])
    {
        $this->id_servicio         = $args['id_servicio']         ?? null;
        $this->placa               = $args['placa']               ?? '';
        $this->id_tipo_servicio    = $args['id_tipo_servicio']    ?? null;
        $this->fecha_realizado     = $args['fecha_realizado']     ?? date('Y-m-d');
        $this->km_al_servicio      = $args['km_al_servicio']      ?? 0;
        $this->km_proximo_servicio = $args['km_proximo_servicio'] ?? null;
        $this->fecha_proximo       = $args['fecha_proximo']       ?? null;
        $this->observaciones       = $args['observaciones']       ?? '';
        $this->responsable         = $args['responsable']         ?? '';
    }

    // Historial completo de servicios de un vehículo
    public static function traerPorPlaca(string $placa): array
    {
        $sql = "SELECT 
                    s.*,
                    ts.nombre          AS tipo_nombre,
                    ts.intervalo_km    AS intervalo_km,
                    ts.intervalo_dias  AS intervalo_dias
                FROM servicios s
                JOIN tipos_servicio ts ON s.id_tipo_servicio = ts.id_tipo_servicio
                WHERE s.placa = ?
                ORDER BY s.fecha_realizado DESC, s.id_servicio DESC";

        return self::fetchArray($sql, [$placa]);
    }

    // Próximos servicios pendientes (km actual ya superó km_proximo)
    public static function traerPendientes(string $placa, int $kmActual): array
    {
        $sql = "SELECT 
                    s.*,
                    ts.nombre AS tipo_nombre
                FROM servicios s
                JOIN tipos_servicio ts ON s.id_tipo_servicio = ts.id_tipo_servicio
                WHERE s.placa = ?
                  AND s.km_proximo_servicio IS NOT NULL
                  AND s.km_proximo_servicio <= ?
                ORDER BY s.km_proximo_servicio ASC";

        return self::fetchArray($sql, [$placa, $kmActual]);
    }
}
