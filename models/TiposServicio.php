<?php

namespace Model;

class TiposServicio extends ActiveRecord
{
    protected static $tabla   = 'tipos_servicio';
    protected static $idTabla = 'id_tipo_servicio';
    protected static $columnasDB = [
        'id_tipo_servicio',
        'nombre',
        'descripcion',
        'intervalo_km',
        'intervalo_dias'
    ];

    public $id_tipo_servicio;
    public $nombre;
    public $descripcion;
    public $intervalo_km;
    public $intervalo_dias;

    public function __construct($args = [])
    {
        $this->id_tipo_servicio = $args['id_tipo_servicio'] ?? null;
        $this->nombre           = $args['nombre']           ?? '';
        $this->descripcion      = $args['descripcion']      ?? '';
        $this->intervalo_km     = $args['intervalo_km']     ?? null;
        $this->intervalo_dias   = $args['intervalo_dias']   ?? null;
    }

    public static function traerTodos(): array
    {
        return self::fetchArray("SELECT * FROM tipos_servicio ORDER BY nombre");
    }
}
