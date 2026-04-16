<?php

namespace Model;

class Accidentes extends ActiveRecord
{
    protected static $tabla      = 'accidentes';
    protected static $idTabla    = 'id_accidente';
    protected static $columnasDB = [
        'placa',
        'id_seguro',
        'fecha_accidente',
        'lugar',
        'tipo_accidente',
        'descripcion',
        'conductor_responsable',
        'costo_estimado',
        'costo_real',
        'estado_caso',
        'numero_expediente',
        'archivo_fotos',
        'archivo_foto_1',   // ← nuevo
        'archivo_foto_2',   // ← nuevo
        'archivo_foto_3',   // ← nuevo
        'archivo_foto_4',   // ← nuevo
        'archivo_informe',
        'observaciones'
    ];

    public $id_accidente;
    public $placa;
    public $id_seguro;
    public $fecha_accidente;
    public $lugar;
    public $tipo_accidente;
    public $descripcion;
    public $conductor_responsable;
    public $costo_estimado;
    public $costo_real;
    public $estado_caso;
    public $numero_expediente;
    public $archivo_fotos;
    public $archivo_foto_1;
    public $archivo_foto_2;
    public $archivo_foto_3;
    public $archivo_foto_4;
    public $archivo_informe;
    public $observaciones;

    public function __construct($args = [])
    {
        $this->id_accidente          = $args['id_accidente']          ?? null;
        $this->placa                 = $args['placa']                 ?? '';
        $this->id_seguro             = $args['id_seguro']             ?? null;
        $this->fecha_accidente       = $args['fecha_accidente']       ?? null;
        $this->lugar                 = $args['lugar']                 ?? '';
        $this->tipo_accidente        = $args['tipo_accidente']        ?? 'Colisión';
        $this->descripcion           = $args['descripcion']           ?? '';
        $this->conductor_responsable = $args['conductor_responsable'] ?? null;
        $this->costo_estimado        = $args['costo_estimado']        ?? null;
        $this->costo_real            = $args['costo_real']            ?? null;
        $this->estado_caso           = $args['estado_caso']           ?? 'Reportado';
        $this->numero_expediente     = $args['numero_expediente']     ?? null;
        $this->archivo_fotos         = $args['archivo_fotos']         ?? null;
        $this->archivo_foto_1       = $args['archivo_foto_1']       ?? null;
        $this->archivo_foto_2       = $args['archivo_foto_2']       ?? null;
        $this->archivo_foto_3       = $args['archivo_foto_3']       ?? null;
        $this->archivo_foto_4       = $args['archivo_foto_4']       ?? null;
        $this->archivo_informe       = $args['archivo_informe']       ?? null;
        $this->observaciones         = $args['observaciones']         ?? null;
    }

    // ── CONSULTAS ─────────────────────────────────────────────────────────────

    /**
     * Trae todos los accidentes de una placa, con datos del seguro si aplica.
     */
    public static function traerPorPlaca(string $placa): array
    {
        $pdo  = self::$db;
        $sql  = "SELECT a.*,
                        s.aseguradora,
                        s.numero_poliza
                 FROM   accidentes a
                 LEFT JOIN seguros s ON s.id_seguro = a.id_seguro
                 WHERE  a.placa = :placa
                 ORDER  BY a.fecha_accidente DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':placa' => $placa]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Trae un accidente por ID con datos del seguro.
     */
    public static function traerConSeguro(int $id): ?array
    {
        $pdo  = self::$db;
        $sql  = "SELECT a.*,
                        s.aseguradora,
                        s.numero_poliza,
                        s.tipo_cobertura
                 FROM   accidentes a
                 LEFT JOIN seguros s ON s.id_seguro = a.id_seguro
                 WHERE  a.id_accidente = :id
                 LIMIT  1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row  = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Cuenta accidentes activos (no cerrados) de una placa.
     */
    public static function contarActivos(string $placa): int
    {
        $pdo  = self::$db;
        $sql  = "SELECT COUNT(*) FROM accidentes
                 WHERE placa      = :placa
                   AND estado_caso != 'Cerrado'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':placa' => $placa]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Suma el costo real total de accidentes de una placa.
     */
    public static function costoTotal(string $placa): float
    {
        $pdo  = self::$db;
        $sql  = "SELECT COALESCE(SUM(costo_real), 0)
                 FROM   accidentes
                 WHERE  placa = :placa";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':placa' => $placa]);
        return (float)$stmt->fetchColumn();
    }
}
