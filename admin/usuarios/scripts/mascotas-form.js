// admin/usuarios/scripts/mascotas-form.js
// Depende de: mascotas-config.js
// Responsabilidad: form crear/editar mascota + vacunas inline + modal eliminar

// ── Estado vacunas ─────────────────────────────────────────────────────────────
let _vacunas = []; // [{ id, nombre, fecha_aplicacion, fecha_proxima, veterinario, lote, notas, _delete }]

// ── Selector de especie ────────────────────────────────────────────────────────
function resetEspecieSelector() {
    document.querySelectorAll('#especie-selector input[type="radio"]').forEach(r => r.checked = false);
    actualizarAvatarPreview(null);
}

function actualizarAvatarPreview(especie) {
    const preview = document.getElementById('mascota-avatar-preview');
    const color   = getEspecieColor(especie);
    const svg     = getEspecieSvg(especie);
    preview.className = `w-20 h-20 rounded-2xl border-2 flex items-center justify-center p-3 ${color} border-current border-opacity-20`;
    preview.innerHTML = svg;
    if (window.lucide) {
        window.lucide.createIcons();
    }
}

document.getElementById('especie-selector').addEventListener('change', (e) => {
    if (e.target.type === 'radio') actualizarAvatarPreview(e.target.value);
});

// ── Campos extendidos ──────────────────────────────────────────────────────────
function cargarCamposExtendidos(m) {
    document.getElementById('m_peso').value             = m.peso             ?? '';
    document.getElementById('m_chip').value             = m.numero_chip      ?? '';
    document.getElementById('m_esterilizado').checked   = m.esterilizado == 1;
    document.getElementById('m_ultima_revision').value  = m.ultima_revision  ?? '';
    document.getElementById('m_alergias').value         = m.alergias         ?? '';
    document.getElementById('m_notas_internas').value   = m.notas_internas   ?? '';
}

function resetCamposExtendidos() {
    ['m_peso', 'm_chip', 'm_ultima_revision', 'm_alergias', 'm_notas_internas']
        .forEach(id => { document.getElementById(id).value = ''; });
    document.getElementById('m_esterilizado').checked = false;
    resetVacunas();
}

// ── Vacunas inline ─────────────────────────────────────────────────────────────
function resetVacunas() {
    _vacunas = [];
    renderVacunas();
}

function renderVacunas() {
    const lista  = document.getElementById('lista-vacunas');
    const vacio  = document.getElementById('vacunas-vacio');
    const activas = _vacunas.filter(v => !v._delete);

    if (activas.length === 0) {
        lista.innerHTML = '';
        vacio.classList.remove('hidden');
        return;
    }
    vacio.classList.add('hidden');
    lista.innerHTML = '';

    activas.forEach(v => {
        const realIdx = _vacunas.indexOf(v);
        const div = document.createElement('div');
        div.className = 'bg-gray-50 border border-gray-100 rounded-xl p-3 space-y-2';
        div.innerHTML = `
            <div class="flex items-center justify-between gap-2">
                <input type="hidden"  name="vacuna_id[]"    value="${v.id ?? ''}">
                <input type="text"    name="vacuna_nombre[]" value="${escM(v.nombre)}"
                    placeholder="Nombre de la vacuna *"
                    class="flex-1 border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-400">
                <button type="button" data-vidx="${realIdx}"
                    class="btn-quitar-vacuna text-red-400 hover:text-red-600 shrink-0">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs text-gray-500 block mb-0.5">Fecha aplicación *</label>
                    <input type="date" name="vacuna_fecha_aplicacion[]" value="${v.fecha_aplicacion ?? ''}"
                        class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-0.5">Próxima dosis</label>
                    <input type="date" name="vacuna_fecha_proxima[]" value="${v.fecha_proxima ?? ''}"
                        class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <input type="text" name="vacuna_veterinario[]" value="${escM(v.veterinario ?? '')}"
                    placeholder="Veterinario"
                    class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-400">
                <input type="text" name="vacuna_lote[]" value="${escM(v.lote ?? '')}"
                    placeholder="Lote"
                    class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-400">
            </div>
            <input type="text" name="vacuna_notas[]" value="${escM(v.notas ?? '')}"
                placeholder="Notas"
                class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-400">
        `;
        lista.appendChild(div);
    });

    lista.querySelectorAll('.btn-quitar-vacuna').forEach(btn => {
        btn.addEventListener('click', () => {
            const i = parseInt(btn.dataset.vidx);
            if (_vacunas[i].id) {
                _vacunas[i]._delete = true;
            } else {
                _vacunas.splice(i, 1);
            }
            renderVacunas();
            lucide.createIcons();
        });
    });
    lucide.createIcons();
}

async function cargarVacunasMascota(mascotaId) {
    try {
        const res  = await fetch(`${BASE_MASCOTA}/listar_vacunas.php?mascota_id=${mascotaId}`);
        const data = await res.json();
        _vacunas = data.success ? data.vacunas : [];
    } catch {
        _vacunas = [];
    }
    renderVacunas();
}

document.getElementById('btn-agregar-vacuna').addEventListener('click', () => {
    _vacunas.push({ id: null, nombre: '', fecha_aplicacion: '', fecha_proxima: '', veterinario: '', lote: '', notas: '' });
    renderVacunas();
    lucide.createIcons();
});

