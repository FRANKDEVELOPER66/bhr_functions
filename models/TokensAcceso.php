<?php

namespace Model;

class TokensAcceso extends ActiveRecord
{
    protected static $tabla = 'tokens_acceso';
    protected static $columnasDB = [
        'id',
        'catalogo',
        'token',
        'expira_at',
        'usado'
    ];

    public $id;
    public $catalogo;
    public $token;
    public $expira_at;
    public $usado = 0;

    public function __construct($args = [])
    {
        $this->catalogo  = $args['catalogo'] ?? '';
        $this->token     = $args['token'] ?? '';
        $this->expira_at = $args['expira_at'] ?? '';
        $this->usado     = $args['usado'] ?? 0;
    }

    // ── Generar token único ───────────────────────────────────────────────────
    public static function generar(string $catalogo): self
    {
        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', strtotime('+2 hours'));

        $t = new self([
            'catalogo'  => $catalogo,
            'token'     => $token,
            'expira_at' => $expira,
        ]);

        return $t;
    }

    // ── Buscar token válido ───────────────────────────────────────────────────
    public static function buscarValido(string $token): ?self
    {
        $ahora = date('Y-m-d H:i:s');
        $resultado = self::fetchArray("
        SELECT * FROM tokens_acceso 
        WHERE token = '{$token}' 
        AND usado = 0 
        AND expira_at > '{$ahora}'
        LIMIT 1
    ");

        if (empty($resultado)) return null;
        $t = new self($resultado[0]);
        $t->id = $resultado[0]['id'];
        return $t;
    }
}
