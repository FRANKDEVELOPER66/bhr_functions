import { Toast, validarFormulario } from "../funciones";
import { Dropdown } from "bootstrap";
import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { lenguaje } from "../lenguaje";

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

// ── DATATABLE ────────────────────────────────────────────────────────────────
const datatable = new DataTable('#tablaVehiculos', {
    language: lenguaje,
    pageLength: 15,
    lengthMenu: [10, 15, 25, 50, 100],
    columns: [
        {
            title: 'No.',
            data: 'placa',
            width: '4%',
            render: (data, type, row, meta) => meta.row + 1
        },
        {
            title: 'Placa',
            data: 'placa',
            width: '9%',
            render: (data) =>
                `<span style="font-family:'Rajdhani',sans-serif;font-weight:700;
                    letter-spacing:1px;color:var(--accent)">${data}</span>`
        },
        {
            title: 'Vehículo',
            data: 'marca',
            width: '18%',
            render: (data, type, row) =>
                `<strong>${row.marca} ${row.modelo}</strong>
                 <br><small style="color:var(--text-muted)">${row.tipo} · ${row.anio}</small>`
        },
        {
            title: 'Color',
            data: 'color',
            width: '8%'
        },
        {
            title: 'Kilometraje',
            data: 'km_actuales',
            width: '11%',
            render: (data) =>
                `<span class="chip-km">
                    <i class="bi bi-speedometer"></i>
                    ${Number(data).toLocaleString('es-GT')} km
                 </span>`
        },
        {
            title: 'Estado',
            data: 'estado',
            width: '9%',
            render: (data) => {
                const map = {
                    'Alta': 'badge-alta',
                    'Baja': 'badge-baja',
                    'Taller': 'badge-taller'
                };
                return `<span class="badge-estado ${map[data] || ''}">${data}</span>`;
            }
        },
        {
            title: 'Ingreso',
            data: 'fecha_ingreso',
            width: '10%',
            render: (data) => {
                if (!data) return '—';
                const [y, m, d] = data.split('-');
                return `${d}/${m}/${y}`;
            }
        },
        {
            title: 'Servicios',
            data: 'total_servicios',
            width: '8%',
            render: (data) =>
                `<span style="color:var(--text-muted);font-size:.85rem">
                    <i class="bi bi-wrench" style="color:var(--accent)"></i> ${data ?? 0}
                 </span>`
        },
        {
            title: 'Acciones',
            data: 'placa',
            width: '10%',
            searchable: false,
            orderable: false,
            render: (data, type, row) => `
                <button class="btn-tbl btn-tbl-edit modificar"
                    title="Modificar"
                    data-placa="${row.placa}"
                    data-numero_serie="${row.numero_serie}"
                    data-marca="${row.marca}"
                    data-modelo="${row.modelo}"
                    data-anio="${row.anio}"
                    data-color="${row.color}"
                    data-tipo="${row.tipo}"
                    data-km_actuales="${row.km_actuales}"
                    data-estado="${row.estado}"
                    data-fecha_ingreso="${row.fecha_ingreso}"
                    data-observaciones="${row.observaciones ?? ''}">
                    <i class="bi bi-pencil-square"></i>
                </button>
                <button class="btn-tbl btn-tbl-del eliminar"
                    title="Eliminar"
                    data-placa="${data}">
                    <i class="bi bi-trash3"></i>
                </button>
            `
        }
    ]
});

// ── ESTADO INICIAL BOTONES ───────────────────────────────────────────────────
btnModificar.parentElement.style.display = 'none';

