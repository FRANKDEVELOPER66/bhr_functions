import { Toast, validarFormulario } from "../funciones";
import Swal from "sweetalert2";

const BASE = '/bhr_functions';

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

// Estado global
let todosLosVehiculos = [];

// ── FILE UPLOAD PREVIEW ───────────────────────────────────────────────────────
inputFoto.addEventListener('change', () => {
    const file = inputFoto.files[0];
    if (file) {
        areaFoto.classList.add('has-file');
        const reader = new FileReader();
        reader.onload = (e) => {
            fotoPreview.src = e.target.result;
            fotoPreview.classList.add('visible');
        };
        reader.readAsDataURL(file);
    } else {
        areaFoto.classList.remove('has-file');
        fotoPreview.classList.remove('visible');
    }
});

inputPdf.addEventListener('change', () => {
    const file = inputPdf.files[0];
    if (file) {
        areaPdf.classList.add('has-file');
        pdfNombre.style.display = 'block';
        pdfNombre.querySelector('span').textContent = file.name;
    } else {
        areaPdf.classList.remove('has-file');
        pdfNombre.style.display = 'none';
    }
});

// ── HELPERS UI ───────────────────────────────────────────────────────────────
const resetArchivos = () => {
    // Foto
    areaFoto.classList.remove('has-file');
    fotoPreview.classList.remove('visible');
    fotoPreview.src = '';
    areaFoto.querySelector('.upload-icon i').className = 'bi bi-image';
    areaFoto.querySelector('.upload-label').innerHTML = `
        <span>Haz clic</span> o arrastra la foto aquí<br>
        <small>JPG, PNG, WEBP — máx. 5 MB</small>`;

    // PDF
    areaPdf.classList.remove('has-file');
    pdfNombre.style.display = 'none';
    areaPdf.querySelector('.upload-icon i').className = 'bi bi-file-pdf';
    areaPdf.querySelector('.upload-label').innerHTML = `
        <span>Haz clic</span> o arrastra el PDF aquí<br>
        <small>Solo PDF — máx. 10 MB</small>`;

    // Ocultar foto actual (col derecha)
    fotoActualContainer.style.display = 'none';
    const pdfPreview = document.getElementById('pdfPreviewIframe');
    if (pdfPreview) {
        pdfPreview.style.display = 'none';
        pdfPreview.src = '';
    }
};

