<?php

namespace Model;

class Seguros extends ActiveRecord
{
    protected static $tabla        = 'seguros';
    protected static $idTabla      = 'id_seguro';
    protected static $columnasDB   = [
        'placa',
        'aseguradora',
        'numero_poliza',
        'tipo_cobertura',
        'fecha_inicio',
        'fecha_vencimiento',
        'prima_anual',
        'agente_contacto',
        'telefono_agente',
        'archivo_poliza',
        'estado',
        'observaciones'
    ];

    public $id_seguro;
    public $placa;
    public $aseguradora;
    public $numero_poliza;
    public $tipo_cobertura;
    public $fecha_inicio;
    public $fecha_vencimiento;
    public $prima_anual;
    public $agente_contacto;
    public $telefono_agente;
    public $archivo_poliza;
    public $estado;
    public $observaciones;

    public function __construct($args = [])
    {
        $this->id_seguro         = $args['id_seguro']         ?? null;
        $this->placa             = $args['placa']             ?? '';
        $this->aseguradora       = $args['aseguradora']       ?? '';
        $this->numero_poliza     = $args['numero_poliza']     ?? '';
        $this->tipo_cobertura    = $args['tipo_cobertura']    ?? 'Básico';
        $this->fecha_inicio      = $args['fecha_inicio']      ?? null;
        $this->fecha_vencimiento = $args['fecha_vencimiento'] ?? null;
        $this->prima_anual       = $args['prima_anual']       ?? null;
        $this->agente_contacto   = $args['agente_contacto']   ?? null;
        $this->telefono_agente   = $args['telefono_agente']   ?? null;
        $this->archivo_poliza    = $args['archivo_poliza']    ?? null;
        $this->estado            = $args['estado']            ?? 'Vigente';
        $this->observaciones     = $args['observaciones']     ?? null;
    }

    // ── CONSULTAS ─────────────────────────────────────────────────────────────

    /**
     * Trae todos los seguros de una placa, ordenados por fecha de inicio desc.
     */
    public static function traerPorPlaca(string $placa): array
    {
        $pdo   = self::$db;
        $sql   = "SELECT * FROM seguros
                  WHERE placa = :placa
                  ORDER BY fecha_inicio DESC";
        $stmt  = $pdo->prepare($sql);
        $stmt->execute([':placa' => $placa]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Trae el seguro vigente activo de una placa (el más reciente con estado Vigente).
     */
    public static function traerVigente(string $placa): ?array
    {
        $pdo  = self::$db;
        $sql  = "SELECT * FROM seguros
                 WHERE placa  = :placa
                   AND estado = 'Vigente'
                 ORDER BY fecha_vencimiento DESC
                 LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':placa' => $placa]);
        $row  = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Verifica si ya existe una póliza con ese número (excluyendo un id al editar).
     */
    public static function existePoliza(string $numero_poliza, int $excluirId = 0): bool
    {
        $pdo  = self::$db;
        $sql  = "SELECT COUNT(*) FROM seguros
                 WHERE numero_poliza = :poliza
                   AND id_seguro    != :excluir";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':poliza'   => $numero_poliza,
            ':excluir'  => $excluirId
        ]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Actualiza el estado de seguros vencidos para una placa.
     * Útil para llamar antes de mostrar la ficha.
     */
    public static function actualizarEstadosVencidos(string $placa): void
    {
        $pdo  = self::$db;
        $sql  = "UPDATE seguros
                 SET estado = 'Vencido'
                 WHERE placa             = :placa
                   AND estado           = 'Vigente'
                   AND fecha_vencimiento < CURDATE()";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':placa' => $placa]);
    }

    /**
     * Comprueba si el seguro está próximo a vencer (dentro de N días).
     */
    public function proximoAVencer(int $dias = 30): bool
    {
        if (!$this->fecha_vencimiento) return false;
        $vence = new \DateTime($this->fecha_vencimiento);
        $hoy   = new \DateTime();
        $diff  = (int)$hoy->diff($vence)->days;
        return $vence > $hoy && $diff <= $dias;
    }
}
