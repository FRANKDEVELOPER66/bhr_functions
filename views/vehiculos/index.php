<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap');

    :root {
        --dark: #0f1117;
        --dark-2: #1a1d27;
        --dark-3: #242837;
        --accent: #e8b84b;
        --accent-2: #d4a032;
        --danger: #e05252;
        --success: #4caf7d;
        --text-main: #e8eaf0;
        --text-muted: #7c8398;
        --border: #2e3347;
        --radius: 12px;
    }

    body {
        background: var(--dark);
        color: var(--text-main);
        font-family: 'Inter', sans-serif;
    }

    /* ── HEADER ───────────────────────────────── */
    .veh-header {
        background: linear-gradient(135deg, var(--dark-2), var(--dark-3));
        border: 1px solid var(--border);
        border-left: 4px solid var(--accent);
        padding: 1.75rem 2rem;
        border-radius: var(--radius);
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }

    .veh-header .icon-wrap {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, var(--accent), var(--accent-2));
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        color: var(--dark);
        flex-shrink: 0;
    }

    .veh-header h1 {
        font-family: 'Rajdhani', sans-serif;
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        letter-spacing: 1px;
    }

    .veh-header p {
        margin: 0;
        color: var(--text-muted);
        font-size: .875rem;
    }

    /* ── FLOATING BTN ─────────────────────────── */
    .floating-btn {
        position: fixed;
        bottom: 32px;
        right: 32px;
        width: 62px;
        height: 62px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--accent), var(--accent-2));
        border: none;
        color: var(--dark);
        font-size: 26px;
        box-shadow: 0 8px 30px rgba(232, 184, 75, .35);
        z-index: 1000;
        transition: all .3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .floating-btn:hover {
        transform: translateY(-4px) scale(1.08);
        box-shadow: 0 14px 40px rgba(232, 184, 75, .5);
    }

    .floating-btn.activo {
        background: linear-gradient(135deg, var(--danger), #c93030);
        box-shadow: 0 8px 30px rgba(224, 82, 82, .35);
        color: #fff;
    }

    .floating-btn.activo:hover {
        box-shadow: 0 14px 40px rgba(224, 82, 82, .5);
    }

    /* ── FORM CONTAINER ───────────────────────── */
    .form-container {
        background: var(--dark-2);
        border: 1px solid var(--border);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .4);
    }

    .form-header {
        background: linear-gradient(90deg, var(--dark-3), #1f2335);
        border-bottom: 1px solid var(--border);
        padding: 1.25rem 2rem;
        display: flex;
        align-items: center;
        gap: .75rem;
    }

    .form-header .fh-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: rgba(232, 184, 75, .15);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--accent);
        font-size: 1.1rem;
    }

    .form-header h3 {
        font-family: 'Rajdhani', sans-serif;
        font-size: 1.3rem;
        font-weight: 600;
        color: var(--text-main);
        margin: 0;
        letter-spacing: .5px;
    }

    .form-body {
        padding: 2rem;
    }

    /* ── INPUTS ───────────────────────────────── */
    .form-label {
        font-size: .8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: var(--text-muted);
        margin-bottom: .5rem;
        display: flex;
        align-items: center;
        gap: .4rem;
    }

    .form-label i {
        color: var(--accent);
    }

    .form-control,
    .form-select {
        background: var(--dark-3) !important;
        border: 1.5px solid var(--border) !important;
        border-radius: 8px !important;
        color: var(--text-main) !important;
        padding: .7rem 1rem !important;
        transition: all .25s ease;
        font-family: 'Inter', sans-serif;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--accent) !important;
        box-shadow: 0 0 0 3px rgba(232, 184, 75, .15) !important;
        background: var(--dark-3) !important;
    }

    .form-control::placeholder {
        color: var(--text-muted) !important;
    }

    .form-select option {
        background: var(--dark-3);
        color: var(--text-main);
    }

    /* File input */
    .file-upload-area {
        border: 2px dashed var(--border);
        border-radius: 10px;
        padding: 1.25rem;
        text-align: center;
        cursor: pointer;
        transition: all .25s ease;
        position: relative;
        overflow: hidden;
    }

    .file-upload-area:hover {
        border-color: var(--accent);
        background: rgba(232, 184, 75, .05);
    }

    .file-upload-area input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }

    .file-upload-area .upload-icon {
        font-size: 1.8rem;
        color: var(--text-muted);
        margin-bottom: .4rem;
    }

    .file-upload-area .upload-label {
        font-size: .82rem;
        color: var(--text-muted);
    }

    .file-upload-area .upload-label span {
        color: var(--accent);
        font-weight: 600;
    }

    .file-upload-area.has-file {
        border-color: var(--success);
        background: rgba(76, 175, 125, .05);
    }

    .file-upload-area.has-file .upload-icon {
        color: var(--success);
    }

    /* Preview foto */
    .foto-preview {
        width: 100%;
        height: 140px;
        object-fit: cover;
        border-radius: 8px;
        display: none;
        margin-top: .75rem;
        border: 2px solid var(--border);
    }

    .foto-preview.visible {
        display: block;
    }

    /* Section divider */
    .section-divider {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin: 1.75rem 0 1.25rem;
        color: var(--text-muted);
        font-size: .75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .8px;
    }

    .section-divider::before,
    .section-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border);
    }

    .section-divider i {
        color: var(--accent);
    }

    /* ── BUTTONS ──────────────────────────────── */
    .btn-guardar {
        background: linear-gradient(135deg, var(--accent), var(--accent-2));
        border: none;
        color: var(--dark);
        padding: .85rem 2rem;
        border-radius: 10px;
        font-family: 'Rajdhani', sans-serif;
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: .5px;
        transition: all .3s ease;
        cursor: pointer;
        width: 100%;
    }

    .btn-guardar:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(232, 184, 75, .4);
    }

    .btn-guardar:disabled {
        opacity: .5;
        cursor: not-allowed;
        transform: none;
    }

    .btn-modificar-main {
        background: linear-gradient(135deg, #3a7bd5, #2563b0);
        border: none;
        color: #fff;
        padding: .85rem 2rem;
        border-radius: 10px;
        font-family: 'Rajdhani', sans-serif;
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: .5px;
        transition: all .3s ease;
        cursor: pointer;
        width: 100%;
    }

    .btn-modificar-main:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(58, 123, 213, .4);
    }

    .btn-cancelar-main {
        background: transparent;
        border: 1.5px solid var(--danger);
        color: var(--danger);
        padding: .85rem 2rem;
        border-radius: 10px;
        font-family: 'Rajdhani', sans-serif;
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: .5px;
        transition: all .3s ease;
        cursor: pointer;
        width: 100%;
    }

    .btn-cancelar-main:hover {
        background: var(--danger);
        color: #fff;
        box-shadow: 0 8px 25px rgba(224, 82, 82, .3);
    }

    /* ── FILTROS ──────────────────────────────── */
    .filtros-container {
        background: var(--dark-2);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .filtros-container label {
        color: var(--text-muted);
        font-size: .8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .5px;
        white-space: nowrap;
    }

    .filtro-select {
        background: var(--dark-3);
        border: 1.5px solid var(--border);
        border-radius: 8px;
        color: var(--text-main);
        padding: .5rem 1rem;
        font-size: .875rem;
        cursor: pointer;
        transition: all .2s ease;
        min-width: 150px;
    }

    .filtro-select:focus {
        border-color: var(--accent);
        outline: none;
        box-shadow: 0 0 0 3px rgba(232, 184, 75, .15);
    }

    .filtro-select option {
        background: var(--dark-3);
    }

    .filtro-search {
        background: var(--dark-3);
        border: 1.5px solid var(--border);
        border-radius: 8px;
        color: var(--text-main);
        padding: .5rem 1rem;
        font-size: .875rem;
        transition: all .2s ease;
        flex: 1;
        min-width: 180px;
    }

    .filtro-search:focus {
        border-color: var(--accent);
        outline: none;
        box-shadow: 0 0 0 3px rgba(232, 184, 75, .15);
    }

    .filtro-search::placeholder {
        color: var(--text-muted);
    }

    .btn-limpiar-filtros {
        background: transparent;
        border: 1.5px solid var(--border);
        color: var(--text-muted);
        border-radius: 8px;
        padding: .5rem 1rem;
        font-size: .8rem;
        cursor: pointer;
        transition: all .2s ease;
        white-space: nowrap;
    }

    .btn-limpiar-filtros:hover {
        border-color: var(--accent);
        color: var(--accent);
    }

    .contador-resultados {
        margin-left: auto;
        color: var(--text-muted);
        font-size: .82rem;
        white-space: nowrap;
    }

    .contador-resultados span {
        color: var(--accent);
        font-weight: 700;
    }

    /* ── CARDS GRID ───────────────────────────── */
    .cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1.25rem;
    }

    /* ── VEHICLE CARD ─────────────────────────── */
    .vehicle-card {
        background: var(--dark-2);
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
        transition: all .3s ease;
        cursor: default;
        display: flex;
        flex-direction: column;
        animation: fadeInCard .4s ease both;
    }

    .vehicle-card:hover {
        border-color: rgba(232, 184, 75, .4);
        box-shadow: 0 12px 40px rgba(0, 0, 0, .4);
        transform: translateY(-4px);
    }

    @keyframes fadeInCard {
        from {
            opacity: 0;
            transform: translateY(16px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Foto */
    .card-foto {
        width: 100%;
        aspect-ratio: 1 / 1;
        background: var(--dark-3);
        position: relative;
        overflow: hidden;
    }

    .card-foto img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .4s ease;
    }

    .vehicle-card:hover .card-foto img {
        transform: scale(1.05);
    }

    .card-foto .no-foto {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        color: var(--text-muted);
    }

    .card-foto .no-foto i {
        font-size: 3rem;
        opacity: .3;
    }

    .card-foto .no-foto span {
        font-size: .75rem;
        opacity: .5;
    }

    /* Badge estado sobre la foto */
    .card-estado {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: .25rem .7rem;
        border-radius: 20px;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .4px;
        backdrop-filter: blur(8px);
    }

    .estado-Alta {
        background: rgba(76, 175, 125, .25);
        color: #4caf7d;
        border: 1px solid rgba(76, 175, 125, .4);
    }

    .estado-Baja {
        background: rgba(224, 82, 82, .25);
        color: var(--danger);
        border: 1px solid rgba(224, 82, 82, .4);
    }

    .estado-Taller {
        background: rgba(232, 184, 75, .25);
        color: var(--accent);
        border: 1px solid rgba(232, 184, 75, .4);
    }

    /* Badge PDF */
    .card-pdf-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: rgba(224, 82, 82, .8);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .85rem;
        text-decoration: none;
        backdrop-filter: blur(8px);
        transition: all .2s ease;
    }

    .card-pdf-badge:hover {
        background: rgba(224, 82, 82, 1);
        transform: scale(1.1);
        color: #fff;
    }

    /* Info */
    .card-info {
        padding: 1rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: .35rem;
    }

    .card-placa {
        font-family: 'Rajdhani', sans-serif;
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--accent);
        letter-spacing: 2px;
        line-height: 1;
    }

    .card-vehiculo {
        font-size: .9rem;
        font-weight: 600;
        color: var(--text-main);
        line-height: 1.3;
    }

    .card-tipo {
        font-size: .75rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: .3rem;
    }

    /* SweetAlert sobre el modal de ficha */
    .swal-over-modal {
        z-index: 9999 !important;
    }

    /* Acciones */
    .card-acciones {
        padding: .75rem 1rem;
        border-top: 1px solid var(--border);
        display: flex;
        gap: .5rem;
    }

    .btn-card-action {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        padding: .5rem;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-size: .78rem;
        font-weight: 600;
        transition: all .2s ease;
        font-family: 'Inter', sans-serif;
    }

    .btn-card-edit {
        background: rgba(58, 123, 213, .15);
        color: #5b9bd5;
        border: 1px solid rgba(58, 123, 213, .2);
    }

    .btn-card-edit:hover {
        background: rgba(58, 123, 213, .3);
    }

    .btn-card-del {
        background: rgba(224, 82, 82, .15);
        color: var(--danger);
        border: 1px solid rgba(224, 82, 82, .2);
    }

    .btn-card-del:hover {
        background: rgba(224, 82, 82, .3);
    }

    /* Empty state */
    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 4rem;
        opacity: .2;
        display: block;
        margin-bottom: 1rem;
    }

    .empty-state p {
        font-size: .95rem;
        margin: 0;
    }

    /* Animaciones */
    .slide-down {
        animation: slideDown .4s ease-out;
    }

    .slide-up {
        animation: slideUp .3s ease-in;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-24px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideUp {
        from {
            opacity: 1;
            transform: translateY(0);
        }

        to {
            opacity: 0;
            transform: translateY(-24px);
        }
    }

    @media (max-width: 576px) {
        .cards-grid {
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: .75rem;
        }

        .filtros-container {
            gap: .6rem;
        }
    }

    .ficha-tab.activo {
        color: var(--accent) !important;
        border-bottom-color: var(--accent) !important;
        background: rgba(232, 184, 75, .05) !important;
    }

    .ficha-tab:hover {
        color: var(--text-main) !important;
    }

    .ficha-dato {
        background: var(--dark-3);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: .6rem .85rem;
        display: flex;
        flex-direction: column;
        gap: .2rem;
    }

    .ficha-dato span {
        font-size: .7rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .5px;
        font-weight: 600;
    }

    .ficha-dato strong {
        font-size: .9rem;
        color: var(--text-main);
        font-weight: 600;
    }

    .svc-row {
        background: var(--dark-3);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: .85rem 1rem;
        margin-bottom: .6rem;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr auto;
        gap: .5rem;
        align-items: center;
    }

    .svc-label {
        font-size: .68rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .svc-val {
        font-size: .85rem;
        color: var(--text-main);
        font-weight: 600;
    }
</style>

<div class="container-fluid mt-4">

    <!-- Header -->
    <div class="veh-header">
        <div class="icon-wrap"><i class="bi bi-truck-front-fill"></i></div>
        <div>
            <h1>Control de Vehículos</h1>
            <p>Registro, historial de servicios y estado de flota</p>
        </div>
    </div>

    <!-- Botón flotante -->
    <button id="btnFlotante" class="floating-btn" title="Nuevo Vehículo">
        <i class="bi bi-plus"></i>
    </button>

    <!-- ── FORMULARIO (inicia oculto) ─────────── -->
    <div class="row justify-content-center mb-4" id="contenedorFormulario" style="display:none;">
        <div class="col-lg-11">
            <div class="form-container">
                <div class="form-header">
                    <div class="fh-icon"><i class="bi bi-truck"></i></div>
                    <h3 id="tituloFormulario">Nuevo Vehículo</h3>
                </div>

                <form id="formularioVehiculo" class="form-body" enctype="multipart/form-data">
                    <input type="hidden" name="placa_original" id="placa_original">

                    <!-- Identificación -->
                    <div class="section-divider"><i class="bi bi-card-text"></i> Identificación</div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label"><i class="bi bi-hash"></i> Placa *</label>
                            <input type="text" name="placa" id="placa" class="form-control"
                                placeholder="Ej: ABC-123" required
                                style="text-transform:uppercase;font-weight:600;letter-spacing:1px">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label"><i class="bi bi-upc-scan"></i> Número de Serie *</label>
                            <input type="text" name="numero_serie" id="numero_serie" class="form-control"
                                placeholder="VIN / Número de chasis" required style="text-transform:uppercase">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-calendar-event"></i> Fecha de Ingreso *</label>
                            <input type="date" name="fecha_ingreso" id="fecha_ingreso" class="form-control" required>
                        </div>
                    </div>

                    <!-- Datos del vehículo -->
                    <div class="section-divider"><i class="bi bi-car-front"></i> Datos del Vehículo</div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label"><i class="bi bi-building"></i> Marca *</label>
                            <input type="text" name="marca" id="marca" class="form-control" placeholder="Toyota, Ford..." required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><i class="bi bi-tag"></i> Modelo *</label>
                            <input type="text" name="modelo" id="modelo" class="form-control" placeholder="Hilux, Ranger..." required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label"><i class="bi bi-calendar3"></i> Año *</label>
                            <input type="number" name="anio" id="anio" class="form-control" placeholder="2024" min="1990" max="2035" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label"><i class="bi bi-palette"></i> Color *</label>
                            <input type="text" name="color" id="color" class="form-control" placeholder="Blanco..." required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label"><i class="bi bi-truck"></i> Tipo *</label>
                            <select name="tipo" id="tipo" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <option value="Automóvil">Automóvil</option>
                                <option value="Pickup">Pickup</option>
                                <option value="Camión">Camión</option>
                                <option value="Motocicleta">Motocicleta</option>
                                <option value="Furgoneta">Furgoneta</option>
                                <option value="Blindado">Blindado</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                    </div>

                    <!-- Estado y KM -->
                    <div class="section-divider"><i class="bi bi-speedometer2"></i> Estado Operacional</div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label"><i class="bi bi-activity"></i> Estado *</label>
                            <select name="estado" id="estado" class="form-select" required>
                                <option value="Alta">Alta (Operativo)</option>
                                <option value="Baja">Baja (Fuera de servicio)</option>
                                <option value="Taller">Taller (En reparación)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><i class="bi bi-speedometer"></i> Kilometraje Actual</label>
                            <input type="number" name="km_actuales" id="km_actuales" class="form-control" placeholder="0" min="0" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-file-text"></i> Observaciones</label>
                            <textarea name="observaciones" id="observaciones" class="form-control" rows="2" placeholder="Información adicional..."></textarea>
                        </div>
                    </div>

                    <!-- Archivos -->
                    <div class="section-divider"><i class="bi bi-paperclip"></i> Archivos</div>
                    <div class="row mb-4">
                        <div class="col-md-5">
                            <label class="form-label"><i class="bi bi-camera"></i> Foto de Frente</label>
                            <div class="file-upload-area" id="areaFoto">
                                <input type="file" name="foto_frente" id="foto_frente" accept="image/jpeg,image/png,image/webp">
                                <div class="upload-icon"><i class="bi bi-image"></i></div>
                                <div class="upload-label">
                                    <span>Haz clic</span> o arrastra la foto aquí<br>
                                    <small>JPG, PNG, WEBP — máx. 5 MB</small>
                                </div>
                            </div>
                            <img id="fotoPreview" class="foto-preview" src="" alt="Preview">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label"><i class="bi bi-file-earmark-pdf"></i> Tarjeta de Circulación (PDF)</label>
                            <div class="file-upload-area" id="areaPdf">
                                <input type="file" name="tarjeta_pdf" id="tarjeta_pdf" accept="application/pdf">
                                <div class="upload-icon"><i class="bi bi-file-pdf"></i></div>
                                <div class="upload-label">
                                    <span>Haz clic</span> o arrastra el PDF aquí<br>
                                    <small>Solo PDF — máx. 10 MB</small>
                                </div>
                            </div>
                            <div id="pdfNombre" style="margin-top:.5rem;font-size:.8rem;color:var(--success);display:none;">
                                <i class="bi bi-check-circle-fill"></i> <span></span>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div id="fotoActualContainer" style="display:none;width:100%">
                                <label class="form-label"><i class="bi bi-image-fill"></i> Foto actual</label>
                                <img id="fotoActual" src="" alt="Foto actual"
                                    style="width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:8px;border:2px solid var(--border)">
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="row">
                        <div class="col" id="contenedorBtnGuardar">
                            <button type="submit" form="formularioVehiculo" id="btnGuardar" class="btn-guardar">
                                <i class="bi bi-save-fill me-2"></i> Registrar Vehículo
                            </button>
                        </div>
                        <div class="col" id="contenedorBtnModificar" style="display:none;">
                            <button type="button" id="btnModificar" class="btn-modificar-main">
                                <i class="bi bi-pencil-square me-2"></i> Guardar Cambios
                            </button>
                        </div>
                        <div class="col" id="contenedorBtnCancelar">
                            <button type="button" id="btnCancelar" class="btn-cancelar-main">
                                <i class="bi bi-x-circle me-2"></i> Cancelar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ── VISTA DE CARTAS ─────────────────────── -->
    <div id="contenedorTabla">

        <!-- Filtros -->
        <div class="filtros-container">
            <label><i class="bi bi-funnel"></i> Filtrar:</label>

            <select id="filtroTipo" class="filtro-select">
                <option value="">Todos los tipos</option>
                <option value="Automóvil">Automóvil</option>
                <option value="Pickup">Pickup</option>
                <option value="Camión">Camión</option>
                <option value="Motocicleta">Motocicleta</option>
                <option value="Furgoneta">Furgoneta</option>
                <option value="Blindado">Blindado</option>
                <option value="Otro">Otro</option>
            </select>

            <select id="filtroEstado" class="filtro-select">
                <option value="">Todos los estados</option>
                <option value="Alta">Alta</option>
                <option value="Baja">Baja</option>
                <option value="Taller">Taller</option>
            </select>

            <input type="text" id="filtroBusqueda" class="filtro-search"
                placeholder="&#xF52A; Buscar por placa, marca, modelo...">

            <button id="btnLimpiarFiltros" class="btn-limpiar-filtros">
                <i class="bi bi-x-circle"></i> Limpiar
            </button>

            <div class="contador-resultados">
                Mostrando <span id="contadorVisible">0</span> vehículo(s)
            </div>
        </div>

        <!-- Grid de cartas -->
        <div class="cards-grid" id="cardsGrid">
            <div class="empty-state">
                <i class="bi bi-truck"></i>
                <p>Cargando vehículos...</p>
            </div>
        </div>
    </div>
    <!-- ── MODAL FICHA VEHÍCULO ──────────────────────────────────────────────── -->
    <div id="modalFicha" style="
    display:none;
    position:fixed;inset:0;
    background:rgba(0,0,0,.7);
    backdrop-filter:blur(6px);
    z-index:2000;
    align-items:center;
    justify-content:center;
    padding:1rem;">

        <div style="
        background:var(--dark-2);
        border:1px solid var(--border);
        border-radius:18px;
        width:100%;max-width:900px;
        max-height:90vh;
        display:flex;flex-direction:column;
        box-shadow:0 30px 80px rgba(0,0,0,.6);
        overflow:hidden;">

            <!-- Header modal -->
            <div style="
            background:linear-gradient(90deg,var(--dark-3),#1f2335);
            border-bottom:1px solid var(--border);
            padding:1.25rem 1.5rem;
            display:flex;align-items:center;gap:1rem;flex-shrink:0;">
                <div style="
                width:42px;height:42px;border-radius:10px;
                background:linear-gradient(135deg,var(--accent),var(--accent-2));
                display:flex;align-items:center;justify-content:center;
                color:var(--dark);font-size:1.2rem;flex-shrink:0;">
                    <i class="bi bi-card-checklist"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div id="fichaPlaca" style="
                    font-family:'Rajdhani',sans-serif;font-size:1.4rem;
                    font-weight:700;color:var(--accent);letter-spacing:2px;"></div>
                    <div id="fichaVehiculo" style="font-size:.85rem;color:var(--text-muted);"></div>
                </div>
                <button onclick="cerrarFicha()" style="
                background:rgba(224,82,82,.15);border:1px solid rgba(224,82,82,.3);
                color:var(--danger);width:36px;height:36px;border-radius:8px;
                cursor:pointer;font-size:1rem;display:flex;align-items:center;
                justify-content:center;flex-shrink:0;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Tabs -->
            <div style="
            display:flex;border-bottom:1px solid var(--border);
            background:var(--dark-3);flex-shrink:0;">
                <button class="ficha-tab activo" data-tab="info" onclick="switchTab(this,'info')" style="
                flex:1;padding:.85rem;border:none;background:transparent;
                color:var(--text-muted);cursor:pointer;font-size:.85rem;
                font-weight:600;letter-spacing:.4px;transition:all .2s;
                border-bottom:2px solid transparent;font-family:'Inter',sans-serif;">
                    <i class="bi bi-info-circle me-1"></i> Info General
                </button>
                <button class="ficha-tab" data-tab="servicios" onclick="switchTab(this,'servicios')" style="
                flex:1;padding:.85rem;border:none;background:transparent;
                color:var(--text-muted);cursor:pointer;font-size:.85rem;
                font-weight:600;letter-spacing:.4px;transition:all .2s;
                border-bottom:2px solid transparent;font-family:'Inter',sans-serif;">
                    <i class="bi bi-tools me-1"></i> Servicios
                    <span id="badgeServicios" style="
                    background:var(--accent);color:var(--dark);
                    border-radius:20px;padding:.1rem .5rem;
                    font-size:.7rem;margin-left:.3rem;">0</span>
                </button>
                <button class="ficha-tab" data-tab="reparaciones" onclick="switchTab(this,'reparaciones')" style="
                flex:1;padding:.85rem;border:none;background:transparent;
                color:var(--text-muted);cursor:pointer;font-size:.85rem;
                font-weight:600;letter-spacing:.4px;transition:all .2s;
                border-bottom:2px solid transparent;font-family:'Inter',sans-serif;">
                    <i class="bi bi-wrench-adjustable me-1"></i> Reparaciones
                    <span id="badgeReparaciones" style="
                    background:var(--danger);color:#fff;
                    border-radius:20px;padding:.1rem .5rem;
                    font-size:.7rem;margin-left:.3rem;">0</span>
                </button>
            </div>

            <!-- Contenido tabs -->
            <div style="overflow-y:auto;flex:1;padding:1.5rem;">

                <!-- TAB INFO ─────────────────────────────────────── -->
                <div id="tabInfo" class="ficha-tab-content">
                    <div style="display:grid;grid-template-columns:200px 1fr;gap:1.5rem;">
                        <!-- Foto -->
                        <div>
                            <div id="fichaFotoWrap" style="
                width:100%;aspect-ratio:1/1;border-radius:12px;
                overflow:hidden;background:var(--dark-3);
                border:2px solid var(--border);position:relative;">
                                <img id="fichaFoto" src="" alt="" style="
                    width:100%;height:100%;object-fit:cover;display:none;">
                                <div id="fichaNoFoto" style="
                    width:100%;height:100%;display:flex;flex-direction:column;
                    align-items:center;justify-content:center;
                    color:var(--text-muted);gap:.5rem;">
                                    <i class="bi bi-truck-front" style="font-size:3rem;opacity:.3;"></i>
                                    <span style="font-size:.75rem;opacity:.5;">Sin foto</span>
                                </div>
                            </div>
                            <a id="fichaPdfBtn" href="#" target="_blank" style="
                display:none;margin-top:.75rem;width:100%;
                padding:.6rem;border-radius:8px;text-align:center;
                background:rgba(224,82,82,.15);border:1px solid rgba(224,82,82,.3);
                color:#e05252;font-size:.8rem;font-weight:600;
                text-decoration:none;">
                                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Ver Tarjeta PDF
                            </a>
                        </div>
                        <!-- Datos -->
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                            <div class="ficha-dato"><span>Placa</span><strong id="fd-placa">—</strong></div>
                            <div class="ficha-dato"><span>N° Serie</span><strong id="fd-serie">—</strong></div>
                            <div class="ficha-dato"><span>Marca</span><strong id="fd-marca">—</strong></div>
                            <div class="ficha-dato"><span>Modelo</span><strong id="fd-modelo">—</strong></div>
                            <div class="ficha-dato"><span>Año</span><strong id="fd-anio">—</strong></div>
                            <div class="ficha-dato"><span>Color</span><strong id="fd-color">—</strong></div>
                            <div class="ficha-dato"><span>Tipo</span><strong id="fd-tipo">—</strong></div>
                            <div class="ficha-dato"><span>Estado</span><strong id="fd-estado">—</strong></div>
                            <div class="ficha-dato"><span>KM Actuales</span><strong id="fd-km">—</strong></div>
                            <div class="ficha-dato"><span>Ingreso</span><strong id="fd-ingreso">—</strong></div>
                            <div class="ficha-dato" style="grid-column:1/-1;">
                                <span>Observaciones</span><strong id="fd-obs">—</strong>
                            </div>
                        </div>
                    </div>
                    <!-- Alerta km vencido -->
                    <div id="fichaAlerta" style="display:none;margin-top:1.25rem;
        background:rgba(224,82,82,.1);border:1px solid rgba(224,82,82,.4);
        border-radius:10px;padding:1rem 1.25rem;
        display:flex;align-items:center;gap:.75rem;">
                        <i class="bi bi-exclamation-triangle-fill" style="color:var(--danger);font-size:1.4rem;flex-shrink:0;"></i>
                        <div>
                            <div style="color:var(--danger);font-weight:700;font-size:.9rem;">¡Servicio vencido!</div>
                            <div id="fichaAlertaTexto" style="color:var(--text-muted);font-size:.82rem;"></div>
                        </div>
                    </div>
                    <!-- Próximo servicio OK -->
                    <div id="fichaProximo" style="display:none;margin-top:1.25rem;
        background:rgba(76,175,125,.1);border:1px solid rgba(76,175,125,.4);
        border-radius:10px;padding:1rem 1.25rem;
        display:flex;align-items:center;gap:.75rem;">
                        <i class="bi bi-check-circle-fill" style="color:var(--success);font-size:1.4rem;flex-shrink:0;"></i>
                        <div>
                            <div style="color:var(--success);font-weight:700;font-size:.9rem;">Próximo servicio programado</div>
                            <div id="fichaProximoTexto" style="color:var(--text-muted);font-size:.82rem;"></div>
                        </div>
                    </div>
                </div>

                <!-- TAB SERVICIOS ────────────────────────────────── -->
                <div id="tabServicios" class="ficha-tab-content" style="display:none;">
                    <button id="btnToggleFormServicio" onclick="toggleFormServicio()" style="
        margin-bottom:1.25rem;
        background:linear-gradient(135deg,var(--accent),var(--accent-2));
        border:none;color:var(--dark);padding:.7rem 1.5rem;border-radius:8px;
        font-family:'Rajdhani',sans-serif;font-weight:700;font-size:.95rem;
        cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:.5rem;">
                        <i class="bi bi-plus-circle"></i> Registrar Nuevo Servicio
                    </button>
                    <div id="formNuevoServicio" style="display:none;
        background:var(--dark-3);border:1px solid var(--border);
        border-radius:12px;padding:1.25rem;margin-bottom:1.5rem;">
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;">
                            <div>
                                <label class="form-label"><i class="bi bi-gear"></i> Tipo *</label>
                                <select id="svcTipo" class="form-select"></select>
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-calendar"></i> Fecha *</label>
                                <input type="date" id="svcFecha" class="form-control">
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-speedometer"></i> KM al servicio *</label>
                                <input type="number" id="svcKm" class="form-control" placeholder="0" min="0">
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-person"></i> Responsable</label>
                                <input type="text" id="svcResponsable" class="form-control" placeholder="Nombre...">
                            </div>
                            <div style="grid-column:2/-1;">
                                <label class="form-label"><i class="bi bi-chat-text"></i> Observaciones</label>
                                <input type="text" id="svcObs" class="form-control" placeholder="Notas adicionales...">
                            </div>
                        </div>
                        <button onclick="guardarServicio()" style="
            margin-top:1rem;background:linear-gradient(135deg,var(--accent),var(--accent-2));
            border:none;color:var(--dark);padding:.7rem 1.5rem;border-radius:8px;
            font-family:'Rajdhani',sans-serif;font-weight:700;font-size:.95rem;
            cursor:pointer;transition:all .2s;">
                            <i class="bi bi-save me-1"></i> Guardar Servicio
                        </button>
                    </div>
                    <div id="tablaServiciosWrap"></div>
                </div>

                <!-- TAB REPARACIONES ─────────────────────────────── -->
                <div id="tabReparaciones" class="ficha-tab-content" style="display:none;">
                    <button id="btnToggleFormReparacion" onclick="toggleFormReparacion()" style="
        margin-bottom:1.25rem;
        background:linear-gradient(135deg,var(--danger),#c93030);
        border:none;color:#fff;padding:.7rem 1.5rem;border-radius:8px;
        font-family:'Rajdhani',sans-serif;font-weight:700;font-size:.95rem;
        cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:.5rem;">
                        <i class="bi bi-plus-circle"></i> Registrar Nueva Reparación
                    </button>
                    <div id="formNuevaReparacion" style="display:none;
        background:var(--dark-3);border:1px solid var(--border);
        border-radius:12px;padding:1.25rem;margin-bottom:1.5rem;">
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;">
                            <div>
                                <label class="form-label"><i class="bi bi-wrench"></i> Tipo *</label>
                                <select id="repTipo" class="form-select"></select>
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-calendar"></i> Fecha Inicio *</label>
                                <input type="date" id="repFechaInicio" class="form-control">
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-calendar-check"></i> Fecha Fin</label>
                                <input type="date" id="repFechaFin" class="form-control">
                            </div>
                            <div style="grid-column:1/-1;">
                                <label class="form-label"><i class="bi bi-card-text"></i> Descripción *</label>
                                <input type="text" id="repDescripcion" class="form-control" placeholder="Detalle de la reparación...">
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-speedometer"></i> KM al momento *</label>
                                <input type="number" id="repKm" class="form-control" placeholder="0" min="0">
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-currency-dollar"></i> Costo (Q)</label>
                                <input type="number" id="repCosto" class="form-control" placeholder="0.00" step="0.01" min="0">
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-activity"></i> Estado</label>
                                <select id="repEstado" class="form-select">
                                    <option value="En proceso">En proceso</option>
                                    <option value="Finalizada">Finalizada</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-shop"></i> Proveedor/Taller</label>
                                <input type="text" id="repProveedor" class="form-control" placeholder="Nombre del taller...">
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-person"></i> Responsable</label>
                                <input type="text" id="repResponsable" class="form-control" placeholder="Nombre...">
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-chat-text"></i> Observaciones</label>
                                <input type="text" id="repObs" class="form-control" placeholder="Notas adicionales...">
                            </div>
                        </div>
                        <button onclick="guardarReparacion()" style="
            margin-top:1rem;background:linear-gradient(135deg,var(--danger),#c93030);
            border:none;color:#fff;padding:.7rem 1.5rem;border-radius:8px;
            font-family:'Rajdhani',sans-serif;font-weight:700;font-size:.95rem;
            cursor:pointer;transition:all .2s;">
                            <i class="bi bi-save me-1"></i> Guardar Reparación
                        </button>
                    </div>
                    <div id="tablaReparacionesWrap"></div>
                </div>

            </div><!-- fin contenido tabs -->

        </div>
    </div>
</div>
</div>

<script src="build/js/vehiculos/index.js" type="module"></script>