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

    /* ── SEGURO TOGGLE en formulario ── */
    .seguro-opcion-wrap {
        margin-bottom: 1rem;
    }

    .seguro-toggle-btns {
        display: flex;
        gap: 10px;
        margin-top: .5rem;
    }

    .seguro-toggle-btn {
        flex: 1;
        padding: .85rem 1rem;
        border: 1.5px solid var(--border);
        border-radius: 10px;
        background: var(--dark-3);
        color: var(--text-muted);
        font-size: .9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all .2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        font-family: 'Inter', sans-serif;
    }

    .seguro-toggle-btn:hover {
        border-color: var(--text-muted);
        color: var(--text-main);
    }

    .seguro-toggle-btn.sel-si {
        background: rgba(76, 175, 125, .12);
        color: var(--success);
        border-color: var(--success);
    }

    .seguro-toggle-btn.sel-no {
        background: rgba(232, 184, 75, .10);
        color: var(--accent);
        border-color: var(--accent);
    }

    .seguro-form-panel {
        background: var(--dark-3);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 1rem;
        animation: slideDown .3s ease-out;
    }

    .seguro-aviso {
        border-radius: 8px;
        padding: .75rem 1rem;
        font-size: .83rem;
        margin-top: .75rem;
        display: flex;
        align-items: center;
        gap: .6rem;
    }

    .seguro-aviso.aviso-ok {
        background: rgba(76, 175, 125, .1);
        color: var(--success);
        border: 1px solid rgba(76, 175, 125, .3);
    }

    .seguro-aviso.aviso-warn {
        background: rgba(232, 184, 75, .1);
        color: var(--accent);
        border: 1px solid rgba(232, 184, 75, .3);
    }

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
        /* ← QUITA min-width: 150px de aquí */
    }

    /* Tamaños individuales por filtro */
    #filtroTipo {
        min-width: 130px;
        flex: 0 0 130px;
    }

    #filtroEstado {
        min-width: 130px;
        flex: 0 0 130px;
    }

    #filtroUnidad {
        min-width: 180px;
        max-width: 220px;
        flex: 1 1 180px;
    }

    /* ← key fix */
    #filtroBusqueda {
        min-width: 180px;
        flex: 2 1 180px;
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

    .cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1.25rem;
    }

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

    .card-foto {
        width: 100%;
        aspect-ratio: 1/1;
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

    /* Badge seguro en card */
    .card-seguro-badge {
        position: absolute;
        bottom: 10px;
        left: 10px;
        padding: .2rem .6rem;
        border-radius: 20px;
        font-size: .68rem;
        font-weight: 700;
        backdrop-filter: blur(8px);
    }

    .seguro-vigente {
        background: rgba(76, 175, 125, .25);
        color: #4caf7d;
        border: 1px solid rgba(76, 175, 125, .4);
    }

    .seguro-vencido {
        background: rgba(224, 82, 82, .25);
        color: var(--danger);
        border: 1px solid rgba(224, 82, 82, .4);
    }

    .seguro-ninguno {
        background: rgba(124, 131, 152, .2);
        color: var(--text-muted);
        border: 1px solid rgba(124, 131, 152, .3);
    }

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

    .card-unidad {
        font-size: .72rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: .3rem;
        margin-top: .1rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .card-unidad i {
        color: var(--accent);
        flex-shrink: 0;
    }

    .swal-over-modal {
        z-index: 99999 !important;
    }

    .swal2-container {
        z-index: 99999 !important;
    }

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

    .empty-state {
        grid-column: 1/-1;
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

    @media (max-width:576px) {
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

    .info-destacamento-panel {
        background: rgba(232, 184, 75, .08);
        border: 1px solid rgba(232, 184, 75, .25);
        border-radius: 8px;
        padding: .65rem 1rem;
        font-size: .82rem;
        color: var(--text-muted);
        line-height: 1.6;
        width: 100%;
    }

    .info-destacamento-panel .panel-titulo {
        color: var(--accent);
        font-weight: 600;
        margin-bottom: .15rem;
        font-size: .85rem;
    }

    /* ── SEGURO CARD en ficha ── */
    .seguro-card {
        background: var(--dark-3);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        position: relative;
        overflow: hidden;
    }

    .seguro-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--success);
    }

    .seguro-card.vencido::before {
        background: var(--danger);
    }

    .seguro-card.cancelado::before {
        background: var(--text-muted);
    }

    .seguro-card.pronto::before {
        background: var(--accent);
    }

    .seguro-badge {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .25rem .75rem;
        border-radius: 20px;
        font-size: .72rem;
        font-weight: 700;
    }

    .badge-vigente {
        background: rgba(76, 175, 125, .2);
        color: var(--success);
        border: 1px solid rgba(76, 175, 125, .4);
    }

    .badge-vencido {
        background: rgba(224, 82, 82, .2);
        color: var(--danger);
        border: 1px solid rgba(224, 82, 82, .4);
    }

    .badge-cancelado {
        background: rgba(124, 131, 152, .15);
        color: var(--text-muted);
        border: 1px solid var(--border);
    }

    .badge-pronto {
        background: rgba(232, 184, 75, .2);
        color: var(--accent);
        border: 1px solid rgba(232, 184, 75, .4);
    }

    /* ── ACCIDENTE CARD en ficha ── */
    .accidente-card {
        background: var(--dark-3);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        position: relative;
        overflow: hidden;
    }

    .accidente-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--danger);
    }

    .accidente-card.cerrado::before {
        background: var(--text-muted);
    }

    .accidente-card.tramite::before {
        background: var(--accent);
    }

    .badge-reportado {
        background: rgba(224, 82, 82, .2);
        color: var(--danger);
        border: 1px solid rgba(224, 82, 82, .4);
        font-size: .72rem;
        font-weight: 700;
        padding: .2rem .65rem;
        border-radius: 20px;
        display: inline-block;
    }

    .badge-tramite {
        background: rgba(232, 184, 75, .2);
        color: var(--accent);
        border: 1px solid rgba(232, 184, 75, .4);
        font-size: .72rem;
        font-weight: 700;
        padding: .2rem .65rem;
        border-radius: 20px;
        display: inline-block;
    }

    .badge-cerrado {
        background: rgba(76, 175, 125, .15);
        color: var(--success);
        border: 1px solid rgba(76, 175, 125, .3);
        font-size: .72rem;
        font-weight: 700;
        padding: .2rem .65rem;
        border-radius: 20px;
        display: inline-block;
    }

    .badge-sinseguro {
        background: rgba(124, 131, 152, .15);
        color: var(--text-muted);
        border: 1px solid var(--border);
        font-size: .72rem;
        font-weight: 700;
        padding: .2rem .65rem;
        border-radius: 20px;
        display: inline-block;
    }

    .costo-resumen {
        background: rgba(224, 82, 82, .08);
        border: 1px solid rgba(224, 82, 82, .2);
        border-radius: 10px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* ── LIGHTBOX ── */
    #bhr-lightbox {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(0, 0, 0, .92);
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    #bhr-lightbox.visible {
        display: flex;
    }

    #bhr-lightbox #lbImagen {
        max-width: 90vw;
        max-height: 85vh;
        object-fit: contain;
        border-radius: 10px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .6);
    }

    .lightbox-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: rgba(255, 255, 255, .1);
        border: 1px solid rgba(255, 255, 255, .15);
        color: #fff;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all .2s;
        z-index: 1;
    }

    .lightbox-close:hover {
        background: rgba(224, 82, 82, .4);
        border-color: rgba(224, 82, 82, .5);
    }

    .lightbox-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, .1);
        border: 1px solid rgba(255, 255, 255, .15);
        color: #fff;
        width: 44px;
        height: 44px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all .2s;
        z-index: 1;
    }

    .lightbox-nav:hover {
        background: rgba(232, 184, 75, .25);
        border-color: rgba(232, 184, 75, .4);
    }

    .lightbox-nav.prev {
        left: 1rem;
    }

    .lightbox-nav.next {
        right: 1rem;
    }

    .lightbox-nav.hidden {
        display: none;
    }

    .lightbox-caption {
        position: absolute;
        bottom: 1.25rem;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, .6);
        color: #e8eaf0;
        padding: .4rem 1rem;
        border-radius: 20px;
        font-size: .78rem;
        white-space: nowrap;
        backdrop-filter: blur(6px);
        pointer-events: none;
    }

    /* ── TIPO SELECTOR ── */
    .tipos-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        /* ← 5 columnas que llenan el ancho */
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .tipo-card {
        background: var(--dark-2);
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        cursor: pointer;
        transition: all .3s ease;
        animation: fadeInCard .4s ease both;
        position: relative;
    }

    .tipo-card:hover {
        border-color: var(--accent);
        box-shadow: 0 0 0 2px rgba(232, 184, 75, .25),
            0 16px 40px rgba(0, 0, 0, .5);
        transform: translateY(-6px) scale(1.02);
    }

    .tipo-card-ilustracion {
        width: 100%;
        aspect-ratio: 4/3;
        /* ← más cuadrado */
        overflow: hidden;
        position: relative;
    }

    .tipo-card-ilustracion img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform .4s ease;
        filter: brightness(.9);
    }

    .tipo-card:hover .tipo-card-ilustracion img {
        transform: scale(1.08);
        filter: brightness(1.1);
    }

    /* Overlay dorado al hacer hover */
    .tipo-card::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(to bottom,
                rgba(232, 184, 75, .08) 0%,
                transparent 50%,
                rgba(0, 0, 0, .3) 100%);
        opacity: 0;
        transition: opacity .3s ease;
        pointer-events: none;
        border-radius: 12px;
    }

    .tipo-card:hover::after {
        opacity: 1;
    }

    .tipo-card-body {
        padding: .85rem 1rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-top: 1px solid var(--border);
        transition: background .3s ease;
    }

    .tipo-card:hover .tipo-card-body {
        background: rgba(232, 184, 75, .05);
    }

    .tipo-card-nombre {
        font-family: 'Rajdhani', sans-serif;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-main);
        letter-spacing: .5px;
        transition: color .3s ease;
    }

    .tipo-card:hover .tipo-card-nombre {
        color: var(--accent);
    }

    .tipo-card-count {
        background: rgba(232, 184, 75, .15);
        color: var(--accent);
        border: 1px solid rgba(232, 184, 75, .3);
        border-radius: 20px;
        padding: .2rem .65rem;
        font-size: .75rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .tipo-card-count.sin-vehiculos {
        background: rgba(124, 131, 152, .1);
        color: var(--text-muted);
        border-color: var(--border);
    }

    @media (max-width: 768px) {
        .tipos-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: .75rem;
        }
    }

    @media (max-width: 480px) {
        .tipos-grid {
            grid-template-columns: 1fr;
        }
    }

    #btnVolverTipos {
        display: flex;
        align-items: center;
        gap: .5rem;
        background: linear-gradient(135deg, var(--accent), var(--accent-2));
        border: none;
        color: var(--dark);
        padding: .6rem 1.25rem;
        border-radius: 10px;
        font-family: 'Rajdhani', sans-serif;
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: .5px;
        cursor: pointer;
        transition: all .3s ease;
        white-space: nowrap;
    }

    #btnVolverTipos:hover {
        transform: translateX(-4px);
        box-shadow: 0 6px 20px rgba(232, 184, 75, .4);
    }

    #btnVolverTipos i {
        font-size: 1rem;
        transition: transform .3s ease;
    }

    #btnVolverTipos:hover i {
        transform: translateX(-3px);
    }
