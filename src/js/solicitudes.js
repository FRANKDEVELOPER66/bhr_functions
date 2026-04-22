import { Toast } from './funciones';
import Swal from 'sweetalert2';

const BASE = document.querySelector('[data-base]')?.dataset.base ?? '';
const ROL = document.querySelector('[data-rol]')?.dataset.rol ?? '';

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

// ── TOAST ÉXITO ───────────────────────────────────────────────────────────────
const toastExito = (mensaje) => Swal.fire({
    position: 'top-end',
    icon: 'success',
    title: mensaje,
    showConfirmButton: true,
    confirmButtonText: 'ok',
    customClass: { confirmButton: 'd-none' },
    timer: 2500,
    draggable: true,
    background: '#1a1d27',
    color: '#e8eaf0',
    toast: true
});

// ── CAMPANITA ─────────────────────────────────────────────────────────────────
const actualizarCampanita = async () => {
    try {
        const r = await fetch(`${BASE}/API/solicitudes/contar`);
        const d = await r.json();
        const badge = document.getElementById('badgeNotificaciones');
        const icono = document.getElementById('iconoCampana');
        const texto = document.getElementById('textoNotif');
        const btnNotif = document.getElementById('btnNotificaciones');

        if (!badge) return;

        if (d.total > 0) {
            // Mostrar badge
            badge.textContent = d.total;
            badge.style.display = 'flex';

            // Mostrar texto según rol
            if (texto) {
                const label = (ROL === 'COMTE_CIA' || ROL === 'SUPERUSUARIO')
                    ? `${d.total} solicitud${d.total > 1 ? 'es' : ''} pendiente${d.total > 1 ? 's' : ''}`
                    : `${d.total} notificación${d.total > 1 ? 'es' : ''}`;
                texto.textContent = label;
                texto.style.display = 'inline';
            }

            // Animar campanita
            if (icono) {
                icono.classList.remove('campana-activa');
                void icono.offsetWidth; // reflow para reiniciar animación
                icono.classList.add('campana-activa');
                icono.style.color = '#e8b84b';
            }

            // Estilo activo del botón
            if (btnNotif) btnNotif.classList.add('btn-notif-activo');

        } else {
            badge.style.display = 'none';
            if (texto) texto.style.display = 'none';
            if (icono) {
                icono.classList.remove('campana-activa');
                icono.style.color = '#e8eaf0';
            }
            if (btnNotif) btnNotif.classList.remove('btn-notif-activo');
        }
    } catch (err) {
        console.error('Error al contar notificaciones:', err);
    }
};

actualizarCampanita();
setInterval(actualizarCampanita, 30000);

// ── PANEL NOTIFICACIONES ──────────────────────────────────────────────────────
const abrirNotificaciones = async () => {
    mostrarLoader('Cargando notificaciones...');
    try {
        let url, titulo;

        if (ROL === 'COMTE_CIA' || ROL === 'SUPERUSUARIO') {
            url = `${BASE}/API/solicitudes/pendientes`;
            titulo = 'Solicitudes Pendientes';
        } else {
            url = `${BASE}/API/solicitudes/mis-notificaciones`;
            titulo = 'Mis Notificaciones';
        }

        const r = await fetch(url);
        const d = await r.json();

        if (!d.datos || d.datos.length === 0) {
            Swal.fire({
                title: titulo,
                html: `<div style="text-align:center;padding:2rem;color:#7c8398;">
                    <i class="bi bi-bell-slash" style="font-size:3rem;opacity:.3;display:block;margin-bottom:1rem;"></i>
                    <p>No hay notificaciones pendientes</p>
                </div>`,
                background: '#1a1d27',
                color: '#e8eaf0',
                confirmButtonColor: '#e8b84b',
                confirmButtonText: 'Cerrar'
            });
            return;
        }

        actualizarCampanita();

        if (ROL === 'COMTE_CIA' || ROL === 'SUPERUSUARIO') {
            mostrarPendientesRevisor(d.datos, titulo);
        } else {
            mostrarNotificacionesSolicitante(d.datos, titulo);
        }

    } catch (err) {
        Toast.fire({ icon: 'error', title: 'Error al cargar notificaciones' });
    } finally {
        ocultarLoader();
    }
};

