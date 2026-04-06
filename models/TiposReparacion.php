<?php

namespace Model;

class TiposReparacion extends ActiveRecord
{
    protected static $tabla   = 'tipos_reparacion';
    protected static $idTabla = 'id_tipo_reparacion';
    protected static $columnasDB = [
        'nombre',
        'descripcion'
    ];

    public $id_tipo_reparacion;
    public $nombre;
    public $descripcion;

    public function __construct($args = [])
    {
        $this->id_tipo_reparacion = $args['id_tipo_reparacion'] ?? null;
        $this->nombre             = $args['nombre']             ?? '';
        $this->descripcion        = $args['descripcion']        ?? '';
    }

    public static function traerTodos(): array
    {
        return self::fetchArray("SELECT * FROM tipos_reparacion ORDER BY nombre");
    }
}
