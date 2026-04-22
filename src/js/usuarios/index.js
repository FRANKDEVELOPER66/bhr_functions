import { Toast } from '../funciones';
import Swal from 'sweetalert2';
import { Dropdown } from "bootstrap";


const BASE = document.querySelector('[data-base]')?.dataset.base ?? '';

// ── LOADER ────────────────────────────────────────────────────────────────────
const mostrarLoader = (msg = 'Procesando...') => {
    const loader = document.getElementById('bhr-loader');
    const msgEl = document.getElementById('loaderMensaje');
    if (msgEl) msgEl.textContent = msg;
    if (loader) loader.classList.add('visible');
};
const ocultarLoader = () => {
    const loader = document.getElementById('bhr-loader');
    if (loader) loader.classList.remove('visible');
};

// ── ROLES ─────────────────────────────────────────────────────────────────────
const ROLES = {
    SUPERUSUARIO: { label: 'Desarrollador', icon: 'bi-code-slash' },
    COMTE_BHR: { label: 'Comandante BHR', icon: 'bi-star-fill' },
    COMTE_CIA: { label: 'Comandante Cía', icon: 'bi-shield-fill' },
    COMTE_PTN: { label: 'Comandante Ptn', icon: 'bi-person-fill' },
};

// ── RENDER ────────────────────────────────────────────────────────────────────
const renderUsuarios = (usuarios) => {
    const grid = document.getElementById('usuariosGrid');
    if (!usuarios.length) {
        grid.innerHTML = `<div style="text-align:center;padding:3rem;color:#7c8398;grid-column:1/-1;">
            <i class="bi bi-people" style="font-size:3rem;opacity:.2;display:block;margin-bottom:1rem;"></i>
            <p>No hay usuarios registrados</p>
        </div>`;
        return;
    }

    grid.innerHTML = usuarios.map(u => {
        const rol = ROLES[u.rol] || { label: u.rol, icon: 'bi-person' };
        const inactivo = u.activo == 0;
        const primerIngreso = u.primer_ingreso == 1;

        return `
        <div class="usuario-card ${inactivo ? 'inactivo' : ''}">
            <div class="usuario-rol rol-${u.rol}">
                <i class="bi ${rol.icon}"></i> ${rol.label}
            </div>
            ${primerIngreso ? `
            <div class="primer-ingreso-badge">
                <i class="bi bi-clock-history"></i> Pendiente de activación
            </div>` : ''}
            <div class="usuario-nombre">${u.grado} ${u.nombre_completo}</div>
            <div class="usuario-sub">
                <i class="bi bi-shield" style="color:#e8b84b;"></i> ${u.arma_servicio}
                · <i class="bi bi-briefcase"></i> ${u.plaza}
            </div>
            <div class="usuario-correo">
                <i class="bi bi-envelope"></i>
                ${u.correo || '<span style="color:#3a3d4e;font-style:italic;">Sin correo registrado</span>'}
            </div>
            <div class="usuario-acciones">
                <button class="btn-usr" onclick="editarUsuario('${u.catalogo}')">
                    <i class="bi bi-pencil-square"></i> Editar
                </button>
                <button class="btn-usr" onclick="resetPassword('${u.catalogo}', '${u.correo || ''}')">
                    <i class="bi bi-key-fill"></i> Reset
                </button>
                <button class="btn-usr ${inactivo ? 'success' : 'danger'}"
                    onclick="toggleActivo('${u.catalogo}', ${inactivo ? 1 : 0})">
                    <i class="bi bi-${inactivo ? 'person-check' : 'person-slash'}"></i>
                    ${inactivo ? 'Activar' : 'Desactivar'}
                </button>
            </div>
        </div>`;
    }).join('');
};

// ── CARGAR USUARIOS ───────────────────────────────────────────────────────────
const cargarUsuarios = async () => {
    mostrarLoader('Cargando usuarios...');
    try {
        const r = await fetch(`${BASE}/API/usuarios/listar`);
        const d = await r.json();
        if (d.codigo === 1) renderUsuarios(d.datos);
    } catch {
        Toast.fire({ icon: 'error', title: 'Error al cargar usuarios' });
    } finally {
        ocultarLoader();
    }
};