</style>

<div class="container-fluid mt-4" data-base="<?= $_ENV['APP_NAME'] ? '/' . $_ENV['APP_NAME'] : '' ?>">

    <!-- HEADER -->
    <div class="veh-header">
        <div class="icon-wrap"><i class="bi bi-truck-front-fill"></i></div>
        <div style="flex:1;">
            <h1>Control de Vehículos</h1>
            <p>Registro, historial de servicios y estado de flota</p>
        </div>
        <?php if (isset($_SESSION['auth_user'])): ?>
            <div style="text-align:right;">
                <div style="font-family:'Rajdhani',sans-serif;font-size:1.1rem;color:#e8b84b;font-weight:700;letter-spacing:.5px;">
                    <i class="bi bi-person-fill"></i>
                    <?= htmlspecialchars($_SESSION['auth_grado']) ?> de <?= htmlspecialchars($_SESSION['auth_arma'] ?? '') ?> <?= htmlspecialchars($_SESSION['auth_nombre']) ?>
                </div>
                <div style="font-size:.9rem;color:#7c8398;margin-top:.2rem;">
                    <?= htmlspecialchars($_SESSION['auth_plaza']) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- BOTÓN FLOTANTE -->
    <button id="btnFlotante" class="floating-btn" title="Nuevo Vehículo">
        <i class="bi bi-plus"></i>
    </button>

    <!-- FORMULARIO -->
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
                            <label class="form-label"><i class="bi bi-hash"></i> Catalogo *</label>
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
                                <option value="Cuatrimoto">Cuatrimoto</option>
                                <option value="Microbús">Microbús</option>
                                <option value="Blindado">Blindado</option>
                                <option value="Camioneta">Camioneta</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                    </div>

                    <!-- Asignación -->
                    <div class="section-divider"><i class="bi bi-diagram-3"></i> Asignación</div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-people"></i> Unidad Asignada</label>
                            <select name="id_unidad" id="id_unidad" class="form-select" required>
                                <option value="0">— Sin asignar —</option>
                                <?php foreach ($destacamentos as $destacamento): ?>
                                    <option value="<?= $destacamento['id_unidad'] ?>">
                                        <?= $destacamento['unidad_destacamento'] ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div id="infoDestacamento" style="display:none;width:100%;" class="info-destacamento-panel">
                                <div class="panel-titulo">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    <span id="infoNombreDestacamento"></span>
                                </div>
                                <div><i class="bi bi-map"></i> <span id="infoUbicacion"></span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Estado Operacional -->
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
                            <input type="number" name="km_actuales" id="km_actuales" class="form-control"
                                placeholder="0" min="0" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-file-text"></i> Observaciones</label>
                            <textarea name="observaciones" id="observaciones" class="form-control"
                                rows="2" placeholder="Información adicional..."></textarea>
                        </div>
                    </div>

                    <!-- Archivos -->
                    <div class="section-divider"><i class="bi bi-paperclip"></i> Archivos</div>
                    <div class="row mb-3">
                        <!-- FOTO FRENTE -->
                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-camera"></i> Foto Frente</label>
                            <div class="file-upload-area" id="areaFoto">
                                <input type="file" name="foto_frente" id="foto_frente" accept="image/jpeg,image/png,image/webp">
                                <div class="upload-icon"><i class="bi bi-image"></i></div>
                                <div class="upload-label"><span>Haz clic</span> o arrastra<br><small>JPG, PNG, WEBP — máx. 5 MB</small></div>
                            </div>
                            <img id="fotoPreview" class="foto-preview" src="" alt="Preview">
                        </div>

                        <!-- FOTO LATERAL -->
                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-camera"></i> Foto Lateral</label>
                            <div class="file-upload-area" id="areaFotoLateral">
                                <input type="file" name="foto_lateral" id="foto_lateral" accept="image/jpeg,image/png,image/webp">
                                <div class="upload-icon"><i class="bi bi-image"></i></div>
                                <div class="upload-label"><span>Haz clic</span> o arrastra<br><small>JPG, PNG, WEBP — máx. 5 MB</small></div>
                            </div>
                            <img id="fotoLateralPreview" class="foto-preview" src="" alt="Preview Lateral">
                        </div>

                        <!-- FOTO TRASERA -->
                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-camera"></i> Foto Trasera</label>
                            <div class="file-upload-area" id="areaFotoTrasera">
                                <input type="file" name="foto_trasera" id="foto_trasera" accept="image/jpeg,image/png,image/webp">
                                <div class="upload-icon"><i class="bi bi-image"></i></div>
                                <div class="upload-label"><span>Haz clic</span> o arrastra<br><small>JPG, PNG, WEBP — máx. 5 MB</small></div>
                            </div>
                            <img id="fotoTraseraPreview" class="foto-preview" src="" alt="Preview Trasera">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <!-- TARJETA PDF -->
                        <div class="col-md-3">
                            <label class="form-label"><i class="bi bi-file-earmark-pdf"></i> Tarjeta de Circulación</label>
                            <div class="file-upload-area" id="areaPdf">
                                <input type="file" name="tarjeta_pdf" id="tarjeta_pdf" accept="application/pdf">
                                <div class="upload-icon"><i class="bi bi-file-pdf"></i></div>
                                <div class="upload-label"><span>Haz clic</span> o arrastra<br><small>Solo PDF — máx. 10 MB</small></div>
                            </div>
                            <div id="pdfNombre" style="margin-top:.5rem;font-size:.8rem;color:var(--success);display:none;">
                                <i class="bi bi-check-circle-fill"></i> <span></span>
                            </div>
                        </div>

                        <!-- CERT INVENTARIO -->
                        <div class="col-md-3">
                            <label class="form-label"><i class="bi bi-file-earmark-pdf"></i> Certificación Inventario</label>
                            <div class="file-upload-area" id="areaCertInventario">
                                <input type="file" name="cert_inventario" id="cert_inventario" accept="application/pdf">
                                <div class="upload-icon"><i class="bi bi-file-pdf"></i></div>
                                <div class="upload-label"><span>Haz clic</span> o arrastra<br><small>Solo PDF — máx. 10 MB</small></div>
                            </div>
                            <div id="certInventarioNombre" style="margin-top:.5rem;font-size:.8rem;color:var(--success);display:none;">
                                <i class="bi bi-check-circle-fill"></i> <span></span>
                            </div>
                        </div>

                        <!-- CERT SICOIN -->
                        <div class="col-md-3">
                            <label class="form-label"><i class="bi bi-file-earmark-pdf"></i> Certificación SICOIN</label>
                            <div class="file-upload-area" id="areaCertSicoin">
                                <input type="file" name="cert_sicoin" id="cert_sicoin" accept="application/pdf">
                                <div class="upload-icon"><i class="bi bi-file-pdf"></i></div>
                                <div class="upload-label"><span>Haz clic</span> o arrastra<br><small>Solo PDF — máx. 10 MB</small></div>
                            </div>
                            <div id="certSicoinNombre" style="margin-top:.5rem;font-size:.8rem;color:var(--success);display:none;">
                                <i class="bi bi-check-circle-fill"></i> <span></span>
                            </div>
                        </div>

                        <!-- ESPACIO PARA FOTO ACTUAL (edición) -->
                        <div class="col-md-3 d-flex align-items-end">
                            <div id="fotoActualContainer" style="display:none;width:100%">
                                <label class="form-label"><i class="bi bi-image-fill"></i> Foto actual</label>
                                <img id="fotoActual" src="" alt="Foto actual"
                                    style="width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:8px;border:2px solid var(--border)">
                            </div>
                        </div>
                    </div>

                    <!-- ── SEGURO (NUEVO) ── -->
                    <div class="section-divider"><i class="bi bi-shield-check"></i> Seguro del Vehículo</div>
                    <div class="seguro-opcion-wrap">
                        <label class="form-label"><i class="bi bi-question-circle"></i> ¿Este vehículo tiene seguro?</label>
                        <div class="seguro-toggle-btns">
                            <button type="button" class="seguro-toggle-btn" id="btnSeguroSi" onclick="elegirSeguro('si')">
                                <i class="bi bi-shield-fill-check"></i> Sí, tiene seguro
                            </button>
                            <button type="button" class="seguro-toggle-btn" id="btnSeguroNo" onclick="elegirSeguro('no')">
                                <i class="bi bi-shield-slash"></i> No tiene seguro
                            </button>
                        </div>

                        <!-- Panel del formulario de seguro -->
                        <div id="panelFormSeguro" style="display:none;">
                            <div class="seguro-form-panel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="bi bi-building"></i> Aseguradora *</label>
                                        <input type="text" name="seg_aseguradora" id="seg_aseguradora" class="form-control" placeholder="Ej: Seguros Universales, Mapfre...">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="bi bi-file-text"></i> Número de Póliza *</label>
                                        <input type="text" name="seg_numero_poliza" id="seg_numero_poliza" class="form-control" placeholder="POL-00001">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label"><i class="bi bi-shield"></i> Tipo de Cobertura</label>
                                        <select name="seg_tipo_cobertura" id="seg_tipo_cobertura" class="form-select">
                                            <option value="Básico">Básico</option>
                                            <option value="Amplio">Amplio</option>
                                            <option value="Todo riesgo">Todo riesgo</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label"><i class="bi bi-calendar-check"></i> Fecha Inicio *</label>
                                        <input type="date" name="seg_fecha_inicio" id="seg_fecha_inicio" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label"><i class="bi bi-calendar-x"></i> Fecha Vencimiento *</label>
                                        <input type="date" name="seg_fecha_vencimiento" id="seg_fecha_vencimiento" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label"><i class="bi bi-currency-dollar"></i> Prima Anual (Q)</label>
                                        <input type="number" name="seg_prima_anual" id="seg_prima_anual" class="form-control" placeholder="0.00" step="0.01" min="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label"><i class="bi bi-person"></i> Agente de Contacto</label>
                                        <input type="text" name="seg_agente_contacto" id="seg_agente_contacto" class="form-control" placeholder="Nombre del agente">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label"><i class="bi bi-telephone"></i> Teléfono Agente</label>
                                        <input type="tel" name="seg_telefono_agente" id="seg_telefono_agente" class="form-control" placeholder="502-XXXX-XXXX">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="bi bi-file-earmark-pdf"></i> Póliza PDF <small style="color:var(--text-muted);font-size:.7rem;text-transform:none;">(opcional)</small></label>
                                        <div class="file-upload-area" id="areaPoliza">
                                            <input type="file" name="archivo_poliza" id="archivo_poliza" accept="application/pdf">
                                            <div class="upload-icon"><i class="bi bi-file-pdf"></i></div>
                                            <div class="upload-label"><span>Haz clic</span> para subir la póliza<br><small>Solo PDF — máx. 10 MB</small></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="bi bi-chat-text"></i> Observaciones</label>
                                        <textarea name="seg_observaciones" id="seg_observaciones" class="form-control" rows="3" placeholder="Detalles adicionales sobre la cobertura..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="seguro-aviso aviso-ok mt-2">
                                <i class="bi bi-info-circle-fill"></i>
                                Podés omitir campos opcionales y completarlos después desde la ficha del vehículo.
                            </div>
                        </div>

                        <!-- Aviso sin seguro -->
                        <div id="avisoSinSeguro" style="display:none;" class="seguro-aviso aviso-warn mt-2">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            El vehículo quedará registrado sin seguro. Podés asignarlo después desde la ficha del vehículo.
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="row mt-4">
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

    <!-- PANTALLA SELECTOR DE TIPO -->
    <div id="contenedorTipos">
        <div class="tipos-grid" id="tiposGrid"></div>
    </div>

    <!-- VISTA DE CARTAS -->
    <div id="contenedorTabla" style="display:none;">
        <div class="filtros-container">
            <button id="btnVolverTipos" class="btn-limpiar-filtros" style="white-space:nowrap;">
                <i class="bi bi-arrow-left-circle"></i> Volver
            </button>
            <label id="labelTipoActual" style="font-family:'Rajdhani',sans-serif;font-size:1rem;
            font-weight:700;color:var(--accent);letter-spacing:.5px;white-space:nowrap;">
            </label>
            <select id="filtroUnidad" class="filtro-select" style="min-width:180px;flex:1 1 180px;">
                <option value="">Todas las unidades</option>
            </select>
            <input type="text" id="filtroBusqueda" class="filtro-search"
                placeholder="Buscar por placa, marca, modelo...">
            <div class="contador-resultados">
                Mostrando <span id="contadorVisible">0</span> vehículo(s)
            </div>
        </div>
        <div class="cards-grid" id="cardsGrid">
            <div class="empty-state">
                <i class="bi bi-truck"></i>
                <p>Cargando vehículos...</p>
            </div>
        </div>
    </div>

    <!-- MODAL FICHA VEHÍCULO -->
    <div id="modalFicha" style="
        display:none; position:fixed; inset:0;
        background:rgba(0,0,0,.7); backdrop-filter:blur(6px);
        z-index:2000; align-items:center; justify-content:center; padding:1rem;">

        <div style="
            background:var(--dark-2); border:1px solid var(--border); border-radius:18px;
            width:100%; max-width:960px; max-height:90vh;
            display:flex; flex-direction:column;
            box-shadow:0 30px 80px rgba(0,0,0,.6); overflow:hidden;">

            <!-- Header modal -->
            <div style="background:linear-gradient(90deg,var(--dark-3),#1f2335);border-bottom:1px solid var(--border);padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem;flex-shrink:0;">
                <div style="width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,var(--accent),var(--accent-2));display:flex;align-items:center;justify-content:center;color:var(--dark);font-size:1.2rem;flex-shrink:0;">
                    <i class="bi bi-card-checklist"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div id="fichaPlaca" style="font-family:'Rajdhani',sans-serif;font-size:1.4rem;font-weight:700;color:var(--accent);letter-spacing:2px;"></div>
                    <div id="fichaVehiculo" style="font-size:.85rem;color:var(--text-muted);"></div>
                </div>
                <button onclick="cerrarFicha()" style="background:rgba(224,82,82,.15);border:1px solid rgba(224,82,82,.3);color:var(--danger);width:36px;height:36px;border-radius:8px;cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Tabs -->
            <div style="display:flex;border-bottom:1px solid var(--border);background:var(--dark-3);flex-shrink:0;overflow-x:auto;">
                <button class="ficha-tab activo" data-tab="info" onclick="switchTab(this,'info')" style="flex:1;min-width:100px;padding:.85rem;border:none;background:transparent;color:var(--text-muted);cursor:pointer;font-size:.82rem;font-weight:600;letter-spacing:.4px;transition:all .2s;border-bottom:2px solid transparent;font-family:'Inter',sans-serif;white-space:nowrap;">
                    <i class="bi bi-info-circle me-1"></i> Info General
                </button>
                <button class="ficha-tab" data-tab="servicios" onclick="switchTab(this,'servicios')" style="flex:1;min-width:100px;padding:.85rem;border:none;background:transparent;color:var(--text-muted);cursor:pointer;font-size:.82rem;font-weight:600;letter-spacing:.4px;transition:all .2s;border-bottom:2px solid transparent;font-family:'Inter',sans-serif;white-space:nowrap;">
                    <i class="bi bi-tools me-1"></i> Servicios
                    <span id="badgeServicios" style="background:var(--accent);color:var(--dark);border-radius:20px;padding:.1rem .5rem;font-size:.7rem;margin-left:.3rem;">0</span>
                </button>
                <button class="ficha-tab" data-tab="reparaciones" onclick="switchTab(this,'reparaciones')" style="flex:1;min-width:100px;padding:.85rem;border:none;background:transparent;color:var(--text-muted);cursor:pointer;font-size:.82rem;font-weight:600;letter-spacing:.4px;transition:all .2s;border-bottom:2px solid transparent;font-family:'Inter',sans-serif;white-space:nowrap;">
                    <i class="bi bi-wrench-adjustable me-1"></i> Reparaciones
                    <span id="badgeReparaciones" style="background:var(--danger);color:#fff;border-radius:20px;padding:.1rem .5rem;font-size:.7rem;margin-left:.3rem;">0</span>
                </button>
                <!-- TAB SEGURO (NUEVO) -->
                <button class="ficha-tab" data-tab="seguro" onclick="switchTab(this,'seguro')" style="flex:1;min-width:100px;padding:.85rem;border:none;background:transparent;color:var(--text-muted);cursor:pointer;font-size:.82rem;font-weight:600;letter-spacing:.4px;transition:all .2s;border-bottom:2px solid transparent;font-family:'Inter',sans-serif;white-space:nowrap;">
                    <i class="bi bi-shield-check me-1"></i> Seguro
                    <span id="badgeSeguro" style="background:#3a7bd5;color:#fff;border-radius:20px;padding:.1rem .5rem;font-size:.7rem;margin-left:.3rem;">0</span>
                </button>
                <!-- TAB ACCIDENTES (NUEVO) -->
                <button class="ficha-tab" data-tab="accidentes" onclick="switchTab(this,'accidentes')" style="flex:1;min-width:100px;padding:.85rem;border:none;background:transparent;color:var(--text-muted);cursor:pointer;font-size:.82rem;font-weight:600;letter-spacing:.4px;transition:all .2s;border-bottom:2px solid transparent;font-family:'Inter',sans-serif;white-space:nowrap;">
                    <i class="bi bi-car-front me-1"></i> Accidentes
                    <span id="badgeAccidentes" style="background:rgba(224,82,82,.85);color:#fff;border-radius:20px;padding:.1rem .5rem;font-size:.7rem;margin-left:.3rem;">0</span>
                </button>

            </div>

            <!-- Contenido tabs -->
            <div style="overflow-y:auto;flex:1;padding:1.5rem;">

                <!-- TAB INFO (sin cambios) -->
                <div id="tabInfo" class="ficha-tab-content">
                    <div style="display:grid;grid-template-columns:200px 1fr;gap:1.5rem;">

                        <!-- Columna izquierda: foto y botones -->
                        <div>
                            <!-- Foto frente -->
                            <div style="width:100%;aspect-ratio:1/1;border-radius:12px;overflow:hidden;background:var(--dark-3);border:2px solid var(--border);position:relative;">
                                <img id="fichaFoto" src="" alt="" style="width:100%;height:100%;object-fit:cover;display:none;">
                                <div id="fichaNoFoto" style="width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--text-muted);gap:.5rem;">
                                    <i class="bi bi-truck-front" style="font-size:3rem;opacity:.3;"></i>
                                    <span style="font-size:.75rem;opacity:.5;">Sin foto</span>
                                </div>
                            </div>

                            <!-- Fotos lateral y trasera -->
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-top:.5rem;">
                                <div style="aspect-ratio:1/1;border-radius:8px;overflow:hidden;background:var(--dark-3);border:1px solid var(--border);">
                                    <img id="fichaFotoLateral" src="" alt="Lateral"
                                        style="width:100%;height:100%;object-fit:cover;display:none;">
                                    <div id="fichaNoFotoLateral" style="width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--text-muted);gap:.25rem;padding:.5rem;">
                                        <i class="bi bi-truck" style="font-size:1.5rem;opacity:.2;"></i>
                                        <span style="font-size:.65rem;opacity:.4;text-align:center;">Sin foto lateral</span>
                                    </div>
                                </div>
                                <div style="aspect-ratio:1/1;border-radius:8px;overflow:hidden;background:var(--dark-3);border:1px solid var(--border);">
                                    <img id="fichaFotoTrasera" src="" alt="Trasera"
                                        style="width:100%;height:100%;object-fit:cover;display:none;">
                                    <div id="fichaNoFotoTrasera" style="width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--text-muted);gap:.25rem;padding:.5rem;">
                                        <i class="bi bi-truck" style="font-size:1.5rem;opacity:.2;"></i>
                                        <span style="font-size:.65rem;opacity:.4;text-align:center;">Sin foto trasera</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones PDF -->
                            <div style="display:flex;flex-direction:column;gap:.5rem;margin-top:.75rem;">
                                <a id="fichaPdfBtn" href="#" target="_blank"
                                    style="display:none;padding:.6rem 1rem;border-radius:8px;text-align:center;
        background:rgba(224,82,82,.15);border:1px solid rgba(224,82,82,.3);
        color:#e05252;font-size:.78rem;font-weight:600;text-decoration:none;">
                                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Tarjeta de Circulación
                                </a>

                                <a id="fichaCertInventarioBtn" href="#" target="_blank"
                                    style="display:none;padding:.6rem 1rem;border-radius:8px;text-align:center;
        background:rgba(58,123,213,.15);border:1px solid rgba(58,123,213,.3);
        color:#5b9bd5;font-size:.78rem;font-weight:600;text-decoration:none;">
                                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Cert. Inventario
                                </a>

                                <a id="fichaCertSicoinBtn" href="#" target="_blank"
                                    style="display:none;padding:.6rem 1rem;border-radius:8px;text-align:center;
        background:rgba(76,175,125,.15);border:1px solid rgba(76,175,125,.3);
        color:var(--success);font-size:.78rem;font-weight:600;text-decoration:none;">
                                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Cert. SICOIN
                                </a>

                                <button id="btnIrAChequeo" onclick="abrirModalChequeo()"
                                    style="padding:.75rem;border-radius:8px;
        background:linear-gradient(135deg,#6f42c1,#5a2d9e);
        border:none;color:#fff;font-size:.85rem;font-weight:700;
        cursor:pointer;font-family:'Rajdhani',sans-serif;
        display:flex;align-items:center;justify-content:center;gap:.5rem;
        box-shadow:0 4px 15px rgba(111,66,193,.3);">
                                    <i class="bi bi-clipboard2-check-fill"></i> Realizar Chequeo Mensual
                                </button>

                                <button id="btnGenerarExpediente"
                                    onclick="generarExpediente(document.getElementById('fichaPlaca').textContent.trim())"
                                    style="display:none;padding:.75rem;border-radius:8px;
        background:linear-gradient(135deg,#C75B00,#a34900);
        border:none;color:#fff;font-size:.85rem;font-weight:700;
        cursor:pointer;font-family:'Rajdhani',sans-serif;
        display:flex;align-items:center;justify-content:center;gap:.5rem;">
                                    <i class="bi bi-printer-fill"></i> Generar Expediente
                                </button>
                            </div>
                        </div>

                        <!-- Columna derecha: datos del vehículo -->
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                            <div class="ficha-dato"><span>Catalogo</span><strong id="fd-placa">—</strong></div>
                            <div class="ficha-dato"><span>N° Serie</span><strong id="fd-serie">—</strong></div>
                            <div class="ficha-dato"><span>Marca</span><strong id="fd-marca">—</strong></div>
                            <div class="ficha-dato"><span>Modelo</span><strong id="fd-modelo">—</strong></div>
                            <div class="ficha-dato"><span>Año</span><strong id="fd-anio">—</strong></div>
                            <div class="ficha-dato"><span>Color</span><strong id="fd-color">—</strong></div>
                            <div class="ficha-dato"><span>Tipo</span><strong id="fd-tipo">—</strong></div>
                            <div class="ficha-dato"><span>Estado</span><strong id="fd-estado">—</strong></div>
                            <div class="ficha-dato"><span>KM Actuales</span><strong id="fd-km">—</strong></div>
                            <div class="ficha-dato"><span>Ingreso</span><strong id="fd-ingreso">—</strong></div>
                            <div class="ficha-dato"><span>Unidad</span><strong id="fd-unidad">—</strong></div>
                            <div class="ficha-dato"><span>Destacamento</span><strong id="fd-destacamento">—</strong></div>
                            <div class="ficha-dato" style="grid-column:1/-1;"><span>Observaciones</span><strong id="fd-obs">—</strong></div>
                        </div>

                    </div>

                    <div id="fichaAlerta" style="display:none;margin-top:1.25rem;background:rgba(224,82,82,.1);border:1px solid rgba(224,82,82,.4);border-radius:10px;padding:1rem 1.25rem;align-items:center;gap:.75rem;">
                        <i class="bi bi-exclamation-triangle-fill" style="color:var(--danger);font-size:1.4rem;flex-shrink:0;"></i>
                        <div>
                            <div style="color:var(--danger);font-weight:700;font-size:.9rem;">¡Servicio vencido!</div>
                            <div id="fichaAlertaTexto" style="color:var(--text-muted);font-size:.82rem;"></div>
                        </div>
                    </div>

                    <div id="fichaProximo" style="display:none;margin-top:1.25rem;background:rgba(76,175,125,.1);border:1px solid rgba(76,175,125,.4);border-radius:10px;padding:1rem 1.25rem;align-items:center;gap:.75rem;">
                        <i class="bi bi-check-circle-fill" style="color:var(--success);font-size:1.4rem;flex-shrink:0;"></i>
                        <div>
                            <div style="color:var(--success);font-weight:700;font-size:.9rem;">Próximo servicio programado</div>
                            <div id="fichaProximoTexto" style="color:var(--text-muted);font-size:.82rem;"></div>
                        </div>
                    </div>
                </div>

                <!-- TAB SERVICIOS (sin cambios) -->
                <div id="tabServicios" class="ficha-tab-content" style="display:none;">

                    <!-- Alerta: orden en proceso activa -->
                    <div id="ordenEnProcesoAlert" style="display:none;margin-bottom:1.25rem;
        background:rgba(232,184,75,.1);border:1px solid rgba(232,184,75,.3);
        border-radius:10px;padding:1rem 1.25rem;align-items:center;gap:.75rem;">
                        <i class="bi bi-tools" style="color:var(--accent);font-size:1.4rem;flex-shrink:0;"></i>
                        <div style="flex:1;">
                            <div style="color:var(--accent);font-weight:700;font-size:.9rem;">
                                Orden de servicio en proceso
                            </div>
                            <div id="ordenEnProcesoTexto" style="color:var(--text-muted);font-size:.82rem;"></div>
                        </div>
                        <button onclick="abrirOrdenEnProceso()"
                            style="background:rgba(232,184,75,.2);border:1px solid rgba(232,184,75,.4);
            color:var(--accent);border-radius:8px;padding:.45rem .9rem;cursor:pointer;
            font-size:.82rem;font-weight:600;white-space:nowrap;flex-shrink:0;">
                            <i class="bi bi-pencil-square me-1"></i> Continuar orden
                        </button>
                    </div>

                    <!-- Botón abrir nueva orden -->
                    <button id="btnNuevaOrden" onclick="toggleFormNuevaOrden()"
                        style="margin-bottom:1.25rem;background:linear-gradient(135deg,var(--accent),var(--accent-2));
        border:none;color:var(--dark);padding:.7rem 1.5rem;border-radius:8px;
        font-family:'Rajdhani',sans-serif;font-weight:700;font-size:.95rem;
        cursor:pointer;display:flex;align-items:center;gap:.5rem;">
                        <i class="bi bi-plus-circle"></i> Nueva Orden de Servicio
                    </button>

                    <!-- PASO 1: Formulario nueva orden -->
                    <div id="formNuevaOrden" style="display:none;background:var(--dark-3);
        border:1px solid rgba(232,184,75,.3);border-radius:12px;
        padding:1.25rem;margin-bottom:1.5rem;">
                        <div style="font-family:'Rajdhani',sans-serif;font-size:1rem;font-weight:700;
            color:var(--accent);margin-bottom:1rem;letter-spacing:.5px;">
                            <i class="bi bi-clipboard-plus"></i> PASO 1 — Datos de ingreso al taller
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;">
                            <div>
                                <label class="form-label"><i class="bi bi-calendar"></i> Fecha de ingreso *</label>
                                <input type="date" id="ordenFecha" class="form-control">
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-speedometer"></i> KM al ingreso *</label>
                                <input type="number" id="ordenKm" class="form-control" placeholder="0" min="0">
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-person"></i> Responsable</label>
                                <input type="text" id="ordenResponsable" class="form-control" placeholder="Mecánico encargado...">
                            </div>
                            <div style="grid-column:1/-1;">
                                <label class="form-label"><i class="bi bi-chat-text"></i> Observaciones generales</label>
                                <input type="text" id="ordenObs" class="form-control" placeholder="Motivo del ingreso...">
                            </div>
                        </div>
                        <div style="display:flex;gap:.75rem;margin-top:1rem;">
                            <button onclick="crearOrden()"
                                style="background:linear-gradient(135deg,var(--accent),var(--accent-2));
                border:none;color:var(--dark);padding:.7rem 1.5rem;border-radius:8px;
                font-family:'Rajdhani',sans-serif;font-weight:700;font-size:.95rem;cursor:pointer;">
                                <i class="bi bi-door-open me-1"></i> Abrir Orden
                            </button>
                            <button onclick="toggleFormNuevaOrden()"
                                style="background:transparent;border:1.5px solid var(--border);
                color:var(--text-muted);padding:.7rem 1.25rem;border-radius:8px;
                font-family:'Rajdhani',sans-serif;font-weight:700;cursor:pointer;">
                                Cancelar
                            </button>
                        </div>
                    </div>

                    <!-- PASO 2: Panel de orden abierta (agregar items) -->
                    <div id="panelOrdenAbierta" style="display:none;background:var(--dark-3);
        border:1px solid rgba(232,184,75,.4);border-radius:12px;
        padding:1.25rem;margin-bottom:1.5rem;">

                        <!-- Header de la orden -->
                        <div style="display:flex;align-items:center;justify-content:space-between;
            margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem;">
                            <div>
                                <div style="font-family:'Rajdhani',sans-serif;font-size:1rem;font-weight:700;
                    color:var(--accent);letter-spacing:.5px;">
                                    <i class="bi bi-tools"></i> PASO 2 — Registrar servicios realizados
                                </div>
                                <div id="ordenHeaderInfo" style="font-size:.78rem;color:var(--text-muted);margin-top:.25rem;"></div>
                            </div>
                            <div style="display:flex;gap:.5rem;">
                                <button onclick="completarOrden()"
                                    id="btnCompletarOrden"
                                    style="background:linear-gradient(135deg,var(--success),#2e7d52);
                    border:none;color:#fff;padding:.6rem 1.25rem;border-radius:8px;
                    font-family:'Rajdhani',sans-serif;font-weight:700;font-size:.9rem;
                    cursor:pointer;display:flex;align-items:center;gap:.4rem;">
                                    <i class="bi bi-check-circle-fill"></i> Completar Orden
                                </button>
                                <button onclick="confirmarEliminarOrden()"
                                    style="background:transparent;border:1px solid rgba(224,82,82,.3);
                    color:var(--danger);padding:.6rem .9rem;border-radius:8px;cursor:pointer;
                    font-size:.85rem;">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Items ya agregados -->
                        <div id="itemsOrdenWrap" style="margin-bottom:1.25rem;"></div>

                        <!-- Formulario agregar item -->
                        <div style="background:var(--dark-2);border:1px solid var(--border);
            border-radius:10px;padding:1rem;">
                            <div style="font-size:.78rem;color:var(--accent);font-weight:600;
                text-transform:uppercase;letter-spacing:.5px;margin-bottom:.75rem;">
                                <i class="bi bi-plus-circle"></i> Agregar servicio a la orden
                            </div>
                            <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:.75rem;">
                                <div>
                                    <label class="form-label"><i class="bi bi-gear"></i> Tipo de servicio *</label>
                                    <select id="itemTipo" class="form-select"></select>
                                </div>
                                <div>
                                    <label class="form-label"><i class="bi bi-speedometer"></i> Próximo KM</label>
                                    <input type="number" id="itemKmProximo" class="form-control"
                                        placeholder="Auto" min="0">
                                </div>
                                <div style="grid-column:1/-1;">
                                    <label class="form-label"><i class="bi bi-chat-text"></i> Observación</label>
                                    <input type="text" id="itemObservacion" class="form-control"
                                        placeholder="Detalle del servicio...">
                                </div>
                            </div>
                            <button onclick="agregarItem()"
                                style="margin-top:.75rem;background:rgba(232,184,75,.15);
                border:1px solid rgba(232,184,75,.3);color:var(--accent);
                padding:.6rem 1.25rem;border-radius:8px;font-family:'Rajdhani',sans-serif;
                font-weight:700;font-size:.9rem;cursor:pointer;">
                                <i class="bi bi-plus me-1"></i> Agregar
                            </button>
                        </div>
                    </div>

                    <!-- Historial de órdenes -->
                    <div id="tablaOrdenesWrap"></div>
                </div>

                <!-- TAB REPARACIONES (sin cambios) -->
                <div id="tabReparaciones" class="ficha-tab-content" style="display:none;">
                    <button id="btnToggleFormReparacion" onclick="toggleFormReparacion()" style="margin-bottom:1.25rem;background:linear-gradient(135deg,var(--danger),#c93030);border:none;color:#fff;padding:.7rem 1.5rem;border-radius:8px;font-family:'Rajdhani',sans-serif;font-weight:700;font-size:.95rem;cursor:pointer;display:flex;align-items:center;gap:.5rem;">
                        <i class="bi bi-plus-circle"></i> Registrar Nueva Reparación
                    </button>
                    <div id="formNuevaReparacion" style="display:none;background:var(--dark-3);border:1px solid var(--border);border-radius:12px;padding:1.25rem;margin-bottom:1.5rem;">
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;">
                            <div><label class="form-label"><i class="bi bi-wrench"></i> Tipo *</label><select id="repTipo" class="form-select"></select></div>
                            <div><label class="form-label"><i class="bi bi-calendar"></i> Fecha Inicio *</label><input type="date" id="repFechaInicio" class="form-control"></div>
                            <div><label class="form-label"><i class="bi bi-calendar-check"></i> Fecha Fin</label><input type="date" id="repFechaFin" class="form-control"></div>
                            <div style="grid-column:1/-1;"><label class="form-label"><i class="bi bi-card-text"></i> Descripción *</label><input type="text" id="repDescripcion" class="form-control" placeholder="Detalle de la reparación..."></div>
                            <div><label class="form-label"><i class="bi bi-speedometer"></i> KM al momento *</label><input type="number" id="repKm" class="form-control" placeholder="0" min="0"></div>
                            <div><label class="form-label"><i class="bi bi-currency-dollar"></i> Costo (Q)</label><input type="number" id="repCosto" class="form-control" placeholder="0.00" step="0.01" min="0"></div>
                            <div><label class="form-label"><i class="bi bi-activity"></i> Estado</label><select id="repEstado" class="form-select">
                                    <option value="En proceso">En proceso</option>
                                    <option value="Finalizada">Finalizada</option>
                                </select></div>
                            <div><label class="form-label"><i class="bi bi-shop"></i> Proveedor/Taller</label><input type="text" id="repProveedor" class="form-control" placeholder="Nombre del taller..."></div>
                            <div><label class="form-label"><i class="bi bi-person"></i> Responsable</label><input type="text" id="repResponsable" class="form-control" placeholder="Nombre..."></div>
                            <div><label class="form-label"><i class="bi bi-chat-text"></i> Observaciones</label><input type="text" id="repObs" class="form-control" placeholder="Notas adicionales..."></div>
                        </div>
                        <button onclick="guardarReparacion()" style="margin-top:1rem;background:linear-gradient(135deg,var(--danger),#c93030);border:none;color:#fff;padding:.7rem 1.5rem;border-radius:8px;font-family:'Rajdhani',sans-serif;font-weight:700;font-size:.95rem;cursor:pointer;">
                            <i class="bi bi-save me-1"></i> Guardar Reparación
                        </button>
                    </div>
                    <div id="tablaReparacionesWrap"></div>
                </div>

                <!-- TAB SEGURO (NUEVO) -->
                <div id="tabSeguro" class="ficha-tab-content" style="display:none;">
                    <button id="btnToggleFormSeguro" onclick="toggleFormSeguroFicha()" style="margin-bottom:1.25rem;background:linear-gradient(135deg,#3a7bd5,#2563b0);border:none;color:#fff;padding:.7rem 1.5rem;border-radius:8px;font-family:'Rajdhani',sans-serif;font-weight:700;font-size:.95rem;cursor:pointer;display:flex;align-items:center;gap:.5rem;">
                        <i class="bi bi-plus-circle"></i> Agregar Nuevo Seguro
                    </button>

                    <div id="formNuevoSeguroFicha" style="display:none;background:var(--dark-3);border:1px solid var(--border);border-radius:12px;padding:1.25rem;margin-bottom:1.5rem;">
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;">
                            <div style="grid-column:1/-1;">
                                <label class="form-label"><i class="bi bi-building"></i> Aseguradora *</label>
                                <input type="text" id="fsAseguradora" class="form-control" placeholder="Ej: Seguros Universales...">
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-file-text"></i> Número de Póliza *</label>
                                <input type="text" id="fsNumeroPoliza" class="form-control" placeholder="POL-00001">
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-shield"></i> Tipo de Cobertura</label>
                                <select id="fsTipoCobertura" class="form-select">
                                    <option value="Básico">Básico</option>
                                    <option value="Amplio">Amplio</option>
                                    <option value="Todo riesgo">Todo riesgo</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-currency-dollar"></i> Prima Anual (Q)</label>
                                <input type="number" id="fsPrima" class="form-control" placeholder="0.00" step="0.01" min="0">
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-calendar-check"></i> Fecha Inicio *</label>
                                <input type="date" id="fsFechaInicio" class="form-control">
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-calendar-x"></i> Fecha Vencimiento *</label>
                                <input type="date" id="fsFechaVenc" class="form-control">
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-person"></i> Agente de Contacto</label>
                                <input type="text" id="fsAgente" class="form-control" placeholder="Nombre...">
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-telephone"></i> Teléfono Agente</label>
                                <input type="tel" id="fsTelefono" class="form-control" placeholder="502-XXXX-XXXX">
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-file-earmark-pdf"></i> Póliza PDF <small style="font-size:.7rem;color:var(--text-muted);text-transform:none;">(opcional)</small></label>
                                <div class="file-upload-area" id="areaPolizaFicha" style="padding:.75rem;">
                                    <input type="file" id="fsArchivo" accept="application/pdf">
                                    <div class="upload-icon" style="font-size:1.3rem;"><i class="bi bi-file-pdf"></i></div>
                                    <div class="upload-label" style="font-size:.75rem;"><span>Subir PDF</span></div>
                                </div>
                            </div>
                            <div style="grid-column:1/-1;">
                                <label class="form-label"><i class="bi bi-chat-text"></i> Observaciones</label>
                                <textarea id="fsObs" class="form-control" rows="2" placeholder="Detalles adicionales..."></textarea>
                            </div>
                        </div>
                        <button onclick="guardarSeguroFicha()" style="margin-top:1rem;background:linear-gradient(135deg,#3a7bd5,#2563b0);border:none;color:#fff;padding:.7rem 1.5rem;border-radius:8px;font-family:'Rajdhani',sans-serif;font-weight:700;font-size:.95rem;cursor:pointer;">
                            <i class="bi bi-save me-1"></i> Guardar Seguro
                        </button>
                    </div>

                    <div id="tablaSeguroWrap"></div>
                </div>

                <!-- TAB ACCIDENTES (NUEVO) -->
                <div id="tabAccidentes" class="ficha-tab-content" style="display:none;">
                    <button id="btnToggleFormAccidente" onclick="toggleFormAccidente()" style="margin-bottom:1.25rem;background:linear-gradient(135deg,var(--danger),#c93030);border:none;color:#fff;padding:.7rem 1.5rem;border-radius:8px;font-family:'Rajdhani',sans-serif;font-weight:700;font-size:.95rem;cursor:pointer;display:flex;align-items:center;gap:.5rem;">
                        <i class="bi bi-plus-circle"></i> Registrar Accidente / Choque
                    </button>

                    <div id="formNuevoAccidente" style="display:none;background:var(--dark-3);border:1px solid var(--border);border-radius:12px;padding:1.25rem;margin-bottom:1.5rem;">
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;">
                            <div>
                                <label class="form-label"><i class="bi bi-calendar-event"></i> Fecha del Accidente *</label>
                                <input type="date" id="acFecha" class="form-control">
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-geo-alt"></i> Lugar *</label>
                                <input type="text" id="acLugar" class="form-control" placeholder="Dirección o referencia...">
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-exclamation-triangle"></i> Tipo de Accidente *</label>
                                <select id="acTipo" class="form-select">
                                    <option value="Colisión">Colisión</option>
                                    <option value="Volcamiento">Volcamiento</option>
                                    <option value="Atropello">Atropello</option>
                                    <option value="Daño por tercero">Daño por tercero</option>
                                    <option value="Robo parcial">Robo parcial</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div style="grid-column:1/-1;">
                                <label class="form-label"><i class="bi bi-card-text"></i> Descripción *</label>
                                <textarea id="acDescripcion" class="form-control" rows="2" placeholder="Describa cómo ocurrió el accidente..."></textarea>
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-person"></i> Conductor Responsable</label>
                                <input type="text" id="acConductor" class="form-control" placeholder="Nombre del conductor...">
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-hash"></i> No. Expediente / Reclamo</label>
                                <input type="text" id="acExpediente" class="form-control" placeholder="EXP-0001">
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-activity"></i> Estado del Caso</label>
                                <select id="acEstado" class="form-select">
                                    <option value="Reportado">Reportado</option>
                                    <option value="En trámite">En trámite</option>
                                    <option value="Cerrado">Cerrado</option>
                                    <option value="Sin seguro">Sin seguro</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-currency-dollar"></i> Costo Estimado (Q)</label>
                                <input type="number" id="acCostoEst" class="form-control" placeholder="0.00" step="0.01" min="0">
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-currency-dollar"></i> Costo Real (Q)</label>
                                <input type="number" id="acCostoReal" class="form-control" placeholder="0.00" step="0.01" min="0">
                            </div>
                            <div style="grid-column:1/-1;">
                                <label class="form-label">
                                    <i class="bi bi-camera"></i> Fotos / Evidencia
                                    <small style="font-size:.7rem;color:var(--text-muted);text-transform:none;">(JPG, PNG, PDF — máx. 4)</small>
                                </label>
                                <div id="fotosAccContainer" style="display:flex;flex-direction:column;gap:.5rem;">
                                    <!-- Se generan dinámicamente -->
                                </div>
                                <button type="button" id="btnAgregarFotoAcc"
                                    onclick="agregarFotoAcc()"
                                    style="margin-top:.5rem;background:transparent;border:1px dashed var(--border);
                                        color:var(--text-muted);padding:.5rem 1rem;border-radius:8px;
                                        cursor:pointer;font-size:.82rem;display:flex;align-items:center;gap:.5rem;
                                        transition:all .2s;">
                                    <i class="bi bi-plus-circle"></i> Agregar foto / archivo
                                </button>
                                <small id="fotosAccContador" style="color:var(--text-muted);font-size:.72rem;margin-top:.25rem;display:block;">
                                    0 / 4 archivos
                                </small>
                            </div>
                            <div>
                                <label class="form-label"><i class="bi bi-file-earmark-text"></i> Informe Policial <small style="font-size:.7rem;color:var(--text-muted);text-transform:none;">(PDF)</small></label>
                                <div class="file-upload-area" id="areaInformeAcc" style="padding:.75rem;">
                                    <input type="file" id="acInforme" accept="application/pdf">
                                    <div class="upload-icon" style="font-size:1.3rem;"><i class="bi bi-file-pdf"></i></div>
                                    <div class="upload-label" style="font-size:.75rem;"><span>Subir informe PDF</span></div>
                                </div>
                            </div>
                            <div style="grid-column:1/-1;">
                                <label class="form-label"><i class="bi bi-chat-text"></i> Observaciones</label>
                                <textarea id="acObs" class="form-control" rows="2" placeholder="Notas adicionales..."></textarea>
                            </div>
                        </div>
                        <button onclick="guardarAccidente()" style="margin-top:1rem;background:linear-gradient(135deg,var(--danger),#c93030);border:none;color:#fff;padding:.7rem 1.5rem;border-radius:8px;font-family:'Rajdhani',sans-serif;font-weight:700;font-size:.95rem;cursor:pointer;">
                            <i class="bi bi-save me-1"></i> Guardar Accidente
                        </button>
                    </div>

                    <!-- Resumen de costos -->
                    <div id="resumenCostos" style="display:none;" class="costo-resumen">
                        <div>
                            <div style="font-size:.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Costo total acumulado</div>
                            <div id="costoTotalTexto" style="font-size:1.3rem;font-weight:700;color:var(--danger);font-family:'Rajdhani',sans-serif;">Q 0.00</div>
                        </div>
                        <div style="font-size:.8rem;color:var(--text-muted);">
                            <i class="bi bi-exclamation-triangle-fill" style="color:var(--accent);"></i>
                            Solo incluye costos reales registrados
                        </div>
                    </div>

                    <div id="tablaAccidentesWrap"></div>
                </div>

            </div><!-- fin contenido tabs -->
        </div>
    </div><!-- fin modal -->

    <!-- MODAL CHEQUEO -->
    <div id="modalChequeo" style="
    display:none; position:fixed; inset:0;
    background:rgba(0,0,0,.75); backdrop-filter:blur(6px);
    z-index:3000; align-items:center; justify-content:center; padding:1rem;">

        <div style="
        background:var(--dark-2); border:1px solid var(--border); border-radius:18px;
        width:100%; max-width:800px; max-height:92vh;
        display:flex; flex-direction:column;
        box-shadow:0 30px 80px rgba(0,0,0,.6); overflow:hidden;">

            <!-- Header -->
            <div style="background:linear-gradient(90deg,var(--dark-3),#1f2335);border-bottom:1px solid var(--border);padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem;flex-shrink:0;">
                <div style="width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#6f42c1,#5a2d9e);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.2rem;flex-shrink:0;">
                    <i class="bi bi-clipboard2-check"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-family:'Rajdhani',sans-serif;font-size:1.3rem;font-weight:700;color:#a78bfa;letter-spacing:1px;">Hoja de Chequeo</div>
                    <div id="chequeoModalSubtitulo" style="font-size:.82rem;color:var(--text-muted);">Vehículo — Chequeo mensual</div>
                </div>
                <button onclick="cerrarModalChequeo()" style="background:rgba(224,82,82,.15);border:1px solid rgba(224,82,82,.3);color:var(--danger);width:36px;height:36px;border-radius:8px;cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Contenido -->
            <div style="overflow-y:auto;flex:1;padding:1.5rem;">

                <!-- Historial -->
                <div id="chequeoHistorialWrap" style="margin-bottom:1.5rem;"></div>

                <!-- Alerta mes completado -->
                <div id="chequeoAlertaMes" style="display:none;margin-bottom:1.25rem;background:rgba(76,175,125,.1);border:1px solid rgba(76,175,125,.3);border-radius:10px;padding:1rem 1.25rem;align-items:center;gap:.75rem;">
                    <i class="bi bi-check-circle-fill" style="color:var(--success);font-size:1.4rem;flex-shrink:0;"></i>
                    <div>
                        <div style="color:var(--success);font-weight:700;font-size:.9rem;">Chequeo del mes completado</div>
                        <div style="color:var(--text-muted);font-size:.82rem;">Ya existe un chequeo completado este mes.</div>
                    </div>
                </div>

                <button id="btnNuevoChequeo" onclick="iniciarNuevoChequeo()"
                    style="margin-bottom:1.25rem;background:linear-gradient(135deg,#6f42c1,#5a2d9e);border:none;color:#fff;padding:.7rem 1.5rem;border-radius:8px;font-family:'Rajdhani',sans-serif;font-weight:700;font-size:.95rem;cursor:pointer;display:flex;align-items:center;gap:.5rem;">
                    <i class="bi bi-plus-circle"></i> Nuevo Chequeo Mensual
                </button>

                <!-- Formulario -->
                <div id="formNuevoChequeo" style="display:none;background:var(--dark-3);border:1px solid var(--border);border-radius:12px;padding:1.5rem;margin-bottom:1.5rem;">
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;margin-bottom:1.25rem;">
                        <div>
                            <label class="form-label"><i class="bi bi-calendar"></i> Fecha *</label>
                            <input type="date" id="chqFecha" class="form-control">
                        </div>
                        <div>
                            <label class="form-label"><i class="bi bi-speedometer"></i> KM al chequeo *</label>
                            <input type="number" id="chqKm" class="form-control" placeholder="0" min="0">
                        </div>
                        <div>
                            <label class="form-label"><i class="bi bi-person"></i> Realizado por</label>
                            <input type="text" id="chqResponsable" class="form-control" placeholder="Nombre...">
                        </div>
                    </div>

                    <div style="overflow-x:auto;margin-bottom:1.25rem;">
                        <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
                            <thead>
                                <tr style="background:var(--dark-2);border-bottom:2px solid var(--border);">
                                    <th style="padding:.6rem .75rem;text-align:left;color:var(--text-muted);font-size:.72rem;letter-spacing:.5px;text-transform:uppercase;width:40px;">No.</th>
                                    <th style="padding:.6rem .75rem;text-align:left;color:var(--text-muted);font-size:.72rem;letter-spacing:.5px;text-transform:uppercase;">Descripción</th>
                                    <th style="padding:.6rem .75rem;text-align:center;color:#4caf7d;font-size:.72rem;letter-spacing:.5px;text-transform:uppercase;width:60px;">BE</th>
                                    <th style="padding:.6rem .75rem;text-align:center;color:#e8b84b;font-size:.72rem;letter-spacing:.5px;text-transform:uppercase;width:60px;">ME</th>
                                    <th style="padding:.6rem .75rem;text-align:center;color:#e05252;font-size:.72rem;letter-spacing:.5px;text-transform:uppercase;width:60px;">MEI</th>
                                    <th style="padding:.6rem .75rem;text-align:center;color:#7c8398;font-size:.72rem;letter-spacing:.5px;text-transform:uppercase;width:60px;">NT</th>
                                    <th style="padding:.6rem .75rem;text-align:left;color:var(--text-muted);font-size:.72rem;letter-spacing:.5px;text-transform:uppercase;">Obs.</th>
                                </tr>
                            </thead>
                            <tbody id="chequeoTablaItems"></tbody>
                        </table>
                    </div>

                    <div style="margin-bottom:1rem;">
                        <label class="form-label"><i class="bi bi-chat-text"></i> Observaciones Generales</label>
                        <textarea id="chqObservaciones" class="form-control" rows="2" placeholder="Notas adicionales..."></textarea>
                    </div>

                    <div style="margin-bottom:1rem;">
                        <div style="display:flex;justify-content:space-between;margin-bottom:.4rem;">
                            <span style="font-size:.78rem;color:var(--text-muted);">Progreso</span>
                            <span id="chqProgreso" style="font-size:.78rem;color:var(--accent);font-weight:600;">0 / 17</span>
                        </div>
                        <div style="background:var(--dark-2);border-radius:20px;height:6px;overflow:hidden;">
                            <div id="chqBarraProgreso" style="height:100%;width:0%;background:linear-gradient(90deg,var(--accent),var(--success));border-radius:20px;transition:width .3s ease;"></div>
                        </div>
                    </div>

                    <button onclick="guardarChequeo()" id="btnGuardarChequeo"
                        style="background:linear-gradient(135deg,#6f42c1,#5a2d9e);border:none;color:#fff;padding:.7rem 1.5rem;border-radius:8px;font-family:'Rajdhani',sans-serif;font-weight:700;font-size:.95rem;cursor:pointer;opacity:.5;"
                        disabled>
                        <i class="bi bi-clipboard2-check me-1"></i> Completar Chequeo
                    </button>
                    <button onclick="cancelarChequeo()"
                        style="margin-left:.5rem;background:transparent;border:1.5px solid var(--danger);color:var(--danger);padding:.7rem 1.5rem;border-radius:8px;font-family:'Rajdhani',sans-serif;font-weight:700;font-size:.95rem;cursor:pointer;">
                        <i class="bi bi-x-circle me-1"></i> Cancelar
                    </button>
                </div>

                <div id="tablaChequeoWrap"></div>
            </div>
        </div>
    </div>
    <!-- LIGHTBOX -->
    <div id="bhr-lightbox">
        <button class="lightbox-close" onclick="cerrarLightbox()">
            <i class="bi bi-x-lg"></i>
        </button>
        <button class="lightbox-nav prev" id="lbPrev" onclick="navLightbox(-1)">
            <i class="bi bi-chevron-left"></i>
        </button>
        <img id="lbImagen" src="" alt="">
        <button class="lightbox-nav next" id="lbNext" onclick="navLightbox(1)">
            <i class="bi bi-chevron-right"></i>
        </button>
        <div class="lightbox-caption" id="lbCaption"></div>
    </div>

</div><!-- fin container -->
<script>
    const BASE = '<?= $_ENV["APP_NAME"] ? "/" . $_ENV["APP_NAME"] : "" ?>';
    const AUTH_NOMBRE = '<?= isset($_SESSION["auth_grado"])
                                ? htmlspecialchars($_SESSION["auth_grado"] . " " . ($_SESSION["auth_arma"] ?? "") . " " . $_SESSION["auth_nombre"])
                                : "" ?>';
</script>
<script src="build/js/vehiculos/index.js" type="module"></script>