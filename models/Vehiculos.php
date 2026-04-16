<?php

namespace Model;


use phpseclib3\Net\SFTP;

class Vehiculos extends ActiveRecord
{
    protected static $tabla    = 'vehiculos';
    protected static $idTabla  = 'placa';
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
        'foto_frente',
        'foto_lateral',    // ← nuevo
        'foto_trasera',    // ← nuevo
        'tarjeta_pdf',
        'cert_inventario', // ← nuevo
        'cert_sicoin',     // ← nuevo
        'id_unidad'
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
    public $foto_frente;
    public $tarjeta_pdf;
    public $id_unidad;
    public $foto_lateral;
    public $foto_trasera;
    public $cert_inventario;
    public $cert_sicoin;

    public function __construct($args = [])
    {
        $this->placa         = $args['placa']         ?? '';
        $this->numero_serie  = $args['numero_serie']  ?? '';
        $this->marca         = $args['marca']         ?? '';
        $this->modelo        = $args['modelo']        ?? '';
        $this->anio          = $args['anio']          ?? date('Y');
        $this->color         = $args['color']         ?? '';
        $this->tipo          = $args['tipo']          ?? '';
        $this->km_actuales   = $args['km_actuales']   ?? 0;
        $this->estado        = $args['estado']        ?? 'Alta';
        $this->fecha_ingreso = $args['fecha_ingreso'] ?? date('Y-m-d');
        $this->observaciones = $args['observaciones'] ?? '';
        $this->foto_frente   = $args['foto_frente']   ?? null;
        $this->foto_lateral  = $args['foto_lateral']  ?? null;
        $this->foto_trasera  = $args['foto_trasera']  ?? null;
        $this->cert_inventario = $args['cert_inventario'] ?? null;
        $this->cert_sicoin     = $args['cert_sicoin']     ?? null;
        $this->tarjeta_pdf   = $args['tarjeta_pdf']   ?? null;
        $this->id_unidad = !empty($args['id_unidad']) ? (int)$args['id_unidad'] : null;
    }

    // ── QUERIES ──────────────────────────────────────────────────────────────

    public static function traerVehiculos()
    {
        $sql = "SELECT 
                v.*,
                u.nombre           AS unidad_nombre,
                d.nombre           AS destacamento_nombre,
                d.departamento     AS destacamento_depto,
                COUNT(DISTINCT s.id_servicio)   AS total_servicios,
                COUNT(DISTINCT r.id_reparacion) AS total_reparaciones,
                MAX(s.fecha_realizado)           AS ultimo_servicio,
                CASE
                    WHEN seg.placa IS NULL        THEN 'ninguno'
                    WHEN seg.max_venc < CURDATE() THEN 'vencido'
                    ELSE 'vigente'
                END AS seguro_estado
            FROM vehiculos v
            LEFT JOIN unidades      u  ON v.id_unidad       = u.id_unidad
            LEFT JOIN destacamentos d  ON u.id_destacamento = d.id_destacamento
            LEFT JOIN servicios     s  ON v.placa = s.placa
            LEFT JOIN reparaciones  r  ON v.placa = r.placa
            LEFT JOIN (
                SELECT placa, MAX(fecha_vencimiento) AS max_venc
                FROM seguros
                WHERE estado = 'Vigente'
                GROUP BY placa
            ) seg ON v.placa = seg.placa
            GROUP BY 
                v.placa, v.numero_serie, v.marca, v.modelo, v.anio,
                v.color, v.tipo, v.km_actuales, v.estado, v.fecha_ingreso,
                v.observaciones, v.foto_frente, v.tarjeta_pdf, v.id_unidad,
                u.nombre, d.nombre, d.departamento,
                seg.placa, seg.max_venc
            ORDER BY v.marca, v.modelo";

        return self::fetchArray($sql);
    }

    public static function traerConDetalle(string $placa)
    {
        $sql = "SELECT 
                v.*,
                u.nombre            AS unidad_nombre,
                d.nombre            AS destacamento_nombre,
                d.departamento      AS destacamento_depto,
                d.municipio         AS destacamento_municipio,
                (SELECT COUNT(*) FROM servicios    WHERE placa = v.placa) AS total_servicios,
                (SELECT COUNT(*) FROM reparaciones WHERE placa = v.placa) AS total_reparaciones
            FROM vehiculos v
            LEFT JOIN unidades      u ON v.id_unidad        = u.id_unidad
            LEFT JOIN destacamentos d ON u.id_destacamento  = d.id_destacamento
            WHERE v.placa = ?
            LIMIT 1";

        $resultado = self::fetchArray($sql, [$placa]);
        return $resultado[0] ?? null;
    }

