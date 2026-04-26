<?php

namespace Model;

class Reparaciones extends ActiveRecord
{
    protected static $tabla    = 'reparaciones';
    protected static $idTabla  = 'id_reparacion';
    protected static $columnasDB = [
        'placa',
        'id_tipo_reparacion',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'km_al_momento',
        'costo',
        'proveedor',
        'responsable',
        'estado',
        'observaciones'
    ];

    public $id_reparacion;
    public $placa;
    public $id_tipo_reparacion;
    public $descripcion;
    public $fecha_inicio;
    public $fecha_fin;
    public $km_al_momento;
    public $costo;
    public $proveedor;
    public $responsable;
    public $estado;
    public $observaciones;

    public function __construct($args = [])
    {
        $this->id_reparacion      = $args['id_reparacion']      ?? null;
        $this->placa              = $args['placa']              ?? '';
        $this->id_tipo_reparacion = $args['id_tipo_reparacion'] ?? null;
        $this->descripcion        = $args['descripcion']        ?? '';
        $this->fecha_inicio       = $args['fecha_inicio']       ?? date('Y-m-d');
        $this->fecha_fin          = !empty($args['fecha_fin'])  ? $args['fecha_fin'] : null;
        $this->km_al_momento      = $args['km_al_momento']      ?? 0;
        $this->costo              = !empty($args['costo'])      ? $args['costo'] : null;
        $this->proveedor          = $args['proveedor']          ?? '';
        $this->responsable        = $args['responsable']        ?? '';
        $this->estado             = $args['estado']             ?? 'En proceso';
        $this->observaciones      = $args['observaciones']      ?? '';
    }

    public static function traerPorPlaca(string $placa): array
    {
        $sql = "SELECT 
                    r.*,
                    tr.nombre AS tipo_nombre
                FROM reparaciones r
                JOIN tipos_reparacion tr ON r.id_tipo_reparacion = tr.id_tipo_reparacion
                WHERE r.placa = ?
                ORDER BY r.fecha_inicio DESC, r.id_reparacion DESC";

        return self::fetchArray($sql, [$placa]);
    }

    public static function contarEnProceso(string $placa): int
    {
        $resultado = self::fetchArray(
            "SELECT COUNT(*) AS total FROM reparaciones 
         WHERE placa = ? AND estado = 'En proceso'",
            [$placa]
        );
        return (int)($resultado[0]['total'] ?? 0);
    }
    // Verificar si ya existe una reparación en proceso del mismo tipo
    public static function existeEnProcesoPorTipo(string $placa, int $idTipoReparacion): bool
    {
        $sql = "SELECT COUNT(*) AS total
            FROM reparaciones
            WHERE placa = ?
              AND id_tipo_reparacion = ?
              AND estado = 'En proceso'";

        $resultado = self::fetchArray($sql, [$placa, $idTipoReparacion]);
        return (int)($resultado[0]['total'] ?? 0) > 0;
    }

    // Traer la última reparación finalizada del mismo tipo
    public static function traerUltimaPorTipo(string $placa, int $idTipoReparacion): ?array
    {
        $sql = "SELECT fecha_inicio, km_al_momento
            FROM reparaciones
            WHERE placa = ?
              AND id_tipo_reparacion = ?
              AND estado = 'Finalizada'
            ORDER BY fecha_inicio DESC
            LIMIT 1";

        $resultado = self::fetchArray($sql, [$placa, $idTipoReparacion]);
        return $resultado[0] ?? null;
    }
    public static function traerHistorialAgrupado(string $placa): array
    {
        $sql = "SELECT 
                tr.nombre            AS tipo_nombre,
                r.fecha_inicio,
                r.fecha_fin,
                r.descripcion,
                r.km_al_momento,
                r.costo,
                r.estado,
                r.proveedor,
                r.responsable,
                r.observaciones
            FROM reparaciones r
            JOIN tipos_reparacion tr ON r.id_tipo_reparacion = tr.id_tipo_reparacion
            WHERE r.placa = ?
            ORDER BY tr.nombre ASC, r.fecha_inicio ASC";

        $rows = self::fetchArray($sql, [$placa]) ?? [];

        $grupos = [];
        foreach ($rows as $r) {
            $tipo = $r['tipo_nombre'];
            if (!isset($grupos[$tipo])) $grupos[$tipo] = [];
            $grupos[$tipo][] = $r;
        }

        return $grupos;
    }
}
