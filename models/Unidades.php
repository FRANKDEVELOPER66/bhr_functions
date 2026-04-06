<?php

namespace Model;

class Unidades extends ActiveRecord
{
    protected static $tabla    = 'unidades';
    protected static $idTabla  = 'id_unidad';
    protected static $columnasDB = [
        'nombre',
        'tipo',
        'id_destacamento'
    ];

    public $id_unidad;
    public $nombre;
    public $tipo;
    public $id_destacamento;

    public function __construct($args = [])
    {
        $this->id_unidad        = $args['id_unidad']        ?? null;
        $this->nombre           = $args['nombre']           ?? '';
        $this->tipo             = $args['tipo']             ?? null;
        $this->id_destacamento  = $args['id_destacamento']  ?? null;
    }

    // En Model/Unidades.php
    public static function traerTodos(): array
    {
        $sql = "SELECT 
        u.id_unidad,
        u.nombre AS unidad_nombre,
        u.tipo,
        u.id_destacamento,
        d.nombre AS destacamento_nombre,
        d.departamento,
        d.municipio,
        CONCAT(u.nombre, ' / ' ' Destacada en la ', d.nombre, ', ', d.municipio, ' ', d.departamento) AS unidad_destacamento
    FROM unidades u
    LEFT JOIN destacamentos d ON u.id_destacamento = d.id_destacamento
    ORDER BY u.nombre ASC";

        return self::fetchArray($sql);
    }

    public static function traerPorDestacamento(int $id): array
    {
        return self::fetchArray(
            "SELECT * FROM unidades WHERE id_destacamento = ? ORDER BY nombre",
            [$id]
        );
    }
}
