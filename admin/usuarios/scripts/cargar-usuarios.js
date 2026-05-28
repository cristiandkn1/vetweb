// admin/usuario/scripts/cargar-usuarios.js
// Responsabilidad: cargar usuarios desde la API, filtrar y paginar

const BASE_USUARIO = '/admin/usuarios/api';
const POR_PAGINA   = 10;

window.UsuarioState = {
    todos:        [],
    paginaActual: 1,
};

async function cargarUsuarios() {
    const tablaBody = document.getElementById('tabla-usuarios-body');
    tablaBody.innerHTML = `
        <tr><td colspan="7" class="px-5 py-12 text-center text-gray-400">Cargando clientes...</td></tr>`;

    try {
        const res  = await fetch(`${BASE_USUARIO}/listar_usuarios.php`);
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        window.UsuarioState.todos        = data.usuarios;
        window.UsuarioState.paginaActual = 1;
        renderTablaUsuarios();
    } catch {
        tablaBody.innerHTML = `
            <tr><td colspan="7" class="px-5 py-10 text-center text-red-400">Error al cargar clientes.</td></tr>`;
    }
}

function renderTablaUsuarios() {
    const tablaBody      = document.getElementById('tabla-usuarios-body');
    const paginacionInfo = document.getElementById('paginacion-info');
    const paginacionBtns = document.getElementById('paginacion-botones');
    const q              = document.getElementById('buscador-usuario').value.trim().toLowerCase();

    const filtrados = window.UsuarioState.todos.filter(u =>
        u.nombre_completo?.toLowerCase().includes(q) ||
        u.rut?.toLowerCase().includes(q) ||
        u.telefono?.toLowerCase().includes(q)
    );

    const total  = filtrados.length;
    const inicio = (window.UsuarioState.paginaActual - 1) * POR_PAGINA;
    const fin    = inicio + POR_PAGINA;
    const pagina = filtrados.slice(inicio, fin);

    // Info
    paginacionInfo.textContent = total === 0
        ? 'Sin resultados'
        : `Mostrando ${inicio + 1}–${Math.min(fin, total)} de ${total} clientes`;

    // Paginación
    const totalPaginas = Math.ceil(total / POR_PAGINA);
    paginacionBtns.innerHTML = '';
    for (let i = 1; i <= totalPaginas; i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.className = `px-2.5 py-1 rounded text-xs font-medium transition ${
            i === window.UsuarioState.paginaActual
                ? 'bg-brand-600 text-white'
                : 'bg-white border border-gray-200 text-gray-600 hover:border-brand-400'
        }`;
        btn.addEventListener('click', () => {
            window.UsuarioState.paginaActual = i;
            renderTablaUsuarios();
        });
        paginacionBtns.appendChild(btn);
    }

    // Filas
    if (pagina.length === 0) {
        tablaBody.innerHTML = `
            <tr><td colspan="7" class="px-5 py-12 text-center text-gray-400">No se encontraron clientes.</td></tr>`;
        return;
    }

    tablaBody.innerHTML = '';
    pagina.forEach((u, idx) => {
        const fecha = u.fecha_registro
            ? new Date(u.fecha_registro).toLocaleDateString('es-CL', { day: '2-digit', month: 'short', year: 'numeric' })
            : '—';

        const telefono = u.telefono ? `+56 ${escHtml(u.telefono)}` : '—';
        const inicial  = escHtml(u.nombre_completo?.charAt(0).toUpperCase() ?? '?');

        const acciones = `
            <div class="flex items-center gap-1.5 justify-center sm:justify-center flex-wrap">
                <button data-id="${u.id}" data-action="mascotas"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition">
                    <i class="fa-solid fa-paw"></i> Mascotas
                </button>
                <button data-id="${u.id}" data-action="editar"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                    <i class="fa-solid fa-pen-to-square"></i> Editar
                </button>
                <button data-id="${u.id}" data-nombre="${escHtml(u.nombre_completo ?? '')}" data-action="eliminar"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition">
                    <i class="fa-solid fa-trash-can"></i> Eliminar
                </button>
            </div>`;

        // ── Card mobile (visible solo en sm-) ──────────────────────────────
        const cardMobile = document.createElement('tr');
        cardMobile.className = 'block sm:hidden border-b border-gray-100';
        cardMobile.innerHTML = `
            <td colspan="7" class="block p-4">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 space-y-3">
                    <!-- Header card -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-bold shrink-0">
                                ${inicial}
                            </div>
                            <div>
                                <div class="font-semibold text-gray-800 text-sm">${escHtml(u.nombre_completo ?? '—')}</div>
                                <div class="text-xs text-gray-400">${escHtml(u.rut ?? 'Sin RUT')}</div>
                            </div>
                        </div>
                        <span class="text-xs text-gray-400">#${inicio + idx + 1}</span>
                    </div>
                    <!-- Info -->
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-gray-400 block">Celular</span>
                            <span class="text-gray-700 font-medium">${telefono}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block">Registro</span>
                            <span class="text-gray-700 font-medium">${fecha}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-gray-400 block">Email</span>
                            <span class="text-gray-700 font-medium">${escHtml(u.email ?? '—')}</span>
                        </div>
                    </div>
                    <!-- Acciones -->
                    ${acciones}
                </div>
            </td>`;

        // ── Fila desktop (visible solo en sm+) ─────────────────────────────
        const filaDesktop = document.createElement('tr');
        filaDesktop.className = 'hidden sm:table-row border-b border-gray-50 hover:bg-gray-50 transition-colors';
        filaDesktop.innerHTML = `
            <td class="px-5 py-3.5 text-gray-400 text-xs">${inicio + idx + 1}</td>
            <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-bold text-xs shrink-0">
                        ${inicial}
                    </div>
                    <span class="font-medium text-gray-800">${escHtml(u.nombre_completo ?? '—')}</span>
                </div>
            </td>
            <td class="px-5 py-3.5 text-gray-600">${escHtml(u.rut ?? '—')}</td>
            <td class="px-5 py-3.5 text-gray-600">${telefono}</td>
            <td class="px-5 py-3.5 text-gray-500">${escHtml(u.email ?? '—')}</td>
            <td class="px-5 py-3.5 text-gray-400 text-xs">${fecha}</td>
            <td class="px-5 py-3.5">${acciones}</td>`;

        tablaBody.appendChild(cardMobile);
        tablaBody.appendChild(filaDesktop);
    });
}

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}