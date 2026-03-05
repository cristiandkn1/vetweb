// admin/servicios/scripts/servicios.js

document.addEventListener('DOMContentLoaded', () => {

    const BASE = '/vetweb/admin/servicios/api';

    // ── DOM ────────────────────────────────────────────────────────────────────
    const btnAbrir      = document.getElementById('btn-nuevo-servicio');
    const modal         = document.getElementById('modal-nuevo-servicio');
    const btnCerrarX    = document.getElementById('btn-cerrar-modal-servicio');
    const btnCancelar   = document.getElementById('btn-cancelar-modal-servicio');
    const form          = document.getElementById('form-crear-servicio');
    const btnSubmit     = document.getElementById('btn-submit-servicio');
    const modalTitle    = document.getElementById('modal-servicio-title');
    const contenedor    = document.getElementById('contenedor-servicios');

    // ── Modal ──────────────────────────────────────────────────────────────────
    function toggleModal(show) {
        if (show) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
            resetForm();
        }
    }

    function resetForm() {
        form.reset();
        document.getElementById('servicio_id').value = '';
        modalTitle.textContent = 'Nuevo Servicio';
        btnSubmit.textContent = 'Guardar Servicio';
        clearError();
    }

    btnAbrir.addEventListener('click', () => toggleModal(true));
    btnCerrarX.addEventListener('click', () => toggleModal(false));
    btnCancelar.addEventListener('click', () => toggleModal(false));

    modal.addEventListener('click', (e) => {
        if (e.target === modal.querySelector('.fixed.inset-0.bg-gray-900\\/50')) {
            toggleModal(false);
        }
    });

    // ── Error ──────────────────────────────────────────────────────────────────
    function showError(msg) {
        clearError();
        const div = document.createElement('div');
        div.id = 'servicio-error';
        div.className = 'text-sm text-red-600 bg-red-50 border border-red-200 rounded p-3';
        div.textContent = msg;
        form.prepend(div);
    }

    function clearError() {
        document.getElementById('servicio-error')?.remove();
    }

    function setLoading(loading) {
        btnSubmit.disabled = loading;
        btnSubmit.textContent = loading ? 'Guardando...' : 'Guardar Servicio';
    }

    // ── Cargar y renderizar servicios ──────────────────────────────────────────
    async function cargarServicios() {
        contenedor.innerHTML = `
            <div class="col-span-3 flex justify-center items-center h-32 text-gray-400">
                Cargando servicios...
            </div>`;

        try {
            const res  = await fetch(`${BASE}/listar_servicios.php`);
            const data = await res.json();

            if (!data.success || data.servicios.length === 0) {
                contenedor.innerHTML = `
                    <div class="col-span-3 border-2 border-dashed border-gray-300 rounded-lg h-48
                                flex items-center justify-center text-gray-400">
                        No hay servicios registrados aún.
                    </div>`;
                return;
            }

            contenedor.innerHTML = '';
            data.servicios.forEach(s => contenedor.appendChild(crearCard(s)));

        } catch {
            contenedor.innerHTML = `
                <div class="col-span-3 text-red-500 text-center">
                    Error al cargar servicios.
                </div>`;
        }
    }

    function crearCard(s) {
        const precioTexto = s.precio_min > 0 && s.precio_max > 0
            ? `$${Number(s.precio_min).toLocaleString('es-CL')} – $${Number(s.precio_max).toLocaleString('es-CL')}`
            : s.precio_min > 0
                ? `Desde $${Number(s.precio_min).toLocaleString('es-CL')}`
                : 'Precio a consultar';

        const card = document.createElement('div');
        card.className = 'bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col gap-3 hover:shadow-md transition-shadow';
        card.innerHTML = `
            <div class="flex items-start justify-between gap-2">
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-800 text-base">${escHtml(s.nombre)}</h3>
                    ${s.descripcion ? `<p class="text-sm text-gray-500 mt-0.5">${escHtml(s.descripcion)}</p>` : ''}
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${s.activo == 1 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'}">
                    ${s.activo == 1 ? 'Activo' : 'Inactivo'}
                </span>
            </div>

            <div class="flex items-center gap-4 text-sm text-gray-600">
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    ${precioTexto}
                </span>
                ${s.duracion_min ? `
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    ${s.duracion_min} min
                </span>` : ''}
            </div>

            <div class="flex gap-2 pt-1 border-t border-gray-50">
                <button data-id="${s.id}" data-action="editar"
                    class="flex-1 text-sm text-brand-600 hover:text-brand-800 font-medium py-1 rounded hover:bg-brand-50 transition-colors">
                    Editar
                </button>
                <button data-id="${s.id}" data-action="eliminar"
                    class="flex-1 text-sm text-red-500 hover:text-red-700 font-medium py-1 rounded hover:bg-red-50 transition-colors">
                    Eliminar
                </button>
            </div>
        `;
        return card;
    }

    // ── Editar ─────────────────────────────────────────────────────────────────
    async function abrirEditar(id) {
        try {
            const res  = await fetch(`${BASE}/listar_servicios.php`);
            const data = await res.json();
            const s    = data.servicios.find(x => x.id == id);
            if (!s) return;

            document.getElementById('servicio_id').value          = s.id;
            document.getElementById('servicio_nombre').value      = s.nombre;
            document.getElementById('servicio_descripcion').value = s.descripcion || '';
            document.getElementById('servicio_precio_min').value  = s.precio_min;
            document.getElementById('servicio_precio_max').value  = s.precio_max;
            document.getElementById('servicio_duracion').value    = s.duracion_min;
            document.getElementById('servicio_activo').checked    = s.activo == 1;

            modalTitle.textContent  = 'Editar Servicio';
            btnSubmit.textContent   = 'Actualizar Servicio';
            toggleModal(true);
        } catch {
            alert('No se pudo cargar el servicio.');
        }
    }

    // ── Eliminar ───────────────────────────────────────────────────────────────
    async function eliminarServicio(id) {
        if (!confirm('¿Eliminar este servicio? Esta acción no se puede deshacer.')) return;

        try {
            const fd = new FormData();
            fd.append('id', id);
            const res  = await fetch(`${BASE}/eliminar_servicio.php`, { method: 'POST', body: fd });
            const data = await res.json();

            if (data.success) {
                cargarServicios();
            } else {
                alert(data.message || 'Error al eliminar.');
            }
        } catch {
            alert('Error de conexión.');
        }
    }

    // Delegación de eventos en las cards
    contenedor.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;
        const { id, action } = btn.dataset;
        if (action === 'editar')   abrirEditar(id);
        if (action === 'eliminar') eliminarServicio(id);
    });

    // ── Submit ─────────────────────────────────────────────────────────────────
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearError();
        setLoading(true);

        try {
            const res  = await fetch(`${BASE}/guardar_servicio.php`, {
                method: 'POST',
                body: new FormData(form),
            });
            const data = await res.json();

            if (data.success) {
                toggleModal(false);
                cargarServicios();
            } else {
                showError(data.message || 'Error al guardar el servicio.');
            }
        } catch {
            showError('Error de conexión. Intenta de nuevo.');
        } finally {
            setLoading(false);
        }
    });

    // ── Helpers ────────────────────────────────────────────────────────────────
    function escHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // Cargar al inicio
    cargarServicios();
});