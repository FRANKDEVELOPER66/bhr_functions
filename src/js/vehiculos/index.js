import { Toast, validarFormulario } from "../funciones";
import Swal from "sweetalert2";

const BASE = '/bhr_functions';

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

// Filtros
const filtroTipo = document.getElementById('filtroTipo');
const filtroEstado = document.getElementById('filtroEstado');
const filtroBusqueda = document.getElementById('filtroBusqueda');
const btnLimpiarFiltros = document.getElementById('btnLimpiarFiltros');
const contadorVisible = document.getElementById('contadorVisible');

// Archivos
const inputFoto = document.getElementById('foto_frente');
const areaFoto = document.getElementById('areaFoto');
const fotoPreview = document.getElementById('fotoPreview');
const inputPdf = document.getElementById('tarjeta_pdf');
const areaPdf = document.getElementById('areaPdf');
const pdfNombre = document.getElementById('pdfNombre');
const fotoActualContainer = document.getElementById('fotoActualContainer');
const fotoActual = document.getElementById('fotoActual');

// Asignación
const selectUnidad = document.getElementById('id_unidad');
const infoDestacamento = document.getElementById('infoDestacamento');
const infoNombre = document.getElementById('infoNombreDestacamento');
const infoUbicacion = document.getElementById('infoUbicacion');

// Estado global
let todosLosVehiculos = [];
let modoEdicion = false;

// ── SELECT ÚNICO DE UNIDAD ────────────────────────────────────────────────────
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

// ── FILE UPLOAD PREVIEW ───────────────────────────────────────────────────────
inputFoto.addEventListener('change', async () => {
    const file = inputFoto.files[0];
    if (!file) return;

    const hayFotoActual = modoEdicion && fotoPreview.classList.contains('visible');

    if (hayFotoActual) {
        const confirm = await Swal.fire({
            icon: 'question',
            title: '¿Reemplazar foto?',
            html: `Se reemplazará la foto actual por <strong>${file.name}</strong>.<br>
                   <small style="color:var(--text-muted)">El cambio se aplicará al guardar.</small>`,
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-arrow-repeat"></i> Sí, reemplazar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#3a7bd5',
            cancelButtonColor: '#e05252',
            background: '#1a1d27',
            color: '#e8eaf0'
        });

        if (!confirm.isConfirmed) {
            inputFoto.value = '';
            return;
        }
    }

    areaFoto.classList.add('has-file');
    areaFoto.querySelector('.upload-icon i').className = 'bi bi-check-circle-fill';
    areaFoto.querySelector('.upload-label').innerHTML = `
        <span style="color:var(--success)">${file.name}</span><br>
        <small>Nueva foto seleccionada</small>`;

    const reader = new FileReader();
    reader.onload = (e) => {
        fotoPreview.src = e.target.result;
        fotoPreview.classList.add('visible');
    };
    reader.readAsDataURL(file);
});

inputPdf.addEventListener('change', async () => {
    const file = inputPdf.files[0];
    if (!file) return;

    const pdfPreview = document.getElementById('pdfPreviewIframe');
    const hayPdfActual = modoEdicion && pdfPreview && pdfPreview.style.display !== 'none';

    if (hayPdfActual) {
        const confirm = await Swal.fire({
            icon: 'question',
            title: '¿Reemplazar PDF?',
            html: `Se reemplazará la tarjeta actual por <strong>${file.name}</strong>.<br>
                   <small style="color:var(--text-muted)">El cambio se aplicará al guardar.</small>`,
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-arrow-repeat"></i> Sí, reemplazar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#3a7bd5',
            cancelButtonColor: '#e05252',
            background: '#1a1d27',
            color: '#e8eaf0'
        });

        if (!confirm.isConfirmed) {
            inputPdf.value = '';
            return;
        }

        pdfPreview.style.display = 'none';
        pdfPreview.src = '';
    }

    areaPdf.classList.add('has-file');
    areaPdf.querySelector('.upload-icon i').className = 'bi bi-check-circle-fill';
    areaPdf.querySelector('.upload-label').innerHTML = `
        <span style="color:var(--success)">${file.name}</span><br>
        <small>Nuevo PDF seleccionado</small>`;
    pdfNombre.style.display = 'block';
    pdfNombre.querySelector('span').textContent = file.name;
});