// ── Abrir form nueva mascota ───────────────────────────────────────────────────
function abrirFormNuevaMascota() {
    document.getElementById('form-mascota').reset();
    document.getElementById('mascota_id').value         = '';
    document.getElementById('mascota_cliente_id').value = window.MascotaState.clienteId;
    document.getElementById('modal-form-mascota-titulo').textContent = 'Nueva Mascota';
    document.getElementById('btn-submit-mascota').textContent        = 'Guardar Mascota';
    document.getElementById('mascota-error').classList.add('hidden');
    resetEspecieSelector();
    resetCamposExtendidos();
    document.getElementById('modal-form-mascota').classList.remove('hidden');
    lucide.createIcons();
}

// ── Abrir form editar mascota ──────────────────────────────────────────────────
async function abrirFormEditarMascota(id) {
    try {
        const res  = await fetch(`${BASE_MASCOTA}/listar_mascotas.php?cliente_id=${window.MascotaState.clienteId}`);
        const data = await res.json();
        const m    = data.mascotas.find(x => x.id == id);
        if (!m) return;

        document.getElementById('mascota_id').value         = m.id;
        document.getElementById('mascota_cliente_id').value = window.MascotaState.clienteId;
        document.getElementById('m_nombre').value           = m.nombre           ?? '';
        document.getElementById('m_raza').value             = m.raza             ?? '';
        document.getElementById('m_fecha_nac').value        = m.fecha_nacimiento ?? '';
        document.getElementById('m_sexo').value             = m.sexo             ?? '';
        document.getElementById('m_color').value            = m.color            ?? '';
        document.getElementById('m_observaciones').value    = m.observaciones    ?? '';

        cargarCamposExtendidos(m);

        document.querySelectorAll('#especie-selector input[type="radio"]').forEach(r => {
            r.checked = (r.value === m.especie);
        });
        actualizarAvatarPreview(m.especie);

        await cargarVacunasMascota(m.id);

        document.getElementById('modal-form-mascota-titulo').textContent = 'Editar Mascota';
        document.getElementById('btn-submit-mascota').textContent        = 'Guardar Cambios';
        document.getElementById('mascota-error').classList.add('hidden');
        document.getElementById('modal-form-mascota').classList.remove('hidden');
        lucide.createIcons();
    } catch {
        alert('No se pudo cargar la mascota.');
    }
}

// ── Submit ─────────────────────────────────────────────────────────────────────
document.getElementById('form-mascota').addEventListener('submit', async (e) => {
    e.preventDefault();
    const errorDiv  = document.getElementById('mascota-error');
    const btnSubmit = document.getElementById('btn-submit-mascota');
    const esEdicion = !!document.getElementById('mascota_id').value;

    errorDiv.classList.add('hidden');
    btnSubmit.disabled    = true;
    btnSubmit.textContent = 'Guardando...';

    try {
        const res  = await fetch(`${BASE_MASCOTA}/guardar_mascota.php`, {
            method: 'POST',
            body:   new FormData(document.getElementById('form-mascota')),
        });
        const data = await res.json();

        if (data.success) {
            document.getElementById('modal-form-mascota').classList.add('hidden');
            cargarMascotas();
        } else {
            errorDiv.textContent = data.message || 'Error al guardar.';
            errorDiv.classList.remove('hidden');
        }
    } catch {
        errorDiv.textContent = 'Error de conexión.';
        errorDiv.classList.remove('hidden');
    } finally {
        btnSubmit.disabled    = false;
        btnSubmit.textContent = esEdicion ? 'Guardar Cambios' : 'Guardar Mascota';
    }
});

// ── Cerrar form ────────────────────────────────────────────────────────────────
['btn-cerrar-form-mascota', 'btn-cancelar-form-mascota'].forEach(id => {
    document.getElementById(id).addEventListener('click', () => {
        document.getElementById('modal-form-mascota').classList.add('hidden');
    });
});

// ── Modal eliminar ─────────────────────────────────────────────────────────────
function abrirModalEliminarMascota(id, nombre) {
    window.MascotaState.eliminarId = id;
    document.getElementById('eliminar-mascota-nombre').textContent = nombre;
    document.getElementById('modal-eliminar-mascota').classList.remove('hidden');
}

document.getElementById('btn-cancelar-eliminar-mascota').addEventListener('click', () => {
    document.getElementById('modal-eliminar-mascota').classList.add('hidden');
    window.MascotaState.eliminarId = null;
});

document.getElementById('btn-confirmar-eliminar-mascota').addEventListener('click', async () => {
    if (!window.MascotaState.eliminarId) return;
    const btn = document.getElementById('btn-confirmar-eliminar-mascota');
    btn.disabled    = true;
    btn.textContent = 'Eliminando...';
    try {
        const fd = new FormData();
        fd.append('id', window.MascotaState.eliminarId);
        const res  = await fetch(`${BASE_MASCOTA}/eliminar_mascota.php`, { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            document.getElementById('modal-eliminar-mascota').classList.add('hidden');
            window.MascotaState.eliminarId = null;
            cargarMascotas();
        } else {
            alert(data.message || 'Error al eliminar.');
        }
    } catch {
        alert('Error de conexión.');
    } finally {
        btn.disabled    = false;
        btn.textContent = 'Sí, eliminar';
    }
});