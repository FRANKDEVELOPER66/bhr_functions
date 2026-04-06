<?php

namespace Model;

class Vehiculos extends ActiveRecord
{
    protected static $tabla = 'vehiculos';
    protected static $idTabla = 'placa';
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
        'observaciones'
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
        $this->placa          = $args['placa']          ?? '';
        $this->numero_serie   = $args['numero_serie']   ?? '';
        $this->marca          = $args['marca']          ?? '';
        $this->modelo         = $args['modelo']         ?? '';
        $this->anio           = $args['anio']           ?? date('Y');
        $this->color          = $args['color']          ?? '';
        $this->tipo           = $args['tipo']           ?? '';
        $this->km_actuales    = $args['km_actuales']    ?? 0;
        $this->estado         = $args['estado']         ?? 'Alta';
        $this->fecha_ingreso  = $args['fecha_ingreso']  ?? date('Y-m-d');
        $this->observaciones  = $args['observaciones']  ?? '';
    }

    /**
     * Trae todos los vehículos con conteo de servicios y reparaciones
     */
    public static function traerVehiculos()
    {
        $sql = "SELECT 
                    v.*,
                    COUNT(DISTINCT s.id_servicio)    AS total_servicios,
                    COUNT(DISTINCT r.id_reparacion)  AS total_reparaciones,
                    MAX(s.fecha_realizado)            AS ultimo_servicio
                FROM vehiculos v
                LEFT JOIN servicios    s ON v.placa = s.placa
                LEFT JOIN reparaciones r ON v.placa = r.placa
                GROUP BY v.placa
                ORDER BY v.marca, v.modelo";

        return self::fetchArray($sql);
    }

    /**
     * Trae un vehículo con todo su detalle
     */
    public static function traerConDetalle(string $placa)
    {
        $sql = "SELECT 
                v.*,
                (SELECT COUNT(*) FROM servicios    WHERE placa = v.placa) AS total_servicios,
                (SELECT COUNT(*) FROM reparaciones WHERE placa = v.placa) AS total_reparaciones
            FROM vehiculos v
            WHERE v.placa = ?
            LIMIT 1";

        $resultado = self::fetchArray($sql, [$placa]);
        return $resultado[0] ?? null;
    }

    /**
     * Verifica si la placa ya existe
     */
    public static function existePlaca(string $placa): bool
    {
        $sql = "SELECT placa FROM vehiculos WHERE placa = ? LIMIT 1";
        $resultado = self::fetchArray($sql, [$placa]);
        return !empty($resultado);
    }

    /**
     * Verifica si el número de serie ya existe (excluyendo una placa en edición)
     */
    public static function existeNumeroSerie(string $serie, string $placaExcluir = ''): bool
    {
        if ($placaExcluir) {
            $sql = "SELECT placa FROM vehiculos WHERE numero_serie = ? AND placa <> ? LIMIT 1";
            $resultado = self::fetchArray($sql, [$serie, $placaExcluir]);
        } else {
            $sql = "SELECT placa FROM vehiculos WHERE numero_serie = ? LIMIT 1";
            $resultado = self::fetchArray($sql, [$serie]);
        }
        return !empty($resultado);
    }

    /**
     * Actualiza únicamente el kilometraje
     */
    public static function actualizarKm(string $placa, int $km): bool
    {
        $sql = "UPDATE vehiculos SET km_actuales = ? WHERE placa = ?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$km, $placa]);
        return true;
    }
}
