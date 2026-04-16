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

// ── PDF PÓLIZA (formulario nuevo vehículo) ────────────────────────────────────
const inputPoliza = document.getElementById('archivo_poliza');
const areaPoliza = document.getElementById('areaPoliza');

if (inputPoliza && areaPoliza) {
    inputPoliza.addEventListener('change', () => {
        const file = inputPoliza.files[0];
        if (!file) return;

        areaPoliza.classList.add('has-file');
        areaPoliza.querySelector('.upload-icon i').className = 'bi bi-check-circle-fill';
        areaPoliza.querySelector('.upload-label').innerHTML = `
            <span style="color:var(--success)">${file.name}</span><br>
            <small>PDF seleccionado</small>`;
    });
}

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

    // ── Reset área póliza seguro ──────────────────────────────────────────────
    const areaPoliza = document.getElementById('areaPoliza');
    const inputPoliza = document.getElementById('archivo_poliza');
    if (areaPoliza) {
        areaPoliza.classList.remove('has-file');
        areaPoliza.querySelector('.upload-icon i').className = 'bi bi-file-pdf';
        areaPoliza.querySelector('.upload-label').innerHTML = `
            <span>Haz clic</span> para subir la póliza<br>
            <small>Solo PDF — máx. 10 MB</small>`;
    }
    if (inputPoliza) inputPoliza.value = '';
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
    // Reset seguro toggle
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

        // Badge de seguro: posicionado sobre la foto, esquina inferior izquierda
        let seguroBadge = '';
        if (v.seguro_estado === 'vigente') {
            seguroBadge = `<span style="position:absolute;bottom:.5rem;left:.5rem;background:rgba(76,175,125,.85);color:#fff;font-size:.65rem;font-weight:600;padding:.2rem .55rem;border-radius:20px;backdrop-filter:blur(4px);display:flex;align-items:center;gap:.3rem;"><i class="bi bi-shield-check"></i> Vigente</span>`;
        } else if (v.seguro_estado === 'vencido') {
            seguroBadge = `<span style="position:absolute;bottom:.5rem;left:.5rem;background:rgba(224,82,82,.85);color:#fff;font-size:.65rem;font-weight:600;padding:.2rem .55rem;border-radius:20px;backdrop-filter:blur(4px);display:flex;align-items:center;gap:.3rem;"><i class="bi bi-shield-exclamation"></i> Vencido</span>`;
        } else {
            seguroBadge = `<span style="position:absolute;bottom:.5rem;left:.5rem;background:rgba(30,33,48,.85);color:#888;font-size:.65rem;font-weight:600;padding:.2rem .55rem;border-radius:20px;border:1px solid rgba(150,150,150,.25);backdrop-filter:blur(4px);display:flex;align-items:center;gap:.3rem;"><i class="bi bi-shield-slash"></i> Sin seguro</span>`;
        }

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
                    ${seguroBadge}
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
    const filtroSeguro = document.getElementById('filtroSeguro')?.value.toLowerCase() || '';

    const filtrados = todosLosVehiculos.filter(v => {
        const matchTipo = !tipo || v.tipo.toLowerCase() === tipo;
        const matchEstado = !estado || v.estado.toLowerCase() === estado;
        const matchSeguro = !filtroSeguro || (v.seguro_estado || 'sin seguro').toLowerCase() === filtroSeguro;
        const matchBusq = !busq
            || v.placa.toLowerCase().includes(busq)
            || v.marca.toLowerCase().includes(busq)
            || v.modelo.toLowerCase().includes(busq)
            || (v.numero_serie || '').toLowerCase().includes(busq)
            || (v.unidad_nombre || '').toLowerCase().includes(busq)
            || (v.destacamento_nombre || '').toLowerCase().includes(busq);
        return matchTipo && matchEstado && matchSeguro && matchBusq;
    });

    renderCartas(filtrados);
};

