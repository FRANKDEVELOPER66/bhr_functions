<?php

namespace Model;

class ReparacionItems extends ActiveRecord
{
    protected static $tabla   = 'reparacion_items';
    protected static $idTabla = 'id_item';
    protected static $columnasDB = [
        'id_reparacion',
        'id_categoria',
        'especifico',
        'costo'
    ];

    public $id_item;
    public $id_reparacion;
    public $id_categoria;
    public $especifico;
    public $costo;

    public function __construct($args = [])
    {
        $this->id_item        = $args['id_item']        ?? null;
        $this->id_reparacion  = $args['id_reparacion']  ?? null;
        $this->id_categoria   = $args['id_categoria']   ?? null;
        $this->especifico     = $args['especifico']     ?? '';
        $this->costo          = !empty($args['costo'])  ? $args['costo'] : null;
    }

    public static function existeEnReparacion(int $idReparacion, int $idCategoria, string $especifico): bool
    {
        $resultado = self::fetchArray(
            "SELECT COUNT(*) AS total FROM reparacion_items 
             WHERE id_reparacion = ? AND id_categoria = ? AND especifico = ?",
            [$idReparacion, $idCategoria, $especifico]
        );
        return (int)($resultado[0]['total'] ?? 0) > 0;
    }

    public static function eliminarDeReparacion(int $idReparacion): void
    {
        self::fetchArray(
            "DELETE FROM reparacion_items WHERE id_reparacion = ?",
            [$idReparacion]
        );
    }
}
