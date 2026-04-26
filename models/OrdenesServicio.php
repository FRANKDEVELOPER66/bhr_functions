<?php

namespace Model;

class OrdenesServicio extends ActiveRecord
{
    protected static $tabla   = 'ordenes_servicio';
    protected static $idTabla = 'id_orden';
    protected static $columnasDB = [
        'placa',
        'fecha_ingreso',
        'km_al_ingreso',
        'km_anterior',
        'responsable',
        'observaciones',
        'estado',
        'fecha_completado'
    ];

    public $id_orden;
    public $placa;
    public $fecha_ingreso;
    public $km_al_ingreso;
    public $km_anterior;
    public $responsable;
    public $observaciones;
    public $estado;
    public $fecha_completado;

    public function __construct($args = [])
    {
        $this->id_orden         = $args['id_orden']         ?? null;
        $this->placa            = $args['placa']            ?? '';
        $this->fecha_ingreso    = $args['fecha_ingreso']    ?? date('Y-m-d');
        $this->km_al_ingreso    = $args['km_al_ingreso']    ?? 0;
        $this->km_anterior      = $args['km_anterior']      ?? 0;
        $this->responsable      = $args['responsable']      ?? '';
        $this->observaciones    = $args['observaciones']    ?? '';
        $this->estado           = $args['estado']           ?? 'En proceso';
        $this->fecha_completado = $args['fecha_completado'] ?? null;
    }

    public static function traerPorPlaca(string $placa): array
    {
        $sql = "SELECT 
                    o.*,
                    COUNT(i.id_item) AS total_items,
                    SUM(CASE WHEN i.resultado = 'Realizado' THEN 1 ELSE 0 END) AS items_realizados
                FROM ordenes_servicio o
                LEFT JOIN orden_servicio_items i ON o.id_orden = i.id_orden
                WHERE o.placa = ?
                GROUP BY o.id_orden
                ORDER BY o.fecha_ingreso DESC, o.id_orden DESC";

        return self::fetchArray($sql, [$placa]);
    }

    public static function traerConItems(int $idOrden): ?array
    {
        $sql = "SELECT 
                    o.*,
                    COUNT(i.id_item) AS total_items
                FROM ordenes_servicio o
                LEFT JOIN orden_servicio_items i ON o.id_orden = i.id_orden
                WHERE o.id_orden = ?
                GROUP BY o.id_orden";

        $resultado = self::fetchArray($sql, [$idOrden]);
        if (!$resultado) return null;

        $orden = $resultado[0];

        $sqlItems = "SELECT 
                        i.*,
                        ts.nombre          AS tipo_nombre,
                        ts.intervalo_km    AS intervalo_km,
                        ts.intervalo_dias  AS intervalo_dias
                    FROM orden_servicio_items i
                    JOIN tipos_servicio ts ON i.id_tipo_servicio = ts.id_tipo_servicio
                    WHERE i.id_orden = ?
                    ORDER BY i.id_item ASC";

        $orden['items'] = self::fetchArray($sqlItems, [$idOrden]);
        return $orden;
    }

    public static function existeEnProceso(string $placa): bool
    {
        $sql = "SELECT COUNT(*) AS total 
                FROM ordenes_servicio 
                WHERE placa = ? AND estado = 'En proceso'";
        $resultado = self::fetchArray($sql, [$placa]);
        return (int)($resultado[0]['total'] ?? 0) > 0;
    }

    public static function traerEnProceso(string $placa): ?array
    {
        $sql = "SELECT o.*,
                    COUNT(i.id_item) AS total_items
                FROM ordenes_servicio o
                LEFT JOIN orden_servicio_items i ON o.id_orden = i.id_orden
                WHERE o.placa = ? AND o.estado = 'En proceso'
                GROUP BY o.id_orden
                LIMIT 1";
        $resultado = self::fetchArray($sql, [$placa]);
        if (!$resultado) return null;

        $orden = $resultado[0];
        $sqlItems = "SELECT i.*, ts.nombre AS tipo_nombre
                     FROM orden_servicio_items i
                     JOIN tipos_servicio ts ON i.id_tipo_servicio = ts.id_tipo_servicio
                     WHERE i.id_orden = ?
                     ORDER BY i.id_item ASC";
        $orden['items'] = self::fetchArray($sqlItems, [(int)$orden['id_orden']]);
        return $orden;
    }

