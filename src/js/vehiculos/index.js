import { Toast, validarFormulario } from "../funciones";
import Swal from "sweetalert2";
import { Dropdown } from "bootstrap";

const BASE = document.querySelector('[data-base]')?.dataset.base ?? '';
const ROL = document.documentElement.dataset.rol ?? '';
const esCIA = ['COMTE_CIA', 'SUPERUSUARIO'].includes(ROL);
const esBHR = ROL === 'COMTE_BHR';

// ── ELEMENTOS DOM ─────────────────────────────────────────────────────────────
const formulario = document.getElementById('formularioVehiculo');
const btnGuardar = document.getElementById('btnGuardar');
const btnModificar = document.getElementById('btnModificar');
const btnCancelar = document.getElementById('btnCancelar');
const btnFlotante = document.getElementById('btnFlotante');
const contenedorFormulario = document.getElementById('contenedorFormulario');
const contenedorTabla = document.getElementById('contenedorTabla');
const tituloFormulario = document.getElementById('tituloFormulario');
const inputPlaca = document.getElementById('placa');
const inputPlacaOriginal = document.getElementById('placa_original');
const cardsGrid = document.getElementById('cardsGrid');
const filtroBusqueda = document.getElementById('filtroBusqueda');
const contadorVisible = document.getElementById('contadorVisible');
const inputFoto = document.getElementById('foto_frente');
const areaFoto = document.getElementById('areaFoto');
const fotoPreview = document.getElementById('fotoPreview');
const inputPdf = document.getElementById('tarjeta_pdf');
const areaPdf = document.getElementById('areaPdf');
const pdfNombre = document.getElementById('pdfNombre');
const fotoActualContainer = document.getElementById('fotoActualContainer');
const fotoActual = document.getElementById('fotoActual');
const selectUnidad = document.getElementById('id_unidad');
const infoDestacamento = document.getElementById('infoDestacamento');
const infoNombre = document.getElementById('infoNombreDestacamento');
const infoUbicacion = document.getElementById('infoUbicacion');

// ── ESTADO GLOBAL ─────────────────────────────────────────────────────────────
let todosLosVehiculos = [];
let modoEdicion = false;
let tipoSeleccionado = null;
let ordenActualId = null;
let tiposServicio = [];
let tiposReparacion = [];
// Al inicio del archivo, junto a las otras variables globales
let _ordenIdRespaldo = null;

// ── GRUPOS DE TIPOS ───────────────────────────────────────────────────────────
const GRUPOS = [
    { key: 'Pickup', label: 'Pickups', tipos: ['Pickup'] },
    { key: 'Camión', label: 'Camiones', tipos: ['Camión'] },
    { key: 'Motocicleta', label: 'Motocicletas', tipos: ['Motocicleta'] },
    { key: 'Otros', label: 'Otros', tipos: ['Automóvil', 'Microbús', 'Blindado', 'Camioneta', 'Cuatrimoto', 'Otro'] },
    { key: 'Todos', label: 'Todos', tipos: null },
];
const ACENTO = '#e8b84b';

// ── SVG TIPOS ─────────────────────────────────────────────────────────────────
const SVG_TIPOS = {
    'Pickup': () => `<img src="${BASE}/images/tipos/pickup.png" style="width:100%;height:100%;object-fit:cover;display:block;">`,
    'Camión': () => `<img src="${BASE}/images/tipos/camion.png" style="width:100%;height:100%;object-fit:cover;display:block;">`,
    'Motocicleta': () => `<img src="${BASE}/images/tipos/moto.png"   style="width:100%;height:100%;object-fit:cover;display:block;">`,
    'Otros': () => `<img src="${BASE}/images/tipos/otros.png"  style="width:100%;height:100%;object-fit:cover;display:block;">`,
    'Todos': () => `<img src="${BASE}/images/tipos/todos.png"  style="width:100%;height:100%;object-fit:cover;display:block;">`,
};

const fmtFecha = (fecha) => {
    if (!fecha) return '—';
    const [y, m, d] = fecha.split('-');
    if (!y || !m || !d) return fecha;
    return `${d}/${m}/${y}`;
};

// ── RENDER PANTALLA TIPOS ─────────────────────────────────────────────────────
const renderTipos = (vehiculos) => {
    const grid = document.getElementById('tiposGrid');
    if (!grid) return;
    grid.innerHTML = GRUPOS.map((grupo, i) => {
        const count = grupo.tipos === null
            ? vehiculos.length
            : vehiculos.filter(v => grupo.tipos.includes(v.tipo)).length;
        const svg = SVG_TIPOS[grupo.key]?.(ACENTO) ?? '';
        return `
        <div class="tipo-card" style="animation-delay:${i * 0.08}s"
             onclick="seleccionarTipo('${grupo.key}')">
            <div class="tipo-card-ilustracion"
                 style="background:linear-gradient(145deg,#1a1d27,#0f1117);">
                ${svg}
            </div>
            <div class="tipo-card-body">
                <span class="tipo-card-nombre">${grupo.label}</span>
                <span class="tipo-card-count ${count === 0 ? 'sin-vehiculos' : ''}">
                    ${count === 0 ? 'Sin unidades' : count + (count === 1 ? ' unidad' : ' unidades')}
                </span>
            </div>
        </div>`;
    }).join('');
};