    public static function existePlaca(string $placa): bool
    {
        $resultado = self::fetchArray(
            "SELECT placa FROM vehiculos WHERE placa = ? LIMIT 1",
            [$placa]
        );
        return !empty($resultado);
    }

    public static function existeNumeroSerie(string $serie, string $placaExcluir = ''): bool
    {
        if ($placaExcluir) {
            $resultado = self::fetchArray(
                "SELECT placa FROM vehiculos WHERE numero_serie = ? AND placa <> ? LIMIT 1",
                [$serie, $placaExcluir]
            );
        } else {
            $resultado = self::fetchArray(
                "SELECT placa FROM vehiculos WHERE numero_serie = ? LIMIT 1",
                [$serie]
            );
        }
        return !empty($resultado);
    }

    public static function actualizarKm(string $placa, int $km): bool
    {
        $stmt = self::$db->prepare("UPDATE vehiculos SET km_actuales = ? WHERE placa = ?");
        $stmt->execute([$km, $placa]);
        return true;
    }

    // ── PK STRING ────────────────────────────────────────────────────────────

    public static function find($id = [])
    {
        $query     = "SELECT * FROM vehiculos WHERE placa = " . self::$db->quote($id) . " LIMIT 1";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }

    public function eliminar()
    {
        $query = "DELETE FROM vehiculos WHERE placa = " . self::$db->quote($this->placa);
        return self::$db->exec($query);
    }

    // Incluir placa (PK no-autoincrement) en el INSERT
    public function atributos()
    {
        $atributos = [];
        foreach (static::$columnasDB as $columna) {
            $columna             = strtolower($columna);
            $atributos[$columna] = $this->$columna;
        }
        return $atributos;
    }

    public static function subirArchivoSFTP(array $archivo, string $carpeta, string $placa)
    {
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            error_log("SFTP upload error code: " . $archivo['error']);
            return false;
        }

        $host     = $_ENV['SFTP_HOST']   ?? '';
        $port     = (int)($_ENV['SFTP_PORT'] ?? 22);
        $usuario  = $_ENV['SFTP_USER']   ?? '';
        $password = $_ENV['SFTP_PASS']   ?? '';
        $rutaBase = rtrim($_ENV['SFTP_PATH'] ?? '/vehiculos', '/');

        if (!$host || !$usuario || !$password) {
            error_log("SFTP Error: credenciales no configuradas en .env");
            return false;
        }

        try {
            $sftp = new SFTP($host, $port);

            if (!$sftp->login($usuario, $password)) {
                error_log("SFTP: autenticación fallida para {$usuario}@{$host}:{$port}");
                return false;
            }

            $extension    = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $sufijoCarpeta = match ($carpeta) {
                'fotos'           => '_fotos_',
                'tarjetas'        => '_tarjetas_',
                'polizas'         => '_POL_',
                'certificaciones' => '_CERT_',
                'accidentes'      => '_ACC_',
                default           => '_'
            };
            $nombreRemoto = strtoupper($placa) . $sufijoCarpeta . time() . '.' . $extension;
            $rutaRemota   = "{$rutaBase}/{$carpeta}/{$nombreRemoto}";

            // Crear carpeta remota si no existe
            $sftp->mkdir("{$rutaBase}/{$carpeta}", -1, true);

            if (!$sftp->put($rutaRemota, $archivo['tmp_name'], SFTP::SOURCE_LOCAL_FILE)) {
                error_log("SFTP: falló subida a {$rutaRemota}");
                return false;
            }

            return $nombreRemoto;
        } catch (\Exception $e) {
            error_log("SFTP Exception (subir): " . $e->getMessage());
            return false;
        }
    }

    public static function eliminarArchivoSFTP(string $carpeta, string $nombreArchivo): bool
    {
        $host     = $_ENV['SFTP_HOST']   ?? '';
        $port     = (int)($_ENV['SFTP_PORT'] ?? 22);
        $usuario  = $_ENV['SFTP_USER']   ?? '';
        $password = $_ENV['SFTP_PASS']   ?? '';
        $rutaBase = rtrim($_ENV['SFTP_PATH'] ?? '/vehiculos', '/');

        try {
            $sftp = new SFTP($host, $port);
            if (!$sftp->login($usuario, $password)) return false;

            return (bool) $sftp->delete("{$rutaBase}/{$carpeta}/{$nombreArchivo}");
        } catch (\Exception $e) {
            error_log("SFTP Exception (eliminar): " . $e->getMessage());
            return false;
        }
    }
}