// ── HELPERS UI ────────────────────────────────────────────────────────────────
const resetArchivos = () => {
    areaFoto.classList.remove('has-file');
    fotoPreview.classList.remove('visible');
    fotoPreview.src = '';
    areaFoto.querySelector('.upload-icon i').className = 'bi bi-image';
    areaFoto.querySelector('.upload-label').innerHTML = `
        <span>Haz clic</span> o arrastra la foto aquí<br>
        <small>JPG, PNG, WEBP — máx. 5 MB</small>`;

    areaPdf.classList.remove('has-file');
    pdfNombre.style.display = 'none';
    areaPdf.querySelector('.upload-icon i').className = 'bi bi-file-pdf';
    areaPdf.querySelector('.upload-label').innerHTML = `
        <span>Haz clic</span> o arrastra el PDF aquí<br>
        <small>Solo PDF — máx. 10 MB</small>`;

    fotoActualContainer.style.display = 'none';

    const pdfPreview = document.getElementById('pdfPreviewIframe');
    if (pdfPreview) {
        pdfPreview.style.display = 'none';
        pdfPreview.src = '';
    }
};

const resetAsignacion = () => {
    selectUnidad.value = '';
    infoDestacamento.style.display = 'none';
};

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
    contenedorFormulario.style.display === 'none'
        ? mostrarFormulario()
        : ocultarFormulario();
});

// ── RENDER CARTAS ─────────────────────────────────────────────────────────────
const estadoBadge = (estado) => {
    const map = { Alta: 'estado-Alta', Baja: 'estado-Baja', Taller: 'estado-Taller' };
    return `<span class="card-estado ${map[estado] || ''}">${estado}</span>`;
};

const renderCartas = (vehiculos) => {
    if (!vehiculos.length) {
        cardsGrid.innerHTML = `
            <div class="empty-state">
                <i class="bi bi-search"></i>
                <p>No se encontraron vehículos con los filtros aplicados</p>
            </div>`;
        contadorVisible.textContent = '0';
        return;
    }

    contadorVisible.textContent = vehiculos.length;

    cardsGrid.innerHTML = vehiculos.map((v, i) => {
        const fotoHTML = v.foto_url
            ? `<img src="${v.foto_url}" alt="${v.placa}" loading="lazy"
                onerror="this.parentElement.innerHTML='<div class=\\'no-foto\\'><i class=\\'bi bi-image-slash\\'></i><span>Sin foto</span></div>'">`
            : `<div class="no-foto"><i class="bi bi-truck-front"></i><span>Sin foto</span></div>`;

        const pdfBadge = v.pdf_url
            ? `<a href="${v.pdf_url}" target="_blank" class="card-pdf-badge" title="Ver tarjeta">
                   <i class="bi bi-file-earmark-pdf-fill"></i>
               </a>` : '';

        const unidadHTML = v.unidad_nombre
            ? `<div class="card-unidad">
           <i class="bi bi-people-fill"></i>
           ${v.unidad_nombre}
       </div>
       <div class="card-unidad">
           <i class="bi bi-geo-alt-fill"></i>
           ${v.destacamento_depto || ''}
       </div>` : '';

        return `
            <div class="vehicle-card" style="animation-delay:${i * 0.05}s">
                <div class="card-foto">
                    ${fotoHTML}
                    ${estadoBadge(v.estado)}
                    ${pdfBadge}
                </div>
                <div class="card-info">
                    <div class="card-placa">${v.placa}</div>
                    <div class="card-vehiculo">${v.marca} ${v.modelo}</div>
                    <div class="card-tipo">
                        <i class="bi bi-truck" style="color:var(--accent)"></i>
                        ${v.tipo} · ${v.anio}
                    </div>
                    ${unidadHTML}
                </div>
                <div class="card-acciones">
                    <button class="btn-card-action btn-card-edit modificar"
                        data-placa="${v.placa}"
                        data-numero_serie="${v.numero_serie}"
                        data-marca="${v.marca}"
                        data-modelo="${v.modelo}"
                        data-anio="${v.anio}"
                        data-color="${v.color}"
                        data-tipo="${v.tipo}"
                        data-km_actuales="${v.km_actuales}"
                        data-estado="${v.estado}"
                        data-fecha_ingreso="${v.fecha_ingreso}"
                        data-observaciones="${v.observaciones || ''}"
                        data-foto_url="${v.foto_url || ''}"
                        data-pdf_url="${v.pdf_url || ''}"
                        data-id_unidad="${v.id_unidad || ''}">
                        <i class="bi bi-pencil-square"></i> Editar
                    </button>
                    <button class="btn-card-action btn-card-del eliminar"
                        data-placa="${v.placa}">
                        <i class="bi bi-trash3"></i>
                    </button>
                    <button class="btn-card-action"
                        style="background:rgba(232,184,75,.15);color:var(--accent);border:1px solid rgba(232,184,75,.2);"
                        onclick="abrirFicha('${v.placa}')">
                        <i class="bi bi-card-checklist"></i> Ficha
                    </button>
                </div>
            </div>`;
    }).join('');

    cardsGrid.querySelectorAll('.modificar').forEach(btn => btn.addEventListener('click', traerDatos));
    cardsGrid.querySelectorAll('.eliminar').forEach(btn => btn.addEventListener('click', eliminar));
};

