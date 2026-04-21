<?php

namespace Model;

class Usuarios extends ActiveRecord
{
    protected static $tabla = 'usuarios';
    protected static $columnasDB = [
        'catalogo',
        'grado',
        'arma_servicio',
        'nombre_completo',
        'plaza',
        'correo',
        'password',
        'rol',
        'activo',
        'primer_ingreso'
    ];

    public $catalogo;
    public $grado;
    public $arma_servicio;
    public $nombre_completo;
    public $plaza;
    public $correo;
    public $password;
    public $rol = 'COMTE_PTN';
    public $activo = 1;
    public $primer_ingreso = 1;

    public function __construct($args = [])
    {
        $this->catalogo        = $args['catalogo'] ?? '';
        $this->grado           = $args['grado'] ?? '';
        $this->arma_servicio   = $args['arma_servicio'] ?? '';
        $this->nombre_completo = $args['nombre_completo'] ?? '';
        $this->plaza           = $args['plaza'] ?? '';
        $this->correo          = $args['correo'] ?? '';
        $this->password        = $args['password'] ?? '';
        $this->rol             = $args['rol'] ?? 'COMTE_PTN';
        $this->activo          = $args['activo'] ?? 1;
        $this->primer_ingreso  = $args['primer_ingreso'] ?? 1;
    }

    // ── Buscar por catálogo ───────────────────────────────────────────────────
    public static function buscarPorCatalogo(string $catalogo): ?self
    {
        $resultado = self::where('catalogo', $catalogo);
        return $resultado[0] ?? null;
    }

    // ── Buscar por correo ─────────────────────────────────────────────────────
    public static function buscarPorCorreo(string $correo): ?self
    {
        $resultado = self::where('correo', $correo);
        return $resultado[0] ?? null;
    }

    // ── Verificar contraseña ──────────────────────────────────────────────────
    public function verificarPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    // ── Hashear contraseña ────────────────────────────────────────────────────
    public function hashearPassword(): void
    {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    // ── Saludo personalizado por plaza ────────────────────────────────────────
    public function saludo(): string
    {
        $esFemenino = in_array($this->catalogo, ['512921', '648782']);
        $bienvenida = $esFemenino ? 'Bienvenida' : 'Bienvenido';
        return "{$bienvenida}, {$this->plaza}";
    }
}
