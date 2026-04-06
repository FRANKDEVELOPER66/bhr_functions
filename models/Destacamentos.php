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
            "SELECT * FROM destacamentos ORDER BY departamento, nombre"
        );
    }
}