// ── FILTROS ───────────────────────────────────────────────────────────────────
const aplicarFiltros = () => {
    const tipo = filtroTipo.value.toLowerCase();
    const estado = filtroEstado.value.toLowerCase();
    const busq = filtroBusqueda.value.toLowerCase().trim();

    const filtrados = todosLosVehiculos.filter(v => {
        const matchTipo = !tipo || v.tipo.toLowerCase() === tipo;
        const matchEstado = !estado || v.estado.toLowerCase() === estado;
        const matchBusq = !busq
            || v.placa.toLowerCase().includes(busq)
            || v.marca.toLowerCase().includes(busq)
            || v.modelo.toLowerCase().includes(busq)
            || (v.numero_serie || '').toLowerCase().includes(busq)
            || (v.unidad_nombre || '').toLowerCase().includes(busq)
            || (v.destacamento_nombre || '').toLowerCase().includes(busq);
        return matchTipo && matchEstado && matchBusq;
    });

    renderCartas(filtrados);
};

filtroTipo.addEventListener('change', aplicarFiltros);
filtroEstado.addEventListener('change', aplicarFiltros);
filtroBusqueda.addEventListener('input', aplicarFiltros);
btnLimpiarFiltros.addEventListener('click', () => {
    filtroTipo.value = '';
    filtroEstado.value = '';
    filtroBusqueda.value = '';
    renderCartas(todosLosVehiculos);
});

// ── BUSCAR ────────────────────────────────────────────────────────────────────
const buscar = async () => {
    try {
        const respuesta = await fetch(`${BASE}/API/vehiculos/buscar`, { method: 'GET' });
        const data = await respuesta.json();
        todosLosVehiculos = data.datos || [];
        aplicarFiltros();
    } catch (error) {
        console.error('Error al buscar vehículos:', error);
        cardsGrid.innerHTML = `
            <div class="empty-state">
                <i class="bi bi-wifi-off"></i>
                <p>Error al cargar los vehículos</p>
            </div>`;
    }
};

// ── GUARDAR ───────────────────────────────────────────────────────────────────
const guardar = async (e) => {
    e.preventDefault();
    btnGuardar.disabled = true;

    if (!validarFormulario(formulario, ['placa_original', 'observaciones', 'foto_frente', 'tarjeta_pdf', 'id_unidad'])) {
        Swal.fire({
            title: 'Campos vacíos',
            text: 'Debe llenar todos los campos obligatorios',
            icon: 'info',
            background: '#1a1d27',
            color: '#e8eaf0',
            confirmButtonColor: '#e8b84b'
        });
        btnGuardar.disabled = false;
        return;
    }

    try {
        const body = new FormData(formulario);
        const respuesta = await fetch(`${BASE}/API/vehiculos/guardar`, { method: 'POST', body });
        const data = await respuesta.json();

        if (data.codigo == 1) {
            formulario.reset();
            resetArchivos();
            resetAsignacion();
            buscar();
            ocultarFormulario();
        }

        Toast.fire({ icon: data.codigo == 1 ? 'success' : 'error', title: data.mensaje });
    } catch (error) {
        console.error(error);
        Toast.fire({ icon: 'error', title: 'Error de conexión al guardar' });
    }

    btnGuardar.disabled = false;
};

