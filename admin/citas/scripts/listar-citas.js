// admin/citas/scripts/listar-citas.js

document.addEventListener('DOMContentLoaded', () => {

    const BASE       = '/vetweb/admin/citas/api';
    const contenedor = document.getElementById('contenedor-citas');

    let todasLasCitas = [];
    let filtroActual  = 'todos';

    // ── Cargar citas desde la API ──────────────────────────────────────────────
    async function cargarCitas() {
        contenedor.innerHTML = `
            <div class="col-span-3 flex justify-center items-center h-32 text-gray-400">
                Cargando citas...
            </div>`;

        try {
            const res  = await fetch(`${BASE}/listar_citas.php`);
            const data = await res.json();

            if (!data.success) {
                mostrarVacio('Error al cargar las citas.');
                return;
            }

            todasLasCitas = data.citas;
            renderizar();

        } catch {
            mostrarVacio('Error de conexión.');
        }
    }

    // ── Renderizar según filtro activo ─────────────────────────────────────────
    function renderizar() {
        const filtradas = filtroActual === 'todos'
            ? todasLasCitas
            : todasLasCitas.filter(c => c.estado === filtroActual);

        if (filtradas.length === 0) {
            mostrarVacio('No hay citas para mostrar.');
            return;
        }

        contenedor.innerHTML = '';
        filtradas.forEach(c => contenedor.appendChild(crearCard(c)));
    }

    function mostrarVacio(msg) {
        contenedor.innerHTML = `
            <div class="col-span-3 border-2 border-dashed border-gray-300 rounded-lg h-48
                        flex items-center justify-center text-gray-400">
                ${msg}
            </div>`;
    }

    // ── Card de cita ───────────────────────────────────────────────────────────
    function crearCard(c) {
        const estadoConfig = {
            pendiente:  { cls: 'bg-yellow-100 text-yellow-700', label: 'Pendiente'  },
            confirmada: { cls: 'bg-blue-100 text-blue-700',     label: 'Confirmada' },
            completada: { cls: 'bg-green-100 text-green-700',   label: 'Completada' },
            cancelada:  { cls: 'bg-red-100 text-red-600',       label: 'Cancelada'  },
        };
        const estado = estadoConfig[c.estado] ?? { cls: 'bg-gray-100 text-gray-600', label: c.estado };

        const fecha  = new Date(c.fecha);
        const fechaFmt = fecha.toLocaleDateString('es-CL', { day: '2-digit', month: 'short', year: 'numeric' });
        const horaFmt  = fecha.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' });

        const card = document.createElement('div');
        card.className = 'bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col gap-3 hover:shadow-md transition-shadow';
        card.innerHTML = `
            <!-- Header: fecha + estado -->
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">${fechaFmt}</p>
                    <p class="text-lg font-bold text-gray-800">${horaFmt}</p>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${estado.cls}">
                    ${estado.label}
                </span>
            </div>

            <!-- Servicio -->
            <div class="flex items-center gap-2 text-sm text-gray-700">
                <svg class="w-4 h-4 text-brand-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span class="font-medium">${escHtml(c.tipo)}</span>
            </div>

            <!-- Cliente -->
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span>${escHtml(c.cliente_nombre)}</span>
                ${c.cliente_telefono ? `<span class="text-gray-400">· ${escHtml(c.cliente_telefono)}</span>` : ''}
            </div>

            <!-- Mascota -->
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <span>${escHtml(c.mascota_nombre)}${c.mascota_especie ? ` <span class="text-gray-400">(${escHtml(c.mascota_especie)})</span>` : ''}</span>
            </div>

            ${c.nota ? `
            <!-- Nota -->
            <p class="text-xs text-gray-500 bg-gray-50 rounded p-2 border border-gray-100 line-clamp-2">
                ${escHtml(c.nota)}
            </p>` : ''}

            <!-- Acciones -->
            <div class="flex gap-2 pt-1 border-t border-gray-50">
                <select data-id="${c.id}" data-action="cambiar-estado"
                    class="flex-1 text-xs border border-gray-200 rounded px-2 py-1.5 bg-white text-gray-600 focus:outline-none focus:border-brand-400">
                    <option value="pendiente"  ${c.estado === 'pendiente'  ? 'selected' : ''}>Pendiente</option>
                    <option value="confirmada" ${c.estado === 'confirmada' ? 'selected' : ''}>Confirmada</option>
                    <option value="completada" ${c.estado === 'completada' ? 'selected' : ''}>Completada</option>
                    <option value="cancelada"  ${c.estado === 'cancelada'  ? 'selected' : ''}>Cancelada</option>
                </select>
                <button data-id="${c.id}" data-action="eliminar"
                    class="px-3 py-1.5 text-xs text-red-500 hover:text-red-700 border border-gray-200 hover:border-red-300 rounded transition-colors">
                    Eliminar
                </button>
            </div>
        `;
        return card;
    }

    // ── Cambiar estado ─────────────────────────────────────────────────────────
    async function cambiarEstado(id, estado) {
        try {
            const fd = new FormData();
            fd.append('id', id);
            fd.append('estado', estado);
            const res  = await fetch(`${BASE}/cambiar_estado_cita.php`, { method: 'POST', body: fd });
            const data = await res.json();

            if (data.success) {
                // Actualizar localmente sin recargar
                const cita = todasLasCitas.find(c => c.id == id);
                if (cita) cita.estado = estado;
                renderizar();
            } else {
                alert(data.message || 'Error al cambiar estado.');
            }
        } catch {
            alert('Error de conexión.');
        }
    }

    // ── Eliminar cita ──────────────────────────────────────────────────────────
    async function eliminarCita(id) {
        if (!confirm('¿Eliminar esta cita? Esta acción no se puede deshacer.')) return;

        try {
            const fd = new FormData();
            fd.append('id', id);
            const res  = await fetch(`${BASE}/eliminar_cita.php`, { method: 'POST', body: fd });
            const data = await res.json();

            if (data.success) {
                todasLasCitas = todasLasCitas.filter(c => c.id != id);
                renderizar();
            } else {
                alert(data.message || 'Error al eliminar.');
            }
        } catch {
            alert('Error de conexión.');
        }
    }

    // ── Delegación de eventos en cards ─────────────────────────────────────────
    contenedor.addEventListener('change', (e) => {
        const sel = e.target.closest('[data-action="cambiar-estado"]');
        if (sel) cambiarEstado(sel.dataset.id, sel.value);
    });

    contenedor.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action="eliminar"]');
        if (btn) eliminarCita(btn.dataset.id);
    });

    // ── Filtros de estado ──────────────────────────────────────────────────────
    document.getElementById('filtros-estado').addEventListener('click', (e) => {
        const btn = e.target.closest('[data-estado]');
        if (!btn) return;

        filtroActual = btn.dataset.estado;

        document.querySelectorAll('.filtro-btn').forEach(b => {
            b.classList.remove('bg-brand-600', 'text-white', 'activo');
            b.classList.add('bg-white', 'text-gray-600', 'border', 'border-gray-200');
        });
        btn.classList.add('bg-brand-600', 'text-white', 'activo');
        btn.classList.remove('bg-white', 'text-gray-600');

        renderizar();
    });

    // ── Helper ─────────────────────────────────────────────────────────────────
    function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── Recargar al crear nueva cita ───────────────────────────────────────────
    // crear-cita.js hace window.location.reload(), pero si prefieres sin reload
    // puedes emitir un evento custom y escucharlo aquí:
    window.addEventListener('citaCreada', cargarCitas);

    // Carga inicial
    cargarCitas();
});