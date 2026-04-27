<?php

namespace Model;

class Reparaciones extends ActiveRecord
{
    protected static $tabla   = 'reparaciones';
    protected static $idTabla = 'id_reparacion';
    protected static $columnasDB = [
        'placa',
        'fecha_inicio',
        'fecha_fin',
        'costo',
        'proveedor',
        'responsable',
        'estado',
        'observaciones',
        'es_externa',
        'destino_externo',
        'fecha_fin_indefinida'
    ];

    public $id_reparacion;
    public $placa;
    public $fecha_inicio;
    public $fecha_fin;
    public $costo;
    public $proveedor;
    public $responsable;
    public $estado;
    public $observaciones;
    public $es_externa;
    public $destino_externo;
    public $fecha_fin_indefinida;

    public function __construct($args = [])
    {
        $this->id_reparacion        = $args['id_reparacion']        ?? null;
        $this->placa                = $args['placa']                ?? '';
        $this->fecha_inicio         = $args['fecha_inicio']         ?? date('Y-m-d');
        $this->fecha_fin            = !empty($args['fecha_fin'])     ? $args['fecha_fin'] : null;
        $this->costo                = !empty($args['costo'])         ? $args['costo']     : null;
        $this->proveedor            = $args['proveedor']             ?? '';
        $this->responsable          = $args['responsable']          ?? '';
        $this->estado               = $args['estado']               ?? 'En proceso';
        $this->observaciones        = $args['observaciones']        ?? '';
        $this->es_externa           = $args['es_externa']           ?? 0;
        $this->destino_externo      = $args['destino_externo']      ?? null;
        $this->fecha_fin_indefinida = $args['fecha_fin_indefinida'] ?? 0;
    }

    // ── Traer todas las reparaciones de un vehículo con sus items ─────────
    public static function traerPorPlaca(string $placa): array
    {
        $sql = "SELECT 
                    r.*
                FROM reparaciones r
                WHERE r.placa = ?
                ORDER BY r.fecha_inicio DESC, r.id_reparacion DESC";

        $reparaciones = self::fetchArray($sql, [$placa]);

        // Traer items de cada reparación
        foreach ($reparaciones as &$rep) {
            $rep['items'] = self::traerItems((int)$rep['id_reparacion']);
        }
        unset($rep);

        return $reparaciones;
    }

    // ── Traer items de una reparación ─────────────────────────────────────
    public static function traerItems(int $idReparacion): array
    {
        $sql = "SELECT 
                    ri.*,
                    tr.nombre AS categoria_nombre
                FROM reparacion_items ri
                JOIN tipos_reparacion tr ON ri.id_categoria = tr.id_tipo_reparacion
                WHERE ri.id_reparacion = ?
                ORDER BY ri.id_item ASC";

        return self::fetchArray($sql, [$idReparacion]) ?? [];
    }

    // ── Contar reparaciones en proceso ────────────────────────────────────
    public static function contarEnProceso(string $placa): int
    {
        $resultado = self::fetchArray(
            "SELECT COUNT(*) AS total FROM reparaciones 
             WHERE placa = ? AND estado IN ('En proceso', 'Externa')",
            [$placa]
        );
        return (int)($resultado[0]['total'] ?? 0);
    }

    // ── Historial agrupado por categoría ──────────────────────────────────
    public static function traerHistorialAgrupado(string $placa): array
    {
        $sql = "SELECT 
                    tr.nombre        AS categoria_nombre,
                    ri.especifico,
                    ri.costo         AS costo_item,
                    r.fecha_inicio,
                    r.fecha_fin,
                    r.estado,
                    r.proveedor,
                    r.responsable,
                    r.observaciones,
                    r.es_externa,
                    r.destino_externo,
                    r.fecha_fin_indefinida
                FROM reparaciones r
                JOIN reparacion_items ri ON ri.id_reparacion   = r.id_reparacion
                JOIN tipos_reparacion tr ON ri.id_categoria    = tr.id_tipo_reparacion
                WHERE r.placa = ?
                ORDER BY tr.nombre ASC, r.fecha_inicio ASC";

        $rows = self::fetchArray($sql, [$placa]) ?? [];

        $grupos = [];
        foreach ($rows as $r) {
            $cat = $r['categoria_nombre'];
            if (!isset($grupos[$cat])) $grupos[$cat] = [];
            $grupos[$cat][] = $r;
        }

        return $grupos;
    }
}