// ── TRAER DATOS ───────────────────────────────────────────────────────────────
const traerDatos = (e) => {
    modoEdicion = true;
    const d = e.currentTarget.dataset;

    inputPlaca.value = d.placa;
    inputPlaca.readOnly = true;
    inputPlaca.style.opacity = '.6';
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

    // Foto actual
    if (d.foto_url && d.foto_url !== 'null' && d.foto_url !== '') {
        areaFoto.classList.add('has-file');
        areaFoto.querySelector('.upload-icon i').className = 'bi bi-check-circle-fill';
        areaFoto.querySelector('.upload-label').innerHTML = `
            <span style="color:var(--success)">Foto cargada</span><br>
            <small>Sube una nueva para reemplazarla</small>`;
        fotoPreview.src = d.foto_url;
        fotoPreview.classList.add('visible');
    }

    // PDF actual
    if (d.pdf_url && d.pdf_url !== 'null' && d.pdf_url !== '') {
        areaPdf.classList.add('has-file');
        areaPdf.querySelector('.upload-icon i').className = 'bi bi-check-circle-fill';
        areaPdf.querySelector('.upload-label').innerHTML = `
            <span style="color:var(--success)">PDF cargado</span><br>
            <small>Sube uno nuevo para reemplazarlo</small>`;

        let pdfPreview = document.getElementById('pdfPreviewIframe');
        if (!pdfPreview) {
            pdfPreview = document.createElement('iframe');
            pdfPreview.id = 'pdfPreviewIframe';
            pdfPreview.style.cssText = `
                width:100%;height:180px;
                border:2px solid var(--border);
                border-radius:8px;margin-top:.75rem;
                background:var(--dark-3);`;
            areaPdf.parentElement.appendChild(pdfPreview);
        }
        pdfPreview.src = d.pdf_url;
        pdfPreview.style.display = 'block';
    }

    // Unidad asignada — seleccionar y disparar el panel informativo
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
    formulario.reset();
    resetArchivos();
    resetAsignacion();
    inputPlaca.readOnly = false;
    inputPlaca.style.opacity = '1';
    ocultarFormulario();
    btnGuardar.parentElement.style.display = '';
    btnModificar.parentElement.style.display = 'none';
};

// ── MODIFICAR ─────────────────────────────────────────────────────────────────
const modificar = async () => {
    if (!validarFormulario(formulario, ['observaciones', 'foto_frente', 'tarjeta_pdf', 'id_unidad'])) {
        Swal.fire({
            title: 'Campos vacíos',
            text: 'Debe llenar todos los campos obligatorios',
            icon: 'info',
            background: '#1a1d27',
            color: '#e8eaf0',
            confirmButtonColor: '#e8b84b'
        });
        return;
    }

    try {
        const body = new FormData(formulario);
        body.set('placa', inputPlacaOriginal.value);

        const respuesta = await fetch(`${BASE}/API/vehiculos/modificar`, { method: 'POST', body });
        const data = await respuesta.json();

        if (data.codigo == 1) {
            formulario.reset();
            resetArchivos();
            resetAsignacion();
            buscar();
            cancelar();
        }

        Toast.fire({ icon: data.codigo == 1 ? 'success' : 'error', title: data.mensaje });
    } catch (error) {
        console.error(error);
        Toast.fire({ icon: 'error', title: 'Error de conexión al modificar' });
    }
};