// ── VISTA REVISOR (COMTE_CIA) ─────────────────────────────────────────────────
const mostrarPendientesRevisor = (datos, titulo) => {
    const excluir = ['tipo_solicitud'];

    const html = datos.map(s => {
        let cambiosHTML = '';
        if (s.datos_cambio) {
            try {
                const cambios = JSON.parse(s.datos_cambio);
                const filas = Object.entries(cambios)
                    .filter(([k]) => !excluir.includes(k))
                    .map(([k, info]) => {
                        const antes = typeof info === 'object' ? (info.antes ?? '—') : '—';
                        const ahora = typeof info === 'object' ? (info.ahora ?? String(info)) : String(info);
                        return `
                        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.3rem;flex-wrap:wrap;">
                            <span style="color:#555;min-width:80px;">${k}:</span>
                            <span style="background:rgba(224,82,82,.15);color:#e05252;
                                padding:.15rem .5rem;border-radius:4px;text-decoration:line-through;">
                                ${antes}
                            </span>
                            <i class="bi bi-arrow-right" style="color:#7c8398;"></i>
                            <span style="background:rgba(76,175,125,.15);color:#4caf7d;
                                padding:.15rem .5rem;border-radius:4px;font-weight:600;">
                                ${ahora}
                            </span>
                        </div>`;
                    }).join('');

                cambiosHTML = `
                <div style="background:#1a1d27;border-radius:8px;padding:.6rem .8rem;
                    margin-bottom:.75rem;font-size:.8rem;">
                    <div style="color:#e8b84b;margin-bottom:.4rem;font-weight:600;
                        font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;">
                        <i class="bi bi-pencil-square"></i> Cambios solicitados
                    </div>
                    ${filas}
                </div>`;
            } catch (e) { }
        }

        return `
        <div style="background:#242837;border:1px solid #2e3347;border-radius:12px;
            overflow:hidden;margin-bottom:.75rem;text-align:left;">

            ${s.foto_frente ? `
            <div style="width:100%;aspect-ratio:4/3;overflow:hidden;position:relative;">
                <img src="${BASE}/API/vehiculos/foto?archivo=/${s.foto_frente}"
                    style="width:100%;height:100%;object-fit:cover;object-position:center;"
                    onerror="this.parentElement.style.display='none'">
                <div style="position:absolute;top:.5rem;right:.5rem;background:rgba(15,17,23,.85);
                    border:1px solid #2e3347;border-radius:8px;padding:.3rem .7rem;
                    font-size:.72rem;color:#7c8398;">
                    ${s.fecha_solicitud}
                </div>
            </div>` : ''}

            <div style="padding:1rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem;">
                    <span style="font-family:'Rajdhani',sans-serif;font-size:1.3rem;font-weight:700;color:#e8b84b;">
                        ${s.tipo === 'eliminacion' ? '🗑️ Eliminación' : '✏️ Modificación'} — Catálogo ${s.placa}
                    </span>
                </div>

                <div style="font-size:1rem;color:#c8cfe0;margin-bottom:.25rem;font-weight:600;">
                    <i class="bi bi-truck" style="color:#e8b84b;"></i> ${s.marca} ${s.modelo} · ${s.tipo_vehiculo}
                </div>

                <div style="font-size:.88rem;color:#7c8398;margin-bottom:.75rem;">
                    <i class="bi bi-person-fill"></i> ${s.grado} ${s.nombre_completo} · ${s.plaza}
                </div>

                ${cambiosHTML}

                <div style="display:flex;gap:.5rem;">
                    <button onclick="aprobarSolicitud(${s.id})"
                        style="flex:1;background:rgba(76,175,125,.15);border:1px solid rgba(76,175,125,.3);
                        color:#4caf7d;border-radius:8px;padding:.75rem;cursor:pointer;
                        font-size:.95rem;font-weight:600;">
                        <i class="bi bi-check-circle-fill"></i> Aprobar
                    </button>
                    <button onclick="rechazarSolicitud(${s.id})"
                        style="flex:1;background:rgba(224,82,82,.15);border:1px solid rgba(224,82,82,.3);
                        color:#e05252;border-radius:8px;padding:.75rem;cursor:pointer;
                        font-size:.95rem;font-weight:600;">
                        <i class="bi bi-x-circle-fill"></i> Rechazar
                    </button>
                </div>
            </div>
        </div>`;
    }).join('');

    Swal.fire({
        title: `<span style="font-family:'Rajdhani',sans-serif;font-size:1.3rem;">${titulo}</span>`,
        html: `<div style="max-height:500px;overflow-y:auto;padding-right:.25rem;">${html}</div>`,
        background: '#1a1d27',
        color: '#e8eaf0',
        confirmButtonColor: '#e8b84b',
        confirmButtonText: 'Cerrar',
        width: '780px'
    });
};

