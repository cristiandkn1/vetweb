// admin/usuarios/scripts/mascotas-listado.js
// Depende de: mascotas-config.js
// Responsabilidad: abrir el modal de listado, cargar y renderizar cards

// ── Abrir modal ────────────────────────────────────────────────────────────────
function abrirModalMascotas(clienteId, clienteNombre) {
    window.MascotaState.clienteId     = clienteId;
    window.MascotaState.clienteNombre = clienteNombre;
    document.getElementById('modal-mascotas-titulo').textContent    = `Mascotas de ${clienteNombre}`;
    document.getElementById('modal-mascotas-subtitulo').textContent = 'Historial de pacientes';
    document.getElementById('modal-mascotas').classList.remove('hidden');
    cargarMascotas();
    lucide.createIcons();
}

// ── Cargar cards ───────────────────────────────────────────────────────────────
async function cargarMascotas() {
    const contenedor = document.getElementById('contenedor-mascotas');
    contenedor.innerHTML = `<div class="flex justify-center items-center h-32 text-gray-400 text-sm">Cargando...</div>`;

    try {
        const res  = await fetch(`${BASE_MASCOTA}/listar_mascotas.php?cliente_id=${window.MascotaState.clienteId}`);
        const data = await res.json();
        if (!data.success) throw new Error();

        if (data.mascotas.length === 0) {
            contenedor.innerHTML = `
                <div class="flex flex-col items-center justify-center h-40 text-gray-400 gap-3">
                    <div class="w-14 h-14 text-gray-300">${ESPECIE_SVG.default}</div>
                    <p class="text-sm">Este cliente no tiene mascotas registradas.</p>
                </div>`;
            return;
        }

        contenedor.innerHTML = '';
        const grid = document.createElement('div');
        grid.className = 'grid grid-cols-1 sm:grid-cols-2 gap-4';
        data.mascotas.forEach(m => grid.appendChild(crearCardMascota(m)));
        contenedor.appendChild(grid);
        lucide.createIcons();

    } catch {
        contenedor.innerHTML = `<div class="text-center text-red-400 text-sm py-8">Error al cargar mascotas.</div>`;
    }
}

// ── Crear card ─────────────────────────────────────────────────────────────────
function crearCardMascota(m) {
    const edad  = calcularEdad(m.fecha_nacimiento);
    const color = getEspecieColor(m.especie);
    const svg   = getEspecieSvg(m.especie);

    const card = document.createElement('div');
    card.className = 'bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow';
    card.innerHTML = `
        <div class="flex items-center gap-4 p-4 border-b border-gray-50">
            <div class="w-16 h-16 rounded-xl ${color} flex items-center justify-center shrink-0">
                ${svg}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h4 class="font-bold text-gray-800 text-base truncate">${escM(m.nombre)}</h4>
                        <p class="text-xs text-gray-400 truncate">${escM(m.especie ?? '')}${m.raza ? ' · ' + escM(m.raza) : ''}</p>
                    </div>
                    ${m.sexo ? `
                    <span class="shrink-0 text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                        ${m.sexo === 'Macho'
                            ? '<svg class="w-3 h-3 inline mr-0.5 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="10" cy="14" r="5"/><path d="M19 5l-5.5 5.5M19 5h-5M19 5v5"/></svg>'
                            : '<svg class="w-3 h-3 inline mr-0.5 text-pink-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="9" r="5"/><path d="M12 14v6M9 17h6"/></svg>'
                        }
                        ${escM(m.sexo)}
                    </span>` : ''}
                </div>
            </div>
        </div>

        <div class="px-4 py-3 space-y-2">
            <div class="flex flex-wrap gap-2">
                ${edad ? `
                <span class="inline-flex items-center gap-1 text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full">
                    <i data-lucide="cake" class="w-3 h-3"></i> ${edad}
                </span>` : ''}
                ${m.color ? `
                <span class="inline-flex items-center gap-1 text-xs bg-purple-50 text-purple-600 px-2 py-0.5 rounded-full">
                    <i data-lucide="palette" class="w-3 h-3"></i> ${escM(m.color)}
                </span>` : ''}
                ${m.peso ? `
                <span class="inline-flex items-center gap-1 text-xs bg-green-50 text-green-600 px-2 py-0.5 rounded-full">
                    <i data-lucide="weight" class="w-3 h-3"></i> ${escM(m.peso)} kg
                </span>` : ''}
                ${m.numero_chip ? `
                <span class="inline-flex items-center gap-1 text-xs bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full">
                    <i data-lucide="fingerprint" class="w-3 h-3"></i> ${escM(m.numero_chip)}
                </span>` : ''}
                ${m.esterilizado == 1 ? `
                <span class="inline-flex items-center gap-1 text-xs bg-teal-50 text-teal-600 px-2 py-0.5 rounded-full" title="Esterilizado">
                    <i data-lucide="scissors" class="w-3 h-3"></i> Esterilizado
                </span>` : ''}
            </div>
            ${m.alergias ? `
            <div class="flex gap-2 bg-red-50 border border-red-100 rounded-lg p-2">
                <i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-red-500 shrink-0 mt-0.5"></i>
                <p class="text-xs text-red-700 line-clamp-2"><strong>Alergias:</strong> ${escM(m.alergias)}</p>
            </div>` : ''}
            ${m.observaciones ? `
            <div class="flex gap-2 bg-amber-50 border border-amber-100 rounded-lg p-2">
                <i data-lucide="stethoscope" class="w-3.5 h-3.5 text-amber-500 shrink-0 mt-0.5"></i>
                <p class="text-xs text-gray-600 line-clamp-2">${escM(m.observaciones)}</p>
            </div>` : ''}
        </div>

        <div class="flex gap-0 border-t border-gray-100">
            <button data-id="${m.id}" data-action="editar-mascota"
                class="flex-1 flex items-center justify-center gap-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 py-2.5 transition border-r border-gray-100">
                <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Editar
            </button>
            <button data-id="${m.id}" data-nombre="${escM(m.nombre)}" data-action="eliminar-mascota"
                class="flex-1 flex items-center justify-center gap-1.5 text-xs font-medium text-red-500 hover:bg-red-50 py-2.5 transition">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Eliminar
            </button>
        </div>`;
    return card;
}

// ── Listeners del modal de listado ─────────────────────────────────────────────
document.getElementById('btn-cerrar-modal-mascotas').addEventListener('click', () => {
    document.getElementById('modal-mascotas').classList.add('hidden');
});

document.getElementById('btn-agregar-mascota').addEventListener('click', abrirFormNuevaMascota);

// Delegación de clicks en cards (editar / eliminar)
document.getElementById('contenedor-mascotas').addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action]');
    if (!btn) return;
    const { id, action, nombre } = btn.dataset;
    if (action === 'editar-mascota')   abrirFormEditarMascota(id);
    if (action === 'eliminar-mascota') abrirModalEliminarMascota(id, nombre);
});