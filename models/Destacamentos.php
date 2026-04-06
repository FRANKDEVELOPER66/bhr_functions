<?php

namespace Model;

class Destacamentos extends ActiveRecord
{
    protected static $tabla    = 'destacamentos';
    protected static $idTabla  = 'id_destacamento';
    protected static $columnasDB = [
        'nombre',
        'departamento',
        'municipio',
        'descripcion'
    ];

    public $id_destacamento;
    public $nombre;
    public $departamento;
    public $municipio;
    public $descripcion;

    public function __construct($args = [])
    {
        $this->id_destacamento = $args['id_destacamento'] ?? null;
        $this->nombre          = $args['nombre']          ?? '';
        $this->departamento    = $args['departamento']    ?? '';
        $this->municipio       = $args['municipio']       ?? null;
        $this->descripcion     = $args['descripcion']     ?? null;
    }

    public static function traerTodos(): array
    {
        return self::fetchArray(
            "SELECT * FROM destacamentos ORDER BY nombre ASC"
        );
    }


    public static function obtenerUnidades()
    {
        $sql = "SELECT 
        u.id_unidad,
        u.nombre AS unidad_nombre,
        u.tipo,
        u.id_destacamento,
        
        d.nombre AS destacamento_nombre,
        d.departamento,
        d.municipio,
        d.descripcion,

        CONCAT(u.nombre, ' destacada en ', d.departamento) AS unidad_destacamento

    FROM unidades u
    LEFT JOIN destacamentos d 
        ON u.id_destacamento = d.id_destacamento

    ORDER BY u.id_unidad DESC";

        return self::fetchArray($sql);
    }
}