// ── VISTA SOLICITANTE (COMTE_PTN) ─────────────────────────────────────────────
const mostrarNotificacionesSolicitante = (datos, titulo) => {
    const html = datos.map(s => {
        const aprobada = s.estado === 'aprobada';
        const color = aprobada ? '#4caf7d' : '#e05252';
        const icono = aprobada ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
        const texto = aprobada ? 'Aprobada' : 'Rechazada';

        // Parsear datos_cambio
        let cambiosHTML = '';
        if (s.datos_cambio && aprobada) {
            try {
                const cambios = JSON.parse(s.datos_cambio);
                cambiosHTML = `
                <div style="background:#1a1d27;border-radius:8px;padding:.6rem .8rem;margin-top:.5rem;font-size:.78rem;">
                    <div style="color:#4caf7d;margin-bottom:.4rem;font-weight:600;font-size:.72rem;
                        text-transform:uppercase;letter-spacing:.5px;">
                        <i class="bi bi-arrow-left-right"></i> Cambio realizado
                    </div>
                    ${Object.entries(cambios).map(([campo, info]) => {
                    const antes = typeof info === 'object' ? info.antes : '—';
                    const ahora = typeof info === 'object' ? info.ahora : info;
                    return `
                        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.3rem;flex-wrap:wrap;">
                            <span style="color:#555;min-width:90px;font-size:.75rem;">${campo}:</span>
                            <span style="background:rgba(224,82,82,.15);color:#e05252;
                                padding:.15rem .5rem;border-radius:4px;text-decoration:line-through;">
                                ${antes}
                            </span>
                            <i class="bi bi-arrow-right" style="color:#7c8398;"></i>
                            <span style="background:rgba(76,175,125,.15);color:#4caf7d;
                                padding:.15rem .5rem;border-radius:4px;font-weight:600;">
                                ${ahora}
                            </span>
                        </div>`;
                }).join('')}
                </div>`;
            } catch (e) { }
        }

        return `
        <div style="background:#242837;border:1px solid #2e3347;border-left:3px solid ${color};
            border-radius:12px;padding:1rem;margin-bottom:.75rem;text-align:left;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem;">
                <span style="color:${color};font-weight:700;font-size:.9rem;">
                    <i class="bi ${icono}"></i> ${texto}
                </span>
                <span style="font-size:.72rem;color:#7c8398;">${s.fecha_resolucion}</span>
            </div>
            <div style="font-size:.82rem;color:#c8cfe0;margin-bottom:.25rem;">
                ${s.tipo === 'eliminacion' ? '🗑️ Eliminación' : '✏️ Modificación'} —
                <strong>${s.marca} ${s.modelo}</strong> (Catálogo: ${s.placa})
            </div>
            ${s.motivo_resolucion && s.motivo_resolucion !== 'Aprobado' ? `
            <div style="background:#1a1d27;border-radius:8px;padding:.6rem .8rem;margin-top:.5rem;font-size:.78rem;color:#7c8398;">
                <i class="bi bi-chat-text"></i> <strong style="color:#c8cfe0;">Motivo:</strong> ${s.motivo_resolucion}
            </div>` : ''}
            ${cambiosHTML}
        </div>`;
    }).join('');

    Swal.fire({
        title: `<span style="font-family:'Rajdhani',sans-serif;">${titulo}</span>`,
        html: `<div style="max-height:500px;overflow-y:auto;padding-right:.25rem;">${html}</div>`,
        background: '#1a1d27',
        color: '#e8eaf0',
        confirmButtonColor: '#e8b84b',
        confirmButtonText: 'Cerrar',
        width: '580px'
    });
};

