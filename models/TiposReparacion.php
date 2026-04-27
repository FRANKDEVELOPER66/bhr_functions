<?php

namespace Model;

class TiposReparacion extends ActiveRecord
{
    protected static $tabla   = 'tipos_reparacion';
    protected static $idTabla = 'id_tipo_reparacion';
    protected static $columnasDB = [
        'nombre',
        'descripcion',
        'activo'
    ];

    public $id_tipo_reparacion;
    public $nombre;
    public $descripcion;
    public $activo;

    public function __construct($args = [])
    {
        $this->id_tipo_reparacion = $args['id_tipo_reparacion'] ?? null;
        $this->nombre             = $args['nombre']             ?? '';
        $this->descripcion        = $args['descripcion']        ?? '';
        $this->activo             = $args['activo']             ?? 1;
    }

    public static function traerTodos(): array
    {
        return self::fetchArray(
            "SELECT * FROM tipos_reparacion WHERE activo = 1 ORDER BY nombre ASC"
        ) ?? [];
    }

    public static function existeNombre(string $nombre): bool
    {
        $resultado = self::fetchArray(
            "SELECT COUNT(*) AS total FROM tipos_reparacion WHERE nombre = ?",
            [$nombre]
        );
        return (int)($resultado[0]['total'] ?? 0) > 0;
    }

    public static function crearSiNoExiste(string $nombre): int
    {
        $existe = self::fetchFirst(
            "SELECT id_tipo_reparacion FROM tipos_reparacion WHERE nombre = ?",
            [$nombre]
        );
        if ($existe) return (int)$existe['id_tipo_reparacion'];

        $nuevo = new self(['nombre' => $nombre, 'activo' => 1]);
        $resultado = $nuevo->crear();
        return (int)$resultado['id'];
    }
}