const mostrarFormulario = () => {
    contenedorFormulario.style.display = '';
    contenedorFormulario.classList.add('slide-down');
    contenedorTabla.style.display = 'none';
    tituloFormulario.textContent = 'Nuevo Vehículo';
    formulario.reset();
    resetArchivos();

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

// ── RENDER CARTAS ────────────────────────────────────────────────────────────
const estadoBadge = (estado) => {
    const map = { 'Alta': 'estado-Alta', 'Baja': 'estado-Baja', 'Taller': 'estado-Taller' };
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
            ? `<img src="${v.foto_url}" alt="${v.placa}" loading="lazy" onerror="this.parentElement.innerHTML='<div class=\\'no-foto\\'><i class=\\'bi bi-image-slash\\'></i><span>Sin foto</span></div>'">`
            : `<div class="no-foto"><i class="bi bi-truck-front"></i><span>Sin foto</span></div>`;

        const pdfBadge = v.pdf_url
            ? `<a href="${v.pdf_url}" target="_blank" class="card-pdf-badge" title="Ver tarjeta de circulación">
                   <i class="bi bi-file-earmark-pdf-fill"></i>
               </a>` : '';

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
                </div>
                <div class="card-acciones">
                    <button class="btn-card-action btn-card-edit modificar"
                        title="Modificar"
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
                        data-pdf_url="${v.pdf_url || ''}">
                        <i class="bi bi-pencil-square"></i> Editar
                    </button>
                    <button class="btn-card-action btn-card-del eliminar"
                        title="Eliminar"
                        data-placa="${v.placa}">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </div>`;
    }).join('');

    // Re-attachar eventos a los botones dentro de las cartas
    cardsGrid.querySelectorAll('.modificar').forEach(btn => btn.addEventListener('click', traerDatos));
    cardsGrid.querySelectorAll('.eliminar').forEach(btn => btn.addEventListener('click', eliminar));
};

// ── FILTROS ──────────────────────────────────────────────────────────────────
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
            || (v.numero_serie || '').toLowerCase().includes(busq);
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

// ── BUSCAR ───────────────────────────────────────────────────────────────────
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

inputFoto.addEventListener('change', async () => {
    const file = inputFoto.files[0];
    if (!file) return;

    // Si ya había foto cargada (modo edición), pedir confirmación
    if (areaFoto.classList.contains('has-file') && fotoPreview.src && !fotoPreview.src.endsWith('#')) {
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
            inputFoto.value = ''; // limpiar selección
            return;
        }
    }

    // Mostrar preview de la nueva foto
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

    // Si ya había PDF cargado (modo edición), pedir confirmación
    const pdfPreview = document.getElementById('pdfPreviewIframe');
    if (areaPdf.classList.contains('has-file') && pdfPreview && pdfPreview.src) {
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
            inputPdf.value = ''; // limpiar selección
            return;
        }

        // Ocultar preview del PDF anterior
        pdfPreview.style.display = 'none';
        pdfPreview.src = '';
    }

    // Mostrar nombre del nuevo PDF
    areaPdf.classList.add('has-file');
    areaPdf.querySelector('.upload-icon i').className = 'bi bi-check-circle-fill';
    areaPdf.querySelector('.upload-label').innerHTML = `
        <span style="color:var(--success)">${file.name}</span><br>
        <small>Nuevo PDF seleccionado</small>`;
    pdfNombre.style.display = 'block';
    pdfNombre.querySelector('span').textContent = file.name;
});

// ── GUARDAR ──────────────────────────────────────────────────────────────────
const guardar = async (e) => {
    e.preventDefault();
    btnGuardar.disabled = true;

    if (!validarFormulario(formulario, ['placa_original', 'observaciones', 'foto_frente', 'tarjeta_pdf'])) {
        Swal.fire({ title: 'Campos vacíos', text: 'Debe llenar todos los campos obligatorios', icon: 'info', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b' });
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

    // ── Foto actual dentro del área de foto ──────────────────────────────────
    if (d.foto_url && d.foto_url !== 'null' && d.foto_url !== '') {
        areaFoto.classList.add('has-file');
        areaFoto.querySelector('.upload-icon i').className = 'bi bi-check-circle-fill';
        areaFoto.querySelector('.upload-label').innerHTML = `
            <span style="color:var(--success)">Foto cargada</span><br>
            <small>Sube una nueva para reemplazarla</small>`;
        fotoPreview.src = d.foto_url;
        fotoPreview.classList.add('visible');
    }
    // ── PDF actual dentro del área de PDF ────────────────────────────────────
    if (d.pdf_url && d.pdf_url !== 'null' && d.pdf_url !== '') {
        areaPdf.classList.add('has-file');
        areaPdf.querySelector('.upload-icon i').className = 'bi bi-check-circle-fill';
        areaPdf.querySelector('.upload-label').innerHTML = `
        <span style="color:var(--success)">PDF cargado</span><br>
        <small>Sube uno nuevo para reemplazarlo</small>`;

        // Crear o reutilizar el iframe preview
        let pdfPreview = document.getElementById('pdfPreviewIframe');
        if (!pdfPreview) {
            pdfPreview = document.createElement('iframe');
            pdfPreview.id = 'pdfPreviewIframe';
            pdfPreview.style.cssText = `
            width: 100%;
            height: 180px;
            border: 2px solid var(--border);
            border-radius: 8px;
            margin-top: .75rem;
            background: var(--dark-3);
        `;
            areaPdf.parentElement.appendChild(pdfPreview);
        }

        pdfPreview.src = d.pdf_url;
        pdfPreview.style.display = 'block';
    }

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

// ── CANCELAR ─────────────────────────────────────────────────────────────────
const cancelar = () => {
    formulario.reset();
    resetArchivos();
    inputPlaca.readOnly = false;
    inputPlaca.style.opacity = '1';
    ocultarFormulario();
    btnGuardar.parentElement.style.display = '';
    btnModificar.parentElement.style.display = 'none';
};

// ── MODIFICAR ────────────────────────────────────────────────────────────────
const modificar = async () => {
    if (!validarFormulario(formulario, ['observaciones', 'foto_frente', 'tarjeta_pdf'])) {
        Swal.fire({ title: 'Campos vacíos', text: 'Debe llenar todos los campos obligatorios', icon: 'info', background: '#1a1d27', color: '#e8eaf0', confirmButtonColor: '#e8b84b' });
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
            buscar();
            cancelar();
        }

        Toast.fire({ icon: data.codigo == 1 ? 'success' : 'error', title: data.mensaje });
    } catch (error) {
        console.error(error);
        Toast.fire({ icon: 'error', title: 'Error de conexión al modificar' });
    }
};

// ── ELIMINAR ─────────────────────────────────────────────────────────────────
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

// ── AUTO-UPPERCASE ────────────────────────────────────────────────────────────
document.getElementById('placa').addEventListener('input', function () { this.value = this.value.toUpperCase(); });
document.getElementById('numero_serie').addEventListener('input', function () { this.value = this.value.toUpperCase(); });

// ── EVENT LISTENERS ───────────────────────────────────────────────────────────
formulario.addEventListener('submit', guardar);
btnCancelar.addEventListener('click', cancelar);
btnModificar.addEventListener('click', modificar);

// ── INIT ──────────────────────────────────────────────────────────────────────
buscar();