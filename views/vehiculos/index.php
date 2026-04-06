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

    * {
        box-sizing: border-box;
    }

    body {
        background: var(--dark);
        color: var(--text-main);
        font-family: 'Inter', sans-serif;
    }

    /* ── PAGE HEADER ─────────────────────────────── */
    .veh-header {
        background: linear-gradient(135deg, var(--dark-2) 0%, var(--dark-3) 100%);
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
        color: var(--text-main);
        margin: 0;
        letter-spacing: 1px;
    }

    .veh-header p {
        margin: 0;
        color: var(--text-muted);
        font-size: .875rem;
    }

    /* ── FLOATING BTN ─────────────────────────────── */
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

    /* ── FORM CONTAINER ───────────────────────────── */
    .form-container {
        background: var(--dark-2);
        border: 1px solid var(--border);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .4);
    }

    .form-header {
        background: linear-gradient(90deg, var(--dark-3) 0%, #1f2335 100%);
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

    /* ── INPUTS ───────────────────────────────────── */
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

    /* Estado de error */
    .form-control.error,
    .form-select.error {
        border-color: var(--danger) !important;
    }

    /* ── SECTION DIVIDER ──────────────────────────── */
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

    /* ── BADGE ESTADO ─────────────────────────────── */
    .badge-alta {
        background: rgba(76, 175, 125, .15);
        color: #4caf7d;
        border: 1px solid rgba(76, 175, 125, .3);
    }

    .badge-baja {
        background: rgba(224, 82, 82, .15);
        color: var(--danger);
        border: 1px solid rgba(224, 82, 82, .3);
    }

    .badge-taller {
        background: rgba(232, 184, 75, .15);
        color: var(--accent);
        border: 1px solid rgba(232, 184, 75, .3);
    }

    .badge-estado {
        padding: .3rem .75rem;
        border-radius: 20px;
        font-size: .78rem;
        font-weight: 600;
    }

    /* ── BUTTONS ──────────────────────────────────── */
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
    }

    .btn-cancelar-main:hover {
        background: var(--danger);
        color: #fff;
        box-shadow: 0 8px 25px rgba(224, 82, 82, .3);
    }

    /* ── TABLE CONTAINER ──────────────────────────── */
    .table-container {
        background: var(--dark-2);
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 2rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, .2);
    }

    .table-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid var(--border);
        margin-bottom: 1.5rem;
    }

    .table-header h2 {
        font-family: 'Rajdhani', sans-serif;
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--text-main);
        margin: 0;
        display: flex;
        align-items: center;
        gap: .6rem;
    }

    .table-header h2 i {
        color: var(--accent);
    }

    /* DataTable dark overrides */
    #tablaVehiculos {
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    #tablaVehiculos thead th {
        background: var(--dark-3) !important;
        color: var(--text-muted) !important;
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .7px;
        font-weight: 600;
        border: none !important;
        padding: .9rem 1rem !important;
    }

    #tablaVehiculos thead th:first-child {
        border-radius: 10px 0 0 10px !important;
    }

    #tablaVehiculos thead th:last-child {
        border-radius: 0 10px 10px 0 !important;
    }

    #tablaVehiculos tbody tr {
        transition: all .2s ease;
        border-bottom: 1px solid var(--border) !important;
    }

    #tablaVehiculos tbody tr:hover td {
        background: var(--dark-3) !important;
    }

    #tablaVehiculos tbody td {
        padding: .9rem 1rem !important;
        color: var(--text-main) !important;
        vertical-align: middle !important;
        border: none !important;
        background: transparent !important;
    }

    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_filter label,
    .dataTables_wrapper .dataTables_info {
        color: var(--text-muted) !important;
        font-size: .85rem !important;
    }

    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        background: var(--dark-3) !important;
        border: 1.5px solid var(--border) !important;
        border-radius: 8px !important;
        color: var(--text-main) !important;
        padding: .4rem .75rem !important;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: var(--accent) !important;
        outline: none !important;
        box-shadow: 0 0 0 3px rgba(232, 184, 75, .15) !important;
    }

    .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        color: var(--text-muted) !important;
        border: 1.5px solid var(--border) !important;
        padding: .4rem .9rem !important;
        margin: 0 2px !important;
        background: var(--dark-3) !important;
        transition: all .2s ease !important;
    }

    .dataTables_paginate .paginate_button:hover {
        background: var(--accent) !important;
        border-color: var(--accent) !important;
        color: var(--dark) !important;
    }

    .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, var(--accent), var(--accent-2)) !important;
        border-color: var(--accent) !important;
        color: var(--dark) !important;
        font-weight: 700 !important;
    }

    .dataTables_paginate .paginate_button.disabled,
    .dataTables_paginate .paginate_button.disabled:hover {
        opacity: .3 !important;
        background: var(--dark-3) !important;
        color: var(--text-muted) !important;
    }

    /* ── BOTONES DE ACCIÓN EN TABLA ───────────────── */
    .btn-tbl {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        transition: all .2s ease;
        font-size: .9rem;
    }

    .btn-tbl-edit {
        background: rgba(58, 123, 213, .15);
        color: #5b9bd5;
        border: 1px solid rgba(58, 123, 213, .25);
    }

    .btn-tbl-edit:hover {
        background: rgba(58, 123, 213, .3);
        transform: translateY(-2px);
    }

    .btn-tbl-del {
        background: rgba(224, 82, 82, .15);
        color: var(--danger);
        border: 1px solid rgba(224, 82, 82, .25);
    }

    .btn-tbl-del:hover {
        background: rgba(224, 82, 82, .3);
        transform: translateY(-2px);
    }

    /* ── KM chip ──────────────────────────────────── */
    .chip-km {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        background: rgba(232, 184, 75, .1);
        color: var(--accent);
        border: 1px solid rgba(232, 184, 75, .2);
        border-radius: 20px;
        padding: .2rem .7rem;
        font-size: .78rem;
        font-weight: 600;
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
</style>

<div class="container-fluid mt-4">

    <!-- Header -->
    <div class="veh-header">
        <div class="icon-wrap">
            <i class="bi bi-truck-front-fill"></i>
        </div>
        <div>
            <h1>Control de Vehículos</h1>
            <p>Registro, historial de servicios y estado de flota</p>
        </div>
    </div>

    <!-- Botón flotante -->
    <button id="btnFlotante" class="floating-btn" title="Nuevo Vehículo">
        <i class="bi bi-plus"></i>
    </button>

    <!-- ── FORMULARIO (inicia oculto) ─────────────── -->
    <div class="row justify-content-center mb-4" id="contenedorFormulario" style="display:none;">
        <div class="col-lg-11">
            <div class="form-container">

                <div class="form-header">
                    <div class="fh-icon"><i class="bi bi-truck"></i></div>
                    <h3 id="tituloFormulario">Nuevo Vehículo</h3>
                </div>

                <form id="formularioVehiculo" class="form-body">
                    <input type="hidden" name="placa_original" id="placa_original">

                    <!-- ── IDENTIFICACIÓN ──────────────────── -->
                    <div class="section-divider">
                        <i class="bi bi-card-text"></i> Identificación
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">
                                <i class="bi bi-hash"></i> Placa *
                            </label>
                            <input type="text" name="placa" id="placa" class="form-control"
                                placeholder="Ej: ABC-123" required
                                style="text-transform:uppercase; font-weight:600; letter-spacing:1px;">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">
                                <i class="bi bi-upc-scan"></i> Número de Serie *
                            </label>
                            <input type="text" name="numero_serie" id="numero_serie" class="form-control"
                                placeholder="VIN / Número de chasis" required
                                style="text-transform:uppercase;">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="bi bi-calendar-event"></i> Fecha de Ingreso *
                            </label>
                            <input type="date" name="fecha_ingreso" id="fecha_ingreso"
                                class="form-control" required>
                        </div>
                    </div>

                    <!-- ── DATOS DEL VEHÍCULO ──────────────── -->
                    <div class="section-divider">
                        <i class="bi bi-car-front"></i> Datos del Vehículo
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">
                                <i class="bi bi-building"></i> Marca *
                            </label>
                            <input type="text" name="marca" id="marca" class="form-control"
                                placeholder="Toyota, Ford, Chevrolet..." required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">
                                <i class="bi bi-tag"></i> Modelo *
                            </label>
                            <input type="text" name="modelo" id="modelo" class="form-control"
                                placeholder="Hilux, Ranger, Colorado..." required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">
                                <i class="bi bi-calendar3"></i> Año *
                            </label>
                            <input type="number" name="anio" id="anio" class="form-control"
                                placeholder="2024" min="1990" max="2030" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">
                                <i class="bi bi-palette"></i> Color *
                            </label>
                            <input type="text" name="color" id="color" class="form-control"
                                placeholder="Blanco, Negro..." required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">
                                <i class="bi bi-truck"></i> Tipo *
                            </label>
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

                    <!-- ── ESTADO Y KM ─────────────────────── -->
                    <div class="section-divider">
                        <i class="bi bi-speedometer2"></i> Estado Operacional
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">
                                <i class="bi bi-activity"></i> Estado *
                            </label>
                            <select name="estado" id="estado" class="form-select" required>
                                <option value="Alta">Alta (Operativo)</option>
                                <option value="Baja">Baja (Fuera de servicio)</option>
                                <option value="Taller">Taller (En reparación)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">
                                <i class="bi bi-speedometer"></i> Kilometraje Actual
                            </label>
                            <input type="number" name="km_actuales" id="km_actuales" class="form-control"
                                placeholder="0" min="0" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="bi bi-file-text"></i> Observaciones
                            </label>
                            <textarea name="observaciones" id="observaciones" class="form-control"
                                rows="2" placeholder="Información adicional del vehículo..."></textarea>
                        </div>
                    </div>

                    <!-- ── BOTONES ──────────────────────────── -->
                    <div class="row mt-4">
                        <div class="col" id="contenedorBtnGuardar">
                            <button type="submit" form="formularioVehiculo" id="btnGuardar"
                                class="btn-guardar w-100">
                                <i class="bi bi-save-fill me-2"></i> Registrar Vehículo
                            </button>
                        </div>
                        <div class="col" id="contenedorBtnModificar" style="display:none;">
                            <button type="button" id="btnModificar" class="btn-modificar-main w-100">
                                <i class="bi bi-pencil-square me-2"></i> Guardar Cambios
                            </button>
                        </div>
                        <div class="col" id="contenedorBtnCancelar">
                            <button type="button" id="btnCancelar" class="btn-cancelar-main w-100">
                                <i class="bi bi-x-circle me-2"></i> Cancelar
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- ── TABLA DE VEHÍCULOS ──────────────────────── -->
    <div class="row justify-content-center" id="contenedorTabla">
        <div class="col-12">
            <div class="table-container">
                <div class="table-header">
                    <h2><i class="bi bi-list-check"></i> Flota Registrada</h2>
                </div>
                <div class="table-responsive">
                    <table id="tablaVehiculos" class="table"></table>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="build/js/vehiculos/index.js" type="module"></script>