// ── ELIMINAR ──────────────────────────────────────────────────────────────────
const eliminar = async (e) => {
    const placa = e.currentTarget.dataset.placa;

    const confirmacion = await Swal.fire({
        icon: 'warning',
        title: '¿Eliminar vehículo?',
        html: `Se eliminará el vehículo con placa <strong>${placa}</strong> y sus archivos.<br>Esta acción no se puede deshacer.`,
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e05252',
        cancelButtonColor: '#3a7bd5',
        background: '#1a1d27',
        color: '#e8eaf0'
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

// ── MODAL FICHA ───────────────────────────────────────────────────────────────
let fichaPlacaActual = '';
let tiposServicio = [];
let tiposReparacion = [];
let reparacionEditandoId = null;

const cargarTiposServicio = async () => {
    const sel = document.getElementById('svcTipo');
    if (sel.options.length > 1) return;

    if (tiposServicio.length) {
        sel.innerHTML = '<option value="">Seleccione tipo...</option>' +
            tiposServicio.map(t =>
                `<option value="${t.id_tipo_servicio}"
                    data-km="${t.intervalo_km || ''}"
                    data-dias="${t.intervalo_dias || ''}">
                    ${t.nombre}
                </option>`
            ).join('');
        return;
    }

    const r = await fetch(`${BASE}/API/vehiculos/tipos-servicio`);
    const d = await r.json();
    if (d.codigo === 1) {
        tiposServicio = d.datos;
        sel.innerHTML = '<option value="">Seleccione tipo...</option>' +
            tiposServicio.map(t =>
                `<option value="${t.id_tipo_servicio}"
                    data-km="${t.intervalo_km || ''}"
                    data-dias="${t.intervalo_dias || ''}">
                    ${t.nombre}
                </option>`
            ).join('');
    }
};

const cargarTiposReparacion = async () => {
    const sel = document.getElementById('repTipo');
    if (sel.options.length > 1) return;

    if (tiposReparacion.length) {
        sel.innerHTML = '<option value="">Seleccione tipo...</option>' +
            tiposReparacion.map(t =>
                `<option value="${t.id_tipo_reparacion}">${t.nombre}</option>`
            ).join('');
        return;
    }

    const r = await fetch(`${BASE}/API/vehiculos/tipos-reparacion`);
    const d = await r.json();
    if (d.codigo === 1) {
        tiposReparacion = d.datos;
        sel.innerHTML = '<option value="">Seleccione tipo...</option>' +
            tiposReparacion.map(t =>
                `<option value="${t.id_tipo_reparacion}">${t.nombre}</option>`
            ).join('');
    }
};

const toggleFormServicio = () => {
    const form = document.getElementById('formNuevoServicio');
    const btn = document.getElementById('btnToggleFormServicio');
    const visible = form.style.display !== 'none';
    form.style.display = visible ? 'none' : 'block';
    btn.innerHTML = visible
        ? '<i class="bi bi-plus-circle"></i> Registrar Nuevo Servicio'
        : '<i class="bi bi-x-circle"></i> Cancelar';
};

const resetFormServicio = () => {
    const form = document.getElementById('formNuevoServicio');
    const btn = document.getElementById('btnToggleFormServicio');
    if (form) form.style.display = 'none';
    if (btn) btn.innerHTML = '<i class="bi bi-plus-circle"></i> Registrar Nuevo Servicio';
};

const toggleFormReparacion = () => {
    const form = document.getElementById('formNuevaReparacion');
    const btn = document.getElementById('btnToggleFormReparacion');
    const visible = form.style.display !== 'none';
    form.style.display = visible ? 'none' : 'block';
    btn.innerHTML = visible
        ? '<i class="bi bi-plus-circle"></i> Registrar Nueva Reparación'
        : '<i class="bi bi-x-circle"></i> Cancelar';
};

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

const abrirFicha = async (placa) => {
    fichaPlacaActual = placa;
    const modal = document.getElementById('modalFicha');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    resetFormServicio();
    resetFormReparacion();

    switchTab(document.querySelector('.ficha-tab[data-tab="info"]'), 'info');

    document.getElementById('fichaPlaca').textContent = placa;
    document.getElementById('fichaVehiculo').textContent = 'Cargando...';

    await cargarTiposServicio();
    await cargarTiposReparacion();

    document.getElementById('svcFecha').value = new Date().toISOString().split('T')[0];

    try {
        const r = await fetch(`${BASE}/API/vehiculos/ficha?placa=${placa}`);
        const d = await r.json();
        if (d.codigo !== 1) return;

        const v = d.vehiculo;

        // Header
        document.getElementById('fichaPlaca').textContent = v.placa;
        document.getElementById('fichaVehiculo').textContent = `${v.marca} ${v.modelo} · ${v.anio}`;

        // Foto
        const img = document.getElementById('fichaFoto');
        const noFoto = document.getElementById('fichaNoFoto');
        if (v.foto_url) {
            img.src = v.foto_url;
            img.style.display = 'block';
            noFoto.style.display = 'none';
        } else {
            img.style.display = 'none';
            noFoto.style.display = 'flex';
        }

        // PDF
        const pdfBtn = document.getElementById('fichaPdfBtn');
        if (v.pdf_url) {
            pdfBtn.href = v.pdf_url;
            pdfBtn.style.display = 'block';
        } else {
            pdfBtn.style.display = 'none';
        }

        // Datos generales
        document.getElementById('fd-placa').textContent = v.placa;
        document.getElementById('fd-serie').textContent = v.numero_serie;
        document.getElementById('fd-marca').textContent = v.marca;
        document.getElementById('fd-modelo').textContent = v.modelo;
        document.getElementById('fd-anio').textContent = v.anio;
        document.getElementById('fd-color').textContent = v.color;
        document.getElementById('fd-tipo').textContent = v.tipo;
        document.getElementById('fd-km').textContent = Number(v.km_actuales).toLocaleString() + ' km';
        document.getElementById('fd-ingreso').textContent = v.fecha_ingreso;
        document.getElementById('fd-obs').textContent = v.observaciones || '—';

        // Unidad y Destacamento en ficha
        document.getElementById('fd-unidad').textContent = v.unidad_nombre || '—';
        document.getElementById('fd-destacamento').textContent = v.destacamento_nombre
            ? `${v.destacamento_nombre} (${v.destacamento_depto})`
            : '—';

        // Estado con color
        const estadoEl = document.getElementById('fd-estado');
        const colores = { Alta: '#4caf7d', Baja: '#e05252', Taller: '#e8b84b' };
        estadoEl.textContent = v.estado;
        estadoEl.style.color = colores[v.estado] || 'inherit';

        // Alertas de servicio
        document.getElementById('fichaAlerta').style.display = 'none';
        document.getElementById('fichaProximo').style.display = 'none';

        if (d.proximo_servicio) {
            const ps = d.proximo_servicio;
            if (d.alerta_km) {
                document.getElementById('fichaAlerta').style.display = 'flex';
                document.getElementById('fichaAlertaTexto').textContent =
                    `${ps.tipo_nombre} — venció a los ${Number(ps.km_proximo_servicio).toLocaleString()} km. KM actual: ${Number(v.km_actuales).toLocaleString()} km`;
            } else {
                document.getElementById('fichaProximo').style.display = 'flex';
                let texto = `${ps.tipo_nombre} a los ${Number(ps.km_proximo_servicio).toLocaleString()} km`;
                if (ps.fecha_proximo) texto += ` · Fecha límite: ${ps.fecha_proximo}`;
                document.getElementById('fichaProximoTexto').textContent = texto;
            }
        }

        document.getElementById('svcKm').value = v.km_actuales;

        // Badges
        document.getElementById('badgeServicios').textContent = d.servicios.length;
        document.getElementById('badgeReparaciones').textContent = d.reparaciones.length;

        renderTablaServicios(d.servicios);
        renderTablaReparaciones(d.reparaciones);

    } catch (err) {
        console.error(err);
        Toast.fire({ icon: 'error', title: 'Error al cargar la ficha' });
    }
};

const cerrarFicha = () => {
    document.getElementById('modalFicha').style.display = 'none';
    document.body.style.overflow = '';
    fichaPlacaActual = '';
};

document.getElementById('modalFicha').addEventListener('click', (e) => {
    if (e.target === document.getElementById('modalFicha')) cerrarFicha();
});

const switchTab = (btn, tab) => {
    document.querySelectorAll('.ficha-tab').forEach(b => b.classList.remove('activo'));
    document.querySelectorAll('.ficha-tab-content').forEach(c => c.style.display = 'none');
    btn.classList.add('activo');
    document.getElementById('tab' + tab.charAt(0).toUpperCase() + tab.slice(1)).style.display = 'block';
};

// ── RENDER SERVICIOS ──────────────────────────────────────────────────────────
const renderTablaServicios = (servicios) => {
    const wrap = document.getElementById('tablaServiciosWrap');
    if (!servicios.length) {
        wrap.innerHTML = `
            <div style="text-align:center;padding:2rem;color:var(--text-muted);">
                <i class="bi bi-tools" style="font-size:2.5rem;opacity:.2;display:block;margin-bottom:.75rem;"></i>
                <p>No hay servicios registrados aún</p>
            </div>`;
        return;
    }

    wrap.innerHTML = servicios.map(s => `
        <div class="svc-row">
            <div><div class="svc-label">Tipo</div><div class="svc-val">${s.tipo_nombre}</div></div>
            <div><div class="svc-label">Fecha</div><div class="svc-val">${s.fecha_realizado}</div></div>
            <div><div class="svc-label">KM Realizado</div><div class="svc-val">${Number(s.km_al_servicio).toLocaleString()} km</div></div>
            <div>
                <div class="svc-label">Próximo KM</div>
                <div class="svc-val" style="color:${s.km_proximo_servicio ? 'var(--accent)' : 'var(--text-muted)'}">
                    ${s.km_proximo_servicio ? Number(s.km_proximo_servicio).toLocaleString() + ' km' : '—'}
                </div>
            </div>
            <div style="display:flex;gap:.4rem;align-items:center;">
                <button onclick="eliminarServicio(${s.id_servicio})" style="
                    background:rgba(224,82,82,.15);border:1px solid rgba(224,82,82,.3);
                    color:var(--danger);border-radius:6px;padding:.35rem .6rem;
                    cursor:pointer;font-size:.8rem;" title="Eliminar">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>
        </div>
        ${s.responsable ? `
        <div style="font-size:.75rem;color:var(--text-muted);margin-top:-.4rem;margin-bottom:.4rem;padding-left:.25rem;">
            <i class="bi bi-person"></i> ${s.responsable}
            ${s.observaciones ? ' · ' + s.observaciones : ''}
        </div>` : ''}
    `).join('');
};

// ── GUARDAR SERVICIO ──────────────────────────────────────────────────────────
const guardarServicio = async () => {
    const tipo = document.getElementById('svcTipo').value;
    const fecha = document.getElementById('svcFecha').value;
    const km = document.getElementById('svcKm').value;

    if (!tipo || !fecha || !km) {
        Swal.fire({
            icon: 'info',
            title: 'Tipo, fecha y KM son obligatorios',
            background: '#1a1d27',
            color: '#e8eaf0',
            confirmButtonColor: '#e8b84b',
            customClass: { container: 'swal-over-modal' }
        });
        return;
    }

    const body = new FormData();
    body.append('placa', fichaPlacaActual);
    body.append('id_tipo_servicio', tipo);
    body.append('fecha_realizado', fecha);
    body.append('km_al_servicio', km);
    body.append('responsable', document.getElementById('svcResponsable').value);
    body.append('observaciones', document.getElementById('svcObs').value);

    try {
        const r = await fetch(`${BASE}/API/vehiculos/servicio/guardar`, { method: 'POST', body });
        const d = await r.json();

        Toast.fire({ icon: d.codigo === 1 ? 'success' : 'error', title: d.mensaje });

        if (d.codigo === 1) {
            document.getElementById('svcTipo').value = '';
            document.getElementById('svcResponsable').value = '';
            document.getElementById('svcObs').value = '';
            resetFormServicio();
            await abrirFicha(fichaPlacaActual);
            switchTab(document.querySelector('.ficha-tab[data-tab="servicios"]'), 'servicios');
            buscar();
        }
    } catch (err) {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    }
};

// ── ELIMINAR SERVICIO ─────────────────────────────────────────────────────────
const eliminarServicio = async (id) => {
    const conf = await Swal.fire({
        icon: 'warning',
        title: '¿Eliminar servicio?',
        text: 'Esta acción no se puede deshacer.',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e05252',
        cancelButtonColor: '#3a7bd5',
        background: '#1a1d27',
        color: '#e8eaf0',
        customClass: { container: 'swal-over-modal' }
    });

    if (!conf.isConfirmed) return;

    const body = new FormData();
    body.append('id_servicio', id);

    const r = await fetch(`${BASE}/API/vehiculos/servicio/eliminar`, { method: 'POST', body });
    const d = await r.json();

    Toast.fire({ icon: d.codigo === 1 ? 'success' : 'error', title: d.mensaje });
    if (d.codigo === 1) {
        await abrirFicha(fichaPlacaActual);
        switchTab(document.querySelector('.ficha-tab[data-tab="servicios"]'), 'servicios');
    }
};

// ── RENDER REPARACIONES ───────────────────────────────────────────────────────
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
            <div>
                <div class="svc-label">Estado</div>
                <div class="svc-val" style="color:${r.estado === 'En proceso' ? 'var(--accent)' : 'var(--success)'}">
                    ${r.estado}
                </div>
            </div>
            <div><div class="svc-label">Inicio</div><div class="svc-val">${r.fecha_inicio}</div></div>
            <div><div class="svc-label">Fin</div><div class="svc-val">${r.fecha_fin || '—'}</div></div>
            <div><div class="svc-label">Costo</div><div class="svc-val">${r.costo ? 'Q ' + Number(r.costo).toLocaleString() : '—'}</div></div>
            <div style="display:flex;gap:.4rem;align-items:center;">
                <button onclick="editarReparacion(${JSON.stringify(r).replace(/"/g, '&quot;')})" style="
                    background:rgba(58,123,213,.15);border:1px solid rgba(58,123,213,.3);
                    color:#5b9bd5;border-radius:6px;padding:.35rem .6rem;
                    cursor:pointer;font-size:.8rem;" title="Editar">
                    <i class="bi bi-pencil-square"></i>
                </button>
                <button onclick="eliminarReparacion(${r.id_reparacion})" style="
                    background:rgba(224,82,82,.15);border:1px solid rgba(224,82,82,.3);
                    color:var(--danger);border-radius:6px;padding:.35rem .6rem;
                    cursor:pointer;font-size:.8rem;" title="Eliminar">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>
        </div>
        <div style="font-size:.75rem;color:var(--text-muted);margin-top:-.4rem;margin-bottom:.6rem;padding-left:.25rem;">
            ${r.descripcion}
            ${r.proveedor ? ' · <i class="bi bi-shop"></i> ' + r.proveedor : ''}
            ${r.responsable ? ' · <i class="bi bi-person"></i> ' + r.responsable : ''}
            ${r.km_al_momento ? ' · <i class="bi bi-speedometer"></i> ' + Number(r.km_al_momento).toLocaleString() + ' km' : ''}
        </div>
    `).join('');
};

// ── GUARDAR REPARACIÓN ────────────────────────────────────────────────────────
const guardarReparacion = async () => {
    const tipo = document.getElementById('repTipo').value;
    const desc = document.getElementById('repDescripcion').value;
    const fecha = document.getElementById('repFechaInicio').value;
    const km = document.getElementById('repKm').value;

    if (!tipo || !desc || !fecha || !km) {
        Swal.fire({
            icon: 'info',
            title: 'Tipo, descripción, fecha y KM son obligatorios',
            background: '#1a1d27',
            color: '#e8eaf0',
            confirmButtonColor: '#e8b84b',
            customClass: { container: 'swal-over-modal' }
        });
        return;
    }

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

    const url = esEdicion
        ? `${BASE}/API/vehiculos/reparacion/modificar`
        : `${BASE}/API/vehiculos/reparacion/guardar`;

    try {
        const r = await fetch(url, { method: 'POST', body });
        const d = await r.json();

        Toast.fire({ icon: d.codigo === 1 ? 'success' : 'error', title: d.mensaje });

        if (d.codigo === 1) {
            reparacionEditandoId = null;
            ['repTipo', 'repDescripcion', 'repFechaFin', 'repCosto',
                'repProveedor', 'repResponsable', 'repObs'].forEach(id => {
                    document.getElementById(id).value = '';
                });
            document.getElementById('repEstado').value = 'En proceso';
            resetFormReparacion();
            await abrirFicha(fichaPlacaActual);
            switchTab(document.querySelector('.ficha-tab[data-tab="reparaciones"]'), 'reparaciones');
            buscar();
        }
    } catch (err) {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    }
};

// ── EDITAR REPARACIÓN ─────────────────────────────────────────────────────────
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

    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

// ── ELIMINAR REPARACIÓN ───────────────────────────────────────────────────────
const eliminarReparacion = async (id) => {
    const conf = await Swal.fire({
        icon: 'warning',
        title: '¿Eliminar reparación?',
        text: 'Esta acción no se puede deshacer.',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e05252',
        cancelButtonColor: '#3a7bd5',
        background: '#1a1d27',
        color: '#e8eaf0',
        customClass: { container: 'swal-over-modal' }
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

// ── AUTO-UPPERCASE ────────────────────────────────────────────────────────────
document.getElementById('placa').addEventListener('input', function () {
    this.value = this.value.toUpperCase();
});
document.getElementById('numero_serie').addEventListener('input', function () {
    this.value = this.value.toUpperCase();
});

// ── EVENT LISTENERS ───────────────────────────────────────────────────────────
formulario.addEventListener('submit', guardar);
btnCancelar.addEventListener('click', cancelar);
btnModificar.addEventListener('click', modificar);

// ── EXPONER GLOBALES (type="module") ──────────────────────────────────────────
window.abrirFicha = abrirFicha;
window.cerrarFicha = cerrarFicha;
window.switchTab = switchTab;
window.guardarServicio = guardarServicio;
window.eliminarServicio = eliminarServicio;
window.toggleFormServicio = toggleFormServicio;
window.toggleFormReparacion = toggleFormReparacion;
window.guardarReparacion = guardarReparacion;
window.editarReparacion = editarReparacion;
window.eliminarReparacion = eliminarReparacion;

// ── INIT ──────────────────────────────────────────────────────────────────────
cargarUnidades();
buscar();