// ── FORM USUARIO (crear/editar) ───────────────────────────────────────────────
const mostrarFormUsuario = async (datos = null) => {
    const esEdicion = datos !== null;

    const { value: formValues, isConfirmed } = await Swal.fire({
        title: esEdicion ? 'Editar Usuario' : 'Nuevo Usuario',
        html: `
            <div style="text-align:left;font-size:.85rem;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                    <div>
                        <label style="color:#e8b84b;font-size:.75rem;font-weight:600;">Catálogo *</label>
                        <input id="f-catalogo" type="text" class="form-control"
                            value="${datos?.catalogo || ''}"
                            ${esEdicion ? 'readonly style="opacity:.6;"' : ''}
                            placeholder="Ej: 656207"
                            style="margin-top:.3rem;background:#242837;color:#e8eaf0;border:1px solid #2e3347;">
                    </div>
                    <div>
    <label style="color:#e8b84b;font-size:.75rem;font-weight:600;">Rol *</label>
    <select id="f-rol" class="form-select"
        ${esEdicion && datos?.catalogo === MI_CATALOGO ? 'disabled style="opacity:.6;"' : ''}
        style="margin-top:.3rem;background:#242837;color:#e8eaf0;border:1px solid #2e3347;">
        <option value="SUPERUSUARIO" ${datos?.rol === 'SUPERUSUARIO' ? 'selected' : ''}>Desarrollador</option>
        <option value="COMTE_BHR"    ${datos?.rol === 'COMTE_BHR' ? 'selected' : ''}>Comandante BHR</option>
        <option value="COMTE_CIA"    ${datos?.rol === 'COMTE_CIA' ? 'selected' : ''}>Comandante Cía</option>
        <option value="COMTE_PTN"    ${datos?.rol === 'COMTE_PTN' ? 'selected' : ''}>Comandante Ptn</option>
    </select>
    ${esEdicion && datos?.catalogo === MI_CATALOGO ?
                '<div style="font-size:.72rem;color:#7c8398;margin-top:.3rem;"><i class="bi bi-lock-fill"></i> No puedes cambiar tu propio rol</div>'
                : ''}
</div>
                </div>
                <div style="margin-bottom:.75rem;">
                    <label style="color:#e8b84b;font-size:.75rem;font-weight:600;">Grado *</label>
                    <input id="f-grado" type="text" class="form-control"
                        value="${datos?.grado || ''}" placeholder="Ej: Coronel"
                        style="margin-top:.3rem;background:#242837;color:#e8eaf0;border:1px solid #2e3347;">
                </div>
                <div style="margin-bottom:.75rem;">
                    <label style="color:#e8b84b;font-size:.75rem;font-weight:600;">Nombre completo *</label>
                    <input id="f-nombre" type="text" class="form-control"
                        value="${datos?.nombre_completo || ''}" placeholder="Nombre y apellidos"
                        style="margin-top:.3rem;background:#242837;color:#e8eaf0;border:1px solid #2e3347;">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                    <div>
                        <label style="color:#e8b84b;font-size:.75rem;font-weight:600;">Arma/Servicio *</label>
                        <input id="f-arma" type="text" class="form-control"
                            value="${datos?.arma_servicio || ''}" placeholder="Ej: Infantería"
                            style="margin-top:.3rem;background:#242837;color:#e8eaf0;border:1px solid #2e3347;">
                    </div>
                    <div>
                        <label style="color:#e8b84b;font-size:.75rem;font-weight:600;">Plaza *</label>
                        <input id="f-plaza" type="text" class="form-control"
                            value="${datos?.plaza || ''}" placeholder="Ej: Comandante BHR"
                            style="margin-top:.3rem;background:#242837;color:#e8eaf0;border:1px solid #2e3347;">
                    </div>
                </div>
            </div>`,
        background: '#1a1d27',
        color: '#e8eaf0',
        showCancelButton: true,
        confirmButtonText: esEdicion ? 'Guardar cambios' : 'Crear usuario',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e8b84b',
        cancelButtonColor: '#555',
        width: '600px',
        preConfirm: () => {
            const catalogo = document.getElementById('f-catalogo').value.trim();
            const grado = document.getElementById('f-grado').value.trim();
            const nombre = document.getElementById('f-nombre').value.trim();
            const arma = document.getElementById('f-arma').value.trim();
            const plaza = document.getElementById('f-plaza').value.trim();
            const rol = document.getElementById('f-rol').value;

            if (!catalogo || !grado || !nombre || !arma || !plaza) {
                Swal.showValidationMessage('Todos los campos son obligatorios');
                return false;
            }
            return { catalogo, grado, nombre_completo: nombre, arma_servicio: arma, plaza, rol };
        }
    });

    if (!isConfirmed || !formValues) return;

    mostrarLoader(esEdicion ? 'Guardando cambios...' : 'Creando usuario...');
    try {
        const body = new FormData();
        Object.entries(formValues).forEach(([k, v]) => body.append(k, v));

        const url = esEdicion ? `${BASE}/API/usuarios/actualizar` : `${BASE}/API/usuarios/crear`;
        const r = await fetch(url, { method: 'POST', body });
        const d = await r.json();

        if (d.codigo === 1) {
            Toast.fire({ icon: 'success', title: d.mensaje });
            cargarUsuarios();
        } else {
            Toast.fire({ icon: 'error', title: d.mensaje });
        }
    } catch {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    } finally {
        ocultarLoader();
    }
};

