<?php
$base = $_ENV['APP_NAME'] ? '/' . $_ENV['APP_NAME'] : '';
?>

<style>
    /* ── Tabla de usuarios ──────────────────────────────────────────────────────── */
    .usuarios-header {
        background: linear-gradient(135deg, #1a1d27, #242837);
        border: 1px solid #2e3347;
        border-left: 4px solid #e8b84b;
        border-radius: 14px;
        padding: 1.5rem 2rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }

    .usuarios-header .icon-wrap {
        background: rgba(232, 184, 75, .15);
        border: 1px solid rgba(232, 184, 75, .25);
        border-radius: 12px;
        width: 52px;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #e8b84b;
        flex-shrink: 0;
    }

    .usuarios-header h1 {
        font-family: 'Rajdhani', sans-serif;
        font-size: 1.5rem;
        font-weight: 700;
        color: #e8eaf0;
        margin: 0;
        letter-spacing: .5px;
    }

    .usuarios-header p {
        font-size: .82rem;
        color: #7c8398;
        margin: .2rem 0 0;
    }

    .usuarios-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1rem;
    }

    .usuario-card {
        background: #1a1d27;
        border: 1px solid #2e3347;
        border-radius: 14px;
        padding: 1.25rem;
        transition: all .25s;
    }

    .usuario-card:hover {
        border-color: rgba(232, 184, 75, .3);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, .3);
    }

    .usuario-card.inactivo {
        opacity: .5;
        border-color: rgba(224, 82, 82, .2);
    }

    .usuario-rol {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .2rem .65rem;
        border-radius: 20px;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
        margin-bottom: .75rem;
    }

    .rol-SUPERUSUARIO {
        background: rgba(232, 184, 75, .15);
        color: #e8b84b;
        border: 1px solid rgba(232, 184, 75, .3);
    }

    .rol-COMTE_BHR {
        background: rgba(111, 66, 193, .15);
        color: #a78bfa;
        border: 1px solid rgba(111, 66, 193, .3);
    }

    .rol-COMTE_CIA {
        background: rgba(58, 123, 213, .15);
        color: #5b9bd5;
        border: 1px solid rgba(58, 123, 213, .3);
    }

    .rol-COMTE_PTN {
        background: rgba(76, 175, 125, .15);
        color: #4caf7d;
        border: 1px solid rgba(76, 175, 125, .3);
    }

    .usuario-nombre {
        font-family: 'Rajdhani', sans-serif;
        font-size: 1.1rem;
        font-weight: 700;
        color: #e8eaf0;
        margin-bottom: .2rem;
    }

    .usuario-sub {
        font-size: .78rem;
        color: #7c8398;
        margin-bottom: .75rem;
    }

    .usuario-correo {
        font-size: .78rem;
        color: #4a5068;
        margin-bottom: .75rem;
        display: flex;
        align-items: center;
        gap: .4rem;
    }

    .usuario-acciones {
        display: flex;
        gap: .4rem;
        flex-wrap: wrap;
    }

    .btn-usr {
        flex: 1;
        background: #242837;
        border: 1px solid #2e3347;
        border-radius: 8px;
        color: #e8eaf0;
        padding: .45rem .5rem;
        font-size: .75rem;
        cursor: pointer;
        transition: all .2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .3rem;
        min-width: 0;
    }

    .btn-usr:hover {
        border-color: #e8b84b;
        color: #e8b84b;
    }

    .btn-usr.danger:hover {
        border-color: #e05252;
        color: #e05252;
    }

    .btn-usr.success:hover {
        border-color: #4caf7d;
        color: #4caf7d;
    }

    .btn-nuevo {
        background: linear-gradient(135deg, #e8b84b, #d4a032);
        border: none;
        border-radius: 10px;
        color: #0f1117;
        padding: .6rem 1.25rem;
        font-family: 'Rajdhani', sans-serif;
        font-size: .95rem;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: .5rem;
        transition: all .3s;
        letter-spacing: .5px;
    }

    .btn-nuevo:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(232, 184, 75, .35);
    }

    .primer-ingreso-badge {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        background: rgba(232, 184, 75, .1);
        color: #e8b84b;
        border: 1px solid rgba(232, 184, 75, .25);
        border-radius: 20px;
        padding: .15rem .55rem;
        font-size: .68rem;
        font-weight: 600;
        margin-bottom: .5rem;
    }
</style>

<!-- Header -->
<div class="usuarios-header">
    <div class="icon-wrap"><i class="bi bi-people-fill"></i></div>
    <div style="flex:1;">
        <h1>Gestión de Usuarios</h1>
        <p>Administración de accesos al sistema VEHÍCULOS BHR</p>
    </div>
    <button class="btn-nuevo" id="btnNuevoUsuario">
        <i class="bi bi-person-plus-fill"></i> Nuevo Usuario
    </button>
</div>

<!-- Grid de usuarios -->
<div class="usuarios-grid" id="usuariosGrid">
    <div style="text-align:center;padding:3rem;color:#7c8398;grid-column:1/-1;">
        <i class="bi bi-hourglass-split" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.75rem;"></i>
        <p>Cargando usuarios...</p>
    </div>
</div>
<script>
    const MI_CATALOGO = '<?= $_SESSION['auth_user'] ?? '' ?>';
</script>
<script src="<?= asset('build/js/usuarios/index.js') ?>" type="module"></script>