// ── SELECCIONAR TIPO ──────────────────────────────────────────────────────────
const seleccionarTipo = (grupoKey) => {
    tipoSeleccionado = grupoKey;
    const grupo = GRUPOS.find(g => g.key === grupoKey);
    document.getElementById('contenedorTipos').style.display = 'none';
    document.getElementById('contenedorTabla').style.display = '';
    const label = document.getElementById('labelTipoActual');
    if (label && grupo) label.innerHTML = `<i class="bi bi-chevron-right"></i> ${grupo.label.toUpperCase()}`;
    const fu = document.getElementById('filtroUnidad');
    const fb = document.getElementById('filtroBusqueda');
    if (fu) fu.value = '';
    if (fb) fb.value = '';
    mostrarLoader('Cargando vehículos...');
    aplicarFiltros();
    ocultarLoader();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
window.seleccionarTipo = seleccionarTipo;

// ── VOLVER A TIPOS ────────────────────────────────────────────────────────────
const volverATipos = () => {
    tipoSeleccionado = null;
    mostrarLoader('Volviendo...');
    document.getElementById('contenedorTabla').style.display = 'none';
    document.getElementById('contenedorTipos').style.display = '';
    ocultarLoader();
    const fu = document.getElementById('filtroUnidad');
    const fb = document.getElementById('filtroBusqueda');
    if (fu) fu.value = '';
    if (fb) fb.value = '';
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
document.getElementById('btnVolverTipos')?.addEventListener('click', volverATipos);

// ── FILTROS ───────────────────────────────────────────────────────────────────
const aplicarFiltros = () => {
    const busq = (document.getElementById('filtroBusqueda')?.value ?? '').toLowerCase().trim();
    const unidad = document.getElementById('filtroUnidad')?.value || '';
    const grupo = GRUPOS.find(g => g.key === tipoSeleccionado);
    const filtrados = todosLosVehiculos.filter(v => {
        const matchTipo = !grupo || grupo.tipos === null || grupo.tipos.includes(v.tipo);
        const matchUnidad = !unidad || String(v.id_unidad) === unidad;
        const matchBusq = !busq
            || (v.placa ?? '').toLowerCase().includes(busq)
            || (v.marca ?? '').toLowerCase().includes(busq)
            || (v.modelo ?? '').toLowerCase().includes(busq)
            || (v.numero_serie ?? '').toLowerCase().includes(busq)
            || (v.color ?? '').toLowerCase().includes(busq)
            || (v.anio ?? '').toString().includes(busq)
            || (v.estado ?? '').toLowerCase().includes(busq)
            || (v.km_actuales ?? '').toString().includes(busq)
            || (v.unidad_nombre ?? '').toLowerCase().includes(busq)
            || (v.observaciones ?? '').toLowerCase().includes(busq);
        return matchTipo && matchUnidad && matchBusq;
    });
    renderCartas(filtrados);
};
document.getElementById('filtroBusqueda')?.addEventListener('input', aplicarFiltros);
document.getElementById('filtroUnidad')?.addEventListener('change', aplicarFiltros);

// ── LOADER ────────────────────────────────────────────────────────────────────
const mostrarLoader = (mensaje = 'Procesando...') => {
    const loader = document.getElementById('bhr-loader');
    const msg = document.getElementById('loaderMensaje');
    if (msg) msg.textContent = mensaje;
    if (loader) loader.classList.add('visible');
};
const ocultarLoader = () => {
    const loader = document.getElementById('bhr-loader');
    if (loader) loader.classList.remove('visible');
};

// ── CARGAR UNIDADES ───────────────────────────────────────────────────────────
const cargarUnidades = async () => {
    try {
        const r = await fetch(`${BASE}/API/unidades/lista`);
        const d = await r.json();
        if (d.codigo !== 1) return;
        selectUnidad.innerHTML =
            '<option value="">— Sin asignar —</option>' +
            d.datos.map(u =>
                `<option value="${u.id_unidad}"
                    data-destacamento="${u.destacamento_nombre || ''}"
                    data-depto="${u.departamento || ''}"
                    data-municipio="${u.municipio || ''}">
                    ${u.unidad_destacamento}
                </option>`
            ).join('');
        const filtroUnidad = document.getElementById('filtroUnidad');
        if (filtroUnidad) {
            filtroUnidad.innerHTML =
                '<option value="">Todas las unidades</option>' +
                d.datos.map(u =>
                    `<option value="${u.id_unidad}">${u.unidad_destacamento}</option>`
                ).join('');
        }
    } catch (err) {
        console.error('Error cargando unidades:', err);
    }
};
selectUnidad.addEventListener('change', () => {
    const opt = selectUnidad.options[selectUnidad.selectedIndex];
    const destacamento = opt.dataset.destacamento;
    const depto = opt.dataset.depto;
    const municipio = opt.dataset.municipio;
    if (!selectUnidad.value || !destacamento) {
        infoDestacamento.style.display = 'none';
        return;
    }
    infoNombre.textContent = destacamento;
    infoUbicacion.textContent = municipio ? `${municipio}, ${depto}` : depto;
    infoDestacamento.style.display = 'block';
});

// ── FILE UPLOAD — FOTO FRENTE ─────────────────────────────────────────────────
inputFoto.addEventListener('change', async () => {
    const file = inputFoto.files[0];
    if (!file) return;
    const hayFotoActual = modoEdicion && fotoPreview.classList.contains('visible');
    if (hayFotoActual) {
        const confirm = await Swal.fire({
            icon: 'question', title: '¿Reemplazar foto?',
            html: `Se reemplazará la foto actual por <strong>${file.name}</strong>.`,
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-arrow-repeat"></i> Sí, reemplazar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#3a7bd5', cancelButtonColor: '#e05252',
            background: '#1a1d27', color: '#e8eaf0'
        });
        if (!confirm.isConfirmed) { inputFoto.value = ''; return; }
    }
    areaFoto.classList.add('has-file');
    areaFoto.querySelector('.upload-icon i').className = 'bi bi-check-circle-fill';
    areaFoto.querySelector('.upload-label').innerHTML = `<span style="color:var(--success)">${file.name}</span><br><small>Nueva foto seleccionada</small>`;
    const reader = new FileReader();
    reader.onload = (e) => { fotoPreview.src = e.target.result; fotoPreview.classList.add('visible'); };
    reader.readAsDataURL(file);
});

// ── FILE UPLOAD — FOTO LATERAL ────────────────────────────────────────────────
const inputFotoLateral = document.getElementById('foto_lateral');
const areaFotoLateral = document.getElementById('areaFotoLateral');
const fotoLateralPreview = document.getElementById('fotoLateralPreview');
if (inputFotoLateral) {
    inputFotoLateral.addEventListener('change', () => {
        const file = inputFotoLateral.files[0];
        if (!file) return;
        areaFotoLateral.classList.add('has-file');
        areaFotoLateral.querySelector('.upload-icon i').className = 'bi bi-check-circle-fill';
        areaFotoLateral.querySelector('.upload-label').innerHTML = `<span style="color:var(--success)">${file.name}</span><br><small>Foto lateral seleccionada</small>`;
        const reader = new FileReader();
        reader.onload = (e) => { fotoLateralPreview.src = e.target.result; fotoLateralPreview.classList.add('visible'); };
        reader.readAsDataURL(file);
    });
}

// ── FILE UPLOAD — FOTO TRASERA ────────────────────────────────────────────────
const inputFotoTrasera = document.getElementById('foto_trasera');
const areaFotoTrasera = document.getElementById('areaFotoTrasera');
const fotoTraseraPreview = document.getElementById('fotoTraseraPreview');
if (inputFotoTrasera) {
    inputFotoTrasera.addEventListener('change', () => {
        const file = inputFotoTrasera.files[0];
        if (!file) return;
        areaFotoTrasera.classList.add('has-file');
        areaFotoTrasera.querySelector('.upload-icon i').className = 'bi bi-check-circle-fill';
        areaFotoTrasera.querySelector('.upload-label').innerHTML = `<span style="color:var(--success)">${file.name}</span><br><small>Foto trasera seleccionada</small>`;
        const reader = new FileReader();
        reader.onload = (e) => { fotoTraseraPreview.src = e.target.result; fotoTraseraPreview.classList.add('visible'); };
        reader.readAsDataURL(file);
    });
}

// ── FILE UPLOAD — CERT INVENTARIO ─────────────────────────────────────────────
const inputCertInventario = document.getElementById('cert_inventario');
const areaCertInventario = document.getElementById('areaCertInventario');
if (inputCertInventario) {
    inputCertInventario.addEventListener('change', () => {
        const file = inputCertInventario.files[0];
        if (!file) return;
        areaCertInventario.classList.add('has-file');
        areaCertInventario.querySelector('.upload-icon i').className = 'bi bi-check-circle-fill';
        areaCertInventario.querySelector('.upload-label').innerHTML = `<span style="color:var(--success)">${file.name}</span><br><small>PDF seleccionado</small>`;
        const nombreEl = document.getElementById('certInventarioNombre');
        if (nombreEl) { nombreEl.style.display = 'block'; nombreEl.querySelector('span').textContent = file.name; }
    });
}

// ── FILE UPLOAD — CERT SICOIN ─────────────────────────────────────────────────
const inputCertSicoin = document.getElementById('cert_sicoin');
const areaCertSicoin = document.getElementById('areaCertSicoin');
if (inputCertSicoin) {
    inputCertSicoin.addEventListener('change', () => {
        const file = inputCertSicoin.files[0];
        if (!file) return;
        areaCertSicoin.classList.add('has-file');
        areaCertSicoin.querySelector('.upload-icon i').className = 'bi bi-check-circle-fill';
        areaCertSicoin.querySelector('.upload-label').innerHTML = `<span style="color:var(--success)">${file.name}</span><br><small>PDF seleccionado</small>`;
        const nombreEl = document.getElementById('certSicoinNombre');
        if (nombreEl) { nombreEl.style.display = 'block'; nombreEl.querySelector('span').textContent = file.name; }
    });
}

// ── FILE UPLOAD — TARJETA PDF ─────────────────────────────────────────────────
inputPdf.addEventListener('change', async () => {
    const file = inputPdf.files[0];
    if (!file) return;
    const pdfPreview = document.getElementById('pdfPreviewIframe');
    const hayPdfActual = modoEdicion && pdfPreview && pdfPreview.style.display !== 'none';
    if (hayPdfActual) {
        const confirm = await Swal.fire({
            icon: 'question', title: '¿Reemplazar PDF?',
            html: `Se reemplazará la tarjeta actual por <strong>${file.name}</strong>.`,
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-arrow-repeat"></i> Sí, reemplazar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#3a7bd5', cancelButtonColor: '#e05252',
            background: '#1a1d27', color: '#e8eaf0'
        });
        if (!confirm.isConfirmed) { inputPdf.value = ''; return; }
        pdfPreview.style.display = 'none';
        pdfPreview.src = '';
    }
    areaPdf.classList.add('has-file');
    areaPdf.querySelector('.upload-icon i').className = 'bi bi-check-circle-fill';
    areaPdf.querySelector('.upload-label').innerHTML = `<span style="color:var(--success)">${file.name}</span><br><small>Nuevo PDF seleccionado</small>`;
    pdfNombre.style.display = 'block';
    pdfNombre.querySelector('span').textContent = file.name;
});

// ── FILE UPLOAD — PÓLIZA ──────────────────────────────────────────────────────
const inputPoliza = document.getElementById('archivo_poliza');
const areaPoliza = document.getElementById('areaPoliza');
if (inputPoliza && areaPoliza) {
    inputPoliza.addEventListener('change', () => {
        const file = inputPoliza.files[0];
        if (!file) return;
        areaPoliza.classList.add('has-file');
        areaPoliza.querySelector('.upload-icon i').className = 'bi bi-check-circle-fill';
        areaPoliza.querySelector('.upload-label').innerHTML = `<span style="color:var(--success)">${file.name}</span><br><small>PDF seleccionado</small>`;
    });
}

// ── RESET ARCHIVOS ────────────────────────────────────────────────────────────
const resetArchivos = () => {
    areaFoto.classList.remove('has-file');
    fotoPreview.classList.remove('visible');
    fotoPreview.src = '';
    areaFoto.querySelector('.upload-icon i').className = 'bi bi-image';
    areaFoto.querySelector('.upload-label').innerHTML = `<span>Haz clic</span> o arrastra la foto aquí<br><small>JPG, PNG, WEBP — máx. 5 MB</small>`;
    areaPdf.classList.remove('has-file');
    pdfNombre.style.display = 'none';
    areaPdf.querySelector('.upload-icon i').className = 'bi bi-file-pdf';
    areaPdf.querySelector('.upload-label').innerHTML = `<span>Haz clic</span> o arrastra el PDF aquí<br><small>Solo PDF — máx. 10 MB</small>`;
    fotoActualContainer.style.display = 'none';
    const pdfPreview = document.getElementById('pdfPreviewIframe');
    if (pdfPreview) { pdfPreview.style.display = 'none'; pdfPreview.src = ''; }
    const areaPolizaR = document.getElementById('areaPoliza');
    const inputPolizaR = document.getElementById('archivo_poliza');
    if (areaPolizaR) { areaPolizaR.classList.remove('has-file'); areaPolizaR.querySelector('.upload-icon i').className = 'bi bi-file-pdf'; areaPolizaR.querySelector('.upload-label').innerHTML = `<span>Haz clic</span> para subir la póliza<br><small>Solo PDF — máx. 10 MB</small>`; }
    if (inputPolizaR) inputPolizaR.value = '';
    const areaFotoLateralR = document.getElementById('areaFotoLateral');
    const fotoLateralPreviewR = document.getElementById('fotoLateralPreview');
    const inputFotoLateralR = document.getElementById('foto_lateral');
    if (areaFotoLateralR) { areaFotoLateralR.classList.remove('has-file'); areaFotoLateralR.querySelector('.upload-icon i').className = 'bi bi-image'; areaFotoLateralR.querySelector('.upload-label').innerHTML = `<span>Haz clic</span> o arrastra<br><small>JPG, PNG, WEBP — máx. 5 MB</small>`; }
    if (fotoLateralPreviewR) { fotoLateralPreviewR.src = ''; fotoLateralPreviewR.classList.remove('visible'); }
    if (inputFotoLateralR) inputFotoLateralR.value = '';
    const areaFotoTraseraR = document.getElementById('areaFotoTrasera');
    const fotoTraseraPreviewR = document.getElementById('fotoTraseraPreview');
    const inputFotoTraseraR = document.getElementById('foto_trasera');
    if (areaFotoTraseraR) { areaFotoTraseraR.classList.remove('has-file'); areaFotoTraseraR.querySelector('.upload-icon i').className = 'bi bi-image'; areaFotoTraseraR.querySelector('.upload-label').innerHTML = `<span>Haz clic</span> o arrastra<br><small>JPG, PNG, WEBP — máx. 5 MB</small>`; }
    if (fotoTraseraPreviewR) { fotoTraseraPreviewR.src = ''; fotoTraseraPreviewR.classList.remove('visible'); }
    if (inputFotoTraseraR) inputFotoTraseraR.value = '';
    const areaCertInventarioR = document.getElementById('areaCertInventario');
    const inputCertInventarioR = document.getElementById('cert_inventario');
    const certInventarioNombreR = document.getElementById('certInventarioNombre');
    if (areaCertInventarioR) { areaCertInventarioR.classList.remove('has-file'); areaCertInventarioR.querySelector('.upload-icon i').className = 'bi bi-file-pdf'; areaCertInventarioR.querySelector('.upload-label').innerHTML = `<span>Haz clic</span> o arrastra<br><small>Solo PDF — máx. 10 MB</small>`; }
    if (certInventarioNombreR) certInventarioNombreR.style.display = 'none';
    if (inputCertInventarioR) inputCertInventarioR.value = '';
    const areaCertSicoinR = document.getElementById('areaCertSicoin');
    const inputCertSicoinR = document.getElementById('cert_sicoin');
    const certSicoinNombreR = document.getElementById('certSicoinNombre');
    if (areaCertSicoinR) { areaCertSicoinR.classList.remove('has-file'); areaCertSicoinR.querySelector('.upload-icon i').className = 'bi bi-file-pdf'; areaCertSicoinR.querySelector('.upload-label').innerHTML = `<span>Haz clic</span> o arrastra<br><small>Solo PDF — máx. 10 MB</small>`; }
    if (certSicoinNombreR) certSicoinNombreR.style.display = 'none';
    if (inputCertSicoinR) inputCertSicoinR.value = '';
};

const resetAsignacion = () => {
    selectUnidad.value = '';
    infoDestacamento.style.display = 'none';
};

// ── MOSTRAR / OCULTAR FORMULARIO ──────────────────────────────────────────────
const mostrarFormulario = () => {
    modoEdicion = false;
    contenedorFormulario.style.display = '';
    contenedorFormulario.classList.add('slide-down');
    contenedorTabla.style.display = 'none';
    tituloFormulario.textContent = 'Nuevo Vehículo';
    formulario.reset();
    resetArchivos();
    resetAsignacion();
    inputPlaca.readOnly = false;
    inputPlaca.style.opacity = '1';
    inputPlacaOriginal.value = '';
    btnGuardar.parentElement.style.display = '';
    btnModificar.parentElement.style.display = 'none';
    btnFlotante.classList.add('activo');
    btnFlotante.innerHTML = '<i class="bi bi-skip-backward"></i>';
    btnFlotante.setAttribute('title', 'Volver');
    document.getElementById('btnSeguroSi').classList.remove('sel-si');
    document.getElementById('btnSeguroNo').classList.remove('sel-no');
    document.getElementById('panelFormSeguro').style.display = 'none';
    document.getElementById('avisoSinSeguro').style.display = 'none';
};

const ocultarFormulario = () => {
    contenedorFormulario.classList.remove('slide-down');
    contenedorFormulario.classList.add('slide-up');
    setTimeout(() => {
        contenedorFormulario.style.display = 'none';
        contenedorFormulario.classList.remove('slide-up');
        contenedorTabla.style.display = '';
    }, 300);
    btnFlotante.classList.remove('activo');
    btnFlotante.innerHTML = '<i class="bi bi-plus"></i>';
    btnFlotante.setAttribute('title', 'Nuevo Vehículo');
};

btnFlotante.addEventListener('click', () => {
    contenedorFormulario.style.display === 'none' ? mostrarFormulario() : ocultarFormulario();
});

// ── RENDER CARTAS ─────────────────────────────────────────────────────────────
const estadoBadge = (estado) => {
    const map = {
        Alta: { bg: '#1a3d2b', color: '#4caf7d', border: '#2d6b45' },
        Baja: { bg: '#3d1a1a', color: '#e05252', border: '#6b2d2d' },
        Taller: { bg: '#3d300a', color: '#e8b84b', border: '#6b520f' },
    };
    const s = map[estado] || { bg: '#242837', color: '#7c8398', border: '#2e3347' };
    return `<span class="card-estado" style="background:${s.bg};color:${s.color};border:1.5px solid ${s.border};font-weight:700;letter-spacing:.5px;font-size:.7rem;padding:.25rem .65rem;border-radius:20px;position:absolute;top:10px;right:10px;font-family:'Inter',sans-serif;">${estado}</span>`;
};

const renderCartas = (vehiculos) => {
    if (!vehiculos.length) {
        cardsGrid.innerHTML = `<div class="empty-state"><i class="bi bi-search"></i><p>No se encontraron vehículos con los filtros aplicados</p></div>`;
        contadorVisible.textContent = '0';
        return;
    }
    contadorVisible.textContent = vehiculos.length;
    cardsGrid.innerHTML = vehiculos.map((v, i) => {
        const fotoHTML = v.foto_url
            ? `<img src="${v.foto_url}" alt="${v.placa}" loading="lazy" onerror="this.parentElement.innerHTML='<div class=\\'no-foto\\'><i class=\\'bi bi-image-slash\\'></i><span>Sin foto</span></div>'">`
            : `<div class="no-foto"><i class="bi bi-truck-front"></i><span>Sin foto</span></div>`;
        let seguroBadge = '';
        if (v.seguro_estado === 'vigente') {
            seguroBadge = `<span style="position:absolute;bottom:.5rem;left:.5rem;background:rgba(76,175,125,.85);color:#fff;font-size:.65rem;font-weight:600;padding:.2rem .55rem;border-radius:20px;backdrop-filter:blur(4px);display:flex;align-items:center;gap:.3rem;"><i class="bi bi-shield-check"></i> Vigente</span>`;
        } else if (v.seguro_estado === 'vencido') {
            seguroBadge = `<span style="position:absolute;bottom:.5rem;left:.5rem;background:rgba(224,82,82,.85);color:#fff;font-size:.65rem;font-weight:600;padding:.2rem .55rem;border-radius:20px;backdrop-filter:blur(4px);display:flex;align-items:center;gap:.3rem;"><i class="bi bi-shield-exclamation"></i> Vencido</span>`;
        } else {
            seguroBadge = `<span style="position:absolute;bottom:.5rem;left:.5rem;background:rgba(30,33,48,.85);color:#888;font-size:.65rem;font-weight:600;padding:.2rem .55rem;border-radius:20px;border:1px solid rgba(150,150,150,.25);backdrop-filter:blur(4px);display:flex;align-items:center;gap:.3rem;"><i class="bi bi-shield-slash"></i> Sin seguro</span>`;
        }
        const unidadHTML = v.unidad_nombre
            ? `<div class="card-unidad"><i class="bi bi-people-fill"></i>${v.unidad_nombre}</div><div class="card-unidad"><i class="bi bi-geo-alt-fill"></i>${v.destacamento_depto || ''}</div>` : '';
        return `
        <div class="vehicle-card" style="animation-delay:${i * 0.05}s">
            <div class="card-foto">
                ${fotoHTML}
                ${estadoBadge(v.estado)}
                ${seguroBadge}
            </div>
            <div class="card-info">
                <div class="card-placa">${v.placa}</div>
                <div class="card-vehiculo">${v.marca} ${v.modelo}</div>
                <div class="card-tipo"><i class="bi bi-truck" style="color:var(--accent)"></i> ${v.tipo} · ${v.anio}</div>
                ${unidadHTML}
            </div>
            <div class="card-acciones">
                ${!esBHR ? `
                <button class="btn-card-action btn-card-edit ${esCIA ? 'modificar' : ''}"
                    ${esCIA ? `
                        data-placa="${v.placa}" data-numero_serie="${v.numero_serie}"
                        data-marca="${v.marca}" data-modelo="${v.modelo}" data-anio="${v.anio}"
                        data-color="${v.color}" data-tipo="${v.tipo}" data-km_actuales="${v.km_actuales}"
                        data-estado="${v.estado}" data-fecha_ingreso="${v.fecha_ingreso}"
                        data-observaciones="${v.observaciones || ''}"
                        data-foto_url="${v.foto_url || ''}" data-foto_lateral_url="${v.foto_lateral_url || ''}"
                        data-foto_trasera_url="${v.foto_trasera_url || ''}" data-pdf_url="${v.pdf_url || ''}"
                        data-cert_inventario_url="${v.cert_inventario_url || ''}"
                        data-cert_sicoin_url="${v.cert_sicoin_url || ''}" data-id_unidad="${v.id_unidad || ''}"
                    ` : `onclick="solicitarModificacion('${v.placa}')"`}>
                    <i class="bi bi-pencil-square"></i> ${esCIA ? 'Editar' : 'Solicitar cambio'}
                </button>
                <button class="btn-card-action btn-card-del ${esCIA ? 'eliminar' : ''}"
                    ${esCIA ? `data-placa="${v.placa}"` : `onclick="crearSolicitudEliminacion('${v.placa}')"`}>
                    <i class="bi bi-trash3"></i>
                </button>` : ''}
                <button class="btn-card-action"
                    style="background:rgba(232,184,75,.15);color:var(--accent);border:1px solid rgba(232,184,75,.2);"
                    onclick="abrirFicha('${v.placa}')">
                    <i class="bi bi-card-checklist"></i> Ficha
                </button>
            </div>
        </div>`;
    }).join('');
    if (esCIA) {
        cardsGrid.querySelectorAll('.modificar').forEach(btn => btn.addEventListener('click', traerDatos));
        cardsGrid.querySelectorAll('.eliminar').forEach(btn => btn.addEventListener('click', eliminar));
    }
};

// ── BUSCAR ────────────────────────────────────────────────────────────────────
const buscar = async () => {
    mostrarLoader('Cargando flota vehicular...');
    try {
        const respuesta = await fetch(`${BASE}/API/vehiculos/buscar`, { method: 'GET' });
        const data = await respuesta.json();
        todosLosVehiculos = data.datos || [];
        renderTipos(todosLosVehiculos);
        if (tipoSeleccionado) aplicarFiltros();
    } catch (error) {
        console.error('Error al buscar vehículos:', error);
        cardsGrid.innerHTML = `<div class="empty-state"><i class="bi bi-wifi-off"></i><p>Error al cargar los vehículos</p></div>`;
    } finally {
        ocultarLoader();
    }
};

// ── GUARDAR ───────────────────────────────────────────────────────────────────
const guardar = async (e) => {
    e.preventDefault();
    const placa = document.getElementById('placa').value.trim().toUpperCase();
    const serie = document.getElementById('numero_serie').value.trim().toUpperCase();
    const marca = document.getElementById('marca').value.trim();
    const modelo = document.getElementById('modelo').value.trim();
    const anio = document.getElementById('anio').value.trim();
    const color = document.getElementById('color').value.trim();
    const tipo = document.getElementById('tipo').value;
    const estado = document.getElementById('estado').value;
    const fechaIngreso = document.getElementById('fecha_ingreso').value;
    const km = document.getElementById('km_actuales').value;

    if (!placa) { Swal.fire({ icon: 'warning', title: 'Catálogo requerido', text: 'El campo catálogo no puede estar vacío.', background: '#1a1d27', color: '#e8eaf0' }); document.getElementById('placa').focus(); return; }
    if (!/^[0-9]+$/.test(placa)) { Swal.fire({ icon: 'warning', title: 'Catálogo inválido', text: 'El catálogo solo puede contener números.', background: '#1a1d27', color: '#e8eaf0' }); document.getElementById('placa').focus(); return; }
    if (!serie) { Swal.fire({ icon: 'warning', title: 'Número de serie requerido', text: 'El número de serie / VIN no puede estar vacío.', background: '#1a1d27', color: '#e8eaf0' }); document.getElementById('numero_serie').focus(); return; }
    if (!marca) { Swal.fire({ icon: 'warning', title: 'Marca requerida', text: 'Ingresa la marca del vehículo.', background: '#1a1d27', color: '#e8eaf0' }); document.getElementById('marca').focus(); return; }
    if (!modelo) { Swal.fire({ icon: 'warning', title: 'Modelo requerido', text: 'Ingresa el modelo del vehículo.', background: '#1a1d27', color: '#e8eaf0' }); document.getElementById('modelo').focus(); return; }
    if (!anio) { Swal.fire({ icon: 'warning', title: 'Año requerido', text: 'Ingresa el año del vehículo.', background: '#1a1d27', color: '#e8eaf0' }); document.getElementById('anio').focus(); return; }
    const anioNum = parseInt(anio);
    const anioActual = new Date().getFullYear();
    if (isNaN(anioNum) || anioNum < 1900 || anioNum > anioActual + 1) { Swal.fire({ icon: 'warning', title: 'Año inválido', text: `El año debe estar entre 1900 y ${anioActual + 1}.`, background: '#1a1d27', color: '#e8eaf0' }); document.getElementById('anio').focus(); return; }
    if (!color) { Swal.fire({ icon: 'warning', title: 'Color requerido', text: 'Ingresa el color del vehículo.', background: '#1a1d27', color: '#e8eaf0' }); document.getElementById('color').focus(); return; }
    if (!tipo) { Swal.fire({ icon: 'warning', title: 'Tipo requerido', text: 'Selecciona el tipo de vehículo.', background: '#1a1d27', color: '#e8eaf0' }); document.getElementById('tipo').focus(); return; }
    if (!fechaIngreso) { Swal.fire({ icon: 'warning', title: 'Fecha requerida', text: 'Selecciona la fecha de ingreso.', background: '#1a1d27', color: '#e8eaf0' }); document.getElementById('fecha_ingreso').focus(); return; }
    if (km === '' || isNaN(parseInt(km)) || parseInt(km) < 0) { Swal.fire({ icon: 'warning', title: 'Kilometraje inválido', text: 'El kilometraje no puede ser negativo.', background: '#1a1d27', color: '#e8eaf0' }); document.getElementById('km_actuales').focus(); return; }

    const tieneSeguro = document.getElementById('btnSeguroSi').classList.contains('sel-si');
    const noTieneSeguro = document.getElementById('btnSeguroNo').classList.contains('sel-no');
    if (!tieneSeguro && !noTieneSeguro) { Swal.fire({ icon: 'warning', title: 'Seguro requerido', text: 'Indica si el vehículo tiene o no seguro.', background: '#1a1d27', color: '#e8eaf0' }); return; }

    if (tieneSeguro) {
        const aseguradora = document.getElementById('seg_aseguradora').value.trim();
        const poliza = document.getElementById('seg_numero_poliza').value.trim();
        const inicio = document.getElementById('seg_fecha_inicio').value;
        const venc = document.getElementById('seg_fecha_vencimiento').value;
        const prima = document.getElementById('seg_prima_anual').value;
        const agente = document.getElementById('seg_agente_contacto').value.trim();
        const telefono = document.getElementById('seg_telefono_agente').value.trim();
        if (!aseguradora) { Swal.fire({ icon: 'warning', title: 'Aseguradora requerida', text: 'Ingresa el nombre de la aseguradora.', background: '#1a1d27', color: '#e8eaf0' }); document.getElementById('seg_aseguradora').focus(); return; }
        if (!poliza) { Swal.fire({ icon: 'warning', title: 'Póliza requerida', text: 'Ingresa el número de póliza.', background: '#1a1d27', color: '#e8eaf0' }); document.getElementById('seg_numero_poliza').focus(); return; }
        if (!inicio) { Swal.fire({ icon: 'warning', title: 'Fecha inicio requerida', text: 'Selecciona la fecha de inicio del seguro.', background: '#1a1d27', color: '#e8eaf0' }); document.getElementById('seg_fecha_inicio').focus(); return; }
        if (!venc) { Swal.fire({ icon: 'warning', title: 'Fecha vencimiento requerida', text: 'Selecciona la fecha de vencimiento del seguro.', background: '#1a1d27', color: '#e8eaf0' }); document.getElementById('seg_fecha_vencimiento').focus(); return; }
        if (venc <= inicio) { Swal.fire({ icon: 'warning', title: 'Fechas inválidas', text: 'La fecha de vencimiento debe ser posterior a la de inicio.', background: '#1a1d27', color: '#e8eaf0' }); document.getElementById('seg_fecha_vencimiento').focus(); return; }
        if (!prima || isNaN(parseFloat(prima)) || parseFloat(prima) < 0) { Swal.fire({ icon: 'warning', title: 'Prima requerida', text: 'Ingresa la prima anual (puede ser 0).', background: '#1a1d27', color: '#e8eaf0' }); document.getElementById('seg_prima_anual').focus(); return; }
        if (!agente) { Swal.fire({ icon: 'warning', title: 'Agente requerido', text: 'Ingresa el nombre del agente de contacto.', background: '#1a1d27', color: '#e8eaf0' }); document.getElementById('seg_agente_contacto').focus(); return; }
        if (!telefono) { Swal.fire({ icon: 'warning', title: 'Teléfono requerido', text: 'Ingresa el teléfono del agente.', background: '#1a1d27', color: '#e8eaf0' }); document.getElementById('seg_telefono_agente').focus(); return; }
    }

    const body = new FormData();
    body.append('placa', placa); body.append('numero_serie', serie); body.append('marca', marca);
    body.append('modelo', modelo); body.append('anio', anio); body.append('color', color);
    body.append('tipo', tipo); body.append('estado', estado); body.append('fecha_ingreso', fechaIngreso);
    body.append('km_actuales', km); body.append('observaciones', document.getElementById('observaciones').value);
    body.append('id_unidad', document.getElementById('id_unidad').value);

    const foto = document.getElementById('foto_frente');
    if (foto?.files.length > 0) body.append('foto_frente', foto.files[0]);
    const fotoLat = document.getElementById('foto_lateral');
    if (fotoLat?.files.length > 0) body.append('foto_lateral', fotoLat.files[0]);
    const fotoTras = document.getElementById('foto_trasera');
    if (fotoTras?.files.length > 0) body.append('foto_trasera', fotoTras.files[0]);
    const tarjeta = document.getElementById('tarjeta_pdf');
    if (tarjeta?.files.length > 0) body.append('tarjeta_pdf', tarjeta.files[0]);
    const certInv = document.getElementById('cert_inventario');
    if (certInv?.files.length > 0) body.append('cert_inventario', certInv.files[0]);
    const certSic = document.getElementById('cert_sicoin');
    if (certSic?.files.length > 0) body.append('cert_sicoin', certSic.files[0]);

    if (tieneSeguro) {
        body.append('seg_aseguradora', document.getElementById('seg_aseguradora').value.trim());
        body.append('seg_numero_poliza', document.getElementById('seg_numero_poliza').value.trim());
        body.append('seg_tipo_cobertura', document.getElementById('seg_tipo_cobertura').value);
        body.append('seg_fecha_inicio', document.getElementById('seg_fecha_inicio').value);
        body.append('seg_fecha_vencimiento', document.getElementById('seg_fecha_vencimiento').value);
        body.append('seg_prima_anual', document.getElementById('seg_prima_anual').value);
        body.append('seg_agente_contacto', document.getElementById('seg_agente_contacto').value.trim());
        body.append('seg_telefono_agente', document.getElementById('seg_telefono_agente').value.trim());
        body.append('seg_observaciones', document.getElementById('seg_observaciones').value);
        const pdfPoliza = document.getElementById('archivo_poliza');
        if (pdfPoliza?.files.length > 0) {
            if (pdfPoliza.files[0].type !== 'application/pdf') { Swal.fire({ icon: 'warning', title: 'Archivo inválido', text: 'La póliza debe ser PDF.', background: '#1a1d27', color: '#e8eaf0' }); return; }
            body.append('archivo_poliza', pdfPoliza.files[0]);
        }
    }

    try {
        mostrarLoader('Registrando vehículo...');
        const r = await fetch(`${BASE}/API/vehiculos/guardar`, { method: 'POST', body });
        const d = await r.json();
        if (d.codigo === 1) {
            Toast.fire({ icon: 'success', title: d.mensaje });
            cancelar(); buscar();
        } else {
            Swal.fire({ icon: 'error', title: 'No se pudo registrar', text: d.mensaje || 'Error desconocido', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e05252' });
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e05252' });
    } finally {
        ocultarLoader();
    }
};

// ── HELPER ARCHIVO EXISTENTE ──────────────────────────────────────────────────
const _mostrarArchivoExistente = (url, areaId, labelTexto, tipo = 'foto') => {
    if (!url || url === 'null' || url === '') return;
    const area = document.getElementById(areaId);
    if (!area) return;
    area.classList.add('has-file');
    area.querySelector('.upload-icon i').className = 'bi bi-check-circle-fill';
    area.querySelector('.upload-label').innerHTML = `<span style="color:var(--success)">${labelTexto}</span><br><small>Sube uno nuevo para reemplazarlo</small>`;
};

// ── TRAER DATOS (edición) ─────────────────────────────────────────────────────
const traerDatos = (e) => {
    modoEdicion = true;
    const d = e.currentTarget.dataset;
    inputPlaca.value = d.placa; inputPlaca.readOnly = true; inputPlaca.style.opacity = '.6';
    inputPlacaOriginal.value = d.placa;
    formulario.numero_serie.value = d.numero_serie;
    formulario.marca.value = d.marca;
    formulario.modelo.value = d.modelo;
    formulario.anio.value = d.anio;
    formulario.color.value = d.color;
    formulario.tipo.value = d.tipo;
    formulario.km_actuales.value = d.km_actuales;
    formulario.estado.value = d.estado;
    formulario.fecha_ingreso.value = d.fecha_ingreso;
    formulario.observaciones.value = d.observaciones;
    resetArchivos();
    if (d.foto_url && d.foto_url !== 'null' && d.foto_url !== '') {
        areaFoto.classList.add('has-file');
        areaFoto.querySelector('.upload-icon i').className = 'bi bi-check-circle-fill';
        areaFoto.querySelector('.upload-label').innerHTML = `<span style="color:var(--success)">Foto cargada</span><br><small>Sube una nueva para reemplazarla</small>`;
        fotoPreview.src = d.foto_url; fotoPreview.classList.add('visible');
    }
    if (d.foto_lateral_url && d.foto_lateral_url !== 'null' && d.foto_lateral_url !== '') {
        _mostrarArchivoExistente(d.foto_lateral_url, 'areaFotoLateral', 'Foto lateral cargada', 'foto');
        const prev = document.getElementById('fotoLateralPreview');
        if (prev) { prev.src = d.foto_lateral_url; prev.classList.add('visible'); }
    }
    if (d.foto_trasera_url && d.foto_trasera_url !== 'null' && d.foto_trasera_url !== '') {
        _mostrarArchivoExistente(d.foto_trasera_url, 'areaFotoTrasera', 'Foto trasera cargada', 'foto');
        const prev = document.getElementById('fotoTraseraPreview');
        if (prev) { prev.src = d.foto_trasera_url; prev.classList.add('visible'); }
    }
    if (d.pdf_url && d.pdf_url !== 'null' && d.pdf_url !== '') {
        areaPdf.classList.add('has-file');
        areaPdf.querySelector('.upload-icon i').className = 'bi bi-check-circle-fill';
        areaPdf.querySelector('.upload-label').innerHTML = `<span style="color:var(--success)">PDF cargado</span><br><small>Sube uno nuevo para reemplazarlo</small>`;
        let pdfPreviewEl = document.getElementById('pdfPreviewIframe');
        if (!pdfPreviewEl) {
            pdfPreviewEl = document.createElement('iframe');
            pdfPreviewEl.id = 'pdfPreviewIframe';
            pdfPreviewEl.style.cssText = `width:100%;height:180px;border:2px solid var(--border);border-radius:8px;margin-top:.75rem;background:var(--dark-3);`;
            areaPdf.parentElement.appendChild(pdfPreviewEl);
        }
        pdfPreviewEl.src = d.pdf_url; pdfPreviewEl.style.display = 'block';
    }
    if (d.cert_inventario_url && d.cert_inventario_url !== 'null' && d.cert_inventario_url !== '') {
        _mostrarArchivoExistente(d.cert_inventario_url, 'areaCertInventario', 'Cert. Inventario cargado', 'pdf');
        const nombreEl = document.getElementById('certInventarioNombre');
        if (nombreEl) { nombreEl.style.display = 'block'; nombreEl.querySelector('span').textContent = 'Archivo cargado'; }
    }
    if (d.cert_sicoin_url && d.cert_sicoin_url !== 'null' && d.cert_sicoin_url !== '') {
        _mostrarArchivoExistente(d.cert_sicoin_url, 'areaCertSicoin', 'Cert. SICOIN cargado', 'pdf');
        const nombreEl = document.getElementById('certSicoinNombre');
        if (nombreEl) { nombreEl.style.display = 'block'; nombreEl.querySelector('span').textContent = 'Archivo cargado'; }
    }
    selectUnidad.value = d.id_unidad || '';
    selectUnidad.dispatchEvent(new Event('change'));
    tituloFormulario.textContent = 'Modificar Vehículo';
    contenedorFormulario.style.display = '';
    contenedorFormulario.classList.add('slide-down');
    contenedorTabla.style.display = 'none';
    btnGuardar.parentElement.style.display = 'none';
    btnModificar.parentElement.style.display = '';
    btnFlotante.classList.add('activo');
    btnFlotante.innerHTML = '<i class="bi bi-x"></i>';
    btnFlotante.setAttribute('title', 'Cerrar');
};

// ── CANCELAR ──────────────────────────────────────────────────────────────────
const cancelar = () => {
    formulario.reset(); resetArchivos(); resetAsignacion();
    inputPlaca.readOnly = false; inputPlaca.style.opacity = '1';
    ocultarFormulario();
    btnGuardar.parentElement.style.display = '';
    btnModificar.parentElement.style.display = 'none';
    document.getElementById('btnSeguroSi').classList.remove('sel-si');
    document.getElementById('btnSeguroNo').classList.remove('sel-no');
    document.getElementById('panelFormSeguro').style.display = 'none';
    document.getElementById('avisoSinSeguro').style.display = 'none';
    vehiculoTieneSeguro = false;
};

// ── MODIFICAR ─────────────────────────────────────────────────────────────────
const modificar = async () => {
    const camposRequeridos = ['placa', 'numero_serie', 'marca', 'modelo', 'anio', 'color', 'tipo', 'estado', 'fecha_ingreso'];
    let campoVacio = false;
    for (const campo of camposRequeridos) {
        const el = document.getElementById(campo);
        if (!el || !el.value.trim()) { campoVacio = true; if (el) el.style.borderColor = 'var(--danger)'; }
        else { if (el) el.style.borderColor = ''; }
    }
    if (campoVacio) {
        Swal.fire({ title: 'Campos vacíos', text: 'Debe llenar todos los campos obligatorios marcados en rojo', icon: 'info', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b' });
        return;
    }
    try {
        mostrarLoader('Guardando cambios...');
        const body = new FormData(formulario);
        body.set('placa', inputPlacaOriginal.value);
        const fotoLateral = document.getElementById('foto_lateral');
        if (fotoLateral && fotoLateral.files.length > 0) body.set('foto_lateral', fotoLateral.files[0]);
        const fotoTrasera = document.getElementById('foto_trasera');
        if (fotoTrasera && fotoTrasera.files.length > 0) body.set('foto_trasera', fotoTrasera.files[0]);
        const certInventario = document.getElementById('cert_inventario');
        if (certInventario && certInventario.files.length > 0) body.set('cert_inventario', certInventario.files[0]);
        const certSicoin = document.getElementById('cert_sicoin');
        if (certSicoin && certSicoin.files.length > 0) body.set('cert_sicoin', certSicoin.files[0]);
        const respuesta = await fetch(`${BASE}/API/vehiculos/modificar`, { method: 'POST', body });
        const data = await respuesta.json();
        if (data.codigo == 1) { formulario.reset(); resetArchivos(); resetAsignacion(); buscar(); cancelar(); }
        Toast.fire({ icon: data.codigo == 1 ? 'success' : 'error', title: data.mensaje });
    } catch (error) {
        console.error(error);
        Toast.fire({ icon: 'error', title: 'Error de conexión al modificar' });
    } finally {
        ocultarLoader();
    }
};

// ── ELIMINAR ──────────────────────────────────────────────────────────────────
const eliminar = async (e) => {
    const placa = e.currentTarget.dataset.placa;
    const confirmacion = await Swal.fire({
        icon: 'warning', title: '¿Eliminar vehículo?',
        html: `Se eliminará el vehículo con placa <strong>${placa}</strong> y sus archivos.<br>Esta acción no se puede deshacer.`,
        showCancelButton: true, confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e05252', cancelButtonColor: '#3a7bd5',
        background: '#1a1d27', color: '#e8eaf0'
    });
    if (!confirmacion.isConfirmed) return;
    try {
        const body = new FormData();
        body.append('placa', placa);
        const respuesta = await fetch(`${BASE}/API/vehiculos/eliminar`, { method: 'POST', body });
        const data = await respuesta.json();
        if (data.codigo == 1) buscar();
        Toast.fire({ icon: data.codigo == 1 ? 'success' : 'error', title: data.mensaje });
    } catch (error) {
        console.error(error);
        Toast.fire({ icon: 'error', title: 'Error de conexión al eliminar' });
    }
};

// ── SEGURO EN FORMULARIO NUEVO ────────────────────────────────────────────────
let vehiculoTieneSeguro = false;
const elegirSeguro = (opcion) => {
    const btnSi = document.getElementById('btnSeguroSi');
    const btnNo = document.getElementById('btnSeguroNo');
    const panel = document.getElementById('panelFormSeguro');
    const aviso = document.getElementById('avisoSinSeguro');
    btnSi.classList.remove('sel-si'); btnNo.classList.remove('sel-no');
    if (opcion === 'si') { btnSi.classList.add('sel-si'); panel.style.display = 'block'; aviso.style.display = 'none'; vehiculoTieneSeguro = true; }
    else { btnNo.classList.add('sel-no'); panel.style.display = 'none'; aviso.style.display = 'flex'; vehiculoTieneSeguro = false; }
};
window.elegirSeguro = elegirSeguro;

// ── AUTO-UPPERCASE ────────────────────────────────────────────────────────────
document.getElementById('placa').addEventListener('input', function () { this.value = this.value.toUpperCase(); });
document.getElementById('numero_serie').addEventListener('input', function () { this.value = this.value.toUpperCase(); });

// ── EVENT LISTENERS FORMULARIO ────────────────────────────────────────────────
formulario.addEventListener('submit', guardar);
btnCancelar.addEventListener('click', cancelar);
btnModificar.addEventListener('click', modificar);
// ════════════════════════════════════════════════════════════════════════════
// ── MODAL FICHA ───────────────────────────────────────────────────────────────
// ════════════════════════════════════════════════════════════════════════════

let fichaPlacaActual = '';
let fichaTipoVehiculo = '';
let reparacionEditandoId = null;

// ── CARGAR TIPOS SERVICIO ─────────────────────────────────────────────────────
const cargarTiposServicio = async () => {
    try {
        const tipoParam = fichaTipoVehiculo
            ? `?tipo_vehiculo=${encodeURIComponent(fichaTipoVehiculo.toLowerCase())}`
            : '';
        const r = await fetch(`${BASE}/API/vehiculos/tipos-servicio${tipoParam}`);
        const d = await r.json();
        if (d.codigo === 1) tiposServicio = d.tipos;
    } catch (e) {
        console.error('Error cargando tipos servicio:', e);
    }
};



// ── KM PRÓXIMO DINÁMICO ───────────────────────────────────────────────────────
// Servicios con intervalo fijo (km que se suma al actual)
const KM_INTERVALOS = {
    'Cambio de aceite y filtro': 5000,
    'Cambio de filtro de aire': null,   // mecánico decide
    'Cambio de filtro de combustible': null,
    'Cambio de filtro de transmisión': null,
    'Cambio de líquido de frenos': null,
    'Cambio de líquido de dirección': null,
    'Cambio de líquido refrigerante': null,
    'Cambio de bujías': null,
    'Cambio de batería': null,
    'Cambio de correa de distribución': null,
    'Cambio de pastillas de freno': null,
    'Alineación y balanceo': 0,     // 0 = no aplica KM
    'Revisión de suspensión': 0,
    'Revisión de frenos': 0,
    'Revisión general': 0,
    'Servicio Mayor': 10000,
    'Servicio Menor': 5000,
};

// Tipos que NO usan próximo KM (se desactiva el input)
const SIN_KM_PROXIMO = new Set([
    'Alineación y balanceo',
    'Revisión de suspensión',
    'Revisión de frenos',
    'Revisión general',
    'Cambio de batería',
    'Limpieza de inyectores',
]);

const _resetKmProximo = () => {
    const input = document.getElementById('itemKmProximo');
    if (!input) return;
    input.value = '';
    input.readOnly = true;
    input.placeholder = '—';
    input.style.opacity = '.6';
    input.style.background = 'rgba(232,184,75,.05)';
    input.title = '';
};

const _onTipoServicioChange = () => {
    const sel = document.getElementById('itemTipo');
    const input = document.getElementById('itemKmProximo');
    if (!sel || !input) return;

    const nombreTipo = sel.options[sel.selectedIndex]?.text || '';
    const kmActual = parseInt(document.getElementById('ordenKm')?.value || '0');

    const tipoData = tiposServicio.find(t => t.nombre === nombreTipo);
    const intervalo = tipoData?.intervalo_km
        ? parseInt(tipoData.intervalo_km)
        : (KM_INTERVALOS[nombreTipo] ?? null);

    if (!nombreTipo) {
        input.value = '';
        input.placeholder = '—';
        input.readOnly = true;
        input.style.opacity = '.6';
        input.style.background = 'rgba(232,184,75,.05)';

    } else if (SIN_KM_PROXIMO.has(nombreTipo)) {
        input.value = '';
        input.placeholder = 'No aplica';
        input.readOnly = true;
        input.style.opacity = '.35';
        input.style.background = 'rgba(255,255,255,.03)';

    } else if (intervalo && intervalo > 0 && kmActual > 0) {
        input.value = kmActual + intervalo;
        input.readOnly = true;
        input.style.opacity = '.6';
        input.style.background = 'rgba(232,184,75,.05)';
        input.title = `${kmActual.toLocaleString()} km actuales + ${intervalo.toLocaleString()} km intervalo`;

    } else {
        input.value = '';
        input.placeholder = 'Sin intervalo definido';
        input.readOnly = true;
        input.style.opacity = '.6';
        input.style.background = 'rgba(232,184,75,.05)';
    }
};

// ── CARGAR TIPOS REPARACION ───────────────────────────────────────────────────
const cargarTiposReparacion = async () => {
    const sel = document.getElementById('repTipo');
    if (!sel || sel.options.length > 1) return;
    if (tiposReparacion.length) {
        sel.innerHTML = '<option value="">Seleccione tipo...</option>' +
            tiposReparacion.map(t => `<option value="${t.id_tipo_reparacion}">${t.nombre}</option>`).join('');
        return;
    }
    const r = await fetch(`${BASE}/API/vehiculos/tipos-reparacion`);
    const d = await r.json();
    if (d.codigo === 1) {
        tiposReparacion = d.datos;
        sel.innerHTML = '<option value="">Seleccione tipo...</option>' +
            tiposReparacion.map(t => `<option value="${t.id_tipo_reparacion}">${t.nombre}</option>`).join('');
    }
};

// ── RESET FORM SERVICIO ───────────────────────────────────────────────────────
const resetFormServicio = () => {
    const form = document.getElementById('formNuevaOrden');
    const btn = document.getElementById('btnNuevaOrden');
    if (form) form.style.display = 'none';
    if (btn) btn.innerHTML = '<i class="bi bi-plus-circle"></i> Nueva Orden de Servicio';
    const panel = document.getElementById('panelOrdenAbierta');
    if (panel) panel.style.display = 'none';
};

// ── RESET FORM REPARACION ─────────────────────────────────────────────────────
const resetFormReparacion = () => {
    const form = document.getElementById('formNuevaReparacion');
    const btn = document.getElementById('btnToggleFormReparacion');
    if (form) form.style.display = 'none';
    if (btn) btn.innerHTML = '<i class="bi bi-plus-circle"></i> Registrar Nueva Reparación';
    reparacionEditandoId = null;
    const btnGuardarRep = document.querySelector('#formNuevaReparacion button[onclick="guardarReparacion()"]');
    if (btnGuardarRep) {
        btnGuardarRep.innerHTML = '<i class="bi bi-save me-1"></i> Guardar Reparación';
        btnGuardarRep.style.background = 'linear-gradient(135deg,var(--danger),#c93030)';
    }
};

// ── ABRIR FICHA ───────────────────────────────────────────────────────────────

let _fichaLoading = false;

const abrirFicha = async (placa) => {
    if (_fichaLoading) return;
    _fichaLoading = true;

    fichaPlacaActual = placa;
    fichaTipoVehiculo = '';
    tiposServicio = [];

    const modal = document.getElementById('modalFicha');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    resetFormServicio();
    resetFormReparacion();
    resetFormSeguro();
    resetFormAccidente();
    cancelarChequeo();

    switchTab(document.querySelector('.ficha-tab[data-tab="info"]'), 'info');

    const fichaPlacaEl = document.getElementById('fichaPlaca');
    const fichaVehiculoEl = document.getElementById('fichaVehiculo');
    if (fichaPlacaEl) fichaPlacaEl.textContent = placa;
    if (fichaVehiculoEl) fichaVehiculoEl.textContent = 'Cargando...';

    mostrarLoader('Cargando ficha del vehículo...');

    await cargarTiposReparacion();

    const ordenFechaEl = document.getElementById('ordenFecha');
    const hoy = new Date().toISOString().split('T')[0];
    if (ordenFechaEl) ordenFechaEl.value = hoy;

    const repFechaEl = document.getElementById('repFechaInicio');
    if (repFechaEl) { repFechaEl.value = hoy; repFechaEl.max = hoy; }

    try {
        const r = await fetch(`${BASE}/API/vehiculos/ficha?placa=${placa}`);
        const d = await r.json();
        if (d.codigo !== 1) return;

        const v = d.vehiculo;
        fichaTipoVehiculo = v.tipo || '';

        // ── Cargar tipos servicio ahora que ya tenemos el tipo de vehículo
        // ── Cargar tipos servicio ahora que ya tenemos el tipo de vehículo
        await cargarTiposServicio();

        // ── Llenar select de tipos de servicio ───────────────────────────────
        const itemTipoSel = document.getElementById('itemTipo');
        if (itemTipoSel && tiposServicio.length) {
            itemTipoSel.innerHTML = '<option value="">Seleccione tipo...</option>' +
                tiposServicio.map(t =>
                    `<option value="${t.id_tipo_servicio}">${t.nombre}</option>`
                ).join('');
            itemTipoSel.onchange = _onTipoServicioChange;
        }

        if (fichaPlacaEl) fichaPlacaEl.textContent = v.placa;
        if (fichaVehiculoEl) fichaVehiculoEl.textContent = `${v.marca} ${v.modelo} · ${v.anio}`;

        // ── Fotos ─────────────────────────────────────────────────────
        const img = document.getElementById('fichaFoto');
        const noFoto = document.getElementById('fichaNoFoto');
        const imgLateral = document.getElementById('fichaFotoLateral');
        const noImgLateral = document.getElementById('fichaNoFotoLateral');
        const imgTrasera = document.getElementById('fichaFotoTrasera');
        const noImgTrasera = document.getElementById('fichaNoFotoTrasera');

        if (img && noFoto) {
            if (v.foto_url) { img.src = v.foto_url; img.style.display = 'block'; noFoto.style.display = 'none'; }
            else { img.style.display = 'none'; noFoto.style.display = 'flex'; }
        }
        if (imgLateral && noImgLateral) {
            if (v.foto_lateral_url) { imgLateral.src = v.foto_lateral_url; imgLateral.style.display = 'block'; noImgLateral.style.display = 'none'; }
            else { imgLateral.style.display = 'none'; noImgLateral.style.display = 'flex'; }
        }
        if (imgTrasera && noImgTrasera) {
            if (v.foto_trasera_url) { imgTrasera.src = v.foto_trasera_url; imgTrasera.style.display = 'block'; noImgTrasera.style.display = 'none'; }
            else { imgTrasera.style.display = 'none'; noImgTrasera.style.display = 'flex'; }
        }

        // ── Lightbox ──────────────────────────────────────────────────
        const fotosVehiculo = [];
        if (v.foto_url) fotosVehiculo.push({ url: v.foto_url, caption: 'Vista Frontal — ' + v.placa });
        if (v.foto_lateral_url) fotosVehiculo.push({ url: v.foto_lateral_url, caption: 'Vista Lateral — ' + v.placa });
        if (v.foto_trasera_url) fotosVehiculo.push({ url: v.foto_trasera_url, caption: 'Vista Trasera — ' + v.placa });

        if (img && v.foto_url) {
            img.style.cursor = 'zoom-in';
            img.onclick = () => abrirLightbox(fotosVehiculo, 0);
        }
        if (imgLateral && v.foto_lateral_url) {
            imgLateral.style.cursor = 'zoom-in';
            imgLateral.onclick = () => abrirLightbox(fotosVehiculo, fotosVehiculo.findIndex(f => f.url === v.foto_lateral_url));
        }
        if (imgTrasera && v.foto_trasera_url) {
            imgTrasera.style.cursor = 'zoom-in';
            imgTrasera.onclick = () => abrirLightbox(fotosVehiculo, fotosVehiculo.findIndex(f => f.url === v.foto_trasera_url));
        }

        // ── PDFs ──────────────────────────────────────────────────────
        const pdfBtn = document.getElementById('fichaPdfBtn');
        const certInvBtn = document.getElementById('fichaCertInventarioBtn');
        const certSicBtn = document.getElementById('fichaCertSicoinBtn');
        if (pdfBtn) { if (v.pdf_url) { pdfBtn.href = v.pdf_url; pdfBtn.style.display = ''; } else { pdfBtn.style.display = 'none'; } }
        if (certInvBtn) { if (v.cert_inventario_url) { certInvBtn.href = v.cert_inventario_url; certInvBtn.style.display = 'block'; } else { certInvBtn.style.display = 'none'; } }
        if (certSicBtn) { if (v.cert_sicoin_url) { certSicBtn.href = v.cert_sicoin_url; certSicBtn.style.display = 'block'; } else { certSicBtn.style.display = 'none'; } }

        // ── Datos generales ───────────────────────────────────────────
        const _set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
        _set('fd-placa', v.placa);
        _set('fd-serie', v.numero_serie);
        _set('fd-marca', v.marca);
        _set('fd-modelo', v.modelo);
        _set('fd-anio', v.anio);
        _set('fd-color', v.color);
        _set('fd-tipo', v.tipo);
        _set('fd-km', Number(v.km_actuales).toLocaleString() + ' km');
        _set('fd-ingreso', fmtFecha(v.fecha_ingreso));
        _set('fd-obs', v.observaciones || '—');
        _set('fd-unidad', v.unidad_nombre || '—');
        _set('fd-destacamento', v.destacamento_nombre
            ? `${v.destacamento_nombre} (${v.destacamento_depto})` : '—');

        // ── Estado con motivo taller ──────────────────────────────────
        const estadoEl = document.getElementById('fd-estado');
        if (estadoEl) {
            const colores = { Alta: '#4caf7d', Baja: '#e05252', Taller: '#e8b84b' };
            let estadoTexto = v.estado;
            if (v.estado === 'Taller' && d.motivo_taller) {
                estadoTexto = `Taller — Por ${d.motivo_taller}`;
            }
            estadoEl.textContent = estadoTexto;
            estadoEl.style.color = colores[v.estado] || 'inherit';
        }

        // ── KM al ingreso — editable con validación ───────────────────
        const ordenKmEl = document.getElementById('ordenKm');
        if (ordenKmEl) {
            ordenKmEl.value = v.km_actuales;
            ordenKmEl.readOnly = false;
            ordenKmEl.style.opacity = '1';
            ordenKmEl.style.cursor = '';
            ordenKmEl.style.background = '';
            ordenKmEl.title = '';
            ordenKmEl.dataset.kmMinimo = v.km_actuales;

            ordenKmEl.onblur = () => {
                const kmIngresado = parseInt(ordenKmEl.value || '0');
                const kmMinimo = parseInt(ordenKmEl.dataset.kmMinimo || '0');
                const diferencia = kmIngresado - kmMinimo;

                // ── Validación 1: no puede ser menor al registrado ────────────────
                if (!kmIngresado || kmIngresado < kmMinimo) {
                    Swal.fire({
                        icon: 'warning', title: 'KM inválido',
                        html: `El kilometraje ingresado (<strong>${kmIngresado.toLocaleString()} km</strong>) 
                   no puede ser menor al registrado 
                   (<strong>${kmMinimo.toLocaleString()} km</strong>).`,
                        confirmButtonText: 'Corregir', confirmButtonColor: '#e8b84b',
                        background: '#1a1d27', color: '#e8eaf0',
                        customClass: { container: 'swal-over-modal' }
                    }).then(() => { ordenKmEl.value = kmMinimo; ordenKmEl.focus(); });
                    return;
                }

                // ── Validación 2: diferencia mínima solo si ya tiene km registrados
                if (kmMinimo > 0) {
                    const esMoto = fichaTipoVehiculo.toLowerCase().includes('motocicleta');
                    const intervaloMinimo = esMoto ? 500 : 1000;

                    if (diferencia >= 0 && diferencia < intervaloMinimo) {
                        Swal.fire({
                            icon: 'warning', title: 'Kilometraje inválido',
                            html: `La diferencia entre el KM ingresado 
                       (<strong>${kmIngresado.toLocaleString()} km</strong>) y el registrado 
                       (<strong>${kmMinimo.toLocaleString()} km</strong>) es de solo 
                       <strong>${diferencia.toLocaleString()} km</strong>.<br><br>
                       <span style="font-size:.82rem;color:#7c8398;">
                           Para un <strong style="color:#e8b84b;">${fichaTipoVehiculo || 'vehículo'}</strong> 
                           se esperaría un mínimo de 
                           <strong style="color:#e8b84b;">${intervaloMinimo.toLocaleString()} km</strong> 
                           entre servicios.
                       </span>`,
                            confirmButtonText: 'Corregir', confirmButtonColor: '#e8b84b',
                            background: '#1a1d27', color: '#e8eaf0',
                            customClass: { container: 'swal-over-modal' }
                        }).then(() => { ordenKmEl.value = kmMinimo; ordenKmEl.focus(); });
                    }
                }
            };
        }

        // ── KM reparacion ─────────────────────────────────────────────
        const repKmEl = document.getElementById('repKm');
        if (repKmEl) repKmEl.value = v.km_actuales;

        // ── Alertas próximo servicio ──────────────────────────────────
        const fichaAlertaEl = document.getElementById('fichaAlerta');
        const fichaProximoEl = document.getElementById('fichaProximo');
        const fichaAmarillaEl = document.getElementById('fichaAlertaAmarilla');
        if (fichaAlertaEl) fichaAlertaEl.style.display = 'none';
        if (fichaProximoEl) fichaProximoEl.style.display = 'none';
        if (fichaAmarillaEl) fichaAmarillaEl.style.display = 'none';

        if (d.proximo_servicio) {
            const ps = d.proximo_servicio;
            if (d.alerta_km) {
                if (fichaAlertaEl) {
                    fichaAlertaEl.style.display = 'flex';
                    const textoEl = document.getElementById('fichaAlertaTexto');
                    if (textoEl) textoEl.textContent =
                        `${ps.tipo_nombre} — venció a los ${Number(ps.km_proximo).toLocaleString()} km. ` +
                        `KM actual: ${Number(v.km_actuales).toLocaleString()} km ` +
                        `(+${(Number(v.km_actuales) - Number(ps.km_proximo)).toLocaleString()} km de diferencia)`;
                }
            } else if (d.alerta_amarilla) {
                if (fichaAmarillaEl) {
                    fichaAmarillaEl.style.display = 'flex';
                    const faltan = Number(ps.km_proximo) - Number(v.km_actuales);
                    const textoEl = document.getElementById('fichaAlertaAmarillaTexto');
                    if (textoEl) textoEl.textContent =
                        `${ps.tipo_nombre} a los ${Number(ps.km_proximo).toLocaleString()} km. ` +
                        `Faltan ${faltan.toLocaleString()} km.`;
                }
            } else {
                if (fichaProximoEl) {
                    fichaProximoEl.style.display = 'flex';
                    let texto = `${ps.tipo_nombre} a los ${Number(ps.km_proximo).toLocaleString()} km`;
                    if (ps.fecha_proximo) texto += ` · Fecha límite: ${fmtFecha(ps.fecha_proximo)}`;
                    const textoEl = document.getElementById('fichaProximoTexto');
                    if (textoEl) textoEl.textContent = texto;
                }
            }
        }

        // ── Badges ────────────────────────────────────────────────────
        const _badge = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
        _badge('badgeServicios', (d.ordenes || []).length);
        _badge('badgeReparaciones', d.reparaciones.length);
        _badge('badgeSeguro', (d.seguros || []).length);
        _badge('badgeAccidentes', (d.accidentes || []).length);

        // ── Render tabs ───────────────────────────────────────────────
        renderTablaServicios(d.ordenes || []);
        renderTablaReparaciones(d.reparaciones);
        renderTablaSeguros(d.seguros || []);
        renderTablaAccidentes(d.accidentes || []);
        await cargarChequeos();

        // ── Orden en proceso ──────────────────────────────────────────
        const ordenAlerta = document.getElementById('ordenEnProcesoAlert');
        const btnNuevaOrden = document.getElementById('btnNuevaOrden');

        if (d.orden_en_proceso) {
            ordenActualId = parseInt(d.orden_en_proceso.id_orden);
            _ordenIdRespaldo = parseInt(d.orden_en_proceso.id_orden);
            const textoEl = document.getElementById('ordenEnProcesoTexto');
            if (textoEl) textoEl.innerHTML =
                `Abierta el ${fmtFecha(d.orden_en_proceso.fecha_ingreso)} · 
                ${Number(d.orden_en_proceso.km_al_ingreso).toLocaleString()} km · 
                ${d.orden_en_proceso.total_items || 0} servicio(s)`;
            if (ordenAlerta) ordenAlerta.style.display = 'flex';
            if (btnNuevaOrden) btnNuevaOrden.style.display = 'none';

            switchTab(
                document.querySelector('.ficha-tab[data-tab="servicios"]'),
                'servicios'
            );
        } else {
            ordenActualId = null;
            _ordenIdRespaldo = null;
            if (ordenAlerta) ordenAlerta.style.display = 'none';
            if (btnNuevaOrden) btnNuevaOrden.style.display = '';
        }

    } catch (err) {
        console.error('Error en abrirFicha:', err);
        Toast.fire({ icon: 'error', title: 'Error al cargar la ficha' });
    } finally {
        ocultarLoader();
        _fichaLoading = false;
    }
};

const cerrarFicha = () => {
    document.getElementById('modalFicha').style.display = 'none';
    document.body.style.overflow = '';
    fichaPlacaActual = '';
    ordenActualId = null;
    _ordenIdRespaldo = null;
    // ── Refrescar cartas al cerrar para reflejar cambios de estado ────────
    buscar();
};

document.getElementById('modalFicha').addEventListener('click', (e) => {
    if (e.target === document.getElementById('modalFicha')) cerrarFicha();
});

const switchTab = (btn, tab) => {
    const tabActivo = document.querySelector('.ficha-tab.activo')?.dataset?.tab;
    if (tabActivo && tabActivo !== tab) {
        if (tabActivo === 'servicios') resetFormServicio();
        if (tabActivo === 'reparaciones') resetFormReparacion();
        if (tabActivo === 'seguro') resetFormSeguro();
        if (tabActivo === 'accidentes') resetFormAccidente();
    }
    document.querySelectorAll('.ficha-tab').forEach(b => b.classList.remove('activo'));
    document.querySelectorAll('.ficha-tab-content').forEach(c => c.style.display = 'none');
    btn.classList.add('activo');
    document.getElementById('tab' + tab.charAt(0).toUpperCase() + tab.slice(1)).style.display = 'block';
};

// ════════════════════════════════════════════════════════════════════════════
// ── ÓRDENES DE SERVICIO ───────────────────────────────────────────────────────
// ════════════════════════════════════════════════════════════════════════════

// ── TOGGLE FORM NUEVA ORDEN ───────────────────────────────────────────────────
const toggleFormNuevaOrden = () => {
    // ── Bloqueo: ya existe orden en proceso ───────────────────────────────
    if (ordenActualId) {
        Swal.fire({
            icon: 'warning',
            title: 'Orden en proceso',
            html: `Ya existe una orden de servicio abierta para este vehículo.<br><br>
                <span style="font-size:.82rem;color:#7c8398;">
                    Use el botón <strong style="color:#e8b84b;">"Continuar orden"</strong> 
                    para retomar la orden activa.
                </span>`,
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#e8b84b',
            background: '#1a1d27', color: '#e8eaf0',
            customClass: { container: 'swal-over-modal' }
        });
        return;
    }

    const form = document.getElementById('formNuevaOrden');
    const btn = document.getElementById('btnNuevaOrden');
    const visible = form.style.display !== 'none';
    form.style.display = visible ? 'none' : 'block';
    btn.innerHTML = visible
        ? '<i class="bi bi-plus-circle"></i> Nueva Orden de Servicio'
        : '<i class="bi bi-x-circle"></i> Cancelar';
};

// ── CREAR ORDEN ───────────────────────────────────────────────────────────────
const crearOrden = async () => {
    if (ordenActualId) {
        Swal.fire({
            icon: 'warning', title: 'Orden en proceso',
            html: `Ya existe una orden abierta para este vehículo.<br><br>
                <span style="font-size:.82rem;color:#7c8398;">
                    Use <strong style="color:#e8b84b;">"Continuar orden"</strong> para retomar la orden activa.
                </span>`,
            confirmButtonText: 'Entendido', confirmButtonColor: '#e8b84b',
            background: '#1a1d27', color: '#e8eaf0',
            customClass: { container: 'swal-over-modal' }
        });
        return;
    }

    const fecha = document.getElementById('ordenFecha').value;
    const km = document.getElementById('ordenKm').value;
    const responsable = document.getElementById('ordenResponsable').value.trim();
    const obs = document.getElementById('ordenObs').value.trim();

    if (!fecha) {
        Swal.fire({
            icon: 'warning', title: 'Fecha requerida',
            text: 'Selecciona la fecha de ingreso al taller.',
            background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b',
            customClass: { container: 'swal-over-modal' }
        });
        return;
    }

    if (km === '' || parseInt(km) < 0) {
        Swal.fire({
            icon: 'warning', title: 'KM requerido',
            text: 'Ingresa el kilometraje al momento del ingreso.',
            background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b',
            customClass: { container: 'swal-over-modal' }
        });
        return;
    }

    // ── Validar diferencia mínima según tipo de vehículo ─────────────────
    const kmIngresadoVal = parseInt(km);
    const kmMinimoVal = parseInt(document.getElementById('ordenKm')?.dataset.kmMinimo || '0');
    const diferenciaVal = kmIngresadoVal - kmMinimoVal;
    const esMotoVal = fichaTipoVehiculo.toLowerCase().includes('motocicleta');
    const intervaloMinVal = esMotoVal ? 500 : 1000;

    if (kmMinimoVal > 0 && diferenciaVal >= 0 && diferenciaVal < intervaloMinVal) {
        Swal.fire({
            icon: 'warning', title: 'KM inválido',
            html: `El kilometraje ingresado (<strong>${kmIngresadoVal.toLocaleString()} km</strong>) 
                   debe ser al menos <strong>${intervaloMinVal.toLocaleString()} km</strong> mayor 
                   al registrado (<strong>${kmMinimoVal.toLocaleString()} km</strong>).<br><br>
                   <span style="font-size:.82rem;color:#7c8398;">
                       Para un <strong style="color:#e8b84b;">${fichaTipoVehiculo || 'vehículo'}</strong> 
                       se esperaría un mínimo de 
                       <strong style="color:#e8b84b;">${intervaloMinVal.toLocaleString()} km</strong> 
                       entre servicios.
                   </span>`,
            confirmButtonText: 'Corregir', confirmButtonColor: '#e8b84b',
            background: '#1a1d27', color: '#e8eaf0',
            customClass: { container: 'swal-over-modal' }
        });
        return;
    }

    if (!responsable) {
        Swal.fire({
            icon: 'warning', title: 'Responsable requerido',
            text: 'Indica quién está a cargo del ingreso al taller.',
            background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b',
            customClass: { container: 'swal-over-modal' }
        });
        return;
    }

    // ── Verificar alertas de servicios antes de abrir ─────────────────────
    try {
        const rAlertas = await fetch(`${BASE}/API/vehiculos/alertas-orden?placa=${fichaPlacaActual}&km_override=${km}`);
        if (rAlertas.ok) {
            const dAlertas = await rAlertas.json();
            if (dAlertas.codigo === 1 && dAlertas.alertas.length > 0) {

                const colores = {
                    rojo: { icono: '🔴', color: '#e05252', bg: 'rgba(224,82,82,.1)', border: 'rgba(224,82,82,.3)' },
                    amarillo: { icono: '🟡', color: '#e8b84b', bg: 'rgba(232,184,75,.1)', border: 'rgba(232,184,75,.3)' },
                    verde: { icono: '🟢', color: '#4caf7d', bg: 'rgba(76,175,125,.1)', border: 'rgba(76,175,125,.3)' },
                };

                const filasHtml = dAlertas.alertas.map(a => {
                    const c = colores[a.nivel];
                    return `
                    <div style="display:flex;align-items:center;gap:.75rem;
                        background:${c.bg};border:1px solid ${c.border};
                        border-radius:8px;padding:.6rem .85rem;margin-bottom:.4rem;">
                        <span style="font-size:1rem;flex-shrink:0;">${c.icono}</span>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:.85rem;font-weight:600;color:#e8eaf0;">
                                ${a.tipo_nombre}
                            </div>
                            <div style="font-size:.75rem;color:#7c8398;">
                                Programado a los <strong style="color:${c.color};">
                                ${Number(a.km_proximo).toLocaleString()} km
                                </strong>
                            </div>
                        </div>
                        <div style="font-size:.78rem;font-weight:700;color:${c.color};
                            white-space:nowrap;text-align:right;">
                            ${a.texto}
                        </div>
                    </div>`;
                }).join('');

                const hayRojos = dAlertas.alertas.some(a => a.nivel === 'rojo');

                const conf = await Swal.fire({
                    icon: hayRojos ? 'warning' : 'info',
                    title: `<span style="font-family:Rajdhani,sans-serif;">
                        Estado de servicios — ${Number(dAlertas.km_actual).toLocaleString()} km actuales
                    </span>`,
                    html: `
                        <div style="text-align:left;margin-bottom:.75rem;font-size:.82rem;color:#7c8398;">
                            Revisá el estado de los servicios programados antes de abrir la orden:
                        </div>
                        <div style="max-height:300px;overflow-y:auto;">
                            ${filasHtml}
                        </div>
                        ${hayRojos ? `
                        <div style="margin-top:.75rem;background:rgba(224,82,82,.08);
                            border:1px solid rgba(224,82,82,.25);border-radius:8px;
                            padding:.65rem 1rem;font-size:.8rem;color:#e05252;text-align:left;">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            Hay servicios vencidos. Se recomienda atenderlos en esta visita.
                        </div>` : ''}`,
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-door-open"></i> Entendido, abrir orden',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: hayRojos ? '#e8b84b' : '#4caf7d',
                    cancelButtonColor: '#555',
                    background: '#1a1d27', color: '#e8eaf0',
                    width: '560px',
                    customClass: { container: 'swal-over-modal' }
                });

                if (!conf.isConfirmed) return;
            }
        }
    } catch (e) {
        console.warn('No se pudieron cargar alertas:', e);
    }

    // ── Crear la orden ────────────────────────────────────────────────────
    const btnCrear = document.getElementById('btnConfirmarOrden');
    if (btnCrear) { btnCrear.disabled = true; btnCrear.style.opacity = '.5'; }

    try {
        mostrarLoader('Abriendo orden de servicio...');
        const body = new FormData();
        body.append('placa', fichaPlacaActual);
        body.append('fecha_ingreso', fecha);
        body.append('km_al_ingreso', km);
        body.append('responsable', responsable);
        body.append('observaciones', obs);

        const r = await fetch(`${BASE}/API/vehiculos/orden/crear`, { method: 'POST', body });
        const d = await r.json();

        if (d.codigo !== 1) {
            Swal.fire({
                icon: 'error', title: 'No se pudo abrir la orden',
                text: d.mensaje, background: '#1a1d27', color: '#e8eaf0',
                confirmButtonColor: '#e05252',
                customClass: { container: 'swal-over-modal' }
            });
            return;
        }

        ordenActualId = parseInt(d.id_orden);
        _ordenIdRespaldo = parseInt(d.id_orden);

        document.getElementById('formNuevaOrden').style.display = 'none';
        document.getElementById('btnNuevaOrden').innerHTML =
            '<i class="bi bi-plus-circle"></i> Nueva Orden de Servicio';

        _mostrarPanelOrden(d.orden);

        const btnNuevaOrden = document.getElementById('btnNuevaOrden');
        if (btnNuevaOrden) btnNuevaOrden.style.display = 'none';

        const ordenAlerta = document.getElementById('ordenEnProcesoAlert');
        if (ordenAlerta) ordenAlerta.style.display = 'none';

        const estadoEl = document.getElementById('fd-estado');
        if (estadoEl) { estadoEl.textContent = 'Taller'; estadoEl.style.color = '#e8b84b'; }

        const badge = document.getElementById('badgeServicios');
        if (badge) badge.textContent = parseInt(badge.textContent || '0') + 1;

        setTimeout(() => buscar(), 500);

        Swal.fire({
            position: 'top-end', icon: 'success', title: 'Orden abierta',
            html: `<span style="font-size:.85rem;color:#7c8398;">El vehículo está en <strong style="color:#e8b84b;">Taller</strong></span>`,
            showConfirmButton: false, timer: 2000, timerProgressBar: true,
            background: '#1a1d27', color: '#e8eaf0'
        });

    } catch (err) {
        console.error('crearOrden error:', err);
        const esRedError = err instanceof TypeError;
        Swal.fire({
            icon: 'warning',
            title: esRedError ? 'Sin conexión' : 'Error',
            html: esRedError
                ? `<span style="font-size:.85rem;color:#7c8398;">
                    No se pudo conectar con el servidor.<br>
                    <strong style="color:#e8b84b;">Recargá la página</strong> para verificar si la orden se creó.
                   </span>`
                : `<span style="font-size:.85rem;color:#7c8398;">${err.message || 'Intenta de nuevo.'}</span>`,
            confirmButtonText: 'Entendido', confirmButtonColor: '#e8b84b',
            background: '#1a1d27', color: '#e8eaf0',
            customClass: { container: 'swal-over-modal' }
        });
    } finally {
        ocultarLoader();
        if (btnCrear) { btnCrear.disabled = false; btnCrear.style.opacity = '1'; }
    }
};

// ── MOSTRAR PANEL ORDEN ABIERTA ───────────────────────────────────────────────
const _mostrarPanelOrden = (orden) => {
    ordenActualId = parseInt(orden.id_orden);
    _ordenIdRespaldo = parseInt(orden.id_orden);
    const panel = document.getElementById('panelOrdenAbierta');
    const info = document.getElementById('ordenHeaderInfo');
    panel.style.display = 'block';
    info.innerHTML = `
        <i class="bi bi-calendar3"></i> ${fmtFecha(orden.fecha_ingreso)}
        &nbsp;·&nbsp; <i class="bi bi-speedometer"></i> ${Number(orden.km_al_ingreso).toLocaleString()} km
        ${orden.responsable ? `&nbsp;·&nbsp; <i class="bi bi-person"></i> ${orden.responsable}` : ''}
        &nbsp;·&nbsp; <span style="color:var(--accent);">
            ${orden.total_items || (orden.items?.length ?? 0)} servicio(s) registrado(s)
        </span>`;
    _renderItemsOrden(orden.items || []);
};

// ── ABRIR ORDEN EN PROCESO ────────────────────────────────────────────────────
const abrirOrdenEnProceso = async () => {
    if (!ordenActualId) return;
    try {
        mostrarLoader('Cargando orden...');
        const r = await fetch(`${BASE}/API/vehiculos/orden/obtener?id=${ordenActualId}`);
        const d = await r.json();
        if (d.codigo === 1) {
            _mostrarPanelOrden(d.datos);
            const ordenAlerta = document.getElementById('ordenEnProcesoAlert');
            if (ordenAlerta) ordenAlerta.style.display = 'none';
        }
    } catch (err) {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    } finally {
        ocultarLoader();
    }
};

// ── RENDER ITEMS DE ORDEN ─────────────────────────────────────────────────────
const _renderItemsOrden = (items) => {
    const wrap = document.getElementById('itemsOrdenWrap');
    if (!items.length) {
        wrap.innerHTML = `
            <div style="text-align:center;padding:1.5rem;color:var(--text-muted);
                border:1px dashed var(--border);border-radius:8px;font-size:.85rem;">
                <i class="bi bi-inbox" style="font-size:1.5rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                Aún no hay servicios en esta orden
            </div>`;
        return;
    }
    wrap.innerHTML = items.map(item => `
        <div style="display:flex;align-items:center;gap:.75rem;background:var(--dark-2);
            border:1px solid var(--border);border-radius:8px;padding:.65rem .85rem;margin-bottom:.4rem;">
            <i class="bi bi-check-circle-fill" style="color:#4caf7d;flex-shrink:0;"></i>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.85rem;font-weight:600;color:var(--text-main);">${item.tipo_nombre}</div>
                ${item.observacion
            ? `<div style="font-size:.75rem;color:var(--text-muted);">${item.observacion}</div>`
            : ''}
            </div>
            ${item.km_proximo
            ? `<div style="font-size:.75rem;color:var(--accent);white-space:nowrap;flex-shrink:0;">
                    <i class="bi bi-speedometer"></i> próx. ${Number(item.km_proximo).toLocaleString()} km
                   </div>`
            : ''}
            <button onclick="eliminarItemOrden(${item.id_item})"
                style="background:rgba(224,82,82,.15);border:1px solid rgba(224,82,82,.3);
                color:var(--danger);border-radius:6px;padding:.3rem .5rem;
                cursor:pointer;font-size:.8rem;flex-shrink:0;">
                <i class="bi bi-x"></i>
            </button>
        </div>`
    ).join('');
};

// ── AGREGAR ITEM ──────────────────────────────────────────────────────────────
const agregarItem = async () => {
    if (!ordenActualId && _ordenIdRespaldo) {
        ordenActualId = _ordenIdRespaldo;
    }

    const tipoSel = document.getElementById('itemTipo');
    const tipo = tipoSel?.value;
    const kmProximo = document.getElementById('itemKmProximo').value;
    const observacion = document.getElementById('itemObservacion').value.trim();

    if (!tipo) {
        Swal.fire({
            icon: 'warning', title: 'Tipo de servicio requerido',
            text: 'Selecciona el tipo de servicio antes de agregar.',
            background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b',
            customClass: { container: 'swal-over-modal' }
        });
        return;
    }
    if (!ordenActualId) {
        Swal.fire({
            icon: 'error', title: 'Sin orden activa',
            text: 'No hay una orden de servicio abierta.',
            background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e05252',
            customClass: { container: 'swal-over-modal' }
        });
        return;
    }

    try {
        mostrarLoader('Agregando servicio...');
        const body = new FormData();
        body.append('id_orden', ordenActualId);
        body.append('id_tipo_servicio', tipo);
        const inputKm = document.getElementById('itemKmProximo');
        if (inputKm && !inputKm.disabled && kmProximo) {
            body.append('km_proximo', kmProximo);
        }
        if (observacion) body.append('observacion', observacion);

        const r = await fetch(`${BASE}/API/vehiculos/orden/agregar-item`, { method: 'POST', body });

        if (!r.ok) {
            const texto = await r.text();
            console.error('Error agregar-item:', r.status, texto);
            Swal.fire({
                icon: 'error', title: `Error ${r.status}`,
                text: 'No se pudo agregar el servicio. Revisá la consola.',
                background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e05252',
                customClass: { container: 'swal-over-modal' }
            });
            return;
        }

        const d = await r.json();

        if (d.codigo !== 1) {
            Swal.fire({
                icon: 'error', title: 'No se pudo agregar',
                text: d.mensaje || 'Verifica los datos e intenta de nuevo.',
                background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e05252',
                customClass: { container: 'swal-over-modal' }
            });
            return;
        }

        tipoSel.value = '';
        document.getElementById('itemObservacion').value = '';
        _resetKmProximo();

        try {
            const rOrden = await fetch(`${BASE}/API/vehiculos/orden/obtener?id=${ordenActualId}`);
            if (rOrden.ok) {
                const dOrden = await rOrden.json();
                if (dOrden.codigo === 1) _mostrarPanelOrden(dOrden.datos);
            }
        } catch (e) {
            console.warn('No se pudo recargar panel orden:', e);
        }

        Swal.fire({
            position: 'top-end', icon: 'success', title: 'Servicio agregado',
            showConfirmButton: false, timer: 1500, timerProgressBar: true,
            background: '#1a1d27', color: '#e8eaf0'
        });

    } catch (err) {
        console.error('agregarItem error:', err);
        const esRedError = err instanceof TypeError;
        Swal.fire({
            icon: 'warning',
            title: esRedError ? 'Sin conexión' : 'Error',
            html: esRedError
                ? `<span style="font-size:.85rem;color:#7c8398;">
                    No se pudo conectar con el servidor.<br>
                    <strong style="color:#e8b84b;">Recargá la página</strong> para verificar si el servicio se guardó.
                   </span>`
                : `<span style="font-size:.85rem;color:#7c8398;">${err.message || 'Intenta de nuevo.'}</span>`,
            confirmButtonText: 'Entendido', confirmButtonColor: '#e8b84b',
            background: '#1a1d27', color: '#e8eaf0',
            customClass: { container: 'swal-over-modal' }
        });
    } finally {
        ocultarLoader();
    }
};
// ── ELIMINAR ITEM ─────────────────────────────────────────────────────────────
const eliminarItemOrden = async (idItem) => {
    try {
        const body = new FormData();
        body.append('id_item', idItem);
        const r = await fetch(`${BASE}/API/vehiculos/orden/eliminar-item`, { method: 'POST', body });
        const d = await r.json();
        if (d.codigo === 1) {
            const rOrden = await fetch(`${BASE}/API/vehiculos/orden/obtener?id=${ordenActualId}`);
            const dOrden = await rOrden.json();
            if (dOrden.codigo === 1) _mostrarPanelOrden(dOrden.datos);
        }
    } catch (err) {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    }
};

// ── COMPLETAR ORDEN ───────────────────────────────────────────────────────────
const completarOrden = async () => {
    const conf = await Swal.fire({
        icon: 'warning',
        title: '¿Finalizar Orden de Servicio?',
        html: `
            <div style="text-align:left;font-size:.88rem;color:#c8cfe0;line-height:1.7;">
                <p>Está a punto de cerrar definitivamente esta orden de servicio.</p>
                <p>Verifique que <strong style="color:#e8eaf0;">todos los servicios han sido realizados</strong>
                antes de continuar.</p>
                <div style="background:rgba(224,82,82,.1);border:1px solid rgba(224,82,82,.3);
                    border-radius:8px;padding:.75rem 1rem;margin-top:.75rem;
                    display:flex;align-items:flex-start;gap:.6rem;">
                    <i class="bi bi-exclamation-triangle-fill" style="color:#e05252;margin-top:.1rem;flex-shrink:0;"></i>
                    <span style="font-size:.82rem;color:#e05252;">
                        Esta acción es <strong>irreversible</strong>. El vehículo saldrá del taller
                        y quedará operativo en estado <strong>Alta</strong>.
                    </span>
                </div>
            </div>`,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-circle-fill"></i> Sí, finalizar orden',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#4caf7d',
        cancelButtonColor: '#555',
        background: '#1a1d27',
        color: '#e8eaf0',
        customClass: { container: 'swal-over-modal' }
    });
    if (!conf.isConfirmed) return;

    try {
        mostrarLoader('Completando orden...');
        const body = new FormData();
        body.append('id_orden', ordenActualId);
        const r = await fetch(`${BASE}/API/vehiculos/orden/completar`, { method: 'POST', body });
        const d = await r.json();

        if (d.codigo !== 1) {
            Swal.fire({
                icon: 'error', title: 'Error', text: d.mensaje,
                background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e05252',
                customClass: { container: 'swal-over-modal' }
            });
            return;
        }

        const placaCompletada = fichaPlacaActual;

        ordenActualId = null;
        _ordenIdRespaldo = null;

        // ── Mostrar botón nueva orden ─────────────────────────────────────
        const btnNuevaOrden = document.getElementById('btnNuevaOrden');
        if (btnNuevaOrden) btnNuevaOrden.style.display = '';

        document.getElementById('panelOrdenAbierta').style.display = 'none';
        document.getElementById('ordenEnProcesoAlert').style.display = 'none';

        const estadoEl = document.getElementById('fd-estado');
        if (estadoEl) { estadoEl.textContent = 'Alta'; estadoEl.style.color = '#4caf7d'; }

        await abrirFicha(placaCompletada);
        switchTab(document.querySelector('.ficha-tab[data-tab="servicios"]'), 'servicios');

        setTimeout(() => buscar(), 500);

        Swal.fire({
            position: 'top-end', icon: 'success', title: 'Orden completada',
            html: `<span style="font-size:.85rem;color:#7c8398;">El vehículo está operativo en estado <strong style="color:#4caf7d;">Alta</strong></span>`,
            showConfirmButton: false, timer: 2500, timerProgressBar: true,
            background: '#1a1d27', color: '#e8eaf0'
        });

    } catch (err) {
        console.error('completarOrden error:', err);
        const esRedError = err instanceof TypeError;
        Swal.fire({
            icon: 'warning',
            title: esRedError ? 'Sin conexión' : 'Error',
            html: esRedError
                ? `<span style="font-size:.85rem;color:#7c8398;">
                    No se pudo conectar con el servidor.<br>
                    <strong style="color:#e8b84b;">Recargá la página</strong> para verificar si la orden se completó.
                   </span>`
                : `<span style="font-size:.85rem;color:#7c8398;">${err.message || 'Intenta de nuevo.'}</span>`,
            confirmButtonText: 'Entendido', confirmButtonColor: '#e8b84b',
            background: '#1a1d27', color: '#e8eaf0',
            customClass: { container: 'swal-over-modal' }
        });
    } finally {
        ocultarLoader();
    }
};

// ── ELIMINAR ORDEN ────────────────────────────────────────────────────────────
const confirmarEliminarOrden = async () => {
    const conf = await Swal.fire({
        icon: 'warning', title: '¿Eliminar orden de servicio?',
        text: 'Se eliminarán todos los servicios registrados en esta orden.',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e05252', cancelButtonColor: '#555',
        background: '#1a1d27', color: '#e8eaf0',
        customClass: { container: 'swal-over-modal' }
    });
    if (!conf.isConfirmed) return;

    try {
        mostrarLoader('Eliminando orden...');
        const body = new FormData();
        body.append('id_orden', ordenActualId);
        const r = await fetch(`${BASE}/API/vehiculos/orden/eliminar`, { method: 'POST', body });
        const d = await r.json();

        ordenActualId = null;
        _ordenIdRespaldo = null;

        // ── Mostrar botón nueva orden ─────────────────────────────────────
        const btnNuevaOrden = document.getElementById('btnNuevaOrden');
        if (btnNuevaOrden) btnNuevaOrden.style.display = '';

        document.getElementById('panelOrdenAbierta').style.display = 'none';
        document.getElementById('ordenEnProcesoAlert').style.display = 'none';

        // ── Actualizar estado en tab Info ─────────────────────────────────
        const estadoEl = document.getElementById('fd-estado');
        if (estadoEl) { estadoEl.textContent = 'Alta'; estadoEl.style.color = '#4caf7d'; }

        await abrirFicha(fichaPlacaActual);
        switchTab(document.querySelector('.ficha-tab[data-tab="servicios"]'), 'servicios');

        setTimeout(() => buscar(), 500);

        Toast.fire({ icon: d.codigo === 1 ? 'success' : 'error', title: d.mensaje });

    } catch (err) {
        console.error('eliminarOrden error:', err);
        const esRedError = err instanceof TypeError;
        Swal.fire({
            icon: 'warning',
            title: esRedError ? 'Sin conexión' : 'Error',
            html: esRedError
                ? `<span style="font-size:.85rem;color:#7c8398;">
                    No se pudo conectar con el servidor.<br>
                    <strong style="color:#e8b84b;">Recargá la página</strong> para verificar el estado de la orden.
                   </span>`
                : `<span style="font-size:.85rem;color:#7c8398;">${err.message || 'Intenta de nuevo.'}</span>`,
            confirmButtonText: 'Entendido', confirmButtonColor: '#e8b84b',
            background: '#1a1d27', color: '#e8eaf0',
            customClass: { container: 'swal-over-modal' }
        });
    } finally {
        ocultarLoader();
    }
};

// ── RENDER HISTORIAL ÓRDENES ──────────────────────────────────────────────────
const renderTablaServicios = (ordenes) => {
    const wrap = document.getElementById('tablaOrdenesWrap');

    // ── Filtrar: solo mostrar órdenes COMPLETADAS en el historial ─────────
    const ordenesCompletadas = (ordenes || []).filter(o => o.estado === 'Completado');

    if (!ordenesCompletadas.length) {
        wrap.innerHTML = `
            <div style="text-align:center;padding:2rem;color:var(--text-muted);">
                <i class="bi bi-tools" style="font-size:2.5rem;opacity:.2;display:block;margin-bottom:.75rem;"></i>
                <p>No hay órdenes de servicio completadas</p>
            </div>`;
        return;
    }

    wrap.innerHTML = ordenesCompletadas.map(o => {
        return `
        <div class="svc-row" style="grid-template-columns:1fr 1fr 1fr 1fr auto;">
            <div><div class="svc-label">Fecha ingreso</div><div class="svc-val">${fmtFecha(o.fecha_ingreso)}</div></div>
            <div><div class="svc-label">KM</div><div class="svc-val">${Number(o.km_al_ingreso).toLocaleString()} km</div></div>
            <div><div class="svc-label">Servicios</div><div class="svc-val">${o.total_items || 0} servicio(s)</div></div>
            <div><div class="svc-label">Estado</div><div class="svc-val">
                <span style="background:rgba(76,175,125,.15);color:#4caf7d;border:1px solid rgba(76,175,125,.3);
                    padding:.2rem .65rem;border-radius:20px;font-size:.72rem;font-weight:700;">
                    Completado
                </span>
            </div></div>
            <div style="display:flex;gap:.4rem;align-items:center;">
                <button onclick="verOrden(${o.id_orden})"
                    style="background:rgba(111,66,193,.15);border:1px solid rgba(111,66,193,.3);
                    color:#a78bfa;border-radius:6px;padding:.35rem .6rem;cursor:pointer;font-size:.8rem;">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>
        ${o.responsable ? `<div style="font-size:.75rem;color:var(--text-muted);margin-top:-.4rem;margin-bottom:.4rem;padding-left:.25rem;"><i class="bi bi-person"></i> ${o.responsable}${o.observaciones ? ' · ' + o.observaciones : ''}</div>` : ''}`;
    }).join('');
};
const verHistorialServicios = async () => {
    try {
        mostrarLoader('Cargando historial...');
        const r = await fetch(`${BASE}/API/vehiculos/hoja-vida?placa=${fichaPlacaActual}`);
        const d = await r.json();
        if (d.codigo !== 1) {
            Toast.fire({ icon: 'error', title: 'Error al cargar historial' });
            return;
        }

        if (!d.grupos.length) {
            Swal.fire({
                icon: 'info',
                title: 'Sin historial',
                text: 'Este vehículo no tiene servicios completados registrados.',
                background: '#1a1d27', color: '#e8eaf0',
                confirmButtonColor: '#e8b84b',
                customClass: { container: 'swal-over-modal' }
            });
            return;
        }

        const coloresCumplimiento = {
            primer_registro: { color: '#7c8398', icono: '🔵', texto: 'Primer registro' },
            exacto: { color: '#4caf7d', icono: '🟢', texto: 'A tiempo' },
            antes: { color: '#4caf7d', icono: '🟢', texto: 'Antes' },
            tarde: { color: '#e05252', icono: '🔴', texto: 'Tarde' },
        };

        const gruposHtml = d.grupos.map((g, gi) => {
            const filasHtml = g.items.map((item, i) => {
                const c = coloresCumplimiento[item.cumplimiento] ?? coloresCumplimiento.primer_registro;
                const bg = i % 2 === 0 ? 'rgba(255,255,255,.02)' : 'transparent';

                let cumplimientoTexto = c.icono + ' ' + c.texto;
                if (item.cumplimiento === 'tarde') {
                    cumplimientoTexto += ` (+${Number(item.diferencia).toLocaleString()} km)`;
                } else if (item.cumplimiento === 'antes') {
                    cumplimientoTexto += ` (-${Number(item.diferencia).toLocaleString()} km)`;
                }

                return `
                <tr style="background:${bg};border-bottom:1px solid rgba(255,255,255,.04);">
                    <td style="padding:.5rem .75rem;font-size:.8rem;color:#e8eaf0;">
                        ${item.fecha}
                    </td>
                    <td style="padding:.5rem .75rem;font-size:.8rem;color:#e8b84b;font-weight:600;">
                        ${Number(item.km_real).toLocaleString()} km
                    </td>
                    <td style="padding:.5rem .75rem;font-size:.8rem;color:#7c8398;">
                        ${item.km_proximo ? Number(item.km_proximo).toLocaleString() + ' km' : '—'}
                    </td>
                    <td style="padding:.5rem .75rem;font-size:.8rem;color:${c.color};font-weight:600;">
                        ${cumplimientoTexto}
                    </td>
                    <td style="padding:.5rem .75rem;font-size:.75rem;color:#7c8398;">
                        ${item.responsable || '—'}
                    </td>
                </tr>`;
            }).join('');

            return `
            <div style="margin-bottom:1rem;">
                <div style="background:#1f2335;border-left:3px solid #a78bfa;
                    padding:.6rem 1rem;margin-bottom:0;
                    display:flex;align-items:center;justify-content:space-between;
                    cursor:pointer;border-radius:6px 6px 0 0;"
                    onclick="
                        const t=document.getElementById('hv_tabla_${gi}');
                        const i=document.getElementById('hv_icon_${gi}');
                        if(t.style.display==='none'){t.style.display='';i.style.transform='rotate(0deg)';}
                        else{t.style.display='none';i.style.transform='rotate(-90deg)';}
                    ">
                    <div>
                        <span style="font-family:Rajdhani,sans-serif;font-size:.95rem;
                            font-weight:700;color:#e8eaf0;">
                            ${g.tipo}
                        </span>
                        <span style="font-size:.72rem;color:#7c8398;margin-left:.5rem;">
                            ${g.total} registro(s)
                        </span>
                    </div>
                    <i id="hv_icon_${gi}" class="bi bi-chevron-down"
                        style="color:#a78bfa;font-size:.8rem;transition:transform .2s;"></i>
                </div>
                <div id="hv_tabla_${gi}" style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="background:#242837;">
                                <th style="padding:.45rem .75rem;font-size:.7rem;color:#7c8398;
                                    text-align:left;text-transform:uppercase;letter-spacing:.4px;">
                                    Fecha
                                </th>
                                <th style="padding:.45rem .75rem;font-size:.7rem;color:#7c8398;
                                    text-align:left;text-transform:uppercase;letter-spacing:.4px;">
                                    KM Real
                                </th>
                                <th style="padding:.45rem .75rem;font-size:.7rem;color:#7c8398;
                                    text-align:left;text-transform:uppercase;letter-spacing:.4px;">
                                    Próximo KM
                                </th>
                                <th style="padding:.45rem .75rem;font-size:.7rem;color:#7c8398;
                                    text-align:left;text-transform:uppercase;letter-spacing:.4px;">
                                    Cumplimiento
                                </th>
                                <th style="padding:.45rem .75rem;font-size:.7rem;color:#7c8398;
                                    text-align:left;text-transform:uppercase;letter-spacing:.4px;">
                                    Responsable
                                </th>
                            </tr>
                        </thead>
                        <tbody>${filasHtml}</tbody>
                    </table>
                </div>
            </div>`;
        }).join('');

        Swal.fire({
            title: `<span style="font-family:Rajdhani,sans-serif;font-size:1.1rem;">
                <i class="bi bi-clock-history" style="color:#a78bfa;"></i>
                Historial de Servicios — ${fichaPlacaActual}
            </span>`,
            html: `
            <div style="text-align:left;max-height:480px;overflow-y:auto;padding-right:.25rem;">
                ${gruposHtml}
            </div>`,
            background: '#1a1d27', color: '#e8eaf0',
            confirmButtonColor: '#6f42c1',
            confirmButtonText: '<i class="bi bi-x"></i> Cerrar',
            width: '700px',
            customClass: { container: 'swal-over-modal' }
        });

    } catch (err) {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    } finally {
        ocultarLoader();
    }
};



// ── VER ORDEN ─────────────────────────────────────────
const verOrden = async (idOrden) => {
    try {
        mostrarLoader('Cargando orden...');
        const r = await fetch(`${BASE}/API/vehiculos/orden/obtener?id=${idOrden}`);
        const d = await r.json();
        if (d.codigo !== 1) return;

        const o = d.datos;
        const coloresResultado = {
            'Realizado': '#4caf7d', 'Revisado': '#5b9bd5',
            'Pendiente': '#e8b84b', 'No aplica': '#7c8398'
        };
        const filasItems = (o.items || []).map((item, i) => {
            const color = coloresResultado[item.resultado] || '#7c8398';
            return `
            <tr style="border-bottom:1px solid var(--border);">
                <td style="padding:.5rem .75rem;color:var(--text-muted);font-size:.8rem;">${String(i + 1).padStart(2, '0')}</td>
                <td style="padding:.5rem .75rem;font-size:.85rem;font-weight:600;color:var(--text-main);">${item.tipo_nombre}</td>
                <td style="padding:.5rem .75rem;text-align:center;">
                    <span style="color:${color};font-weight:700;font-size:.82rem;">${item.resultado}</span>
                </td>
                <td style="padding:.5rem .75rem;font-size:.78rem;color:var(--accent);">
                    ${item.km_proximo ? Number(item.km_proximo).toLocaleString() + ' km' : '—'}
                </td>
                <td style="padding:.5rem .75rem;font-size:.78rem;color:var(--text-muted);">${item.observacion || ''}</td>
            </tr>`;
        }).join('');

        await Swal.fire({
            title: `<span style="font-family:Rajdhani,sans-serif;font-size:1.1rem;">
                <i class="bi bi-tools" style="color:var(--accent);"></i>
                Orden de Servicio — ${fmtFecha(o.fecha_ingreso)}
            </span>`,
            html: `
            <div style="text-align:left;font-size:.82rem;margin-bottom:1rem;color:#7c8398;">
                <i class="bi bi-speedometer"></i> ${Number(o.km_al_ingreso).toLocaleString()} km
                ${o.responsable ? ` &nbsp;·&nbsp; <i class="bi bi-person"></i> ${o.responsable}` : ''}
                ${o.observaciones ? `<br><i class="bi bi-chat-text"></i> ${o.observaciones}` : ''}
            </div>
            <div style="overflow-x:auto;max-height:350px;overflow-y:auto;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#1a1d27;position:sticky;top:0;">
                            <th style="padding:.5rem;text-align:left;color:#7c8398;font-size:.7rem;">No.</th>
                            <th style="padding:.5rem;text-align:left;color:#7c8398;font-size:.7rem;">Servicio</th>
                            <th style="padding:.5rem;text-align:center;color:#7c8398;font-size:.7rem;">Resultado</th>
                            <th style="padding:.5rem;text-align:left;color:#7c8398;font-size:.7rem;">Próx. KM</th>
                            <th style="padding:.5rem;text-align:left;color:#7c8398;font-size:.7rem;">Obs.</th>
                        </tr>
                    </thead>
                    <tbody>${filasItems}</tbody>
                </table>
            </div>`,
            background: '#1a1d27', color: '#e8eaf0',
            confirmButtonColor: '#6f42c1', confirmButtonText: 'Cerrar',
            width: '700px', customClass: { container: 'swal-over-modal' }
        });

    } catch (err) {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    } finally {
        ocultarLoader();
    }
};

// ════════════════════════════════════════════════════════════════════════════
// ── REPARACIONES ──────────────────────────────────────────────────────────────
// ════════════════════════════════════════════════════════════════════════════

const toggleFormReparacion = () => {
    const form = document.getElementById('formNuevaReparacion');
    const btn = document.getElementById('btnToggleFormReparacion');
    const visible = form.style.display !== 'none';
    form.style.display = visible ? 'none' : 'block';
    btn.innerHTML = visible
        ? '<i class="bi bi-plus-circle"></i> Registrar Nueva Reparación'
        : '<i class="bi bi-x-circle"></i> Cancelar';
};

const verHistorialReparaciones = async () => {
    try {
        mostrarLoader('Cargando historial...');
        const r = await fetch(`${BASE}/API/vehiculos/hoja-vida-reparaciones?placa=${fichaPlacaActual}`);
        const d = await r.json();
        if (d.codigo !== 1) {
            Toast.fire({ icon: 'error', title: 'Error al cargar historial' });
            return;
        }

        if (!d.grupos.length) {
            Swal.fire({
                icon: 'info',
                title: 'Sin historial',
                text: 'Este vehículo no tiene reparaciones registradas.',
                background: '#1a1d27', color: '#e8eaf0',
                confirmButtonColor: '#e05252',
                customClass: { container: 'swal-over-modal' }
            });
            return;
        }

        const estadoColor = {
            'En proceso': '#e8b84b',
            'Finalizada': '#4caf7d',
        };

        const gruposHtml = d.grupos.map((g, gi) => {
            const filasHtml = g.items.map((item, i) => {
                const bg = i % 2 === 0 ? 'rgba(255,255,255,.02)' : 'transparent';
                const color = estadoColor[item.estado] || '#7c8398';
                const fechaFin = item.fecha_fin || '—';
                const costo = item.costo
                    ? `Q ${Number(item.costo).toLocaleString()}`
                    : '—';

                return `
                <tr style="background:${bg};border-bottom:1px solid rgba(255,255,255,.04);">
                    <td style="padding:.5rem .75rem;font-size:.8rem;color:#e8eaf0;">
                        ${item.fecha_inicio}
                    </td>
                    <td style="padding:.5rem .75rem;font-size:.8rem;color:#7c8398;">
                        ${fechaFin}
                    </td>
                    <td style="padding:.5rem .75rem;font-size:.8rem;color:#e8b84b;font-weight:600;">
                        ${Number(item.km_al_momento).toLocaleString()} km
                    </td>
                    <td style="padding:.5rem .75rem;font-size:.78rem;color:#e8eaf0;max-width:180px;">
                        ${item.descripcion}
                    </td>
                    <td style="padding:.5rem .75rem;font-size:.8rem;color:${color};font-weight:600;">
                        ${item.estado}
                    </td>
                    <td style="padding:.5rem .75rem;font-size:.8rem;color:#4caf7d;font-weight:600;">
                        ${costo}
                    </td>
                    <td style="padding:.5rem .75rem;font-size:.75rem;color:#7c8398;">
                        ${item.responsable || '—'}
                    </td>
                </tr>`;
            }).join('');

            const costoTotalStr = g.costo_total > 0
                ? `<span style="color:#e05252;font-weight:700;">
                       Q ${Number(g.costo_total).toLocaleString()}
                   </span>`
                : '';

            return `
            <div style="margin-bottom:1rem;">
                <div style="background:#1f2335;border-left:3px solid #e05252;
                    padding:.6rem 1rem;
                    display:flex;align-items:center;justify-content:space-between;
                    cursor:pointer;border-radius:6px 6px 0 0;"
                    onclick="
                        const t=document.getElementById('hr_tabla_${gi}');
                        const ic=document.getElementById('hr_icon_${gi}');
                        if(t.style.display==='none'){t.style.display='';ic.style.transform='rotate(0deg)';}
                        else{t.style.display='none';ic.style.transform='rotate(-90deg)';}
                    ">
                    <div style="display:flex;align-items:center;gap:.75rem;">
                        <span style="font-family:Rajdhani,sans-serif;font-size:.95rem;
                            font-weight:700;color:#e8eaf0;">
                            ${g.tipo}
                        </span>
                        <span style="font-size:.72rem;color:#7c8398;">
                            ${g.total} registro(s)
                        </span>
                        ${costoTotalStr}
                    </div>
                    <i id="hr_icon_${gi}" class="bi bi-chevron-down"
                        style="color:#e05252;font-size:.8rem;transition:transform .2s;"></i>
                </div>
                <div id="hr_tabla_${gi}" style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="background:#242837;">
                                <th style="padding:.45rem .75rem;font-size:.7rem;color:#7c8398;text-align:left;text-transform:uppercase;letter-spacing:.4px;">F. Inicio</th>
                                <th style="padding:.45rem .75rem;font-size:.7rem;color:#7c8398;text-align:left;text-transform:uppercase;letter-spacing:.4px;">F. Fin</th>
                                <th style="padding:.45rem .75rem;font-size:.7rem;color:#7c8398;text-align:left;text-transform:uppercase;letter-spacing:.4px;">KM</th>
                                <th style="padding:.45rem .75rem;font-size:.7rem;color:#7c8398;text-align:left;text-transform:uppercase;letter-spacing:.4px;min-width:140px;">Descripción</th>
                                <th style="padding:.45rem .75rem;font-size:.7rem;color:#7c8398;text-align:left;text-transform:uppercase;letter-spacing:.4px;">Estado</th>
                                <th style="padding:.45rem .75rem;font-size:.7rem;color:#7c8398;text-align:left;text-transform:uppercase;letter-spacing:.4px;">Costo</th>
                                    <th style="padding:.45rem .75rem;font-size:.7rem;color:#7c8398;text-align:left;text-transform:uppercase;letter-spacing:.4px;">Resp.</th>
                            </tr>
                        </thead>
                        <tbody>${filasHtml}</tbody>
                    </table>
                </div>
            </div>`;
        }).join('');

        Swal.fire({
            title: `<span style="font-family:Rajdhani,sans-serif;font-size:1.1rem;">
                <i class="bi bi-wrench-adjustable" style="color:#e05252;"></i>
                Historial de Reparaciones — ${fichaPlacaActual}
            </span>`,
            html: `
            <div style="text-align:left;max-height:480px;overflow-y:auto;padding-right:.25rem;">
                ${gruposHtml}
            </div>`,
            background: '#1a1d27', color: '#e8eaf0',
            confirmButtonColor: '#e05252',
            confirmButtonText: '<i class="bi bi-x"></i> Cerrar',
            width: '900px',
            customClass: { container: 'swal-over-modal' }
        });

    } catch (err) {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    } finally {
        ocultarLoader();
    }
};


const verDetalleReparacion = (r) => {
    const estadoColor = { 'En proceso': '#e8b84b', 'Finalizada': '#4caf7d' };
    const color = estadoColor[r.estado] || '#7c8398';

    Swal.fire({
        title: `<span style="font-family:Rajdhani,sans-serif;font-size:1.1rem;">
            <i class="bi bi-wrench-adjustable" style="color:#e05252;"></i>
            ${r.tipo_nombre}
        </span>`,
        html: `
        <div style="text-align:left;font-size:.85rem;">
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap;">
                <span style="background:${color}22;color:${color};border:1px solid ${color}44;
                    padding:.25rem .75rem;border-radius:20px;font-size:.75rem;font-weight:700;">
                    ${r.estado}
                </span>
                <span style="color:#7c8398;font-size:.78rem;">
                    <i class="bi bi-calendar3"></i> ${fmtFecha(r.fecha_inicio)}
                    ${r.fecha_fin ? ' → ' + r.fecha_fin : ''}
                </span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:1rem;">
                <div style="background:#1e2130;padding:.6rem .8rem;border-radius:8px;">
                    <div style="font-size:.68rem;color:#555;text-transform:uppercase;margin-bottom:.2rem;">KM al momento</div>
                    <div style="color:#e8b84b;font-weight:700;">${Number(r.km_al_momento).toLocaleString()} km</div>
                </div>
                <div style="background:#1e2130;padding:.6rem .8rem;border-radius:8px;">
                    <div style="font-size:.68rem;color:#555;text-transform:uppercase;margin-bottom:.2rem;">Costo</div>
                    <div style="color:#4caf7d;font-weight:700;">${r.costo ? 'Q ' + Number(r.costo).toLocaleString() : '—'}</div>
                </div>
                <div style="background:#1e2130;padding:.6rem .8rem;border-radius:8px;">
                    <div style="font-size:.68rem;color:#555;text-transform:uppercase;margin-bottom:.2rem;">Proveedor</div>
                    <div style="color:#c8cfe0;">${r.proveedor || '—'}</div>
                </div>
                <div style="background:#1e2130;padding:.6rem .8rem;border-radius:8px;">
                    <div style="font-size:.68rem;color:#555;text-transform:uppercase;margin-bottom:.2rem;">Responsable</div>
                    <div style="color:#c8cfe0;">${r.responsable || '—'}</div>
                </div>
            </div>
            <div style="background:#1e2130;border-left:3px solid #e05252;
                padding:.75rem 1rem;border-radius:0 8px 8px 0;margin-bottom:.75rem;">
                <div style="font-size:.68rem;color:#555;text-transform:uppercase;margin-bottom:.3rem;">Descripción</div>
                <div style="color:#c8cfe0;line-height:1.5;">${r.descripcion || '—'}</div>
            </div>
            ${r.observaciones ? `
            <div style="background:#1e2130;padding:.6rem .8rem;border-radius:8px;">
                <div style="font-size:.68rem;color:#555;text-transform:uppercase;margin-bottom:.2rem;">Observaciones</div>
                <div style="color:#888;">${r.observaciones}</div>
            </div>` : ''}
        </div>`,
        background: '#1a1d27', color: '#e8eaf0',
        confirmButtonColor: '#e05252',
        confirmButtonText: '<i class="bi bi-x"></i> Cerrar',
        width: '500px',
        customClass: { container: 'swal-over-modal' }
    });
};



const renderTablaReparaciones = (reparaciones) => {
    const wrap = document.getElementById('tablaReparacionesWrap');
    if (!reparaciones.length) {
        wrap.innerHTML = `
            <div style="text-align:center;padding:3rem;color:var(--text-muted);">
                <i class="bi bi-wrench-adjustable" style="font-size:3rem;opacity:.2;display:block;margin-bottom:1rem;"></i>
                <p>No hay reparaciones registradas</p>
            </div>`;
        return;
    }
    wrap.innerHTML = reparaciones.map(r => `
        <div class="svc-row" style="grid-template-columns:1.5fr 1fr 1fr 1fr 1fr auto;">
            <div><div class="svc-label">Tipo</div><div class="svc-val">${r.tipo_nombre}</div></div>
            <div><div class="svc-label">Estado</div>
                <div class="svc-val" style="color:${r.estado === 'En proceso' ? 'var(--accent)' : 'var(--success)'}">
                    ${r.estado}
                </div>
            </div>
            <div><div class="svc-label">Inicio</div><div class="svc-val">${fmtFecha(r.fecha_inicio)}</div></div>
            <div><div class="svc-label">Fin</div><div class="svc-val">${fmtFecha(r.fecha_fin)}</div></div>
            <div><div class="svc-label">Costo</div>
                <div class="svc-val">${r.costo ? 'Q ' + Number(r.costo).toLocaleString() : '—'}</div>
            </div>
            <div style="display:flex;gap:.4rem;align-items:center;">
                <button onclick="verDetalleReparacion(${JSON.stringify(r).replace(/"/g, '&quot;')})"
                    style="background:rgba(111,66,193,.15);border:1px solid rgba(111,66,193,.3);
                    color:#a78bfa;border-radius:6px;padding:.35rem .6rem;cursor:pointer;font-size:.8rem;">
                    <i class="bi bi-eye"></i>
                </button>
                <button onclick="editarReparacion(${JSON.stringify(r).replace(/"/g, '&quot;')})"
                    style="background:rgba(58,123,213,.15);border:1px solid rgba(58,123,213,.3);
                    color:#5b9bd5;border-radius:6px;padding:.35rem .6rem;cursor:pointer;font-size:.8rem;">
                    <i class="bi bi-pencil-square"></i>
                </button>
                <button onclick="eliminarReparacion(${r.id_reparacion})"
                    style="background:rgba(224,82,82,.15);border:1px solid rgba(224,82,82,.3);
                    color:var(--danger);border-radius:6px;padding:.35rem .6rem;cursor:pointer;font-size:.8rem;">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>
        </div>
        <div style="font-size:.75rem;color:var(--text-muted);margin-top:-.4rem;margin-bottom:.6rem;padding-left:.25rem;">
            ${r.descripcion}
            ${r.proveedor ? ' · <i class="bi bi-shop"></i> ' + r.proveedor : ''}
            ${r.responsable ? ' · <i class="bi bi-person"></i> ' + r.responsable : ''}
            ${r.km_al_momento ? ' · <i class="bi bi-speedometer"></i> ' + Number(r.km_al_momento).toLocaleString() + ' km' : ''}
        </div>`
    ).join('');
};

const guardarReparacion = async (forzar = false) => {
    const tipo = document.getElementById('repTipo').value;
    const desc = document.getElementById('repDescripcion').value;
    const fecha = document.getElementById('repFechaInicio').value;
    const km = document.getElementById('repKm').value;

    if (!tipo) { Swal.fire({ icon: 'info', title: 'Seleccione el tipo', text: 'Debe seleccionar un tipo antes de guardar.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b', customClass: { container: 'swal-over-modal' } }); return; }
    if (!desc) { Swal.fire({ icon: 'info', title: 'Ingrese una descripción', text: 'Debe describir la reparación.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b', customClass: { container: 'swal-over-modal' } }); return; }
    if (!km || parseInt(km) <= 0) { Swal.fire({ icon: 'info', title: 'Ingrese el KM', text: 'El kilometraje es obligatorio.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b', customClass: { container: 'swal-over-modal' } }); return; }
    if (!fecha) { Swal.fire({ icon: 'info', title: 'Fecha requerida', text: 'La fecha de inicio es requerida.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b', customClass: { container: 'swal-over-modal' } }); return; }

    const body = new FormData();
    body.append('placa', fichaPlacaActual);
    body.append('id_tipo_reparacion', tipo);
    body.append('descripcion', desc);
    body.append('fecha_inicio', fecha);
    body.append('fecha_fin', document.getElementById('repFechaFin').value);
    body.append('km_al_momento', km);
    body.append('costo', document.getElementById('repCosto').value);
    body.append('proveedor', document.getElementById('repProveedor').value);
    body.append('responsable', document.getElementById('repResponsable').value);
    body.append('estado', document.getElementById('repEstado').value);
    body.append('observaciones', document.getElementById('repObs').value);

    const esEdicion = reparacionEditandoId !== null;
    if (esEdicion) body.append('id_reparacion', reparacionEditandoId);
    if (forzar) body.append('forzar', '1');

    const url = esEdicion
        ? `${BASE}/API/vehiculos/reparacion/modificar`
        : `${BASE}/API/vehiculos/reparacion/guardar`;

    try {
        mostrarLoader('Guardando reparación...');
        const r = await fetch(url, { method: 'POST', body });
        const d = await r.json();

        if (d.codigo === 0 && d.bloqueo_duro) {
            Swal.fire({ icon: 'error', title: 'Registro bloqueado', text: d.mensaje, background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e05252', customClass: { container: 'swal-over-modal' } });
            return;
        }
        if (d.codigo === 2) {
            const conf = await Swal.fire({
                icon: 'warning', title: '¿Registrar de todas formas?',
                html: `${d.mensaje}<br><br><small style="color:#888;">Último KM: <strong>${d.ultimo_km ? Number(d.ultimo_km).toLocaleString() + ' km' : '—'}</strong></small>`,
                showCancelButton: true, confirmButtonText: 'Sí, registrar', cancelButtonText: 'Cancelar',
                confirmButtonColor: '#e8b84b', cancelButtonColor: '#555',
                background: '#1a1d27', color: '#e8eaf0', customClass: { container: 'swal-over-modal' }
            });
            if (conf.isConfirmed) await guardarReparacion(true);
            return;
        }

        Toast.fire({ icon: d.codigo === 1 ? 'success' : 'error', title: d.mensaje });
        if (d.codigo === 1) {
            reparacionEditandoId = null;
            ['repTipo', 'repDescripcion', 'repFechaFin', 'repCosto', 'repProveedor', 'repResponsable', 'repObs']
                .forEach(id => { document.getElementById(id).value = ''; });
            document.getElementById('repEstado').value = 'En proceso';
            resetFormReparacion();
            await abrirFicha(fichaPlacaActual);
            switchTab(document.querySelector('.ficha-tab[data-tab="reparaciones"]'), 'reparaciones');
            buscar();
        }
    } catch (err) {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    } finally {
        ocultarLoader();
    }
};

const editarReparacion = async (r) => {
    reparacionEditandoId = r.id_reparacion;
    const form = document.getElementById('formNuevaReparacion');
    const btn = document.getElementById('btnToggleFormReparacion');
    form.style.display = 'block';
    btn.innerHTML = '<i class="bi bi-x-circle"></i> Cancelar';
    await cargarTiposReparacion();
    document.getElementById('repTipo').value = r.id_tipo_reparacion;
    document.getElementById('repFechaInicio').value = r.fecha_inicio;
    document.getElementById('repFechaFin').value = r.fecha_fin || '';
    document.getElementById('repDescripcion').value = r.descripcion;
    document.getElementById('repKm').value = r.km_al_momento;
    document.getElementById('repCosto').value = r.costo || '';
    document.getElementById('repProveedor').value = r.proveedor || '';
    document.getElementById('repResponsable').value = r.responsable || '';
    document.getElementById('repEstado').value = r.estado;
    document.getElementById('repObs').value = r.observaciones || '';
    const btnGuardarRep = document.querySelector('#formNuevaReparacion button[onclick="guardarReparacion()"]');
    if (btnGuardarRep) {
        btnGuardarRep.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Actualizar Reparación';
        btnGuardarRep.style.background = 'linear-gradient(135deg,#3a7bd5,#2563b0)';
    }
    document.querySelectorAll('#tablaReparacionesWrap .svc-row').forEach(fila => {
        if (fila.innerHTML.includes(`eliminarReparacion(${r.id_reparacion})`)) {
            fila.style.opacity = '.3';
            fila.style.pointerEvents = 'none';
            const siguiente = fila.nextElementSibling;
            if (siguiente && !siguiente.classList.contains('svc-row')) siguiente.style.opacity = '.3';
        }
    });
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

const eliminarReparacion = async (id) => {
    const conf = await Swal.fire({
        icon: 'warning', title: '¿Eliminar reparación?', text: 'Esta acción no se puede deshacer.',
        showCancelButton: true, confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e05252', cancelButtonColor: '#3a7bd5',
        background: '#1a1d27', color: '#e8eaf0', customClass: { container: 'swal-over-modal' }
    });
    if (!conf.isConfirmed) return;
    const body = new FormData();
    body.append('id_reparacion', id);
    const r = await fetch(`${BASE}/API/vehiculos/reparacion/eliminar`, { method: 'POST', body });
    const d = await r.json();
    Toast.fire({ icon: d.codigo === 1 ? 'success' : 'error', title: d.mensaje });
    if (d.codigo === 1) {
        await abrirFicha(fichaPlacaActual);
        switchTab(document.querySelector('.ficha-tab[data-tab="reparaciones"]'), 'reparaciones');
    }
};

// ════════════════════════════════════════════════════════════════════════════
// ── SEGUROS ───────────────────────────────────════════════════════════════════
// ════════════════════════════════════════════════════════════════════════════

let seguroEditandoId = null;
let segurosData = [];

const seguroEstadoBadge = (fechaVence) => {
    if (!fechaVence) return `<span style="background:rgba(150,150,150,.15);color:#888;border:1px solid rgba(150,150,150,.25);padding:.2rem .6rem;border-radius:20px;font-size:.7rem;">Sin fecha</span>`;
    const hoy = new Date();
    const vence = new Date(fechaVence);
    const dias = Math.ceil((vence - hoy) / (1000 * 60 * 60 * 24));
    if (dias < 0) return `<span style="background:rgba(224,82,82,.15);color:var(--danger);border:1px solid rgba(224,82,82,.3);padding:.2rem .6rem;border-radius:20px;font-size:.7rem;"><i class="bi bi-shield-exclamation"></i> Vencido</span>`;
    if (dias <= 30) return `<span style="background:rgba(232,184,75,.15);color:var(--accent);border:1px solid rgba(232,184,75,.3);padding:.2rem .6rem;border-radius:20px;font-size:.7rem;"><i class="bi bi-shield-slash"></i> Vence en ${dias}d</span>`;
    return `<span style="background:rgba(76,175,125,.15);color:var(--success);border:1px solid rgba(76,175,125,.3);padding:.2rem .6rem;border-radius:20px;font-size:.7rem;"><i class="bi bi-shield-check"></i> Vigente</span>`;
};

const toggleFormSeguro = () => {
    const form = document.getElementById('formNuevoSeguroFicha');
    const btn = document.getElementById('btnToggleFormSeguro');
    const visible = form.style.display !== 'none';
    form.style.display = visible ? 'none' : 'block';
    btn.innerHTML = visible
        ? '<i class="bi bi-plus-circle"></i> Registrar Nuevo Seguro'
        : '<i class="bi bi-x-circle"></i> Cancelar';
    if (visible) { seguroEditandoId = null; limpiarCamposSeguros(); }
};

const resetFormSeguro = () => {
    const form = document.getElementById('formNuevoSeguroFicha');
    const btn = document.getElementById('btnToggleFormSeguro');
    if (form) form.style.display = 'none';
    if (btn) btn.innerHTML = '<i class="bi bi-plus-circle"></i> Registrar Nuevo Seguro';
    seguroEditandoId = null;
    limpiarCamposSeguros();
};

const limpiarCamposSeguros = () => {
    ['fsNumeroPoliza', 'fsAseguradora', 'fsTipoCobertura', 'fsFechaInicio',
        'fsFechaVenc', 'fsPrima', 'fsAgente', 'fsObs'].forEach(id => {
            const el = document.getElementById(id); if (el) el.value = '';
        });
    const areaSeg = document.getElementById('areaPolizaFicha');
    const nombreSeg = document.getElementById('seguroPdfNombre');
    if (areaSeg) {
        areaSeg.classList.remove('has-file');
        areaSeg.querySelector('.upload-icon i').className = 'bi bi-file-pdf';
        areaSeg.querySelector('.upload-label').innerHTML = `<span>Haz clic</span> o arrastra la póliza aquí<br><small>Solo PDF — máx. 10 MB</small>`;
    }
    if (nombreSeg) nombreSeg.style.display = 'none';
    const btnSave = document.querySelector('#formNuevoSeguroFicha button[onclick="guardarSeguroFicha()"]');
    if (btnSave) { btnSave.innerHTML = '<i class="bi bi-save me-1"></i> Guardar Seguro'; btnSave.style.background = 'linear-gradient(135deg,var(--success),#2e7d52)'; }
};

const renderTablaSeguros = (seguros) => {
    const wrap = document.getElementById('tablaSeguroWrap');
    if (!wrap) return;
    segurosData = seguros;
    if (!seguros.length) {
        wrap.innerHTML = `
            <div style="text-align:center;padding:3rem;color:var(--text-muted);">
                <i class="bi bi-shield-slash" style="font-size:3rem;opacity:.2;display:block;margin-bottom:1rem;"></i>
                <p>No hay seguros registrados para este vehículo</p>
            </div>`;
        return;
    }
    wrap.innerHTML = seguros.map(s => `
        <div class="svc-row" style="grid-template-columns:1.5fr 1fr 1fr 1fr 1fr auto;">
            <div><div class="svc-label">Póliza</div><div class="svc-val" style="font-weight:600;">${s.numero_poliza}</div></div>
            <div><div class="svc-label">Aseguradora</div><div class="svc-val">${s.aseguradora}</div></div>
            <div><div class="svc-label">Vigencia</div><div class="svc-val">${fmtFecha(s.fecha_inicio)} → ${fmtFecha(s.fecha_vencimiento)}</div></div>
            <div><div class="svc-label">Estado</div><div class="svc-val">${seguroEstadoBadge(s.fecha_vencimiento)}</div></div>
            <div><div class="svc-label">Costo Anual</div><div class="svc-val">${s.prima_anual ? 'Q ' + Number(s.prima_anual).toLocaleString() : '—'}</div></div>
            <div style="display:flex;gap:.4rem;align-items:center;flex-wrap:wrap;">
                ${s.pdf_poliza_url ? `<a href="${s.pdf_poliza_url}" target="_blank" style="background:rgba(232,184,75,.15);border:1px solid rgba(232,184,75,.3);color:var(--accent);border-radius:6px;padding:.35rem .6rem;font-size:.8rem;text-decoration:none;"><i class="bi bi-file-earmark-pdf"></i></a>` : ''}
                <button onclick="verSeguro(${s.id_seguro})" style="background:rgba(76,175,125,.15);border:1px solid rgba(76,175,125,.3);color:#4caf7d;border-radius:6px;padding:.35rem .6rem;cursor:pointer;font-size:.8rem;"><i class="bi bi-eye"></i></button>
                <button onclick="editarSeguro(${s.id_seguro})" style="background:rgba(58,123,213,.15);border:1px solid rgba(58,123,213,.3);color:#5b9bd5;border-radius:6px;padding:.35rem .6rem;cursor:pointer;font-size:.8rem;"><i class="bi bi-pencil-square"></i></button>
                <button onclick="eliminarSeguro(${s.id_seguro})" style="background:rgba(224,82,82,.15);border:1px solid rgba(224,82,82,.3);color:var(--danger);border-radius:6px;padding:.35rem .6rem;cursor:pointer;font-size:.8rem;"><i class="bi bi-trash3"></i></button>
            </div>
        </div>
        <div style="font-size:.75rem;color:var(--text-muted);margin-top:-.4rem;margin-bottom:.6rem;padding-left:.25rem;">
            ${s.tipo_cobertura ? '<i class="bi bi-patch-check"></i> ' + s.tipo_cobertura : ''}
            ${s.contacto_agente ? ' · <i class="bi bi-person-lines-fill"></i> ' + s.contacto_agente : ''}
            ${s.observaciones ? ' · ' + s.observaciones : ''}
        </div>`
    ).join('');
};

const guardarSeguro = async () => {
    const poliza = document.getElementById('fsNumeroPoliza').value.trim();
    const aseguradora = document.getElementById('fsAseguradora').value.trim();
    const cobertura = document.getElementById('fsTipoCobertura').value;
    const fechaInicio = document.getElementById('fsFechaInicio').value;
    const fechaVenc = document.getElementById('fsFechaVenc').value;
    const prima = document.getElementById('fsPrima').value;
    const agente = document.getElementById('fsAgente').value.trim();
    const telefono = document.getElementById('fsTelefono').value.trim();
    const esEdicion = seguroEditandoId !== null;

    if (!aseguradora) { Swal.fire({ icon: 'warning', title: 'Aseguradora requerida', text: 'Ingresa el nombre de la aseguradora.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b', customClass: { container: 'swal-over-modal' } }); document.getElementById('fsAseguradora').focus(); return; }
    if (!poliza) { Swal.fire({ icon: 'warning', title: 'Póliza requerida', text: 'Ingresa el número de póliza.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b', customClass: { container: 'swal-over-modal' } }); document.getElementById('fsNumeroPoliza').focus(); return; }
    if (!fechaInicio) { Swal.fire({ icon: 'warning', title: 'Fecha inicio requerida', text: 'Selecciona la fecha de inicio.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b', customClass: { container: 'swal-over-modal' } }); document.getElementById('fsFechaInicio').focus(); return; }
    if (!fechaVenc) { Swal.fire({ icon: 'warning', title: 'Fecha vencimiento requerida', text: 'Selecciona la fecha de vencimiento.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b', customClass: { container: 'swal-over-modal' } }); document.getElementById('fsFechaVenc').focus(); return; }
    if (fechaVenc <= fechaInicio) { Swal.fire({ icon: 'warning', title: 'Fechas inválidas', text: 'La fecha de vencimiento debe ser posterior a la de inicio.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b', customClass: { container: 'swal-over-modal' } }); document.getElementById('fsFechaVenc').focus(); return; }
    if (!prima || isNaN(parseFloat(prima)) || parseFloat(prima) < 0) { Swal.fire({ icon: 'warning', title: 'Prima requerida', text: 'Ingresa la prima anual (puede ser 0).', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b', customClass: { container: 'swal-over-modal' } }); document.getElementById('fsPrima').focus(); return; }
    if (!agente) { Swal.fire({ icon: 'warning', title: 'Agente requerido', text: 'Ingresa el nombre del agente.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b', customClass: { container: 'swal-over-modal' } }); document.getElementById('fsAgente').focus(); return; }
    if (!telefono) { Swal.fire({ icon: 'warning', title: 'Teléfono requerido', text: 'Ingresa el teléfono del agente.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b', customClass: { container: 'swal-over-modal' } }); document.getElementById('fsTelefono').focus(); return; }

    const inputPdfSeg = document.getElementById('fsArchivo');
    if (!esEdicion && (!inputPdfSeg || !inputPdfSeg.files[0])) { Swal.fire({ icon: 'warning', title: 'Póliza PDF requerida', text: 'Debes subir el archivo PDF de la póliza.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b', customClass: { container: 'swal-over-modal' } }); return; }
    if (inputPdfSeg?.files[0] && inputPdfSeg.files[0].type !== 'application/pdf') { Swal.fire({ icon: 'warning', title: 'Archivo inválido', text: 'La póliza debe ser un archivo PDF.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b', customClass: { container: 'swal-over-modal' } }); return; }

    const body = new FormData();
    body.append('placa', fichaPlacaActual);
    body.append('numero_poliza', poliza);
    body.append('aseguradora', aseguradora);
    body.append('tipo_cobertura', cobertura);
    body.append('fecha_inicio', fechaInicio);
    body.append('fecha_vencimiento', fechaVenc);
    body.append('prima_anual', prima);
    body.append('agente_contacto', agente);
    body.append('telefono_agente', telefono);
    body.append('observaciones', document.getElementById('fsObs').value);
    if (inputPdfSeg?.files[0]) body.append('archivo_poliza', inputPdfSeg.files[0]);
    if (esEdicion) body.append('id_seguro', seguroEditandoId);

    const url = esEdicion
        ? `${BASE}/API/vehiculos/seguros/modificar`
        : `${BASE}/API/vehiculos/seguros/guardar`;

    try {
        mostrarLoader('Guardando seguro...');
        const r = await fetch(url, { method: 'POST', body });
        const d = await r.json();
        Toast.fire({ icon: d.codigo === 1 ? 'success' : 'error', title: d.mensaje });
        if (d.codigo === 1) {
            resetFormSeguro();
            await abrirFicha(fichaPlacaActual);
            switchTab(document.querySelector('.ficha-tab[data-tab="seguro"]'), 'seguro');
            buscar();
        }
    } catch (err) {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    } finally {
        ocultarLoader();
    }
};

const editarSeguro = (id) => {
    const s = segurosData.find(x => x.id_seguro == id);
    if (!s) return;
    seguroEditandoId = s.id_seguro;
    const form = document.getElementById('formNuevoSeguroFicha');
    const btn = document.getElementById('btnToggleFormSeguro');
    form.style.display = 'block';
    btn.innerHTML = '<i class="bi bi-x-circle"></i> Cancelar';
    document.getElementById('fsNumeroPoliza').value = s.numero_poliza || '';
    document.getElementById('fsAseguradora').value = s.aseguradora || '';
    document.getElementById('fsTipoCobertura').value = s.tipo_cobertura || '';
    document.getElementById('fsFechaInicio').value = s.fecha_inicio || '';
    document.getElementById('fsFechaVenc').value = s.fecha_vencimiento || '';
    document.getElementById('fsPrima').value = s.prima_anual || '';
    document.getElementById('fsAgente').value = s.agente_contacto || '';
    document.getElementById('fsTelefono').value = s.telefono_agente || '';
    document.getElementById('fsObs').value = s.observaciones || '';
    const areaPoliza = document.getElementById('areaPolizaFicha');
    if (areaPoliza) {
        if (s.pdf_poliza_url) {
            areaPoliza.classList.add('has-file');
            areaPoliza.querySelector('.upload-icon i').className = 'bi bi-check-circle-fill';
            areaPoliza.querySelector('.upload-label').innerHTML = `
                <span style="color:var(--success)">Póliza cargada</span><br>
                <small><a href="${s.pdf_poliza_url}" target="_blank" style="color:var(--accent);text-decoration:none;">
                    <i class="bi bi-file-earmark-pdf"></i> Ver PDF actual
                </a> &nbsp;·&nbsp; Sube uno nuevo para reemplazarlo</small>`;
        } else {
            areaPoliza.classList.remove('has-file');
            areaPoliza.querySelector('.upload-icon i').className = 'bi bi-file-pdf';
            areaPoliza.querySelector('.upload-label').innerHTML = `<span>Subir PDF</span>`;
        }
    }
    const btnSave = document.querySelector('#formNuevoSeguroFicha button[onclick="guardarSeguroFicha()"]');
    if (btnSave) { btnSave.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Actualizar Seguro'; btnSave.style.background = 'linear-gradient(135deg,#3a7bd5,#2563b0)'; }
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

const eliminarSeguro = async (id) => {
    const conf = await Swal.fire({
        icon: 'warning', title: '¿Eliminar seguro?', text: 'Se eliminará la póliza y sus archivos.',
        showCancelButton: true, confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e05252', cancelButtonColor: '#3a7bd5',
        background: '#1a1d27', color: '#e8eaf0', customClass: { container: 'swal-over-modal' }
    });
    if (!conf.isConfirmed) return;
    const body = new FormData();
    body.append('id_seguro', id);
    try {
        const r = await fetch(`${BASE}/API/vehiculos/seguros/eliminar`, { method: 'POST', body });
        const d = await r.json();
        Toast.fire({ icon: d.codigo === 1 ? 'success' : 'error', title: d.mensaje });
        if (d.codigo === 1) { await abrirFicha(fichaPlacaActual); switchTab(document.querySelector('.ficha-tab[data-tab="seguro"]'), 'seguro'); buscar(); }
    } catch (err) { Toast.fire({ icon: 'error', title: 'Error de conexión' }); }
};

const verSeguro = (id) => {
    const s = segurosData.find(x => x.id_seguro == id);
    if (!s) return;
    const hoy = new Date();
    const vence = s.fecha_vencimiento ? new Date(s.fecha_vencimiento) : null;
    const dias = vence ? Math.ceil((vence - hoy) / (1000 * 60 * 60 * 24)) : null;
    const estadoColor = { 'Vigente': '#4caf7d', 'Vencido': '#e05252', 'Cancelado': '#7c8398' };
    let estadoHTML = '';
    if (s.estado === 'Vigente' && dias !== null) {
        if (dias <= 0) estadoHTML = `<span style="background:rgba(224,82,82,.2);color:#e05252;border:1px solid rgba(224,82,82,.4);padding:.25rem .75rem;border-radius:20px;font-size:.75rem;font-weight:700;">VENCIDO</span>`;
        else if (dias <= 30) estadoHTML = `<span style="background:rgba(232,184,75,.2);color:#e8b84b;border:1px solid rgba(232,184,75,.4);padding:.25rem .75rem;border-radius:20px;font-size:.75rem;font-weight:700;">⚠ Vence en ${dias} días</span>`;
        else estadoHTML = `<span style="background:rgba(76,175,125,.2);color:#4caf7d;border:1px solid rgba(76,175,125,.4);padding:.25rem .75rem;border-radius:20px;font-size:.75rem;font-weight:700;">✓ VIGENTE</span>`;
    } else {
        const color = estadoColor[s.estado] || '#888';
        estadoHTML = `<span style="background:${color}22;color:${color};border:1px solid ${color}44;padding:.25rem .75rem;border-radius:20px;font-size:.75rem;font-weight:700;">${s.estado}</span>`;
    }
    const polizaHTML = s.pdf_poliza_url
        ? `<a href="${s.pdf_poliza_url}" target="_blank" style="display:inline-flex;align-items:center;gap:.4rem;background:rgba(232,184,75,.1);border:1px solid rgba(232,184,75,.25);color:var(--accent);padding:.35rem .75rem;border-radius:6px;font-size:.78rem;text-decoration:none;margin:.2rem;"><i class="bi bi-file-earmark-pdf"></i> Ver Póliza PDF</a>`
        : '<span style="color:#555;font-size:.78rem;">Sin póliza adjunta</span>';
    Swal.fire({
        title: `<span style="font-family:Rajdhani,sans-serif;font-size:1.1rem;"><i class="bi bi-shield-check" style="color:#4caf7d;"></i> Seguro — ${s.aseguradora}</span>`,
        html: `
        <div style="text-align:left;font-size:.82rem;">
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap;">
                ${estadoHTML}
                <span style="color:#888;font-size:.78rem;"><i class="bi bi-file-text"></i> Póliza: <strong style="color:#e8eaf0;">${s.numero_poliza}</strong></span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:1rem;">
                <div style="background:#1e2130;padding:.6rem .8rem;border-radius:8px;"><div style="font-size:.68rem;color:#555;text-transform:uppercase;margin-bottom:.2rem;">Aseguradora</div><div style="color:#c8cfe0;font-weight:600;">${s.aseguradora}</div></div>
                <div style="background:#1e2130;padding:.6rem .8rem;border-radius:8px;"><div style="font-size:.68rem;color:#555;text-transform:uppercase;margin-bottom:.2rem;">Tipo de Cobertura</div><div style="color:#c8cfe0;font-weight:600;">${s.tipo_cobertura || '—'}</div></div>
                <div style="background:#1e2130;padding:.6rem .8rem;border-radius:8px;"><div style="font-size:.68rem;color:#555;text-transform:uppercase;margin-bottom:.2rem;">Fecha Inicio</div><div style="color:#c8cfe0;font-weight:600;">${s.fecha_inicio || '—'}</div></div>
                <div style="background:#1e2130;padding:.6rem .8rem;border-radius:8px;"><div style="font-size:.68rem;color:#555;text-transform:uppercase;margin-bottom:.2rem;">Fecha Vencimiento</div><div style="color:${dias !== null && dias <= 30 ? '#e8b84b' : '#c8cfe0'};font-weight:600;">${s.fecha_vencimiento || '—'}${dias !== null && dias > 0 ? `<span style="font-size:.7rem;color:#888;"> (${dias}d)</span>` : ''}</div></div>
                <div style="background:#1e2130;padding:.6rem .8rem;border-radius:8px;"><div style="font-size:.68rem;color:#555;text-transform:uppercase;margin-bottom:.2rem;">Prima Anual</div><div style="color:#4caf7d;font-weight:700;">${s.prima_anual ? 'Q ' + Number(s.prima_anual).toLocaleString() : '—'}</div></div>
                <div style="background:#1e2130;padding:.6rem .8rem;border-radius:8px;"><div style="font-size:.68rem;color:#555;text-transform:uppercase;margin-bottom:.2rem;">Agente</div><div style="color:#c8cfe0;">${s.agente_contacto || '—'}</div></div>
            </div>
            ${s.telefono_agente ? `<div style="background:#1e2130;padding:.6rem .8rem;border-radius:8px;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;"><i class="bi bi-telephone-fill" style="color:#4caf7d;"></i><span style="color:#c8cfe0;">${s.telefono_agente}</span></div>` : ''}
            ${s.observaciones ? `<div style="background:#1e2130;padding:.6rem .8rem;border-radius:8px;margin-bottom:1rem;"><div style="font-size:.68rem;color:#555;text-transform:uppercase;margin-bottom:.2rem;">Observaciones</div><div style="color:#888;">${s.observaciones}</div></div>` : ''}
            <div><div style="font-size:.68rem;color:#555;text-transform:uppercase;margin-bottom:.5rem;"><i class="bi bi-file-earmark-text"></i> Documento de Póliza</div><div>${polizaHTML}</div></div>
        </div>`,
        background: '#1a1d27', color: '#e8eaf0',
        confirmButtonColor: '#3a7bd5', confirmButtonText: '<i class="bi bi-x"></i> Cerrar',
        width: '580px', customClass: { container: 'swal-over-modal' }
    });
};
window.verSeguro = verSeguro;

const inputSeguroPdf = document.getElementById('fsArchivo');
if (inputSeguroPdf) {
    inputSeguroPdf.addEventListener('change', () => {
        const file = inputSeguroPdf.files[0];
        if (!file) return;
        const area = document.getElementById('areaPolizaFicha');
        if (area) {
            area.classList.add('has-file');
            area.querySelector('.upload-icon i').className = 'bi bi-check-circle-fill';
            area.querySelector('.upload-label').innerHTML = `<span style="color:var(--success)">${file.name}</span><br><small>PDF seleccionado</small>`;
        }
    });
}

// ════════════════════════════════════════════════════════════════════════════
// ── ACCIDENTES ────────────────────────────────────────────────────────────────
// ════════════════════════════════════════════════════════════════════════════

let accidenteEditandoId = null;
let accidentesData = [];

const toggleFormAccidente = () => {
    const form = document.getElementById('formNuevoAccidente');
    const btn = document.getElementById('btnToggleFormAccidente');
    const visible = form.style.display !== 'none';
    form.style.display = visible ? 'none' : 'block';
    btn.innerHTML = visible
        ? '<i class="bi bi-plus-circle"></i> Registrar Accidente / Choque'
        : '<i class="bi bi-x-circle"></i> Cancelar';
    if (visible) { accidenteEditandoId = null; limpiarCamposAccidente(); }
};

const resetFormAccidente = () => {
    const form = document.getElementById('formNuevoAccidente');
    const btn = document.getElementById('btnToggleFormAccidente');
    if (form) form.style.display = 'none';
    if (btn) btn.innerHTML = '<i class="bi bi-plus-circle"></i> Registrar Accidente / Choque';
    accidenteEditandoId = null;
    limpiarCamposAccidente();
};

const limpiarCamposAccidente = () => {
    ['acFecha', 'acLugar', 'acDescripcion', 'acConductor', 'acCostoEst', 'acCostoReal', 'acExpediente', 'acObs']
        .forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    const selEstado = document.getElementById('acEstado');
    if (selEstado) selEstado.value = 'Reportado';
    const areaInformeR = document.getElementById('areaInformeAcc');
    const inputInformeR = document.getElementById('acInforme');
    if (areaInformeR) { areaInformeR.classList.remove('has-file'); areaInformeR.querySelector('.upload-icon i').className = 'bi bi-file-pdf'; areaInformeR.querySelector('.upload-label').innerHTML = `<span>Subir informe PDF</span>`; }
    if (inputInformeR) inputInformeR.value = '';
    resetFotosAcc();
    const btnSave = document.querySelector('#formNuevoAccidente button[onclick="guardarAccidente()"]');
    if (btnSave) { btnSave.innerHTML = '<i class="bi bi-save me-1"></i> Guardar Accidente'; btnSave.style.background = 'linear-gradient(135deg,var(--danger),#c93030)'; }
};

const renderTablaAccidentes = (accidentes) => {
    accidentesData = accidentes;
    const wrap = document.getElementById('tablaAccidentesWrap');
    if (!wrap) return;
    if (!accidentes.length) {
        wrap.innerHTML = `
            <div style="text-align:center;padding:3rem;color:var(--text-muted);">
                <i class="bi bi-cone-striped" style="font-size:3rem;opacity:.2;display:block;margin-bottom:1rem;"></i>
                <p>No hay accidentes registrados para este vehículo</p>
            </div>`;
        return;
    }
    const costoTotal = accidentes.reduce((sum, a) => sum + (parseFloat(a.costo_reparacion) || 0) + (parseFloat(a.costo_danos) || 0), 0);
    const resumenHTML = costoTotal > 0
        ? `<div style="background:rgba(224,82,82,.08);border:1px solid rgba(224,82,82,.2);border-radius:8px;padding:.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;gap:.75rem;">
        <span style="color:var(--danger);font-size:1.25rem;font-weight:700;font-family:'Rajdhani',sans-serif;">Q</span>
        <div>
            <div style="font-size:.75rem;color:var(--text-muted);">Costo total acumulado</div>
            <div style="font-weight:700;color:var(--danger);">Q ${Number(costoTotal).toLocaleString()}</div>
        </div>
       </div>`
        : '';
    const estadoColor = (e) => ({ 'Cerrado': 'var(--success)', 'En proceso': 'var(--accent)', 'Pendiente': '#888' }[e] || 'inherit');
    const culpaBadge = (c) => { if (!c) return ''; const map = { 'Propio': 'rgba(224,82,82,.15)', 'Tercero': 'rgba(58,123,213,.15)', 'Compartida': 'rgba(232,184,75,.15)', 'Sin determinar': 'rgba(150,150,150,.15)' }; return `<span style="background:${map[c] || 'rgba(150,150,150,.15)'};padding:.15rem .5rem;border-radius:20px;font-size:.7rem;color:var(--text-secondary);">${c}</span>`; };
    wrap.innerHTML = resumenHTML + accidentes.map(a => `
        <div class="svc-row" style="grid-template-columns:1fr 1fr 1fr 1fr 1fr auto;">
            <div><div class="svc-label">Tipo</div><div class="svc-val">${a.tipo_accidente}</div></div>
            <div><div class="svc-label">Fecha</div><div class="svc-val">${fmtFecha(a.fecha_accidente)}</div></div>
            <div><div class="svc-label">Estado</div><div class="svc-val" style="color:${estadoColor(a.estado)}">${a.estado}</div></div>
            <div><div class="svc-label">Costo Daños</div><div class="svc-val">${a.costo_danos ? 'Q ' + Number(a.costo_danos).toLocaleString() : '—'}</div></div>
            <div><div class="svc-label">Costo Reparación</div><div class="svc-val">${a.costo_reparacion ? 'Q ' + Number(a.costo_reparacion).toLocaleString() : '—'}</div></div>
            <div style="display:flex;gap:.4rem;align-items:center;flex-wrap:wrap;">
                <button onclick="verAccidente(${a.id_accidente})" style="background:rgba(111,66,193,.15);border:1px solid rgba(111,66,193,.3);color:#a78bfa;border-radius:6px;padding:.35rem .6rem;cursor:pointer;font-size:.8rem;"><i class="bi bi-eye"></i></button>
                <button onclick="editarAccidente(${a.id_accidente})" style="background:rgba(58,123,213,.15);border:1px solid rgba(58,123,213,.3);color:#5b9bd5;border-radius:6px;padding:.35rem .6rem;cursor:pointer;font-size:.8rem;"><i class="bi bi-pencil-square"></i></button>
                <button onclick="eliminarAccidente(${a.id_accidente})" style="background:rgba(224,82,82,.15);border:1px solid rgba(224,82,82,.3);color:var(--danger);border-radius:6px;padding:.35rem .6rem;cursor:pointer;font-size:.8rem;"><i class="bi bi-trash3"></i></button>
            </div>
        </div>
        <div style="font-size:.75rem;color:var(--text-muted);margin-top:-.4rem;margin-bottom:.6rem;padding-left:.25rem;">
            ${a.lugar ? '<i class="bi bi-geo-alt"></i> ' + a.lugar : ''}
            ${a.conductor_responsable ? ' · <i class="bi bi-person-fill"></i> ' + a.conductor_responsable : ''}
            ${a.culpabilidad ? ' · ' + culpaBadge(a.culpabilidad) : ''}
            ${a.no_expediente ? ' · <i class="bi bi-journal-text"></i> Exp. ' + a.no_expediente : ''}
            ${a.km_al_momento ? ' · <i class="bi bi-speedometer"></i> ' + Number(a.km_al_momento).toLocaleString() + ' km' : ''}
        </div>
        ${a.descripcion ? `<div style="font-size:.75rem;color:var(--text-secondary);margin-top:-.3rem;margin-bottom:.6rem;padding-left:.25rem;padding-right:1rem;">${a.descripcion}</div>` : ''}`
    ).join('');
};

const guardarAccidente = async () => {
    const fecha = document.getElementById('acFecha').value;
    const lugar = document.getElementById('acLugar').value.trim();
    const tipo = document.getElementById('acTipo').value;
    const desc = document.getElementById('acDescripcion').value.trim();
    const conductor = document.getElementById('acConductor').value.trim();
    const esEdicion = accidenteEditandoId !== null;

    if (!fecha) { Swal.fire({ icon: 'warning', title: 'Fecha requerida', text: 'Selecciona la fecha del accidente.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b', customClass: { container: 'swal-over-modal' } }); document.getElementById('acFecha').focus(); return; }
    if (!lugar) { Swal.fire({ icon: 'warning', title: 'Lugar requerido', text: 'Ingresa el lugar del accidente.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b', customClass: { container: 'swal-over-modal' } }); document.getElementById('acLugar').focus(); return; }
    if (!tipo) { Swal.fire({ icon: 'warning', title: 'Tipo requerido', text: 'Selecciona el tipo de accidente.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b', customClass: { container: 'swal-over-modal' } }); document.getElementById('acTipo').focus(); return; }
    if (!desc) { Swal.fire({ icon: 'warning', title: 'Descripción requerida', text: 'Describe cómo ocurrió el accidente.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b', customClass: { container: 'swal-over-modal' } }); document.getElementById('acDescripcion').focus(); return; }
    if (!conductor) { Swal.fire({ icon: 'warning', title: 'Conductor requerido', text: 'Ingresa el nombre del conductor responsable.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b', customClass: { container: 'swal-over-modal' } }); document.getElementById('acConductor').focus(); return; }

    const acInformeEl = document.getElementById('acInforme');
    if (!esEdicion && (!acInformeEl || !acInformeEl.files[0])) { Swal.fire({ icon: 'warning', title: 'Informe policial requerido', text: 'Debes subir el informe policial en PDF.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b', customClass: { container: 'swal-over-modal' } }); return; }
    if (acInformeEl?.files[0] && acInformeEl.files[0].type !== 'application/pdf') { Swal.fire({ icon: 'warning', title: 'Archivo inválido', text: 'El informe policial debe ser un archivo PDF.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b', customClass: { container: 'swal-over-modal' } }); return; }

    const body = new FormData();
    body.append('placa', fichaPlacaActual);
    body.append('fecha_accidente', fecha);
    body.append('lugar', lugar);
    body.append('tipo_accidente', tipo);
    body.append('descripcion', desc);
    body.append('conductor_responsable', conductor);
    body.append('costo_estimado', document.getElementById('acCostoEst').value);
    body.append('costo_real', document.getElementById('acCostoReal').value);
    body.append('estado_caso', document.getElementById('acEstado').value);
    body.append('numero_expediente', document.getElementById('acExpediente').value);
    body.append('observaciones', document.getElementById('acObs').value);

    document.querySelectorAll('#fotosAccContainer input[type="file"]').forEach((input, i) => {
        if (input.files[0]) body.append(`archivo_foto_${i + 1}`, input.files[0]);
    });
    if (acInformeEl?.files[0]) body.append('archivo_informe', acInformeEl.files[0]);
    if (esEdicion) body.append('id_accidente', accidenteEditandoId);

    const url = esEdicion
        ? `${BASE}/API/vehiculos/accidentes/modificar`
        : `${BASE}/API/vehiculos/accidentes/guardar`;

    try {
        mostrarLoader('Guardando accidente...');
        const r = await fetch(url, { method: 'POST', body });
        const d = await r.json();
        Toast.fire({ icon: d.codigo === 1 ? 'success' : 'error', title: d.mensaje });
        if (d.codigo === 1) {
            resetFormAccidente();
            await abrirFicha(fichaPlacaActual);
            switchTab(document.querySelector('.ficha-tab[data-tab="accidentes"]'), 'accidentes');
            buscar();
        }
    } catch (err) {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    } finally {
        ocultarLoader();
    }
};

const editarAccidente = (id) => {
    const a = accidentesData.find(x => x.id_accidente == id);
    if (!a) return;
    accidenteEditandoId = a.id_accidente;
    const form = document.getElementById('formNuevoAccidente');
    const btn = document.getElementById('btnToggleFormAccidente');
    form.style.display = 'block';
    btn.innerHTML = '<i class="bi bi-x-circle"></i> Cancelar';
    const _sv = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
    _sv('acFecha', a.fecha_accidente);
    _sv('acTipo', a.tipo_accidente);
    _sv('acLugar', a.lugar);
    _sv('acDescripcion', a.descripcion);
    _sv('acConductor', a.conductor_responsable);
    _sv('acCostoEst', a.costo_estimado ?? a.costo_danos);
    _sv('acCostoReal', a.costo_real ?? a.costo_reparacion);
    _sv('acExpediente', a.numero_expediente ?? a.no_expediente);
    _sv('acObs', a.observaciones);
    const selEdAcc = document.getElementById('acEstado');
    if (selEdAcc) selEdAcc.value = a.estado_caso ?? a.estado ?? 'Reportado';
    const btnSave = document.querySelector('#formNuevoAccidente button[onclick="guardarAccidente()"]');
    if (btnSave) { btnSave.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Actualizar Accidente'; btnSave.style.background = 'linear-gradient(135deg,#3a7bd5,#2563b0)'; }
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

const eliminarAccidente = async (id) => {
    const conf = await Swal.fire({
        icon: 'warning', title: '¿Eliminar accidente?', text: 'Esta acción no se puede deshacer.',
        showCancelButton: true, confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e05252', cancelButtonColor: '#3a7bd5',
        background: '#1a1d27', color: '#e8eaf0', customClass: { container: 'swal-over-modal' }
    });
    if (!conf.isConfirmed) return;
    const body = new FormData();
    body.append('id_accidente', id);
    try {
        const r = await fetch(`${BASE}/API/vehiculos/accidentes/eliminar`, { method: 'POST', body });
        const d = await r.json();
        Toast.fire({ icon: d.codigo === 1 ? 'success' : 'error', title: d.mensaje });
        if (d.codigo === 1) { await abrirFicha(fichaPlacaActual); switchTab(document.querySelector('.ficha-tab[data-tab="accidentes"]'), 'accidentes'); }
    } catch (err) { Toast.fire({ icon: 'error', title: 'Error de conexión' }); }
};

const verAccidente = (id) => {
    const a = accidentesData.find(x => x.id_accidente == id);
    if (!a) return;
    const estadoColor = {
        'Cerrado': '#4caf7d',
        'En trámite': '#e8b84b',
        'Reportado': '#5b9bd5',
        'Sin seguro': '#e05252'
    };
    const dias = (() => {
        if (!a.fecha_vencimiento) return null;
        const hoy = new Date();
        const vence = new Date(a.fecha_vencimiento);
        return Math.ceil((vence - hoy) / (1000 * 60 * 60 * 24));
    })();

    // ── Fotos como links a nueva pestaña ──────────────────────────────
    const fotosURLs = [a.foto_1_url, a.foto_2_url, a.foto_3_url, a.foto_4_url].filter(Boolean);
    const fotosHTML = fotosURLs.length
        ? fotosURLs.map((url, i) =>
            `<a href="${url}" target="_blank"
                style="display:inline-flex;align-items:center;gap:.4rem;
                background:rgba(58,123,213,.1);border:1px solid rgba(58,123,213,.25);
                color:#5b9bd5;padding:.35rem .75rem;border-radius:6px;
                font-size:.78rem;text-decoration:none;margin:.2rem;">
                <i class="bi bi-image"></i> Foto ${i + 1}
            </a>`).join('')
        : '<span style="color:#555;font-size:.78rem;">Sin fotos adjuntas</span>';

    const informeHTML = a.informe_url
        ? `<a href="${a.informe_url}" target="_blank"
                style="display:inline-flex;align-items:center;gap:.4rem;
                background:rgba(232,184,75,.1);border:1px solid rgba(232,184,75,.25);
                color:var(--accent);padding:.35rem .75rem;border-radius:6px;
                font-size:.78rem;text-decoration:none;margin:.2rem;">
                <i class="bi bi-file-earmark-pdf"></i> Informe Policial
            </a>`
        : '<span style="color:#555;font-size:.78rem;">Sin informe adjunto</span>';

    const color = estadoColor[a.estado] || '#888';

    Swal.fire({
        title: `<span style="font-family:Rajdhani,sans-serif;font-size:1.1rem;">
            <i class="bi bi-cone-striped" style="color:#e05252;"></i>
            Accidente — ${a.tipo_accidente}
        </span>`,
        html: `
        <div style="text-align:left;font-size:.82rem;">
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap;">
                <span style="background:${color}22;color:${color};
                    border:1px solid ${color}44;padding:.25rem .75rem;
                    border-radius:20px;font-size:.75rem;font-weight:700;">
                    ${a.estado}
                </span>
                <span style="color:#888;font-size:.78rem;">
                    <i class="bi bi-calendar3"></i> ${fmtFecha(a.fecha_accidente)}
                </span>
                ${a.no_expediente
                ? `<span style="color:#888;font-size:.78rem;">
                           <i class="bi bi-journal-text"></i> Exp. ${a.no_expediente}
                       </span>`
                : ''}
            </div>
            <div style="background:#1e2130;border-left:3px solid #e05252;
                padding:.75rem 1rem;border-radius:0 8px 8px 0;margin-bottom:1rem;
                color:#c8cfe0;line-height:1.5;">
                ${a.descripcion}
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:1rem;">
                <div style="background:#1e2130;padding:.6rem .8rem;border-radius:8px;">
                    <div style="font-size:.68rem;color:#555;text-transform:uppercase;margin-bottom:.2rem;">Lugar</div>
                    <div style="color:#c8cfe0;">${a.lugar || '—'}</div>
                </div>
                <div style="background:#1e2130;padding:.6rem .8rem;border-radius:8px;">
                    <div style="font-size:.68rem;color:#555;text-transform:uppercase;margin-bottom:.2rem;">Conductor</div>
                    <div style="color:#c8cfe0;">${a.conductor_responsable || '—'}</div>
                </div>
                <div style="background:#1e2130;padding:.6rem .8rem;border-radius:8px;">
                    <div style="font-size:.68rem;color:#555;text-transform:uppercase;margin-bottom:.2rem;">Costo Daños</div>
                    <div style="color:#e05252;font-weight:700;">
                        ${a.costo_danos ? 'Q ' + Number(a.costo_danos).toLocaleString() : '—'}
                    </div>
                </div>
                <div style="background:#1e2130;padding:.6rem .8rem;border-radius:8px;">
                    <div style="font-size:.68rem;color:#555;text-transform:uppercase;margin-bottom:.2rem;">Costo Reparación</div>
                    <div style="color:#e05252;font-weight:700;">
                        ${a.costo_reparacion ? 'Q ' + Number(a.costo_reparacion).toLocaleString() : '—'}
                    </div>
                </div>
            </div>
            ${(a.costo_danos || a.costo_reparacion) ? `
            <div style="background:rgba(224,82,82,.08);border:1px solid rgba(224,82,82,.2);
                border-radius:8px;padding:.6rem 1rem;margin-bottom:1rem;
                display:flex;justify-content:space-between;align-items:center;">
                <span style="color:#888;font-size:.78rem;">Costo total</span>
                <span style="color:#e05252;font-weight:700;font-size:1rem;font-family:Rajdhani,sans-serif;">
                    Q ${Number((parseFloat(a.costo_danos) || 0) + (parseFloat(a.costo_reparacion) || 0)).toLocaleString()}
                </span>
            </div>` : ''}
            ${a.observaciones ? `
            <div style="background:#1e2130;padding:.6rem .8rem;border-radius:8px;margin-bottom:1rem;">
                <div style="font-size:.68rem;color:#555;text-transform:uppercase;margin-bottom:.2rem;">Observaciones</div>
                <div style="color:#888;">${a.observaciones}</div>
            </div>` : ''}
            <div style="margin-bottom:.75rem;">
                <div style="font-size:.68rem;color:#555;text-transform:uppercase;
                    margin-bottom:.5rem;">
                    <i class="bi bi-images"></i> Fotografías
                </div>
                <div>${fotosHTML}</div>
            </div>
            <div>
                <div style="font-size:.68rem;color:#555;text-transform:uppercase;
                    margin-bottom:.5rem;">
                    <i class="bi bi-file-earmark-text"></i> Informe Policial
                </div>
                <div>${informeHTML}</div>
            </div>
        </div>`,
        background: '#1a1d27', color: '#e8eaf0',
        confirmButtonColor: '#6f42c1',
        confirmButtonText: '<i class="bi bi-x"></i> Cerrar',
        width: '600px',
        customClass: { container: 'swal-over-modal' }
    });
};
window.verAccidente = verAccidente;



const verHistorialAccidentes = async () => {
    try {
        mostrarLoader('Cargando historial...');
        const r = await fetch(`${BASE}/API/vehiculos/hoja-vida-accidentes?placa=${fichaPlacaActual}`);
        const d = await r.json();
        if (d.codigo !== 1) {
            Toast.fire({ icon: 'error', title: 'Error al cargar historial' });
            return;
        }

        if (!d.grupos.length) {
            Swal.fire({
                icon: 'info',
                title: 'Sin historial',
                text: 'Este vehículo no tiene accidentes registrados.',
                background: '#1a1d27', color: '#e8eaf0',
                confirmButtonColor: '#e05252',
                customClass: { container: 'swal-over-modal' }
            });
            return;
        }

        const estadoColor = {
            'Reportado': '#5b9bd5',
            'En trámite': '#e8b84b',
            'Cerrado': '#4caf7d',
            'Sin seguro': '#7c8398',
        };

        const gruposHtml = d.grupos.map((g, gi) => {
            const filasHtml = g.items.map((item, i) => {
                const bg = i % 2 === 0 ? 'rgba(255,255,255,.02)' : 'transparent';
                const color = estadoColor[item.estado] || '#7c8398';
                const costoEst = item.costo_estimado
                    ? `Q ${Number(item.costo_estimado).toLocaleString()}` : '—';
                const costoReal = item.costo_real
                    ? `Q ${Number(item.costo_real).toLocaleString()}` : '—';

                // ── Fotos como links que abren en nueva pestaña ────────
                const fotosHtml = [item.foto_1_url, item.foto_2_url, item.foto_3_url, item.foto_4_url]
                    .filter(Boolean)
                    .map((url, fi) => `
                        <a href="${url}" target="_blank"
                            style="display:inline-flex;align-items:center;gap:.3rem;
                            background:rgba(58,123,213,.1);border:1px solid rgba(58,123,213,.25);
                            color:#5b9bd5;padding:.2rem .5rem;border-radius:6px;
                            font-size:.7rem;text-decoration:none;margin:.15rem 0;">
                            <i class="bi bi-image"></i> Foto ${fi + 1}
                        </a>`)
                    .join('');

                return `
                <tr style="background:${bg};border-bottom:1px solid rgba(255,255,255,.04);">
                    <td style="padding:.5rem .75rem;font-size:.8rem;color:#e8eaf0;white-space:nowrap;">
                        ${item.fecha_accidente}
                    </td>
                    <td style="padding:.5rem .75rem;font-size:.78rem;color:#7c8398;max-width:100px;">
                        ${item.lugar || '—'}
                    </td>
                    <td style="padding:.5rem .75rem;font-size:.78rem;color:#e8eaf0;max-width:150px;">
                        ${item.descripcion || '—'}
                    </td>
                    <td style="padding:.5rem .75rem;font-size:.8rem;color:${color};font-weight:600;white-space:nowrap;">
                        ${item.estado || '—'}
                    </td>
                    <td style="padding:.5rem .75rem;font-size:.8rem;color:#e8b84b;white-space:nowrap;">
                        ${costoEst}
                    </td>
                    <td style="padding:.5rem .75rem;font-size:.8rem;color:#e05252;font-weight:600;white-space:nowrap;">
                        ${costoReal}
                    </td>
                    <td style="padding:.5rem .75rem;font-size:.75rem;color:#7c8398;">
                        ${item.conductor || '—'}
                    </td>
                    <td style="padding:.5rem .75rem;font-size:.75rem;">
                        ${fotosHtml || '<span style="color:#555;">—</span>'}
                    </td>
                </tr>`;
            }).join('');

            const costoTotalStr = g.costo_total > 0
                ? `<span style="color:#e05252;font-weight:700;font-size:.8rem;">
                       Total: Q ${Number(g.costo_total).toLocaleString()}
                   </span>`
                : '';

            return `
            <div style="margin-bottom:1rem;">
                <div style="background:#1f2335;border-left:3px solid #e05252;
                    padding:.6rem 1rem;
                    display:flex;align-items:center;justify-content:space-between;
                    cursor:pointer;border-radius:6px 6px 0 0;"
                    onclick="
                        const t=document.getElementById('ha_tabla_${gi}');
                        const ic=document.getElementById('ha_icon_${gi}');
                        if(t.style.display==='none'){t.style.display='';ic.style.transform='rotate(0deg)';}
                        else{t.style.display='none';ic.style.transform='rotate(-90deg)';}
                    ">
                    <div style="display:flex;align-items:center;gap:.75rem;">
                        <span style="font-family:Rajdhani,sans-serif;font-size:.95rem;
                            font-weight:700;color:#e8eaf0;">
                            ${g.tipo}
                        </span>
                        <span style="font-size:.72rem;color:#7c8398;">
                            ${g.total} registro(s)
                        </span>
                        ${costoTotalStr}
                    </div>
                    <i id="ha_icon_${gi}" class="bi bi-chevron-down"
                        style="color:#e05252;font-size:.8rem;transition:transform .2s;"></i>
                </div>
                <div id="ha_tabla_${gi}" style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="background:#242837;">
                                <th style="padding:.45rem .75rem;font-size:.7rem;color:#7c8398;text-align:left;text-transform:uppercase;letter-spacing:.4px;white-space:nowrap;">Fecha</th>
                                <th style="padding:.45rem .75rem;font-size:.7rem;color:#7c8398;text-align:left;text-transform:uppercase;letter-spacing:.4px;">Lugar</th>
                                <th style="padding:.45rem .75rem;font-size:.7rem;color:#7c8398;text-align:left;text-transform:uppercase;letter-spacing:.4px;">Descripción</th>
                                <th style="padding:.45rem .75rem;font-size:.7rem;color:#7c8398;text-align:left;text-transform:uppercase;letter-spacing:.4px;">Estado</th>
                                <th style="padding:.45rem .75rem;font-size:.7rem;color:#7c8398;text-align:left;text-transform:uppercase;letter-spacing:.4px;white-space:nowrap;">Costo Est.</th>
                                <th style="padding:.45rem .75rem;font-size:.7rem;color:#7c8398;text-align:left;text-transform:uppercase;letter-spacing:.4px;white-space:nowrap;">Costo Real</th>
                                <th style="padding:.45rem .75rem;font-size:.7rem;color:#7c8398;text-align:left;text-transform:uppercase;letter-spacing:.4px;">Conductor</th>
                                <th style="padding:.45rem .75rem;font-size:.7rem;color:#7c8398;text-align:left;text-transform:uppercase;letter-spacing:.4px;">Fotos</th>
                            </tr>
                        </thead>
                        <tbody>${filasHtml}</tbody>
                    </table>
                </div>
            </div>`;
        }).join('');

        Swal.fire({
            title: `<span style="font-family:Rajdhani,sans-serif;font-size:1.1rem;">
                <i class="bi bi-cone-striped" style="color:#e05252;"></i>
                Historial de Accidentes — ${fichaPlacaActual}
            </span>`,
            html: `
            <div style="text-align:left;max-height:480px;overflow-y:auto;padding-right:.25rem;">
                ${gruposHtml}
            </div>`,
            background: '#1a1d27', color: '#e8eaf0',
            confirmButtonColor: '#e05252',
            confirmButtonText: '<i class="bi bi-x"></i> Cerrar',
            width: '900px',
            customClass: { container: 'swal-over-modal' }
        });

    } catch (err) {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    } finally {
        ocultarLoader();
    }
};


// ════════════════════════════════════════════════════════════════════════════
// ── CHEQUEOS ──────────────────────────────────────────────────────────────────
// ════════════════════════════════════════════════════════════════════════════

let chequeoActualId = null;
let chequeoResultados = {};
let chequeoTotalItems = 17;

const ITEMS_CHEQUEO = {
    1: { desc: 'Tren delantero', tipos: null },
    2: { desc: 'Tapicería', tipos: ['Automóvil', 'Pickup', 'Camión', 'Microbús', 'Blindado', 'Camioneta', 'Otro'] },
    3: { desc: 'Carrocería', tipos: ['Automóvil', 'Pickup', 'Camión', 'Microbús', 'Blindado', 'Camioneta', 'Otro'] },
    4: { desc: 'Pintura en general', tipos: null },
    5: { desc: 'Siglas que identifican a los vehículos pintados en color naranja fluorescente y en el lugar correspondiente', tipos: null },
    6: { desc: 'Lona del camión', tipos: ['Camión'] },
    7: { desc: 'Luces y pide vías', tipos: null },
    8: { desc: 'Sistema eléctrico', tipos: null },
    9: { desc: 'Herramienta extra para reparación de vehículos', tipos: ['Automóvil', 'Pickup', 'Camión', 'Microbús', 'Blindado', 'Camioneta', 'Otro'] },
    10: { desc: 'Herramienta básica (Tricket, llave de chuchos, palanca o tubo, trozo, cable o cadena, señalizaciones etc.)', tipos: ['Automóvil', 'Pickup', 'Camión', 'Microbús', 'Blindado', 'Camioneta', 'Otro'] },
    11: { desc: 'Herramienta de emergencia (llave de ½, Nos. 12, 13, 14, alicate, llave ajustable, juego de desatornilladores)', tipos: ['Automóvil', 'Pickup', 'Camión', 'Microbús', 'Blindado', 'Camioneta', 'Otro'] },
    12: { desc: 'Repuestos necesarios de emergencias', tipos: ['Automóvil', 'Pickup', 'Camión', 'Microbús', 'Blindado', 'Camioneta', 'Otro'] },
    13: { desc: 'Neumático de repuesto', tipos: ['Automóvil', 'Pickup', 'Camión', 'Microbús', 'Blindado', 'Camioneta', 'Otro'] },
    14: { desc: 'Acumulador o batería', tipos: null },
    15: { desc: 'Neumáticos', tipos: null },
    16: { desc: 'Lubricante', tipos: null },
    17: { desc: 'Odómetro', tipos: ['Automóvil', 'Pickup', 'Camión', 'Microbús', 'Blindado', 'Camioneta', 'Otro', 'Motocicleta'] },
};

const abrirModalChequeo = async () => {
    mostrarLoader('Cargando hoja de chequeo...');
    const modal = document.getElementById('modalChequeo');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    const subtitulo = document.getElementById('chequeoModalSubtitulo');
    if (subtitulo) subtitulo.textContent = `${fichaPlacaActual} — Chequeo mensual`;
    const formChequeo = document.getElementById('formNuevoChequeo');
    const btnChequeo = document.getElementById('btnNuevoChequeo');
    if (formChequeo) formChequeo.style.display = 'none';
    if (btnChequeo) btnChequeo.style.display = 'flex';
    await cargarChequeos();
    ocultarLoader();
};

const cerrarModalChequeo = () => {
    document.getElementById('modalChequeo').style.display = 'none';
    document.body.style.overflow = '';
    chequeoActualId = null;
    chequeoResultados = {};
    const formChequeo = document.getElementById('formNuevoChequeo');
    const btnChequeo = document.getElementById('btnNuevoChequeo');
    if (formChequeo) formChequeo.style.display = 'none';
    if (btnChequeo) btnChequeo.style.display = 'flex';
};

const actualizarBotonesExpediente = (tieneChequeoMes) => {
    const btnChequeo = document.getElementById('btnIrAChequeo');
    const btnExpediente = document.getElementById('btnGenerarExpediente');
    if (!btnChequeo || !btnExpediente) return;
    if (tieneChequeoMes) {
        btnChequeo.style.display = 'none';
        btnExpediente.style.display = 'flex';
    } else {
        btnChequeo.style.display = 'flex';
        btnExpediente.style.display = 'none';
    }
};

const cargarChequeos = async () => {
    try {
        const r = await fetch(`${BASE}/API/vehiculos/chequeos/listar?placa=${fichaPlacaActual}`);
        const d = await r.json();
        if (d.codigo !== 1) return;

        const badge = document.getElementById('badgeChequeo');
        if (badge) badge.textContent = d.datos.length;

        const ahora = new Date();
        const mesActual = `${ahora.getFullYear()}-${String(ahora.getMonth() + 1).padStart(2, '0')}`;
        const yaHayChequeoEsteMes = d.datos.some(c =>
            c.fecha_chequeo?.startsWith(mesActual) && c.completado == 1
        );

        const btnNuevo = document.getElementById('btnNuevoChequeo');
        const alerta = document.getElementById('chequeoAlertaMes');
        if (btnNuevo) btnNuevo.style.display = yaHayChequeoEsteMes ? 'none' : 'flex';
        if (alerta) alerta.style.display = yaHayChequeoEsteMes ? 'flex' : 'none';

        actualizarBotonesExpediente(d.tiene_chequeo_mes);
        renderTablaChequeos(d.datos);
    } catch (err) {
        console.error('Error cargando chequeos:', err);
    }
};

const renderTablaChequeos = (chequeos) => {
    const wrap = document.getElementById('chequeoHistorialWrap') || document.getElementById('tablaChequeoWrap');
    if (!wrap) return;
    if (!chequeos.length) {
        wrap.innerHTML = `
            <div style="text-align:center;padding:3rem;color:var(--text-muted);">
                <i class="bi bi-clipboard2-x" style="font-size:3rem;opacity:.2;display:block;margin-bottom:1rem;"></i>
                <p>No hay chequeos registrados para este vehículo</p>
            </div>`;
        return;
    }
    wrap.innerHTML = chequeos.map(c => {
        const estadoColor = c.estado === 'Completado' ? 'var(--success)' : 'var(--accent)';
        const estadoBg = c.estado === 'Completado' ? 'rgba(76,175,125,.15)' : 'rgba(232,184,75,.15)';
        const estadoBorder = c.estado === 'Completado' ? 'rgba(76,175,125,.3)' : 'rgba(232,184,75,.3)';
        return `
        <div class="svc-row" style="grid-template-columns:1fr 1fr 1fr 1fr auto;">
            <div><div class="svc-label">Fecha</div><div class="svc-val">${fmtFecha(c.fecha_chequeo)}</div></div>
            <div><div class="svc-label">KM</div><div class="svc-val">${Number(c.km_al_chequeo).toLocaleString()} km</div></div>
            <div><div class="svc-label">Realizado por</div><div class="svc-val">${c.realizado_por || '—'}</div></div>
            <div><div class="svc-label">Estado</div><div class="svc-val">
                <span style="background:${estadoBg};color:${estadoColor};border:1px solid ${estadoBorder};padding:.2rem .65rem;border-radius:20px;font-size:.72rem;font-weight:700;">
                    ${c.estado}
                </span>
            </div></div>
            <div style="display:flex;gap:.4rem;align-items:center;">
                ${c.estado === 'Completado'
                ? `<button onclick="verChequeo(${c.id_chequeo})" style="background:rgba(111,66,193,.15);border:1px solid rgba(111,66,193,.3);color:#a78bfa;border-radius:6px;padding:.35rem .6rem;cursor:pointer;font-size:.8rem;"><i class="bi bi-eye"></i></button>`
                : `<button onclick="continuarChequeo(${c.id_chequeo})" style="background:rgba(232,184,75,.15);border:1px solid rgba(232,184,75,.3);color:var(--accent);border-radius:6px;padding:.35rem .6rem;cursor:pointer;font-size:.8rem;"><i class="bi bi-pencil-square"></i></button>`}
                <button onclick="eliminarChequeo(${c.id_chequeo})" style="background:rgba(224,82,82,.15);border:1px solid rgba(224,82,82,.3);color:var(--danger);border-radius:6px;padding:.35rem .6rem;cursor:pointer;font-size:.8rem;"><i class="bi bi-trash3"></i></button>
            </div>
        </div>
        ${c.observaciones_gen ? `<div style="font-size:.75rem;color:var(--text-muted);margin-top:-.4rem;margin-bottom:.6rem;padding-left:.25rem;"><i class="bi bi-chat-text"></i> ${c.observaciones_gen}</div>` : ''}`;
    }).join('');
};

const generarFilasChequeo = (itemsExistentes = {}) => {
    const tbody = document.getElementById('chequeoTablaItems');
    if (!tbody) return;
    const tipo = fichaTipoVehiculo;
    const itemsAplicables = Object.entries(ITEMS_CHEQUEO).filter(([num, item]) => {
        if (!item.tipos) return true;
        return item.tipos.includes(tipo);
    });
    tbody.innerHTML = itemsAplicables.map(([num, item], idx) => {
        const n = parseInt(num);
        const res = itemsExistentes[n] || {};
        const opciones = ['BE', 'ME', 'MEI', 'NT'];
        const colores = { BE: '#4caf7d', ME: '#e8b84b', MEI: '#e05252', NT: '#7c8398' };
        const radiosCols = opciones.map(op => `
            <td style="text-align:center;padding:.5rem .25rem;">
                <label style="cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <input type="radio" name="chq_item_${n}" value="${op}"
                        ${res.resultado === op ? 'checked' : ''}
                        onchange="onChequeoItemChange(${n}, '${op}')"
                        style="appearance:none;width:22px;height:22px;border-radius:50%;
                        border:2px solid ${colores[op]};
                        background:${res.resultado === op ? colores[op] : 'transparent'};
                        cursor:pointer;transition:all .2s;flex-shrink:0;">
                </label>
            </td>`).join('');
        return `
        <tr style="border-bottom:1px solid var(--border);transition:background .15s;"
            onmouseover="this.style.background='rgba(255,255,255,.03)'"
            onmouseout="this.style.background='transparent'">
            <td style="padding:.6rem .75rem;color:var(--text-muted);font-size:.8rem;font-weight:600;">
                ${String(idx + 1).padStart(2, '0')}
            </td>
            <td style="padding:.6rem .75rem;color:var(--text-main);font-size:.82rem;line-height:1.4;">
                ${item.desc}
            </td>
            ${radiosCols}
            <td style="padding:.5rem .75rem;">
                <input type="text" id="chq_obs_${n}" value="${res.observacion || ''}"
                    placeholder="..." class="form-control"
                    style="font-size:.75rem;padding:.3rem .5rem !important;"
                    oninput="onChequeoObsChange(${n}, this.value)">
            </td>
        </tr>`;
    }).join('');
    chequeoTotalItems = itemsAplicables.length;
    if (Object.keys(itemsExistentes).length) {
        chequeoResultados = {};
        Object.entries(itemsExistentes).forEach(([num, data]) => {
            if (data.resultado) chequeoResultados[parseInt(num)] = {
                resultado: data.resultado,
                observacion: data.observacion || ''
            };
        });
        actualizarProgreso();
    }
};

const onChequeoItemChange = (num, valor) => {
    if (!chequeoResultados[num]) chequeoResultados[num] = {};
    chequeoResultados[num].resultado = valor;
    const colores = { BE: '#4caf7d', ME: '#e8b84b', MEI: '#e05252', NT: '#7c8398' };
    document.querySelectorAll(`input[name="chq_item_${num}"]`).forEach(r => {
        r.style.background = r.value === valor ? colores[r.value] : 'transparent';
    });
    actualizarProgreso();
};

const onChequeoObsChange = (num, valor) => {
    if (!chequeoResultados[num]) chequeoResultados[num] = {};
    chequeoResultados[num].observacion = valor;
};

const actualizarProgreso = () => {
    const total = chequeoTotalItems;
    const completados = Object.values(chequeoResultados).filter(v => v.resultado).length;
    const pct = Math.round((completados / total) * 100);
    const textoEl = document.getElementById('chqProgreso');
    const barraEl = document.getElementById('chqBarraProgreso');
    const btnEl = document.getElementById('btnGuardarChequeo');
    if (textoEl) textoEl.textContent = `${completados} / ${total}`;
    if (barraEl) barraEl.style.width = `${pct}%`;
    if (btnEl) { const ok = completados === total; btnEl.disabled = !ok; btnEl.style.opacity = ok ? '1' : '.5'; }
};

const iniciarNuevoChequeo = () => {
    chequeoResultados = {};
    chequeoActualId = null;
    document.getElementById('formNuevoChequeo').style.display = 'block';
    document.getElementById('btnNuevoChequeo').style.display = 'none';
    document.getElementById('chqFecha').value = new Date().toISOString().split('T')[0];
    document.getElementById('chqKm').value = document.getElementById('fd-km')?.textContent?.replace(/\D/g, '') || '';
    const inputResponsable = document.getElementById('chqResponsable');
    if (inputResponsable) {
        inputResponsable.value = AUTH_NOMBRE;
        inputResponsable.readOnly = true;
        inputResponsable.style.opacity = '.7';
        inputResponsable.style.cursor = 'not-allowed';
    }
    document.getElementById('chqObservaciones').value = '';
    generarFilasChequeo();
    actualizarProgreso();
};

const continuarChequeo = async (id) => {
    try {
        const r = await fetch(`${BASE}/API/vehiculos/chequeos/obtener?id=${id}`);
        const d = await r.json();
        if (d.codigo !== 1) return;
        chequeoActualId = id;
        chequeoResultados = {};
        const itemsMap = {};
        (d.datos.items || []).forEach(item => {
            itemsMap[item.numero_item] = { resultado: item.resultado, observacion: item.observacion };
        });
        document.getElementById('formNuevoChequeo').style.display = 'block';
        document.getElementById('btnNuevoChequeo').style.display = 'none';
        document.getElementById('chqFecha').value = d.datos.fecha_chequeo;
        document.getElementById('chqKm').value = d.datos.km_al_chequeo;
        document.getElementById('chqResponsable').value = d.datos.realizado_por || '';
        document.getElementById('chqObservaciones').value = d.datos.observaciones_gen || '';
        generarFilasChequeo(itemsMap);
    } catch (err) { Toast.fire({ icon: 'error', title: 'Error de conexión' }); }
};

const verChequeo = async (id) => {
    try {
        const r = await fetch(`${BASE}/API/vehiculos/chequeos/obtener?id=${id}`);
        const d = await r.json();
        if (d.codigo !== 1) return;
        const colores = { BE: '#4caf7d', ME: '#e8b84b', MEI: '#e05252', NT: '#7c8398' };
        const itemsMap = {};
        (d.datos.items || []).forEach(item => { itemsMap[item.numero_item] = item; });
        const filas = Object.entries(ITEMS_CHEQUEO).map(([num, item]) => {
            const it = itemsMap[parseInt(num)] || {};
            const color = it.resultado ? colores[it.resultado] : 'var(--text-muted)';
            return `<tr style="border-bottom:1px solid var(--border);">
                <td style="padding:.5rem .75rem;color:var(--text-muted);font-size:.8rem;">${String(num).padStart(2, '0')}</td>
                <td style="padding:.5rem .75rem;color:var(--text-main);font-size:.82rem;">${item.desc}</td>
                <td style="padding:.5rem .75rem;text-align:center;"><span style="color:${color};font-weight:700;font-size:.82rem;">${it.resultado || '—'}</span></td>
                <td style="padding:.5rem .75rem;font-size:.78rem;color:var(--text-muted);">${it.observacion || ''}</td>
            </tr>`;
        }).join('');
        await Swal.fire({
            title: `Chequeo — ${d.datos.fecha_chequeo}`,
            html: `
            <div style="text-align:left;font-size:.82rem;color:#7c8398;margin-bottom:1rem;">
                <i class="bi bi-speedometer"></i> ${Number(d.datos.km_al_chequeo).toLocaleString()} km
                ${d.datos.realizado_por ? ' · <i class="bi bi-person"></i> ' + d.datos.realizado_por : ''}
            </div>
            <div style="overflow-x:auto;max-height:400px;overflow-y:auto;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#1a1d27;position:sticky;top:0;">
                            <th style="padding:.5rem;text-align:left;color:#7c8398;font-size:.7rem;">No.</th>
                            <th style="padding:.5rem;text-align:left;color:#7c8398;font-size:.7rem;">Descripción</th>
                            <th style="padding:.5rem;text-align:center;color:#7c8398;font-size:.7rem;">Resultado</th>
                            <th style="padding:.5rem;text-align:left;color:#7c8398;font-size:.7rem;">Obs.</th>
                        </tr>
                    </thead>
                    <tbody>${filas}</tbody>
                </table>
            </div>
            ${d.datos.observaciones_gen ? `<div style="margin-top:1rem;padding:.75rem;background:#242837;border-radius:8px;font-size:.82rem;color:#7c8398;text-align:left;"><i class="bi bi-chat-text"></i> ${d.datos.observaciones_gen}</div>` : ''}`,
            background: '#1a1d27', color: '#e8eaf0',
            confirmButtonColor: '#6f42c1', confirmButtonText: 'Cerrar',
            width: '700px', customClass: { container: 'swal-over-modal' }
        });
    } catch (err) { Toast.fire({ icon: 'error', title: 'Error de conexión' }); }
};

const guardarChequeo = async () => {
    const fecha = document.getElementById('chqFecha').value;
    const km = document.getElementById('chqKm').value;
    const responsable = document.getElementById('chqResponsable').value.trim();

    if (!fecha) { Swal.fire({ icon: 'warning', title: 'Fecha requerida', text: 'Selecciona la fecha del chequeo.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#6f42c1', customClass: { container: 'swal-over-modal' } }); return; }
    if (!km || parseInt(km) <= 0) { Swal.fire({ icon: 'warning', title: 'KM requerido', text: 'Ingresa el kilometraje al momento del chequeo.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#6f42c1', customClass: { container: 'swal-over-modal' } }); return; }
    if (!responsable) { Swal.fire({ icon: 'warning', title: 'Responsable requerido', text: 'Indica quién realizó el chequeo.', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#6f42c1', customClass: { container: 'swal-over-modal' } }); return; }

    if (!chequeoActualId) {
        try {
            const bodyCrear = new FormData();
            bodyCrear.append('placa', fichaPlacaActual);
            bodyCrear.append('fecha_chequeo', fecha);
            bodyCrear.append('km_al_chequeo', km);
            const rCrear = await fetch(`${BASE}/API/vehiculos/chequeos/crear`, { method: 'POST', body: bodyCrear });
            const dCrear = await rCrear.json();
            if (dCrear.codigo !== 1) { Toast.fire({ icon: 'error', title: dCrear.mensaje }); return; }
            chequeoActualId = dCrear.id_chequeo;
        } catch (err) { Toast.fire({ icon: 'error', title: 'Error al crear el chequeo' }); return; }
    }

    const items = Object.entries(chequeoResultados).map(([num, data]) => ({
        numero_item: parseInt(num),
        resultado: data.resultado,
        observacion: data.observacion || ''
    }));

    const body = new FormData();
    body.append('id_chequeo', chequeoActualId);
    body.append('km_al_chequeo', km);
    body.append('realizado_por', responsable);
    body.append('items', JSON.stringify(items));
    body.append('observaciones_gen', document.getElementById('chqObservaciones').value);

    try {
        const r = await fetch(`${BASE}/API/vehiculos/chequeos/completar`, { method: 'POST', body });
        const d = await r.json();
        if (d.codigo === 1) {
            const placaGuardada = fichaPlacaActual;
            document.getElementById('modalChequeo').style.display = 'none';
            chequeoActualId = null;
            chequeoResultados = {};
            const formChequeo = document.getElementById('formNuevoChequeo');
            const btnChequeo = document.getElementById('btnNuevoChequeo');
            if (formChequeo) formChequeo.style.display = 'none';
            if (btnChequeo) btnChequeo.style.display = 'flex';
            await Swal.fire({
                title: '¡Chequeo completado!', icon: 'success', draggable: true,
                background: '#1a1d27', color: '#e8eaf0',
                confirmButtonColor: '#6f42c1', customClass: { container: 'swal-over-modal' }
            });
            if (placaGuardada) await abrirFicha(placaGuardada);
        } else {
            Toast.fire({ icon: 'error', title: d.mensaje });
        }
    } catch (err) { Toast.fire({ icon: 'error', title: 'Error de conexión' }); }
};

const cancelarChequeo = () => {
    const formChequeo = document.getElementById('formNuevoChequeo');
    const btnChequeo = document.getElementById('btnNuevoChequeo');
    if (formChequeo) formChequeo.style.display = 'none';
    if (btnChequeo) btnChequeo.style.display = 'flex';
    chequeoActualId = null;
    chequeoResultados = {};
};

const eliminarChequeo = async (id) => {
    const conf = await Swal.fire({
        icon: 'warning', title: '¿Eliminar chequeo?', text: 'Esta acción no se puede deshacer.',
        showCancelButton: true, confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e05252', cancelButtonColor: '#3a7bd5',
        background: '#1a1d27', color: '#e8eaf0', customClass: { container: 'swal-over-modal' }
    });
    if (!conf.isConfirmed) return;
    const body = new FormData();
    body.append('id_chequeo', id);
    try {
        const r = await fetch(`${BASE}/API/vehiculos/chequeos/eliminar`, { method: 'POST', body });
        const d = await r.json();
        Toast.fire({ icon: d.codigo === 1 ? 'success' : 'error', title: d.mensaje });
        if (d.codigo === 1) await cargarChequeos();
    } catch (err) { Toast.fire({ icon: 'error', title: 'Error de conexión' }); }
};

// ════════════════════════════════════════════════════════════════════════════
// ── FOTOS ACCIDENTE ───────────────────────────────────────────────────────────
// ════════════════════════════════════════════════════════════════════════════

let fotosAccCount = 0;
const MAX_FOTOS_ACC = 4;

const agregarFotoAcc = () => {
    if (fotosAccCount >= MAX_FOTOS_ACC) {
        Toast.fire({ icon: 'warning', title: `Máximo ${MAX_FOTOS_ACC} archivos permitidos` });
        return;
    }
    fotosAccCount++;
    const id = `acFoto_${fotosAccCount}`;
    const container = document.getElementById('fotosAccContainer');
    const div = document.createElement('div');
    div.id = `fotoAccItem_${fotosAccCount}`;
    div.style.cssText = 'display:flex;align-items:center;gap:.5rem;background:var(--dark-2);border:1px solid var(--border);border-radius:8px;padding:.5rem .75rem;';
    div.innerHTML = `
        <i class="bi bi-paperclip" style="color:var(--text-muted);flex-shrink:0;"></i>
        <div class="file-upload-area" id="area_${id}"
            style="flex:1;padding:.4rem .75rem;margin:0;min-height:unset;display:flex;align-items:center;gap:.5rem;cursor:pointer;"
            onclick="document.getElementById('${id}').click()">
            <input type="file" id="${id}" name="fotos_acc[]"
                accept=".pdf,.jpg,.jpeg,.png" style="display:none;"
                onchange="onFotoAccChange('${id}', ${fotosAccCount})">
            <span id="label_${id}" style="font-size:.78rem;color:var(--text-muted);">Seleccionar archivo...</span>
        </div>
        <button type="button" onclick="quitarFotoAcc(${fotosAccCount})"
            style="background:rgba(224,82,82,.15);border:1px solid rgba(224,82,82,.3);
            color:var(--danger);border-radius:6px;padding:.3rem .5rem;cursor:pointer;flex-shrink:0;">
            <i class="bi bi-x"></i>
        </button>`;
    container.appendChild(div);
    actualizarContadorFotos();
    if (fotosAccCount >= MAX_FOTOS_ACC) {
        document.getElementById('btnAgregarFotoAcc').style.display = 'none';
    }
};

const onFotoAccChange = (id, num) => {
    const input = document.getElementById(id);
    const label = document.getElementById(`label_${id}`);
    if (!input || !input.files[0]) return;
    const file = input.files[0];
    label.innerHTML = `<i class="bi bi-check-circle-fill" style="color:var(--success);"></i><span style="color:var(--success);font-size:.78rem;">${file.name}</span>`;
};

const quitarFotoAcc = (num) => {
    const item = document.getElementById(`fotoAccItem_${num}`);
    if (item) item.remove();
    fotosAccCount = document.querySelectorAll('#fotosAccContainer > div').length;
    actualizarContadorFotos();
    document.getElementById('btnAgregarFotoAcc').style.display = 'flex';
};

const actualizarContadorFotos = () => {
    const count = document.querySelectorAll('#fotosAccContainer > div').length;
    const counter = document.getElementById('fotosAccContador');
    if (counter) counter.textContent = `${count} / ${MAX_FOTOS_ACC} archivos`;
};

const resetFotosAcc = () => {
    const container = document.getElementById('fotosAccContainer');
    if (container) container.innerHTML = '';
    fotosAccCount = 0;
    actualizarContadorFotos();
    const btn = document.getElementById('btnAgregarFotoAcc');
    if (btn) btn.style.display = 'flex';
};

// ── Informe policial upload ───────────────────────────────────────────────────
const inputInformeAcc = document.getElementById('acInforme');
const areaInformeAcc = document.getElementById('areaInformeAcc');
if (inputInformeAcc && areaInformeAcc) {
    inputInformeAcc.addEventListener('change', () => {
        const file = inputInformeAcc.files[0];
        if (!file) return;
        areaInformeAcc.classList.add('has-file');
        areaInformeAcc.querySelector('.upload-icon i').className = 'bi bi-check-circle-fill';
        areaInformeAcc.querySelector('.upload-label').innerHTML = `<span style="color:var(--success)">${file.name}</span>`;
    });
}

// ════════════════════════════════════════════════════════════════════════════
// ── LIGHTBOX ──────────────────────────────────────────────────────────────────
// ════════════════════════════════════════════════════════════════════════════

let lbImagenes = [];
let lbIndice = 0;

const abrirLightbox = (imagenes, indice = 0) => {
    lbImagenes = imagenes;
    lbIndice = indice;
    _renderLightbox();
    document.getElementById('bhr-lightbox').classList.add('visible');
    document.body.style.overflow = 'hidden';
};

const cerrarLightbox = () => {
    document.getElementById('bhr-lightbox').classList.remove('visible');
    document.body.style.overflow = '';
};

const navLightbox = (dir) => {
    lbIndice = Math.max(0, Math.min(lbImagenes.length - 1, lbIndice + dir));
    _renderLightbox();
};

const _renderLightbox = () => {
    const item = lbImagenes[lbIndice];
    const img = document.getElementById('lbImagen');
    const caption = document.getElementById('lbCaption');
    const prev = document.getElementById('lbPrev');
    const next = document.getElementById('lbNext');
    img.src = item.url;
    caption.textContent = item.caption || '';
    prev.classList.toggle('hidden', lbIndice === 0);
    next.classList.toggle('hidden', lbIndice === lbImagenes.length - 1);
};

document.getElementById('bhr-lightbox').addEventListener('click', (e) => {
    if (e.target === document.getElementById('bhr-lightbox')) cerrarLightbox();
});
document.addEventListener('keydown', (e) => {
    const lb = document.getElementById('bhr-lightbox');
    if (!lb.classList.contains('visible')) return;
    if (e.key === 'Escape') cerrarLightbox();
    if (e.key === 'ArrowLeft') navLightbox(-1);
    if (e.key === 'ArrowRight') navLightbox(1);
});

// ════════════════════════════════════════════════════════════════════════════
// ── GENERAR EXPEDIENTE ────────────────────────────────────────────────────────
// ════════════════════════════════════════════════════════════════════════════

const generarExpediente = (placa) => {
    window.open(`${BASE}/vehiculos/expediente?placa=${encodeURIComponent(placa)}`, '_blank');
};

// ════════════════════════════════════════════════════════════════════════════
// ── SOLICITAR MODIFICACIÓN (COMTE_PTN) ───────────────────────────────────────
// ════════════════════════════════════════════════════════════════════════════

window.solicitarModificacion = async (placa) => {
    const vehiculo = todosLosVehiculos.find(v => v.placa === placa);
    if (!vehiculo) return;

    const tipoSolicitud = await new Promise((resolve) => {
        Swal.fire({
            title: 'Solicitar modificación',
            html: `
            <div style="text-align:left;font-size:.85rem;">
                <p style="color:#7c8398;margin-bottom:1rem;">
                    <i class="bi bi-truck" style="color:#e8b84b;"></i>
                    <strong style="color:#e8b84b;">${vehiculo.marca} ${vehiculo.modelo}</strong> — Catálogo ${placa}
                </p>
                <p style="color:#7c8398;margin-bottom:1rem;">¿Qué deseas modificar?</p>
                <div style="display:flex;flex-direction:column;gap:.5rem;">
                    <button id="btn-tipo-texto" style="background:#242837;border:1px solid #2e3347;border-radius:10px;padding:.85rem 1rem;cursor:pointer;text-align:left;color:#e8eaf0;display:flex;align-items:center;gap:.75rem;">
                        <i class="bi bi-pencil-square" style="font-size:1.3rem;color:#e8b84b;"></i>
                        <div><div style="font-weight:600;font-size:.88rem;">Datos del vehículo</div><div style="font-size:.75rem;color:#7c8398;">Marca, modelo, color, kilometraje, estado...</div></div>
                    </button>
                    <button id="btn-tipo-unidad" style="background:#242837;border:1px solid #2e3347;border-radius:10px;padding:.85rem 1rem;cursor:pointer;text-align:left;color:#e8eaf0;display:flex;align-items:center;gap:.75rem;">
                        <i class="bi bi-geo-alt-fill" style="font-size:1.3rem;color:#3a7bd5;"></i>
                        <div><div style="font-weight:600;font-size:.88rem;">Unidad asignada</div><div style="font-size:.75rem;color:#7c8398;">Cambiar destacamento o unidad</div></div>
                    </button>
                    <button id="btn-tipo-archivo" style="background:#242837;border:1px solid #2e3347;border-radius:10px;padding:.85rem 1rem;cursor:pointer;text-align:left;color:#e8eaf0;display:flex;align-items:center;gap:.75rem;">
                        <i class="bi bi-file-earmark-arrow-up" style="font-size:1.3rem;color:#4caf7d;"></i>
                        <div><div style="font-weight:600;font-size:.88rem;">Fotos o documentos</div><div style="font-size:.75rem;color:#7c8398;">Foto frontal, lateral, trasera, tarjeta PDF...</div></div>
                    </button>
                </div>
            </div>`,
            background: '#1a1d27', color: '#e8eaf0',
            showCancelButton: true, cancelButtonText: 'Cancelar', cancelButtonColor: '#555',
            showConfirmButton: false,
            didOpen: () => {
                document.getElementById('btn-tipo-texto').addEventListener('click', () => { Swal.close(); resolve('texto'); });
                document.getElementById('btn-tipo-unidad').addEventListener('click', () => { Swal.close(); resolve('unidad'); });
                document.getElementById('btn-tipo-archivo').addEventListener('click', () => { Swal.close(); resolve('archivo'); });
            }
        }).then(result => { if (result.isDismissed) resolve(null); });
    });

    if (!tipoSolicitud) return;

    if (tipoSolicitud === 'texto') {
        const datosVehiculo = { marca: vehiculo.marca, modelo: vehiculo.modelo, color: vehiculo.color, km_actuales: vehiculo.km_actuales, estado: vehiculo.estado, observaciones: vehiculo.observaciones || '' };
        const { value: formValues, isConfirmed } = await Swal.fire({
            title: 'Modificar datos del vehículo',
            html: `
            <div style="text-align:left;font-size:.85rem;">
                <p style="color:#7c8398;margin-bottom:1rem;">La solicitud será enviada al Comandante de Cía para su autorización.</p>
                <div style="margin-bottom:.75rem;">
                    <label style="color:#e8b84b;font-size:.78rem;font-weight:600;">Campo a modificar</label>
                    <select id="swal-campo" class="form-select" style="margin-top:.3rem;background:#242837;color:#e8eaf0;border:1px solid #2e3347;">
                        <option value="">Seleccione...</option>
                        <option value="marca">Marca</option><option value="modelo">Modelo</option>
                        <option value="color">Color</option><option value="km_actuales">Kilometraje</option>
                        <option value="estado">Estado</option><option value="observaciones">Observaciones</option>
                    </select>
                </div>
                <div id="swal-valor-actual-wrap" style="display:none;margin-bottom:.75rem;">
                    <label style="color:#7c8398;font-size:.75rem;">Valor actual:</label>
                    <div style="background:#1a1d27;border:1px solid #2e3347;border-radius:8px;padding:.5rem .75rem;margin-top:.3rem;color:#e8b84b;font-weight:600;">
                        <i class="bi bi-arrow-right-circle"></i> <span id="swal-valor-actual"></span>
                    </div>
                </div>
                <div>
                    <label style="color:#e8b84b;font-size:.78rem;font-weight:600;">Nuevo valor</label>
                    <input id="swal-valor" type="text" class="form-control" placeholder="Ingresa el nuevo valor..." style="margin-top:.3rem;background:#242837;color:#e8eaf0;border:1px solid #2e3347;">
                </div>
            </div>`,
            background: '#1a1d27', color: '#e8eaf0',
            showCancelButton: true, confirmButtonText: 'Enviar solicitud', cancelButtonText: 'Cancelar',
            confirmButtonColor: '#e8b84b', cancelButtonColor: '#555',
            didOpen: () => {
                document.getElementById('swal-campo').addEventListener('change', function () {
                    const campo = this.value;
                    const wrap = document.getElementById('swal-valor-actual-wrap');
                    const span = document.getElementById('swal-valor-actual');
                    if (campo && datosVehiculo[campo] !== undefined) { span.textContent = datosVehiculo[campo]; wrap.style.display = 'block'; }
                    else wrap.style.display = 'none';
                });
            },
            preConfirm: () => {
                const campo = document.getElementById('swal-campo').value;
                const valor = document.getElementById('swal-valor').value.trim();
                if (!campo) { Swal.showValidationMessage('Selecciona un campo'); return false; }
                if (!valor) { Swal.showValidationMessage('Ingresa el nuevo valor'); return false; }
                return { campo, valor };
            }
        });
        if (!isConfirmed || !formValues) return;
        await crearSolicitudModificacion(placa, { tipo_solicitud: 'texto', [formValues.campo]: { antes: datosVehiculo[formValues.campo], ahora: formValues.valor } });
    }

    else if (tipoSolicitud === 'unidad') {
        let unidades = [];
        try { const r = await fetch(`${BASE}/API/unidades/lista`); const d = await r.json(); unidades = d.datos || []; } catch { }
        const opcionesUnidades = unidades.map(u => `<option value="${u.id_unidad}" data-nombre="${u.unidad_destacamento}">${u.unidad_destacamento}</option>`).join('');
        const unidadActual = vehiculo.unidad_nombre || 'Sin asignar';
        const { value: formValues, isConfirmed } = await Swal.fire({
            title: 'Cambiar unidad asignada',
            html: `
            <div style="text-align:left;font-size:.85rem;">
                <div style="margin-bottom:.75rem;">
                    <label style="color:#7c8398;font-size:.75rem;">Unidad actual:</label>
                    <div style="background:#1a1d27;border:1px solid #2e3347;border-radius:8px;padding:.5rem .75rem;margin-top:.3rem;color:#e8b84b;font-weight:600;"><i class="bi bi-geo-alt-fill"></i> ${unidadActual}</div>
                </div>
                <div>
                    <label style="color:#e8b84b;font-size:.78rem;font-weight:600;">Nueva unidad</label>
                    <select id="swal-unidad" class="form-select" style="margin-top:.3rem;background:#242837;color:#e8eaf0;border:1px solid #2e3347;">
                        <option value="">Seleccione unidad...</option>${opcionesUnidades}
                    </select>
                </div>
            </div>`,
            background: '#1a1d27', color: '#e8eaf0',
            showCancelButton: true, confirmButtonText: 'Enviar solicitud', cancelButtonText: 'Cancelar',
            confirmButtonColor: '#e8b84b', cancelButtonColor: '#555',
            preConfirm: () => {
                const sel = document.getElementById('swal-unidad');
                const id = sel.value;
                const nombre = sel.options[sel.selectedIndex]?.dataset.nombre || '';
                if (!id) { Swal.showValidationMessage('Selecciona una unidad'); return false; }
                return { id, nombre };
            }
        });
        if (!isConfirmed || !formValues) return;
        await crearSolicitudModificacion(placa, { tipo_solicitud: 'unidad', id_unidad: { antes: vehiculo.id_unidad || '—', ahora: formValues.id }, nombre_unidad: { antes: unidadActual, ahora: formValues.nombre } });
    }

    else if (tipoSolicitud === 'archivo') {
        const { value: formValues, isConfirmed } = await Swal.fire({
            title: 'Solicitar cambio de archivo',
            html: `
            <div style="text-align:left;font-size:.85rem;">
                <p style="color:#7c8398;margin-bottom:1rem;">Indica qué archivo necesita ser actualizado y describe el motivo.</p>
                <div style="margin-bottom:.75rem;">
                    <label style="color:#e8b84b;font-size:.78rem;font-weight:600;">Archivo a cambiar</label>
                    <select id="swal-archivo" class="form-select" style="margin-top:.3rem;background:#242837;color:#e8eaf0;border:1px solid #2e3347;">
                        <option value="">Seleccione...</option>
                        <option value="foto_frente">Foto frontal</option><option value="foto_lateral">Foto lateral</option>
                        <option value="foto_trasera">Foto trasera</option><option value="tarjeta_pdf">Tarjeta de circulación (PDF)</option>
                        <option value="cert_inventario">Certificación de inventario</option><option value="cert_sicoin">Certificación SICOIN</option>
                    </select>
                </div>
                <div>
                    <label style="color:#e8b84b;font-size:.78rem;font-weight:600;">Motivo del cambio</label>
                    <textarea id="swal-motivo" class="form-control" rows="3" placeholder="Describe por qué necesita actualizarse..." style="margin-top:.3rem;background:#242837;color:#e8eaf0;border:1px solid #2e3347;border-radius:8px;resize:none;"></textarea>
                </div>
            </div>`,
            background: '#1a1d27', color: '#e8eaf0',
            showCancelButton: true, confirmButtonText: 'Enviar solicitud', cancelButtonText: 'Cancelar',
            confirmButtonColor: '#e8b84b', cancelButtonColor: '#555',
            preConfirm: () => {
                const archivo = document.getElementById('swal-archivo').value;
                const motivo = document.getElementById('swal-motivo').value.trim();
                if (!archivo) { Swal.showValidationMessage('Selecciona un archivo'); return false; }
                if (!motivo) { Swal.showValidationMessage('Describe el motivo'); return false; }
                return { archivo, motivo };
            }
        });
        if (!isConfirmed || !formValues) return;
        await crearSolicitudModificacion(placa, { tipo_solicitud: 'archivo', campo: formValues.archivo, descripcion: { antes: 'Archivo actual', ahora: formValues.motivo } });
    }
};

// ════════════════════════════════════════════════════════════════════════════
// ── WINDOW EXPORTS ────────────────────────────────────────────────────────────
// ════════════════════════════════════════════════════════════════════════════

window.seleccionarTipo = seleccionarTipo;
window.abrirFicha = abrirFicha;
window.cerrarFicha = cerrarFicha;
window.switchTab = switchTab;
window.generarExpediente = generarExpediente;
window.elegirSeguro = elegirSeguro;

// Órdenes de servicio
window.toggleFormNuevaOrden = toggleFormNuevaOrden;
window.crearOrden = crearOrden;
window.agregarItem = agregarItem;
window.eliminarItemOrden = eliminarItemOrden;
window.completarOrden = completarOrden;
window.confirmarEliminarOrden = confirmarEliminarOrden;
window.abrirOrdenEnProceso = abrirOrdenEnProceso;
window.verOrden = verOrden;

// HISTORIALES
window.verHistorialServicios = verHistorialServicios;
window.verHistorialReparaciones = verHistorialReparaciones;
window.verHistorialAccidentes = verHistorialAccidentes;
window.verDetalleReparacion = verDetalleReparacion;


// Reparaciones
window.toggleFormReparacion = toggleFormReparacion;
window.guardarReparacion = guardarReparacion;
window.editarReparacion = editarReparacion;
window.eliminarReparacion = eliminarReparacion;

// Seguros
window.toggleFormSeguro = toggleFormSeguro;
window.toggleFormSeguroFicha = toggleFormSeguro;
window.guardarSeguroFicha = guardarSeguro;
window.guardarSeguro = guardarSeguro;
window.editarSeguro = editarSeguro;
window.eliminarSeguro = eliminarSeguro;
window.verSeguro = verSeguro;

// Accidentes
window.toggleFormAccidente = toggleFormAccidente;
window.guardarAccidente = guardarAccidente;
window.editarAccidente = editarAccidente;
window.eliminarAccidente = eliminarAccidente;
window.verAccidente = verAccidente;
window.agregarFotoAcc = agregarFotoAcc;
window.onFotoAccChange = onFotoAccChange;
window.quitarFotoAcc = quitarFotoAcc;

// Chequeos
window.abrirModalChequeo = abrirModalChequeo;
window.cerrarModalChequeo = cerrarModalChequeo;
window.iniciarNuevoChequeo = iniciarNuevoChequeo;
window.continuarChequeo = continuarChequeo;
window.verChequeo = verChequeo;
window.guardarChequeo = guardarChequeo;
window.cancelarChequeo = cancelarChequeo;
window.eliminarChequeo = eliminarChequeo;
window.onChequeoItemChange = onChequeoItemChange;
window.onChequeoObsChange = onChequeoObsChange;
window.irAChequeo = abrirModalChequeo;

// Lightbox
window.abrirLightbox = abrirLightbox;
window.cerrarLightbox = cerrarLightbox;
window.navLightbox = navLightbox;

// ════════════════════════════════════════════════════════════════════════════
// ── INIT ──────────────────────────────────────────────────────────────────────
// ════════════════════════════════════════════════════════════════════════════

cargarUnidades();
buscar();