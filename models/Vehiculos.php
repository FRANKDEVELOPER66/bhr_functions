<?php

namespace Model;

class Vehiculos extends ActiveRecord
{
    protected static $tabla    = 'vehiculos';
    protected static $idTabla  = 'placa';          // PK es la placa (VARCHAR)
    protected static $columnasDB = [
        'placa',
        'numero_serie',
        'marca',
        'modelo',
        'anio',
        'color',
        'tipo',
        'km_actuales',
        'estado',
        'fecha_ingreso',
        'observaciones',
    ];

    public $placa;
    public $numero_serie;
    public $marca;
    public $modelo;
    public $anio;
    public $color;
    public $tipo;
    public $km_actuales;
    public $estado;
    public $fecha_ingreso;
    public $observaciones;

    public function __construct($args = [])
    {
        $this->placa          = strtoupper(trim($args['placa']         ?? ''));
        $this->numero_serie   = strtoupper(trim($args['numero_serie']  ?? ''));
        $this->marca          = $args['marca']         ?? '';
        $this->modelo         = $args['modelo']        ?? '';
        $this->anio           = $args['anio']          ?? date('Y');
        $this->color          = $args['color']         ?? '';
        $this->tipo           = $args['tipo']          ?? '';
        $this->km_actuales    = (int)($args['km_actuales'] ?? 0);
        $this->estado         = $args['estado']        ?? 'Alta';
        $this->fecha_ingreso  = $args['fecha_ingreso'] ?? date('Y-m-d');
        $this->observaciones  = $args['observaciones'] ?? null;
    }

    // ---------------------------------------------------------------
    // Lecturas
    // ---------------------------------------------------------------

    /** Todos los vehículos, ordenados por placa */
    public static function obtenerTodos(): array
    {
        $sql = "SELECT * FROM " . self::$tabla . "
                ORDER BY placa";
        return self::fetchArray($sql);
    }

    /** Buscar por placa (parcial) o marca/modelo */
    public static function buscar(string $termino): array
    {
        $t   = '%' . $termino . '%';
        $sql = "SELECT * FROM " . self::$tabla . "
                WHERE placa       LIKE ?
                   OR numero_serie LIKE ?
                   OR marca        LIKE ?
                   OR modelo       LIKE ?
                ORDER BY placa
                LIMIT 20";
        return self::fetchArray($sql, [$t, $t, $t, $t]);
    }

    /** Obtener un vehículo por su placa exacta */
    public static function obtenerPorPlaca(string $placa): ?array
    {
        $sql = "SELECT * FROM " . self::$tabla . "
                WHERE placa = ?
                LIMIT 1";
        $rows = self::fetchArray($sql, [strtoupper(trim($placa))]);
        return $rows[0] ?? null;
    }

    /** Vehículos filtrados por estado (Alta / Baja / Taller) */
    public static function obtenerPorEstado(string $estado): array
    {
        $sql = "SELECT * FROM " . self::$tabla . "
                WHERE estado = ?
                ORDER BY placa";
        return self::fetchArray($sql, [$estado]);
    }

    /** Vehículos que tengan servicios próximos (km_actuales ≥ km_proximo_servicio) */
    public static function obtenerConServicioProximo(): array
    {
        $sql = "SELECT v.*, s.km_proximo_servicio, s.fecha_proximo,
                       ts.nombre AS nombre_servicio
                FROM   vehiculos v
                JOIN   servicios s  ON s.placa = v.placa
                JOIN   tipos_servicio ts ON ts.id_tipo_servicio = s.id_tipo_servicio
                WHERE  s.km_proximo_servicio IS NOT NULL
                  AND  v.km_actuales >= s.km_proximo_servicio
                ORDER BY v.placa";
        return self::fetchArray($sql);
    }
}