// ── EDITAR ────────────────────────────────────────────────────────────────────
window.editarUsuario = async (catalogo) => {
    mostrarLoader('Cargando datos...');
    try {
        const r = await fetch(`${BASE}/API/usuarios/listar`);
        const d = await r.json();
        const usuario = d.datos.find(u => u.catalogo === catalogo);
        ocultarLoader();
        if (usuario) mostrarFormUsuario(usuario);
    } catch {
        ocultarLoader();
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    }
};

// ── RESET PASSWORD ────────────────────────────────────────────────────────────
window.resetPassword = async (catalogo, correo) => {
    if (!correo) {
        Toast.fire({ icon: 'warning', title: 'El usuario no tiene correo registrado' });
        return;
    }

    const conf = await Swal.fire({
        icon: 'question',
        title: '¿Restablecer contraseña?',
        html: `Se enviará un enlace de restablecimiento a <strong style="color:#e8b84b;">${correo}</strong>.`,
        showCancelButton: true,
        confirmButtonText: 'Sí, enviar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e8b84b',
        cancelButtonColor: '#555',
        background: '#1a1d27',
        color: '#e8eaf0'
    });

    if (!conf.isConfirmed) return;

    mostrarLoader('Enviando correo...');
    try {
        const body = new FormData();
        body.append('catalogo', catalogo);
        const r = await fetch(`${BASE}/API/usuarios/reset-password`, { method: 'POST', body });
        const d = await r.json();
        Toast.fire({ icon: d.codigo === 1 ? 'success' : 'error', title: d.mensaje });
    } catch {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    } finally {
        ocultarLoader();
    }
};

// ── TOGGLE ACTIVO ─────────────────────────────────────────────────────────────
window.toggleActivo = async (catalogo, nuevoEstado) => {
    const accion = nuevoEstado === 1 ? 'activar' : 'desactivar';

    const conf = await Swal.fire({
        icon: nuevoEstado === 1 ? 'question' : 'warning',
        title: `¿${nuevoEstado === 1 ? 'Activar' : 'Desactivar'} usuario?`,
        text: `El usuario ${nuevoEstado === 0 ? 'no podrá ingresar al sistema.' : 'podrá ingresar nuevamente.'}`,
        showCancelButton: true,
        confirmButtonText: `Sí, ${accion}`,
        cancelButtonText: 'Cancelar',
        confirmButtonColor: nuevoEstado === 1 ? '#4caf7d' : '#e05252',
        cancelButtonColor: '#555',
        background: '#1a1d27',
        color: '#e8eaf0'
    });

    if (!conf.isConfirmed) return;

    mostrarLoader('Procesando...');
    try {
        const body = new FormData();
        body.append('catalogo', catalogo);
        body.append('activo', nuevoEstado);
        const r = await fetch(`${BASE}/API/usuarios/toggle-activo`, { method: 'POST', body });
        const d = await r.json();
        Toast.fire({ icon: d.codigo === 1 ? 'success' : 'error', title: d.mensaje });
        if (d.codigo === 1) cargarUsuarios();
    } catch {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    } finally {
        ocultarLoader();
    }
};

// ── INIT ──────────────────────────────────────────────────────────────────────
document.getElementById('btnNuevoUsuario').addEventListener('click', () => mostrarFormUsuario());
cargarUsuarios();