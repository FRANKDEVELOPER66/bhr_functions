<?php

namespace Model;

class Chequeos extends ActiveRecord
{
    protected static $tabla      = 'chequeos_vehiculo';
    protected static $idTabla    = 'id_chequeo';
    protected static $columnasDB = [
        'placa',
        'fecha_chequeo',
        'km_al_chequeo',
        'realizado_por',
        'observaciones_gen',
        'estado'
    ];

    public $id_chequeo;
    public $placa;
    public $fecha_chequeo;
    public $km_al_chequeo;
    public $realizado_por;
    public $observaciones_gen;
    public $estado;

    // Ítems del chequeo — los 17 de la hoja física
    public static $ITEMS = [
        1  => 'Tren delantero',
        2  => 'Tapicería',
        3  => 'Carrocería',
        4  => 'Pintura en general',
        5  => 'Siglas que identifican a los vehículos pintados en color naranja fluorescente y en el lugar correspondiente',
        6  => 'Lona del camión',
        7  => 'Luces y pide vías',
        8  => 'Sistema eléctrico',
        9  => 'Herramienta extra para reparación de vehículos',
        10 => 'Herramienta básica (Tricket, llave de chuchos, palanca o tubo, trozo, cable o cadena, señalizaciones etc.)',
        11 => 'Herramienta de emergencia (llave de ½, Nos. 12, 13, 14, alicate, llave ajustable, juego de desatornilladores)',
        12 => 'Repuestos necesarios de emergencias',
        13 => 'Neumático de repuesto',
        14 => 'Acumulador o batería',
        15 => 'Neumáticos',
        16 => 'Lubricante',
        17 => 'Combustible',
        // El 17 del PDF es Odómetro pero el físico tiene Combustible y Odómetro
        // Ajusta según necesites
    ];

    public function __construct($args = [])
    {
        $this->id_chequeo        = $args['id_chequeo']        ?? null;
        $this->placa             = $args['placa']             ?? '';
        $this->fecha_chequeo     = $args['fecha_chequeo']     ?? date('Y-m-d');
        $this->km_al_chequeo     = $args['km_al_chequeo']     ?? 0;
        $this->realizado_por     = $args['realizado_por']     ?? null;
        $this->observaciones_gen = $args['observaciones_gen'] ?? null;
        $this->estado            = $args['estado']            ?? 'Pendiente';
    }

    // ── QUERIES ───────────────────────────────────────────────────────────────

    /**
     * Trae todos los chequeos de una placa con conteo de ítems completados
     */
    public static function traerPorPlaca(string $placa): array
    {
        $pdo = self::$db;
        $sql = "SELECT 
                    c.*,
                    COUNT(ci.id_item) AS total_items,
                    SUM(CASE WHEN ci.resultado IS NOT NULL THEN 1 ELSE 0 END) AS items_completados
                FROM chequeos_vehiculo c
                LEFT JOIN chequeo_items ci ON c.id_chequeo = ci.id_chequeo
                WHERE c.placa = :placa
                GROUP BY c.id_chequeo
                ORDER BY c.fecha_chequeo DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':placa' => $placa]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Trae un chequeo completo con todos sus ítems
     */
    public static function traerConItems(int $id): ?array
    {
        $pdo = self::$db;

        $sqlChequeo = "SELECT * FROM chequeos_vehiculo WHERE id_chequeo = :id LIMIT 1";
        $stmt = $pdo->prepare($sqlChequeo);
        $stmt->execute([':id' => $id]);
        $chequeo = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$chequeo) return null;

        $sqlItems = "SELECT * FROM chequeo_items WHERE id_chequeo = :id ORDER BY numero_item ASC";
        $stmt = $pdo->prepare($sqlItems);
        $stmt->execute([':id' => $id]);
        $chequeo['items'] = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        return $chequeo;
    }

    /**
     * Verifica si existe un chequeo completado para el mes actual
     */
    public static function tieneChequeoMesActual(string $placa): bool
    {
        $pdo  = self::$db;
        $sql  = "SELECT COUNT(*) FROM chequeos_vehiculo
                 WHERE placa  = :placa
                   AND estado = 'Completado'
                   AND YEAR(fecha_chequeo)  = YEAR(CURDATE())
                   AND MONTH(fecha_chequeo) = MONTH(CURDATE())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':placa' => $placa]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Guarda los ítems del chequeo — reemplaza los existentes
     */
    public static function guardarItems(int $idChequeo, array $items): void
    {
        $pdo = self::$db;

        // Eliminar ítems anteriores
        $pdo->prepare("DELETE FROM chequeo_items WHERE id_chequeo = ?")->execute([$idChequeo]);

        $sql  = "INSERT INTO chequeo_items (id_chequeo, numero_item, resultado, observacion)
                 VALUES (:id_chequeo, :numero_item, :resultado, :observacion)";
        $stmt = $pdo->prepare($sql);

        foreach ($items as $item) {
            $stmt->execute([
                ':id_chequeo'  => $idChequeo,
                ':numero_item' => (int)$item['numero_item'],
                ':resultado'   => $item['resultado']   ?? null,
                ':observacion' => $item['observacion'] ?? null,
            ]);
        }
    }
}