// ── APROBAR SOLICITUD ─────────────────────────────────────────────────────────
window.aprobarSolicitud = async (id) => {
    const conf = await Swal.fire({
        title: '¿Aprobar solicitud?',
        text: 'El cambio se ejecutará inmediatamente.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, aprobar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#4caf7d',
        cancelButtonColor: '#555',
        background: '#1a1d27',
        color: '#e8eaf0'
    });

    if (!conf.isConfirmed) return;

    mostrarLoader('Aprobando solicitud...');
    const body = new FormData();
    body.append('id', id);
    body.append('accion', 'aprobar');
    body.append('motivo', 'Aprobado');

    try {
        const r = await fetch(`${BASE}/API/solicitudes/resolver`, { method: 'POST', body });
        const d = await r.json();
        if (d.codigo === 1) {
            Swal.close();
            actualizarCampanita();
            toastExito(d.mensaje);
            if (typeof buscar === 'function') buscar();
        } else {
            Toast.fire({ icon: 'error', title: d.mensaje });
        }
    } catch {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    } finally {
        ocultarLoader();
    }
};

// ── RECHAZAR SOLICITUD ────────────────────────────────────────────────────────
window.rechazarSolicitud = async (id) => {
    const { value: motivo, isConfirmed } = await Swal.fire({
        title: 'Rechazar solicitud',
        input: 'textarea',
        inputLabel: 'Motivo del rechazo',
        inputPlaceholder: 'Explica por qué se rechaza la solicitud...',
        inputAttributes: { style: 'background:#242837;color:#e8eaf0;border:1px solid #2e3347;border-radius:8px;' },
        showCancelButton: true,
        confirmButtonText: 'Rechazar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e05252',
        cancelButtonColor: '#555',
        background: '#1a1d27',
        color: '#e8eaf0',
        inputValidator: (v) => !v ? 'Debes indicar el motivo' : null
    });

    if (!isConfirmed || !motivo) return;

    mostrarLoader('Rechazando solicitud...');
    const body = new FormData();
    body.append('id', id);
    body.append('accion', 'rechazar');
    body.append('motivo', motivo);

    try {
        const r = await fetch(`${BASE}/API/solicitudes/resolver`, { method: 'POST', body });
        const d = await r.json();
        if (d.codigo === 1) {
            Swal.close();
            actualizarCampanita();
            toastExito(d.mensaje);
        } else {
            Toast.fire({ icon: 'error', title: d.mensaje });
        }
    } catch {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    } finally {
        ocultarLoader();
    }
};

// ── CREAR SOLICITUD ELIMINACION (COMTE_PTN) ───────────────────────────────────
window.crearSolicitudEliminacion = async (placa) => {
    const conf = await Swal.fire({
        icon: 'warning',
        title: '¿Solicitar eliminación?',
        html: `Se enviará una solicitud al Comandante de Compañía para eliminar el vehículo con catálogo <strong>${placa}</strong>.`,
        showCancelButton: true,
        confirmButtonText: 'Sí, solicitar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e05252',
        cancelButtonColor: '#555',
        background: '#1a1d27',
        color: '#e8eaf0'
    });

    if (!conf.isConfirmed) return;

    mostrarLoader('Enviando solicitud...');
    const body = new FormData();
    body.append('tipo', 'eliminacion');
    body.append('placa', placa);

    try {
        const r = await fetch(`${BASE}/API/solicitudes/crear`, { method: 'POST', body });
        const d = await r.json();
        if (d.codigo === 1) {
            actualizarCampanita();
            toastExito(d.mensaje);
        } else {
            Toast.fire({ icon: 'error', title: d.mensaje });
        }
    } catch {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
    } finally {
        ocultarLoader();
    }
};

// ── CREAR SOLICITUD MODIFICACION (COMTE_PTN) ──────────────────────────────────
window.crearSolicitudModificacion = async (placa, datos) => {
    mostrarLoader('Enviando solicitud...');
    const body = new FormData();
    body.append('tipo', 'modificacion');
    body.append('placa', placa);
    body.append('datos_cambio', JSON.stringify(datos));

    try {
        const r = await fetch(`${BASE}/API/solicitudes/crear`, { method: 'POST', body });
        const d = await r.json();
        if (d.codigo === 1) {
            actualizarCampanita();
            toastExito(d.mensaje);
        } else {
            Toast.fire({ icon: 'error', title: d.mensaje });
        }
        return d.codigo === 1;
    } catch {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
        return false;
    } finally {
        ocultarLoader();
    }
};

// ── INIT ──────────────────────────────────────────────────────────────────────
document.getElementById('btnNotificaciones')?.addEventListener('click', abrirNotificaciones);