filtroTipo.addEventListener('change', aplicarFiltros);
filtroEstado.addEventListener('change', aplicarFiltros);
filtroBusqueda.addEventListener('input', aplicarFiltros);
document.getElementById('filtroSeguro')?.addEventListener('change', aplicarFiltros);
btnLimpiarFiltros.addEventListener('click', () => {
    filtroTipo.value = '';
    filtroEstado.value = '';
    filtroBusqueda.value = '';
    const fs = document.getElementById('filtroSeguro');
    if (fs) fs.value = '';
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

    const body = new FormData();

    /* ===============================
       VEHICULO
    =============================== */

    body.append('placa', document.getElementById('placa').value.trim());
    body.append('numero_serie', document.getElementById('numero_serie').value.trim());
    body.append('marca', document.getElementById('marca').value);
    body.append('modelo', document.getElementById('modelo').value);
    body.append('anio', document.getElementById('anio').value);
    body.append('color', document.getElementById('color').value);
    body.append('tipo', document.getElementById('tipo').value);
    body.append('estado', document.getElementById('estado').value);
    body.append('fecha_ingreso', document.getElementById('fecha_ingreso').value);
    body.append('km_actuales', document.getElementById('km_actuales').value);
    body.append('observaciones', document.getElementById('observaciones').value);
    body.append('id_unidad', document.getElementById('id_unidad').value);

    /* ===============================
       ARCHIVOS VEHICULO
    =============================== */

    const foto = document.getElementById('foto_frente');
    if (foto && foto.files.length > 0) {
        body.append('foto_frente', foto.files[0]);
    }

    const tarjeta = document.getElementById('tarjeta_pdf');
    if (tarjeta && tarjeta.files.length > 0) {
        body.append('tarjeta_pdf', tarjeta.files[0]);
    }

    /* ===============================
       ¿TIENE SEGURO?
    =============================== */

    const tieneSeguro = document
        .getElementById('btnSeguroSi')
        .classList.contains('sel-si');

    if (tieneSeguro) {

        const aseguradora = document.getElementById('seg_aseguradora').value.trim();
        const poliza = document.getElementById('seg_numero_poliza').value.trim();
        const inicio = document.getElementById('seg_fecha_inicio').value;
        const venc = document.getElementById('seg_fecha_vencimiento').value;

        if (!aseguradora || !poliza || !inicio || !venc) {
            Swal.fire({
                icon: 'warning',
                title: 'Complete los datos del seguro',
                text: 'Faltan campos obligatorios del seguro',
                background: '#1a1d27',
                color: '#e8eaf0'
            });
            return;
        }

        body.append('seg_aseguradora', aseguradora);
        body.append('seg_numero_poliza', poliza);
        body.append('seg_tipo_cobertura', document.getElementById('seg_tipo_cobertura').value);
        body.append('seg_fecha_inicio', inicio);
        body.append('seg_fecha_vencimiento', venc);
        body.append('seg_prima_anual', document.getElementById('seg_prima_anual').value);
        body.append('seg_agente_contacto', document.getElementById('seg_agente_contacto').value);
        body.append('seg_telefono_agente', document.getElementById('seg_telefono_agente').value);
        body.append('seg_observaciones', document.getElementById('seg_observaciones').value);

        /* ===============================
           PDF POLIZA
        =============================== */

        const pdfPoliza = document.getElementById('archivo_poliza');

        if (pdfPoliza && pdfPoliza.files && pdfPoliza.files.length > 0) {

            const file = pdfPoliza.files[0];

            // validar tipo
            if (file.type !== "application/pdf") {
                Swal.fire({
                    icon: 'warning',
                    title: 'Archivo inválido',
                    text: 'La póliza debe ser PDF'
                });
                return;
            }

            body.append('archivo_poliza', file);
        }
    }
    /* ===============================
       ENVIAR
    =============================== */

    try {

        const r = await fetch(`${BASE}/API/vehiculos/guardar`, {
            method: 'POST',
            body
        });

        const d = await r.json();

        Toast.fire({
            icon: d.codigo === 1 ? 'success' : 'error',
            title: d.mensaje
        });

        if (d.codigo === 1) {
            cancelar();
            buscar();
        }

    } catch (err) {

        Toast.fire({
            icon: 'error',
            title: 'Error de conexión'
        });

    }
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
    // Reset seguro toggle
    document.getElementById('btnSeguroSi').classList.remove('sel-si');
    document.getElementById('btnSeguroNo').classList.remove('sel-no');
    document.getElementById('panelFormSeguro').style.display = 'none';
    document.getElementById('avisoSinSeguro').style.display = 'none';
    vehiculoTieneSeguro = false;
};

// ── MODIFICAR ─────────────────────────────────────────────────────────────────
const modificar = async () => {
    // Validar solo campos del vehículo
    const camposRequeridos = ['placa', 'numero_serie', 'marca', 'modelo',
        'anio', 'color', 'tipo', 'estado', 'fecha_ingreso'];

    let campoVacio = false;
    for (const campo of camposRequeridos) {
        const el = document.getElementById(campo);
        if (!el || !el.value.trim()) {
            campoVacio = true;
            if (el) el.style.borderColor = 'var(--danger)';
        } else {
            if (el) el.style.borderColor = '';
        }
    }

    if (campoVacio) {
        Swal.fire({
            title: 'Campos vacíos',
            text: 'Debe llenar todos los campos obligatorios marcados en rojo',
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
    resetFormSeguro();
    resetFormAccidente();
    cancelarChequeo();

    switchTab(document.querySelector('.ficha-tab[data-tab="info"]'), 'info');

    const fichaPlacaEl = document.getElementById('fichaPlaca');
    const fichaVehiculoEl = document.getElementById('fichaVehiculo');
    if (fichaPlacaEl) fichaPlacaEl.textContent = placa;
    if (fichaVehiculoEl) fichaVehiculoEl.textContent = 'Cargando...';

    await cargarTiposServicio();
    await cargarTiposReparacion();

    const svcFechaEl = document.getElementById('svcFecha');
    if (svcFechaEl) svcFechaEl.value = new Date().toISOString().split('T')[0];

    try {
        const r = await fetch(`${BASE}/API/vehiculos/ficha?placa=${placa}`);
        const d = await r.json();
        if (d.codigo !== 1) return;

        const v = d.vehiculo;

        // ── Header ────────────────────────────────────────────────────────────
        if (fichaPlacaEl) fichaPlacaEl.textContent = v.placa;
        if (fichaVehiculoEl) fichaVehiculoEl.textContent = `${v.marca} ${v.modelo} · ${v.anio}`;

        // ── Foto ──────────────────────────────────────────────────────────────
        const img = document.getElementById('fichaFoto');
        const noFoto = document.getElementById('fichaNoFoto');
        if (img && noFoto) {
            if (v.foto_url) {
                img.src = v.foto_url;
                img.style.display = 'block';
                noFoto.style.display = 'none';
            } else {
                img.style.display = 'none';
                noFoto.style.display = 'flex';
            }
        }

        // ── PDF ───────────────────────────────────────────────────────────────
        const pdfBtn = document.getElementById('fichaPdfBtn');
        if (pdfBtn) {
            if (v.pdf_url) {
                pdfBtn.href = v.pdf_url;
                pdfBtn.style.display = 'block';
            } else {
                pdfBtn.style.display = 'none';
            }
        }

        // ── Datos generales ───────────────────────────────────────────────────
        const _set = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val;
        };

        _set('fd-placa', v.placa);
        _set('fd-serie', v.numero_serie);
        _set('fd-marca', v.marca);
        _set('fd-modelo', v.modelo);
        _set('fd-anio', v.anio);
        _set('fd-color', v.color);
        _set('fd-tipo', v.tipo);
        _set('fd-km', Number(v.km_actuales).toLocaleString() + ' km');
        _set('fd-ingreso', v.fecha_ingreso);
        _set('fd-obs', v.observaciones || '—');
        _set('fd-unidad', v.unidad_nombre || '—');
        _set('fd-destacamento', v.destacamento_nombre
            ? `${v.destacamento_nombre} (${v.destacamento_depto})`
            : '—');

        // ── Estado con color ──────────────────────────────────────────────────
        const estadoEl = document.getElementById('fd-estado');
        if (estadoEl) {
            const colores = { Alta: '#4caf7d', Baja: '#e05252', Taller: '#e8b84b' };
            estadoEl.textContent = v.estado;
            estadoEl.style.color = colores[v.estado] || 'inherit';
        }

        // ── Alertas de servicio ───────────────────────────────────────────────
        const fichaAlertaEl = document.getElementById('fichaAlerta');
        const fichaProximoEl = document.getElementById('fichaProximo');
        if (fichaAlertaEl) fichaAlertaEl.style.display = 'none';
        if (fichaProximoEl) fichaProximoEl.style.display = 'none';

        if (d.proximo_servicio) {
            const ps = d.proximo_servicio;
            if (d.alerta_km) {
                if (fichaAlertaEl) {
                    fichaAlertaEl.style.display = 'flex';
                    const textoEl = document.getElementById('fichaAlertaTexto');
                    if (textoEl) textoEl.textContent =
                        `${ps.tipo_nombre} — venció a los ${Number(ps.km_proximo_servicio).toLocaleString()} km. KM actual: ${Number(v.km_actuales).toLocaleString()} km`;
                }
            } else {
                if (fichaProximoEl) {
                    fichaProximoEl.style.display = 'flex';
                    let texto = `${ps.tipo_nombre} a los ${Number(ps.km_proximo_servicio).toLocaleString()} km`;
                    if (ps.fecha_proximo) texto += ` · Fecha límite: ${ps.fecha_proximo}`;
                    const textoEl = document.getElementById('fichaProximoTexto');
                    if (textoEl) textoEl.textContent = texto;
                }
            }
        }

        // ── KM para form de servicio ──────────────────────────────────────────
        const svcKmEl = document.getElementById('svcKm');
        if (svcKmEl) svcKmEl.value = v.km_actuales;

        // ── Badges ────────────────────────────────────────────────────────────
        const _badge = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val;
        };
        _badge('badgeServicios', d.servicios.length);
        _badge('badgeReparaciones', d.reparaciones.length);
        _badge('badgeSeguro', (d.seguros || []).length);
        _badge('badgeAccidentes', (d.accidentes || []).length);

        // ── Render tabs ───────────────────────────────────────────────────────
        renderTablaServicios(d.servicios);
        renderTablaReparaciones(d.reparaciones);
        renderTablaSeguros(d.seguros || []);
        renderTablaAccidentes(d.accidentes || []);
        await cargarChequeos();

    } catch (err) {
        console.error('Error en abrirFicha:', err);
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


// ════════════════════════════════════════════════════════════════════════════
// ── SEGUROS ──────────────────────────────────────────────────────────────────
// ════════════════════════════════════════════════════════════════════════════

let seguroEditandoId = null;

// Helpers badge de estado de seguro
const seguroEstadoBadge = (fechaVence) => {
    if (!fechaVence) return `<span style="background:rgba(150,150,150,.15);color:#888;border:1px solid rgba(150,150,150,.25);padding:.2rem .6rem;border-radius:20px;font-size:.7rem;">Sin fecha</span>`;
    const hoy = new Date();
    const vence = new Date(fechaVence);
    const diasRestantes = Math.ceil((vence - hoy) / (1000 * 60 * 60 * 24));

    if (diasRestantes < 0) {
        return `<span style="background:rgba(224,82,82,.15);color:var(--danger);border:1px solid rgba(224,82,82,.3);padding:.2rem .6rem;border-radius:20px;font-size:.7rem;"><i class="bi bi-shield-exclamation"></i> Vencido</span>`;
    } else if (diasRestantes <= 30) {
        return `<span style="background:rgba(232,184,75,.15);color:var(--accent);border:1px solid rgba(232,184,75,.3);padding:.2rem .6rem;border-radius:20px;font-size:.7rem;"><i class="bi bi-shield-slash"></i> Vence en ${diasRestantes}d</span>`;
    } else {
        return `<span style="background:rgba(76,175,125,.15);color:var(--success);border:1px solid rgba(76,175,125,.3);padding:.2rem .6rem;border-radius:20px;font-size:.7rem;"><i class="bi bi-shield-check"></i> Vigente</span>`;
    }
};

// Toggle form seguro
const toggleFormSeguro = () => {
    const form = document.getElementById('formNuevoSeguroFicha');
    const btn = document.getElementById('btnToggleFormSeguro');
    const visible = form.style.display !== 'none';
    form.style.display = visible ? 'none' : 'block';
    btn.innerHTML = visible
        ? '<i class="bi bi-plus-circle"></i> Registrar Nuevo Seguro'
        : '<i class="bi bi-x-circle"></i> Cancelar';
    if (visible) {
        seguroEditandoId = null;
        limpiarCamposSeguros();
    }
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
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
    // Reset área PDF seguro
    const areaSeg = document.getElementById('areaPolizaFicha');
    const nombreSeg = document.getElementById('seguroPdfNombre');
    if (areaSeg) {
        areaSeg.classList.remove('has-file');
        areaSeg.querySelector('.upload-icon i').className = 'bi bi-file-pdf';
        areaSeg.querySelector('.upload-label').innerHTML = `
            <span>Haz clic</span> o arrastra la póliza aquí<br>
            <small>Solo PDF — máx. 10 MB</small>`;
    }
    if (nombreSeg) nombreSeg.style.display = 'none';
    // Reset botón guardar
    const btnSave = document.querySelector('#formNuevoSeguroFicha button[onclick="guardarSeguroFicha()"]');
    if (btnSave) {
        btnSave.innerHTML = '<i class="bi bi-save me-1"></i> Guardar Seguro';
        btnSave.style.background = 'linear-gradient(135deg,var(--success),#2e7d52)';
    }
};

// Render tabla de seguros
const renderTablaSeguros = (seguros) => {
    const wrap = document.getElementById('tablaSeguroWrap');
    if (!wrap) return;

    // guardar en memoria
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
            <div>
                <div class="svc-label">Póliza</div>
                <div class="svc-val" style="font-weight:600;">${s.numero_poliza}</div>
            </div>
            <div>
                <div class="svc-label">Aseguradora</div>
                <div class="svc-val">${s.aseguradora}</div>
            </div>
            <div>
                <div class="svc-label">Vigencia</div>
                <div class="svc-val">${s.fecha_inicio} → ${s.fecha_vencimiento || '—'}</div>
            </div>
            <div>
                <div class="svc-label">Estado</div>
                <div class="svc-val">${seguroEstadoBadge(s.fecha_vencimiento)}</div>
            </div>
            <div>
                <div class="svc-label">Costo Anual</div>
                <div class="svc-val">${s.prima_anual ? 'Q ' + Number(s.prima_anual).toLocaleString() : '—'}</div>
            </div>
            <div style="display:flex;gap:.4rem;align-items:center;flex-wrap:wrap;">
                ${s.pdf_poliza_url ? `
                <a href="${s.pdf_poliza_url}" target="_blank" style="
                    background:rgba(232,184,75,.15);border:1px solid rgba(232,184,75,.3);
                    color:var(--accent);border-radius:6px;padding:.35rem .6rem;
                    font-size:.8rem;text-decoration:none;" title="Ver póliza PDF">
                    <i class="bi bi-file-earmark-pdf"></i>
                </a>` : ''}
                <button onclick="editarSeguro(${s.id_seguro})" style="
                    background:rgba(58,123,213,.15);border:1px solid rgba(58,123,213,.3);
                    color:#5b9bd5;border-radius:6px;padding:.35rem .6rem;
                    cursor:pointer;font-size:.8rem;" title="Editar">
                    <i class="bi bi-pencil-square"></i>
                </button>
                <button onclick="eliminarSeguro(${s.id_seguro})" style="
                    background:rgba(224,82,82,.15);border:1px solid rgba(224,82,82,.3);
                    color:var(--danger);border-radius:6px;padding:.35rem .6rem;
                    cursor:pointer;font-size:.8rem;" title="Eliminar">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>
        </div>
        <div style="font-size:.75rem;color:var(--text-muted);margin-top:-.4rem;margin-bottom:.6rem;padding-left:.25rem;">
            ${s.tipo_cobertura ? '<i class="bi bi-patch-check"></i> ' + s.tipo_cobertura : ''}
            ${s.contacto_agente ? ' · <i class="bi bi-person-lines-fill"></i> ' + s.contacto_agente : ''}
            ${s.observaciones ? ' · ' + s.observaciones : ''}
        </div>
    `).join('');
};

// Guardar seguro (nuevo o edición)
const guardarSeguro = async () => {
    const poliza = document.getElementById('fsNumeroPoliza').value.trim();
    const aseguradora = document.getElementById('fsAseguradora').value.trim();
    const fechaInicio = document.getElementById('fsFechaInicio').value;
    const fechaVenc = document.getElementById('fsFechaVenc').value;

    if (!poliza || !aseguradora || !fechaInicio || !fechaVenc) {
        Swal.fire({
            icon: 'info',
            title: 'Faltan campos obligatorios',
            text: 'Póliza, aseguradora, fecha inicio y fecha vencimiento son requeridos',
            background: '#1a1d27',
            color: '#e8eaf0',
            confirmButtonColor: '#e8b84b',
            customClass: { container: 'swal-over-modal' }
        });
        return;
    }

    const body = new FormData();
    body.append('placa', fichaPlacaActual);
    body.append('numero_poliza', poliza);
    body.append('aseguradora', aseguradora);
    body.append('tipo_cobertura', document.getElementById('fsTipoCobertura').value);
    body.append('fecha_inicio', fechaInicio);
    body.append('fecha_vencimiento', fechaVenc);
    body.append('prima_anual', document.getElementById('fsPrima').value);        // ← fix
    body.append('agente_contacto', document.getElementById('fsAgente').value);       // ← fix
    body.append('telefono_agente', document.getElementById('fsTelefono').value);     // ← fix (bug 6)
    body.append('observaciones', document.getElementById('fsObs').value);

    const inputPdfSeg = document.getElementById('fsArchivo');
    if (inputPdfSeg && inputPdfSeg.files[0]) {
        body.append('archivo_poliza', inputPdfSeg.files[0]);
    }

    const esEdicion = seguroEditandoId !== null;
    if (esEdicion) body.append('id_seguro', seguroEditandoId);

    const url = esEdicion
        ? `${BASE}/API/vehiculos/seguros/modificar`
        : `${BASE}/API/vehiculos/seguros/guardar`;

    try {
        const r = await fetch(url, { method: 'POST', body });
        const d = await r.json();

        Toast.fire({ icon: d.codigo === 1 ? 'success' : 'error', title: d.mensaje });

        if (d.codigo === 1) {
            resetFormSeguro();
            await abrirFicha(fichaPlacaActual);
            switchTab(document.querySelector('.ficha-tab[data-tab="seguro"]'), 'seguro'); // ← fix bug 4
            buscar();
        }
    } catch (err) {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    }
};

// Editar seguro: cargar datos en el form
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

    const btnSave = document.querySelector('#formNuevoSeguro button[onclick="guardarSeguro()"]');
    if (btnSave) {
        btnSave.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Actualizar Seguro';
        btnSave.style.background = 'linear-gradient(135deg,#3a7bd5,#2563b0)';
    }

    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

// Eliminar seguro
const eliminarSeguro = async (id) => {
    const conf = await Swal.fire({
        icon: 'warning',
        title: '¿Eliminar seguro?',
        text: 'Se eliminará la póliza y sus archivos. Esta acción no se puede deshacer.',
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
    body.append('id_seguro', id);

    try {
        // ── FIX: ruta corregida a plural /seguros/ ────────────────────────────
        const r = await fetch(`${BASE}/API/vehiculos/seguros/eliminar`, { method: 'POST', body });
        const d = await r.json();

        Toast.fire({ icon: d.codigo === 1 ? 'success' : 'error', title: d.mensaje });
        if (d.codigo === 1) {
            await abrirFicha(fichaPlacaActual);
            switchTab(document.querySelector('.ficha-tab[data-tab="seguro"]'), 'seguro');
            buscar();
        }
    } catch (err) {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    }
};

// Preview PDF póliza en el form
const inputSeguroPdf = document.getElementById('fsArchivo');
if (inputSeguroPdf) {
    inputSeguroPdf.addEventListener('change', () => {
        const file = inputSeguroPdf.files[0];
        if (!file) return;

        const area = document.getElementById('areaPolizaFicha');
        if (area) {
            area.classList.add('has-file');
            area.querySelector('.upload-icon i').className = 'bi bi-check-circle-fill';
            area.querySelector('.upload-label').innerHTML = `
                <span style="color:var(--success)">${file.name}</span><br>
                <small>PDF seleccionado</small>`;
        }
    });
}


// ════════════════════════════════════════════════════════════════════════════
// ── ACCIDENTES ────────────────────────────────────────────────────────────────
// ════════════════════════════════════════════════════════════════════════════

let accidenteEditandoId = null;

const toggleFormAccidente = () => {
    const form = document.getElementById('formNuevoAccidente');
    const btn = document.getElementById('btnToggleFormAccidente');
    const visible = form.style.display !== 'none';
    form.style.display = visible ? 'none' : 'block';
    btn.innerHTML = visible
        ? '<i class="bi bi-plus-circle"></i> Registrar Nuevo Accidente'
        : '<i class="bi bi-x-circle"></i> Cancelar';
    if (visible) {
        accidenteEditandoId = null;
        limpiarCamposAccidente();
    }
};

const resetFormAccidente = () => {
    const form = document.getElementById('formNuevoAccidente');
    const btn = document.getElementById('btnToggleFormAccidente');
    if (form) form.style.display = 'none';
    if (btn) btn.innerHTML = '<i class="bi bi-plus-circle"></i> Registrar Nuevo Accidente';
    accidenteEditandoId = null;
    limpiarCamposAccidente();
};

const limpiarCamposAccidente = () => {
    // IDs reales del HTML
    ['acFecha', 'acTipo', 'acLugar', 'acDescripcion', 'acConductor',
        'acCostoEst', 'acCostoReal', 'acExpediente', 'acObs'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
    const selEstado = document.getElementById('acEstado');
    if (selEstado) selEstado.value = 'Reportado';
    // Resetear btn guardar
    const btnSave = document.querySelector('#formNuevoAccidente button[onclick="guardarAccidente()"]');
    if (btnSave) {
        btnSave.innerHTML = '<i class="bi bi-save me-1"></i> Guardar Accidente';
        btnSave.style.background = 'linear-gradient(135deg,var(--danger),#c93030)';
    }
};

// Render tabla accidentes
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

    // Calcular costo total acumulado
    const costoTotal = accidentes.reduce((sum, a) => {
        return sum + (parseFloat(a.costo_reparacion) || 0) + (parseFloat(a.costo_danos) || 0);
    }, 0);

    const resumenHTML = costoTotal > 0 ? `
        <div style="
            background:rgba(224,82,82,.08);
            border:1px solid rgba(224,82,82,.2);
            border-radius:8px;padding:.75rem 1rem;
            margin-bottom:1rem;
            display:flex;align-items:center;gap:.75rem;">
            <i class="bi bi-currency-dollar" style="color:var(--danger);font-size:1.25rem;"></i>
            <div>
                <div style="font-size:.75rem;color:var(--text-muted);">Costo total acumulado (daños + reparaciones)</div>
                <div style="font-weight:700;color:var(--danger);">Q ${Number(costoTotal).toLocaleString()}</div>
            </div>
        </div>` : '';

    const estadoColor = (estado) => {
        const map = { 'Cerrado': 'var(--success)', 'En proceso': 'var(--accent)', 'Pendiente': '#888' };
        return map[estado] || 'inherit';
    };

    const culpaBadge = (culpa) => {
        if (!culpa) return '';
        const map = {
            'Propio': 'rgba(224,82,82,.15)',
            'Tercero': 'rgba(58,123,213,.15)',
            'Compartida': 'rgba(232,184,75,.15)',
            'Sin determinar': 'rgba(150,150,150,.15)'
        };
        return `<span style="
            background:${map[culpa] || 'rgba(150,150,150,.15)'};
            padding:.15rem .5rem;border-radius:20px;
            font-size:.7rem;color:var(--text-secondary);">${culpa}</span>`;
    };

    wrap.innerHTML = resumenHTML + accidentes.map(a => `
        <div class="svc-row" style="grid-template-columns:1fr 1fr 1fr 1fr 1fr auto;">
            <div>
                <div class="svc-label">Tipo</div>
                <div class="svc-val">${a.tipo_accidente}</div>
            </div>
            <div>
                <div class="svc-label">Fecha</div>
                <div class="svc-val">${a.fecha_accidente}</div>
            </div>
            <div>
                <div class="svc-label">Estado</div>
                <div class="svc-val" style="color:${estadoColor(a.estado)}">${a.estado}</div>
            </div>
            <div>
                <div class="svc-label">Costo Daños</div>
                <div class="svc-val">${a.costo_danos ? 'Q ' + Number(a.costo_danos).toLocaleString() : '—'}</div>
            </div>
            <div>
                <div class="svc-label">Costo Reparación</div>
                <div class="svc-val">${a.costo_reparacion ? 'Q ' + Number(a.costo_reparacion).toLocaleString() : '—'}</div>
            </div>
            <div style="display:flex;gap:.4rem;align-items:center;flex-wrap:wrap;">
                <button onclick="editarAccidente(${a.id_accidente})" style="
                    background:rgba(58,123,213,.15);border:1px solid rgba(58,123,213,.3);
                    color:#5b9bd5;border-radius:6px;padding:.35rem .6rem;
                    cursor:pointer;font-size:.8rem;" title="Editar">
                    <i class="bi bi-pencil-square"></i>
                </button>
                <button onclick="eliminarAccidente(${a.id_accidente})" style="
                    background:rgba(224,82,82,.15);border:1px solid rgba(224,82,82,.3);
                    color:var(--danger);border-radius:6px;padding:.35rem .6rem;
                    cursor:pointer;font-size:.8rem;" title="Eliminar">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>
        </div>
        <div style="font-size:.75rem;color:var(--text-muted);margin-top:-.4rem;margin-bottom:.6rem;padding-left:.25rem;">
            ${a.lugar ? '<i class="bi bi-geo-alt"></i> ' + a.lugar : ''}
            ${a.conductor_responsable ? ' · <i class="bi bi-person-fill"></i> ' + a.conductor_responsable : ''}
            ${a.culpabilidad ? ' · ' + culpaBadge(a.culpabilidad) : ''}
            ${a.no_expediente ? ' · <i class="bi bi-journal-text"></i> Exp. ' + a.no_expediente : ''}
            ${a.km_al_momento ? ' · <i class="bi bi-speedometer"></i> ' + Number(a.km_al_momento).toLocaleString() + ' km' : ''}
        </div>
        ${a.descripcion ? `
        <div style="font-size:.75rem;color:var(--text-secondary);margin-top:-.3rem;margin-bottom:.6rem;padding-left:.25rem;padding-right:1rem;">
            ${a.descripcion}
        </div>` : ''}
    `).join('');
};

// Guardar accidente
const guardarAccidente = async () => {
    const fecha = document.getElementById('acFecha').value;
    const tipo = document.getElementById('acTipo').value.trim();
    const desc = document.getElementById('acDescripcion').value.trim();

    if (!fecha || !tipo || !desc) {
        Swal.fire({
            icon: 'info',
            title: 'Fecha, tipo y descripción son obligatorios',
            background: '#1a1d27',
            color: '#e8eaf0',
            confirmButtonColor: '#e8b84b',
            customClass: { container: 'swal-over-modal' }
        });
        return;
    }

    const body = new FormData();
    body.append('placa', fichaPlacaActual);
    body.append('fecha_accidente', fecha);
    body.append('tipo_accidente', tipo);
    body.append('descripcion', desc);
    body.append('lugar', (document.getElementById('acLugar') || { value: '' }).value);
    body.append('conductor_responsable', (document.getElementById('acConductor') || { value: '' }).value);
    body.append('costo_estimado', (document.getElementById('acCostoEst') || { value: '' }).value);
    body.append('costo_real', (document.getElementById('acCostoReal') || { value: '' }).value);
    body.append('estado_caso', (document.getElementById('acEstado') || { value: 'Reportado' }).value);
    body.append('numero_expediente', (document.getElementById('acExpediente') || { value: '' }).value);
    body.append('observaciones', (document.getElementById('acObs') || { value: '' }).value);

    // ── FIX: nombres de campo corregidos para coincidir con $_FILES en PHP ────
    const acFotosEl = document.getElementById('acFotos');
    if (acFotosEl && acFotosEl.files[0]) body.append('archivo_fotos', acFotosEl.files[0]);
    const acInformeEl = document.getElementById('acInforme');
    if (acInformeEl && acInformeEl.files[0]) body.append('archivo_informe', acInformeEl.files[0]);

    const esEdicion = accidenteEditandoId !== null;
    if (esEdicion) body.append('id_accidente', accidenteEditandoId);

    // ── FIX: rutas corregidas a plural /accidentes/ ───────────────────────────
    const url = esEdicion
        ? `${BASE}/API/vehiculos/accidentes/modificar`
        : `${BASE}/API/vehiculos/accidentes/guardar`;

    try {
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
    }
};

// Editar accidente
const editarAccidente = (id) => {

    const a = accidentesData.find(x => x.id_accidente == id);
    if (!a) return;

    accidenteEditandoId = a.id_accidente;

    const form = document.getElementById('formNuevoAccidente');
    const btn = document.getElementById('btnToggleFormAccidente');
    form.style.display = 'block';
    btn.innerHTML = '<i class="bi bi-x-circle"></i> Cancelar';

    const _sv = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.value = val || '';
    };

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
    if (btnSave) {
        btnSave.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Actualizar Accidente';
        btnSave.style.background = 'linear-gradient(135deg,#3a7bd5,#2563b0)';
    }

    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

// Eliminar accidente
const eliminarAccidente = async (id) => {
    const conf = await Swal.fire({
        icon: 'warning',
        title: '¿Eliminar accidente?',
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
    body.append('id_accidente', id);

    try {
        // ── FIX: ruta corregida a plural /accidentes/ ─────────────────────────
        const r = await fetch(`${BASE}/API/vehiculos/accidentes/eliminar`, { method: 'POST', body });
        const d = await r.json();

        Toast.fire({ icon: d.codigo === 1 ? 'success' : 'error', title: d.mensaje });
        if (d.codigo === 1) {
            await abrirFicha(fichaPlacaActual);
            switchTab(document.querySelector('.ficha-tab[data-tab="accidentes"]'), 'accidentes');
        }
    } catch (err) {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    }
};

// ════════════════════════════════════════════════════════════════════════════
// ── CHEQUEOS ──────────────────────────────────────────────────────────────────
// ════════════════════════════════════════════════════════════════════════════

let chequeoActualId = null;
let chequeoItemsDef = {};
let chequeoResultados = {};

const ITEMS_CHEQUEO = {
    1: 'Tren delantero',
    2: 'Tapicería',
    3: 'Carrocería',
    4: 'Pintura en general',
    5: 'Siglas que identifican a los vehículos pintados en color naranja fluorescente y en el lugar correspondiente',
    6: 'Lona del camión',
    7: 'Luces y pide vías',
    8: 'Sistema eléctrico',
    9: 'Herramienta extra para reparación de vehículos',
    10: 'Herramienta básica (Tricket, llave de chuchos, palanca o tubo, trozo, cable o cadena, señalizaciones etc.)',
    11: 'Herramienta de emergencia (llave de ½, Nos. 12, 13, 14, alicate, llave ajustable, juego de desatornilladores)',
    12: 'Repuestos necesarios de emergencias',
    13: 'Neumático de repuesto',
    14: 'Acumulador o batería',
    15: 'Neumáticos',
    16: 'Lubricante',
    17: 'Odómetro'
};

// ── NAVEGAR AL TAB CHEQUEO DESDE INFO GENERAL ─────────────────────────────────
const abrirModalChequeo = async () => {
    const modal = document.getElementById('modalChequeo');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    const subtitulo = document.getElementById('chequeoModalSubtitulo');
    if (subtitulo) subtitulo.textContent = `${fichaPlacaActual} — Chequeo mensual`;

    // NO llamar cancelarChequeo() aquí — solo ocultar el formulario visualmente
    const formChequeo = document.getElementById('formNuevoChequeo');
    const btnChequeo = document.getElementById('btnNuevoChequeo');
    if (formChequeo) formChequeo.style.display = 'none';
    if (btnChequeo) btnChequeo.style.display = 'flex';

    await cargarChequeos();
};

const cerrarModalChequeo = () => {
    document.getElementById('modalChequeo').style.display = 'none';
    document.body.style.overflow = '';
    cancelarChequeo();
};

// ── ACTUALIZAR BOTONES SEGÚN ESTADO DEL CHEQUEO ───────────────────────────────
const actualizarBotonesExpediente = (tieneChequeoMes) => {
    const btnChequeo = document.getElementById('btnIrAChequeo');
    const btnExpediente = document.getElementById('btnGenerarExpediente');

    if (!btnChequeo || !btnExpediente) return; // ← guard

    if (tieneChequeoMes) {
        btnChequeo.style.display = 'none';
        btnExpediente.style.display = 'flex';
    } else {
        btnChequeo.style.display = 'flex';
        btnExpediente.style.display = 'none';
    }
};

// ── CARGAR TAB CHEQUEO ────────────────────────────────────────────────────────
const cargarChequeos = async () => {
    try {
        const r = await fetch(`${BASE}/API/vehiculos/chequeos/listar?placa=${fichaPlacaActual}`);
        const d = await r.json();
        if (d.codigo !== 1) return;

        const badge = document.getElementById('badgeChequeo');
        if (badge) badge.textContent = d.datos.length;

        const alerta = document.getElementById('chequeoAlertaMes');
        if (alerta) alerta.style.display = d.tiene_chequeo_mes ? 'flex' : 'none';

        actualizarBotonesExpediente(d.tiene_chequeo_mes);
        renderTablaChequeos(d.datos);
        actualizarBotonesExpediente(d.tiene_chequeo_mes);

    } catch (err) {
        console.error('Error cargando chequeos:', err);
        // No relanzar — para no interrumpir abrirFicha
    }
};

// ── RENDER HISTORIAL ──────────────────────────────────────────────────────────
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
            <div>
                <div class="svc-label">Fecha</div>
                <div class="svc-val">${c.fecha_chequeo}</div>
            </div>
            <div>
                <div class="svc-label">KM</div>
                <div class="svc-val">${Number(c.km_al_chequeo).toLocaleString()} km</div>
            </div>
            <div>
                <div class="svc-label">Realizado por</div>
                <div class="svc-val">${c.realizado_por || '—'}</div>
            </div>
            <div>
                <div class="svc-label">Estado</div>
                <div class="svc-val">
                    <span style="background:${estadoBg};color:${estadoColor};border:1px solid ${estadoBorder};padding:.2rem .65rem;border-radius:20px;font-size:.72rem;font-weight:700;">
                        ${c.estado}
                    </span>
                </div>
            </div>
            <div style="display:flex;gap:.4rem;align-items:center;">
                ${c.estado === 'Completado' ? `
                <button onclick="verChequeo(${c.id_chequeo})" style="
                    background:rgba(111,66,193,.15);border:1px solid rgba(111,66,193,.3);
                    color:#a78bfa;border-radius:6px;padding:.35rem .6rem;
                    cursor:pointer;font-size:.8rem;" title="Ver detalle">
                    <i class="bi bi-eye"></i>
                </button>` : `
                <button onclick="continuarChequeo(${c.id_chequeo})" style="
                    background:rgba(232,184,75,.15);border:1px solid rgba(232,184,75,.3);
                    color:var(--accent);border-radius:6px;padding:.35rem .6rem;
                    cursor:pointer;font-size:.8rem;" title="Continuar chequeo">
                    <i class="bi bi-pencil-square"></i>
                </button>`}
                <button onclick="eliminarChequeo(${c.id_chequeo})" style="
                    background:rgba(224,82,82,.15);border:1px solid rgba(224,82,82,.3);
                    color:var(--danger);border-radius:6px;padding:.35rem .6rem;
                    cursor:pointer;font-size:.8rem;" title="Eliminar">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>
        </div>
        ${c.observaciones_gen ? `
        <div style="font-size:.75rem;color:var(--text-muted);margin-top:-.4rem;margin-bottom:.6rem;padding-left:.25rem;">
            <i class="bi bi-chat-text"></i> ${c.observaciones_gen}
        </div>` : ''}`;
    }).join('');
};

// ── GENERAR FILAS DE LA TABLA ─────────────────────────────────────────────────
const generarFilasChequeo = (itemsExistentes = {}) => {
    const tbody = document.getElementById('chequeoTablaItems');
    if (!tbody) return;

    tbody.innerHTML = Object.entries(ITEMS_CHEQUEO).map(([num, desc]) => {
        const n = parseInt(num);
        const res = itemsExistentes[n] || {};

        const opciones = ['BE', 'ME', 'MEI', 'NT'];
        const colores = { BE: '#4caf7d', ME: '#e8b84b', MEI: '#e05252', NT: '#7c8398' };

        const radiosCols = opciones.map(op => `
            <td style="text-align:center;padding:.5rem .25rem;">
                <label style="cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <input type="radio"
                        name="chq_item_${n}"
                        value="${op}"
                        ${res.resultado === op ? 'checked' : ''}
                        onchange="onChequeoItemChange(${n}, '${op}')"
                        style="
                            appearance:none;
                            width:22px;height:22px;
                            border-radius:50%;
                            border:2px solid ${colores[op]};
                            background:${res.resultado === op ? colores[op] : 'transparent'};
                            cursor:pointer;
                            transition:all .2s;
                            flex-shrink:0;">
                </label>
            </td>`
        ).join('');

        return `
            <tr style="border-bottom:1px solid var(--border);transition:background .15s;"
                onmouseover="this.style.background='rgba(255,255,255,.03)'"
                onmouseout="this.style.background='transparent'">
                <td style="padding:.6rem .75rem;color:var(--text-muted);font-size:.8rem;font-weight:600;">
                    ${String(n).padStart(2, '0')}
                </td>
                <td style="padding:.6rem .75rem;color:var(--text-main);font-size:.82rem;line-height:1.4;">
                    ${desc}
                </td>
                ${radiosCols}
                <td style="padding:.5rem .75rem;">
                    <input type="text"
                        id="chq_obs_${n}"
                        value="${res.observacion || ''}"
                        placeholder="..."
                        class="form-control"
                        style="font-size:.75rem;padding:.3rem .5rem !important;"
                        oninput="onChequeoObsChange(${n}, this.value)">
                </td>
            </tr>`;
    }).join('');

    if (Object.keys(itemsExistentes).length) {
        chequeoResultados = {};
        Object.entries(itemsExistentes).forEach(([num, data]) => {
            if (data.resultado) {
                chequeoResultados[parseInt(num)] = {
                    resultado: data.resultado,
                    observacion: data.observacion || ''
                };
            }
        });
        actualizarProgreso();
    }
};

// ── EVENTOS DE CAMBIO ─────────────────────────────────────────────────────────
const onChequeoItemChange = (num, valor) => {
    if (!chequeoResultados[num]) chequeoResultados[num] = {};
    chequeoResultados[num].resultado = valor;

    const colores = { BE: '#4caf7d', ME: '#e8b84b', MEI: '#e05252', NT: '#7c8398' };
    document.querySelectorAll(`input[name="chq_item_${num}"]`).forEach(radio => {
        radio.style.background = radio.value === valor ? colores[radio.value] : 'transparent';
    });

    actualizarProgreso();
};

const onChequeoObsChange = (num, valor) => {
    if (!chequeoResultados[num]) chequeoResultados[num] = {};
    chequeoResultados[num].observacion = valor;
};

const actualizarProgreso = () => {
    const total = Object.keys(ITEMS_CHEQUEO).length;
    const completados = Object.values(chequeoResultados).filter(v => v.resultado).length;
    const pct = Math.round((completados / total) * 100);

    const textoEl = document.getElementById('chqProgreso');
    const barraEl = document.getElementById('chqBarraProgreso');
    const btnEl = document.getElementById('btnGuardarChequeo');

    if (textoEl) textoEl.textContent = `${completados} / ${total}`;
    if (barraEl) barraEl.style.width = `${pct}%`;

    if (btnEl) {
        const todosCompletos = completados === total;
        btnEl.disabled = !todosCompletos;
        btnEl.style.opacity = todosCompletos ? '1' : '.5';
    }
};

// ── INICIAR NUEVO CHEQUEO ─────────────────────────────────────────────────────
const iniciarNuevoChequeo = async () => {
    // Si ya hay un chequeo activo, no crear otro
    if (chequeoActualId !== null) {
        document.getElementById('formNuevoChequeo').style.display = 'block';
        document.getElementById('btnNuevoChequeo').style.display = 'none';
        return;
    }

    chequeoResultados = {};

    const body = new FormData();
    body.append('placa', fichaPlacaActual);
    body.append('fecha_chequeo', new Date().toISOString().split('T')[0]);
    body.append('km_al_chequeo', document.getElementById('fd-km')?.textContent?.replace(/\D/g, '') || '0');

    try {
        const r = await fetch(`${BASE}/API/vehiculos/chequeos/crear`, { method: 'POST', body });
        const d = await r.json();

        if (d.codigo !== 1) {
            Toast.fire({ icon: 'error', title: d.mensaje });
            return;
        }

        chequeoActualId = d.id_chequeo;

        document.getElementById('formNuevoChequeo').style.display = 'block';
        document.getElementById('btnNuevoChequeo').style.display = 'none';
        document.getElementById('chqFecha').value = new Date().toISOString().split('T')[0];
        document.getElementById('chqKm').value = document.getElementById('fd-km')?.textContent?.replace(/\D/g, '') || '';

        generarFilasChequeo();
        actualizarProgreso();

    } catch (err) {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    }
};

// ── CONTINUAR CHEQUEO PENDIENTE ───────────────────────────────────────────────
const continuarChequeo = async (id) => {
    try {
        const r = await fetch(`${BASE}/API/vehiculos/chequeos/obtener?id=${id}`);
        const d = await r.json();
        if (d.codigo !== 1) return;

        chequeoActualId = id;
        chequeoResultados = {};

        const itemsMap = {};
        (d.datos.items || []).forEach(item => {
            itemsMap[item.numero_item] = {
                resultado: item.resultado,
                observacion: item.observacion
            };
        });

        document.getElementById('formNuevoChequeo').style.display = 'block';
        document.getElementById('btnNuevoChequeo').style.display = 'none';
        document.getElementById('chqFecha').value = d.datos.fecha_chequeo;
        document.getElementById('chqKm').value = d.datos.km_al_chequeo;
        document.getElementById('chqResponsable').value = d.datos.realizado_por || '';
        document.getElementById('chqObservaciones').value = d.datos.observaciones_gen || '';

        generarFilasChequeo(itemsMap);

    } catch (err) {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    }
};

// ── VER CHEQUEO COMPLETADO ────────────────────────────────────────────────────
const verChequeo = async (id) => {
    try {
        const r = await fetch(`${BASE}/API/vehiculos/chequeos/obtener?id=${id}`);
        const d = await r.json();
        if (d.codigo !== 1) return;

        const colores = { BE: '#4caf7d', ME: '#e8b84b', MEI: '#e05252', NT: '#7c8398' };

        const itemsMap = {};
        (d.datos.items || []).forEach(item => { itemsMap[item.numero_item] = item; });

        const filas = Object.entries(ITEMS_CHEQUEO).map(([num, desc]) => {
            const item = itemsMap[parseInt(num)] || {};
            const color = item.resultado ? colores[item.resultado] : 'var(--text-muted)';
            return `
                <tr style="border-bottom:1px solid var(--border);">
                    <td style="padding:.5rem .75rem;color:var(--text-muted);font-size:.8rem;">${String(num).padStart(2, '0')}</td>
                    <td style="padding:.5rem .75rem;color:var(--text-main);font-size:.82rem;">${desc}</td>
                    <td style="padding:.5rem .75rem;text-align:center;">
                        <span style="color:${color};font-weight:700;font-size:.82rem;">${item.resultado || '—'}</span>
                    </td>
                    <td style="padding:.5rem .75rem;font-size:.78rem;color:var(--text-muted);">${item.observacion || ''}</td>
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
            background: '#1a1d27',
            color: '#e8eaf0',
            confirmButtonColor: '#6f42c1',
            confirmButtonText: 'Cerrar',
            width: '700px',
            customClass: { container: 'swal-over-modal' }
        });
    } catch (err) {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    }
};

// ── GUARDAR CHEQUEO COMPLETO ──────────────────────────────────────────────────
const guardarChequeo = async () => {
    const items = Object.entries(chequeoResultados).map(([num, data]) => ({
        numero_item: parseInt(num),
        resultado: data.resultado,
        observacion: data.observacion || ''
    }));

    const body = new FormData();
    body.append('id_chequeo', chequeoActualId);
    body.append('items', JSON.stringify(items));
    body.append('observaciones_gen', document.getElementById('chqObservaciones').value);

    try {
        const r = await fetch(`${BASE}/API/vehiculos/chequeos/completar`, { method: 'POST', body });
        const d = await r.json();

        Toast.fire({ icon: d.codigo === 1 ? 'success' : 'error', title: d.mensaje });

        if (d.codigo === 1) {
            cancelarChequeo();
            await cargarChequeos();
        }
    } catch (err) {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    }
};

// ── CANCELAR FORMULARIO ───────────────────────────────────────────────────────
const cancelarChequeo = () => {
    const formChequeo = document.getElementById('formNuevoChequeo');
    const btnChequeo = document.getElementById('btnNuevoChequeo');
    if (formChequeo) formChequeo.style.display = 'none';
    if (btnChequeo) btnChequeo.style.display = 'flex';
    chequeoActualId = null;
    chequeoResultados = {};
};

// ── ELIMINAR CHEQUEO ──────────────────────────────────────────────────────────
const eliminarChequeo = async (id) => {
    const conf = await Swal.fire({
        icon: 'warning',
        title: '¿Eliminar chequeo?',
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
    body.append('id_chequeo', id);

    try {
        const r = await fetch(`${BASE}/API/vehiculos/chequeos/eliminar`, { method: 'POST', body });
        const d = await r.json();
        Toast.fire({ icon: d.codigo === 1 ? 'success' : 'error', title: d.mensaje });
        if (d.codigo === 1) await cargarChequeos();
    } catch (err) {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    }
};

// ── GENERAR EXPEDIENTE ────────────────────────────────────────────────────────
const generarExpediente = (placa) => {
    window.open(`${BASE}/vehiculos/expediente?placa=${encodeURIComponent(placa)}`, '_blank');
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
window.irAChequeo = abrirModalChequeo;
window.generarExpediente = generarExpediente;
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
// Seguros — alias dobles para coincidir con el HTML
window.toggleFormSeguro = toggleFormSeguro;
window.toggleFormSeguroFicha = toggleFormSeguro;
window.guardarSeguroFicha = guardarSeguro;
window.guardarSeguro = guardarSeguro;
window.editarSeguro = editarSeguro;
window.eliminarSeguro = eliminarSeguro;
// Accidentes
window.toggleFormAccidente = toggleFormAccidente;
window.guardarAccidente = guardarAccidente;
window.editarAccidente = editarAccidente;
window.eliminarAccidente = eliminarAccidente;
window.iniciarNuevoChequeo = iniciarNuevoChequeo;
window.continuarChequeo = continuarChequeo;
window.verChequeo = verChequeo;
window.guardarChequeo = guardarChequeo;
window.cancelarChequeo = cancelarChequeo;
window.eliminarChequeo = eliminarChequeo;
window.onChequeoItemChange = onChequeoItemChange;
window.onChequeoObsChange = onChequeoObsChange;
window.abrirModalChequeo = abrirModalChequeo;
window.cerrarModalChequeo = cerrarModalChequeo;
// ── DATA EN MEMORIA ─────────────────────────────────────
let segurosData = [];
let accidentesData = [];




// ── SEGURO EN FORMULARIO NUEVO ────────────────────────────────────────────────
let vehiculoTieneSeguro = false;

const elegirSeguro = (opcion) => {

    const btnSi = document.getElementById('btnSeguroSi');
    const btnNo = document.getElementById('btnSeguroNo');
    const panel = document.getElementById('panelFormSeguro');
    const aviso = document.getElementById('avisoSinSeguro');

    btnSi.classList.remove('sel-si');
    btnNo.classList.remove('sel-no');

    if (opcion === 'si') {
        btnSi.classList.add('sel-si');
        panel.style.display = 'block';
        aviso.style.display = 'none';
        vehiculoTieneSeguro = true;
    } else {
        btnNo.classList.add('sel-no');
        panel.style.display = 'none';
        aviso.style.display = 'flex';
        vehiculoTieneSeguro = false;
    }
};


// Exponer global (módulo ES)
window.elegirSeguro = elegirSeguro;

// ── INIT ──────────────────────────────────────────────────────────────────────
cargarUnidades();
buscar();