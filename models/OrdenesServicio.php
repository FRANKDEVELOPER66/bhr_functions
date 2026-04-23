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
        'responsable',
        'observaciones',
        'estado',
        'fecha_completado'
    ];

    public $id_orden;
    public $placa;
    public $fecha_ingreso;
    public $km_al_ingreso;
    public $responsable;
    public $observaciones;
    public $estado;
    public $fecha_completado;

    public function __construct($args = [])
    {
        $this->id_orden        = $args['id_orden']        ?? null;
        $this->placa           = $args['placa']           ?? '';
        $this->fecha_ingreso   = $args['fecha_ingreso']   ?? date('Y-m-d');
        $this->km_al_ingreso   = $args['km_al_ingreso']   ?? 0;
        $this->responsable     = $args['responsable']     ?? '';
        $this->observaciones   = $args['observaciones']   ?? '';
        $this->estado          = $args['estado']          ?? 'En proceso';
        $this->fecha_completado = $args['fecha_completado'] ?? null;
    }

    // Traer todas las órdenes de un vehículo con conteo de items
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

    // Traer una orden con todos sus items
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

        // Traer items con nombre del tipo
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

    // Verificar si hay una orden en proceso para un vehículo
    public static function existeEnProceso(string $placa): bool
    {
        $sql = "SELECT COUNT(*) AS total 
                FROM ordenes_servicio 
                WHERE placa = ? AND estado = 'En proceso'";
        $resultado = self::fetchArray($sql, [$placa]);
        return (int)($resultado[0]['total'] ?? 0) > 0;
    }

    // Traer la orden en proceso activa
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

    // Próximo servicio más cercano por km
    public static function traerProximoServicio(string $placa): ?array
    {
        $sql = "SELECT 
                    i.km_proximo,
                    i.fecha_proximo,
                    ts.nombre AS tipo_nombre
                FROM orden_servicio_items i
                JOIN ordenes_servicio o ON i.id_orden = o.id_orden
                JOIN tipos_servicio ts ON i.id_tipo_servicio = ts.id_tipo_servicio
                WHERE o.placa = ?
                  AND o.estado = 'Completado'
                  AND i.km_proximo IS NOT NULL
                  AND i.resultado = 'Realizado'
                ORDER BY i.km_proximo ASC
                LIMIT 1";
        $resultado = self::fetchArray($sql, [$placa]);
        return $resultado[0] ?? null;
    }
}