    public static function traerProximoServicio(string $placa): ?array
    {
        $sql = "SELECT 
                i.km_proximo,
                i.fecha_proximo,
                ts.nombre AS tipo_nombre,
                o.fecha_ingreso,
                o.id_orden
            FROM orden_servicio_items i
            JOIN ordenes_servicio o  ON i.id_orden       = o.id_orden
            JOIN tipos_servicio   ts ON i.id_tipo_servicio = ts.id_tipo_servicio
            WHERE o.placa      = ?
              AND o.estado     = 'Completado'
              AND i.km_proximo IS NOT NULL
              AND i.resultado  = 'Realizado'
            ORDER BY o.id_orden DESC, i.km_proximo DESC
            LIMIT 1";

        $resultado = self::fetchArray($sql, [$placa]);
        return $resultado[0] ?? null;
    }

    public static function traerTodosProximosServicios(string $placa): array
    {
        $sql = "SELECT 
                ts.nombre            AS tipo_nombre,
                i.km_proximo,
                i.fecha_proximo,
                o.id_orden
            FROM orden_servicio_items i
            JOIN ordenes_servicio o  ON i.id_orden        = o.id_orden
            JOIN tipos_servicio   ts ON i.id_tipo_servicio = ts.id_tipo_servicio
            WHERE o.placa     = ?
              AND o.estado    = 'Completado'
              AND i.km_proximo IS NOT NULL
              AND i.resultado = 'Realizado'
              AND o.id_orden = (
                  SELECT MAX(o2.id_orden)
                  FROM ordenes_servicio o2
                  JOIN orden_servicio_items i2 ON i2.id_orden = o2.id_orden
                  WHERE o2.placa   = o.placa
                    AND o2.estado  = 'Completado'
                    AND i2.id_tipo_servicio = i.id_tipo_servicio
                    AND i2.resultado = 'Realizado'
              )
            ORDER BY i.km_proximo ASC";

        return self::fetchArray($sql, [$placa]) ?? [];
    }

    public static function traerServiciosParaPDF(string $placa): array
    {
        $sql = "SELECT 
                o.fecha_ingreso      AS fecha_realizado,
                o.km_al_ingreso      AS km_al_servicio,
                o.responsable,
                o.observaciones,
                ts.nombre            AS tipo_nombre,
                i.km_proximo         AS km_proximo_servicio,
                i.observacion        AS obs_item
            FROM ordenes_servicio o
            JOIN orden_servicio_items i  ON i.id_orden        = o.id_orden
            JOIN tipos_servicio       ts ON i.id_tipo_servicio = ts.id_tipo_servicio
            WHERE o.placa   = ?
              AND o.estado  = 'Completado'
              AND i.resultado = 'Realizado'
            ORDER BY o.fecha_ingreso ASC, o.id_orden ASC";

        return self::fetchArray($sql, [$placa]) ?? [];
    }

    public static function traerHistorialAgrupado(string $placa): array
    {
        $sql = "SELECT 
                ts.nombre            AS tipo_nombre,
                o.fecha_ingreso      AS fecha_realizado,
                o.km_al_ingreso      AS km_al_servicio,
                o.responsable,
                o.observaciones,
                i.km_proximo         AS km_proximo_servicio,
                i.observacion        AS obs_item,
                o.id_orden
            FROM ordenes_servicio o
            JOIN orden_servicio_items i  ON i.id_orden        = o.id_orden
            JOIN tipos_servicio       ts ON i.id_tipo_servicio = ts.id_tipo_servicio
            WHERE o.placa     = ?
              AND o.estado    = 'Completado'
              AND i.resultado = 'Realizado'
            ORDER BY ts.nombre ASC, o.fecha_ingreso ASC, o.id_orden ASC";

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