// ── HELPERS UI ───────────────────────────────────────────────────────────────
const mostrarFormulario = () => {
    contenedorFormulario.style.display = '';
    contenedorFormulario.classList.add('slide-down');
    contenedorTabla.style.display = 'none';
    tituloFormulario.textContent = 'Nuevo Vehículo';
    formulario.reset();

    // Placa editable al crear
    inputPlaca.readOnly = false;
    inputPlaca.style.opacity = '1';
    inputPlacaOriginal.value = '';

    btnGuardar.parentElement.style.display = '';
    btnModificar.parentElement.style.display = 'none';

    btnFlotante.classList.add('activo');
    btnFlotante.innerHTML = '<i class="bi bi-skip-backward"></i>';
    btnFlotante.setAttribute('title', 'Volver a la tabla');
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

// ── BOTÓN FLOTANTE ───────────────────────────────────────────────────────────
btnFlotante.addEventListener('click', () => {
    contenedorFormulario.style.display === 'none'
        ? mostrarFormulario()
        : ocultarFormulario();
});

// ── GUARDAR ──────────────────────────────────────────────────────────────────
const guardar = async (e) => {
    e.preventDefault();
    btnGuardar.disabled = true;

    if (!validarFormulario(formulario, ['placa_original', 'observaciones'])) {
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
        const respuesta = await fetch('/bhr_functions/API/vehiculos/guardar', { method: 'POST', body });
        const data = await respuesta.json();
        const { codigo, mensaje } = data;

        if (codigo == 1) {
            formulario.reset();
            buscar();
            ocultarFormulario();
        }

        Toast.fire({
            icon: codigo == 1 ? 'success' : 'error',
            title: mensaje
        });
    } catch (error) {
        console.error(error);
        Toast.fire({ icon: 'error', title: 'Error de conexión al guardar' });
    }

    btnGuardar.disabled = false;
};

// ── BUSCAR / LISTAR ──────────────────────────────────────────────────────────
const buscar = async () => {
    try {
        const respuesta = await fetch('/bhr_functions/API/vehiculos/buscar', { method: 'GET' });
        const data = await respuesta.json();
        const { datos } = data;

        datatable.clear().draw();
        if (datos && datos.length > 0) {
            datatable.rows.add(datos).draw();
        }
    } catch (error) {
        console.error('Error al buscar vehículos:', error);
    }
};

// ── TRAER DATOS AL FORMULARIO ────────────────────────────────────────────────
const traerDatos = (e) => {
    const d = e.currentTarget.dataset;

    // Placa: solo lectura en edición (es el PK)
    inputPlaca.value = d.placa;
    inputPlaca.readOnly = true;
    inputPlaca.style.opacity = '.6';
    inputPlacaOriginal.value = d.placa;   // para enviarlo al controller

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

    tituloFormulario.textContent = 'Modificar Vehículo';
    contenedorFormulario.style.display = '';
    contenedorFormulario.classList.add('slide-down');
    contenedorTabla.style.display = 'none';

    btnGuardar.parentElement.style.display = 'none';
    btnModificar.parentElement.style.display = '';

    btnFlotante.classList.add('activo');
    btnFlotante.innerHTML = '<i class="bi bi-x"></i>';
    btnFlotante.setAttribute('title', 'Cerrar formulario');
};

// ── CANCELAR ─────────────────────────────────────────────────────────────────
const cancelar = () => {
    formulario.reset();
    inputPlaca.readOnly = false;
    inputPlaca.style.opacity = '1';
    ocultarFormulario();
    btnGuardar.parentElement.style.display = '';
    btnModificar.parentElement.style.display = 'none';
};

// ── MODIFICAR ────────────────────────────────────────────────────────────────
const modificar = async () => {
    if (!validarFormulario(formulario, ['observaciones'])) {
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
        // Enviamos placa_original como "placa" para que el controller haga find()
        const body = new FormData(formulario);
        body.set('placa', inputPlacaOriginal.value);

        const respuesta = await fetch('/bhr_functions/API/vehiculos/modificar', { method: 'POST', body });
        const data = await respuesta.json();
        const { codigo, mensaje } = data;

        if (codigo == 1) {
            formulario.reset();
            buscar();
            cancelar();
        }

        Toast.fire({
            icon: codigo == 1 ? 'success' : 'error',
            title: mensaje
        });
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
        html: `Se eliminará el vehículo con placa <strong>${placa}</strong>.<br>
               Esta acción no se puede deshacer.`,
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

        const respuesta = await fetch('/bhr_functions/API/vehiculos/eliminar', { method: 'POST', body });
        const data = await respuesta.json();
        const { codigo, mensaje } = data;

        if (codigo == 1) buscar();

        Toast.fire({
            icon: codigo == 1 ? 'success' : 'error',
            title: mensaje
        });
    } catch (error) {
        console.error(error);
        Toast.fire({ icon: 'error', title: 'Error de conexión al eliminar' });
    }
};

// ── AUTO-UPPERCASE placa y serie ─────────────────────────────────────────────
document.getElementById('placa').addEventListener('input', function () {
    this.value = this.value.toUpperCase();
});
document.getElementById('numero_serie').addEventListener('input', function () {
    this.value = this.value.toUpperCase();
});

// ── EVENT LISTENERS ──────────────────────────────────────────────────────────
formulario.addEventListener('submit', guardar);
btnCancelar.addEventListener('click', cancelar);
btnModificar.addEventListener('click', modificar);
datatable.on('click', '.modificar', traerDatos);
datatable.on('click', '.eliminar', eliminar);

// ── INIT ─────────────────────────────────────────────────────────────────────
buscar();