<?php

namespace Model;

class OrdenServicioItems extends ActiveRecord
{
    protected static $tabla   = 'orden_servicio_items';
    protected static $idTabla = 'id_item';
    protected static $columnasDB = [
        'id_orden',
        'id_tipo_servicio',
        'resultado',
        'km_proximo',
        'fecha_proximo',
        'observacion'
    ];

    public $id_item;
    public $id_orden;
    public $id_tipo_servicio;
    public $resultado;
    public $km_proximo;
    public $fecha_proximo;
    public $observacion;

    public function __construct($args = [])
    {
        $this->id_item          = $args['id_item']          ?? null;
        $this->id_orden         = $args['id_orden']         ?? null;
        $this->id_tipo_servicio = $args['id_tipo_servicio'] ?? null;
        $this->resultado        = $args['resultado']        ?? 'Realizado';
        $this->km_proximo       = $args['km_proximo']       ?? null;
        $this->fecha_proximo    = $args['fecha_proximo']    ?? null;
        $this->observacion      = $args['observacion']      ?? '';
    }

    // Verificar si ya existe ese tipo de servicio en la orden
    public static function existeEnOrden(int $idOrden, int $idTipoServicio): bool
    {
        $sql = "SELECT COUNT(*) AS total 
                FROM orden_servicio_items 
                WHERE id_orden = ? AND id_tipo_servicio = ?";
        $resultado = self::fetchArray($sql, [$idOrden, $idTipoServicio]);
        return (int)($resultado[0]['total'] ?? 0) > 0;
    }

    public static function ultimoDeOrden(int $idOrden, int $idTipo): ?int
    {
        $resultado = self::fetchArray(
            "SELECT id_item FROM orden_servicio_items 
         WHERE id_orden = ? AND id_tipo_servicio = ? 
         ORDER BY id_item DESC LIMIT 1",
            [$idOrden, $idTipo]
        );
        return $resultado ? (int)$resultado[0]['id_item'] : null;
    }
}
