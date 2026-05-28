// admin/usuarios/scripts/mascotas.js

const BASE_MASCOTA = '/admin/usuarios/api';

window.MascotaState = {
    clienteId:     null,
    clienteNombre: null,
    eliminarId:    null,
};

// ── Estado vacunas ─────────────────────────────────────────────────────────────
let _vacunas = []; // [{ id, nombre, fecha_aplicacion, fecha_proxima, veterinario, lote, notas, _delete }]

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
        if (vacio) vacio.classList.remove('hidden');
        return;
    }
    if (vacio) vacio.classList.add('hidden');
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

// ── SVGs de especie ────────────────────────────────────────────────────────────
const ESPECIE_SVG = {
    Perro:   `<i data-lucide="dog" class="w-full h-full"></i>`,
    Gato:    `<i data-lucide="cat" class="w-full h-full"></i>`,
    Ave:     `<i data-lucide="bird" class="w-full h-full"></i>`,
    Conejo:  `<i data-lucide="rabbit" class="w-full h-full"></i>`,
    Roedor:  `<i data-lucide="squirrel" class="w-full h-full"></i>`,
    Hamster: `<i data-lucide="squirrel" class="w-full h-full"></i>`,
    Cobaya:  `<i data-lucide="squirrel" class="w-full h-full"></i>`,
    Reptil:  `<i data-lucide="turtle" class="w-full h-full"></i>`,
    default: `<i data-lucide="paw-print" class="w-full h-full"></i>`,
};

const ESPECIE_COLOR = {
    Perro:   'text-amber-500 bg-amber-50',
    Gato:    'text-purple-500 bg-purple-50',
    Ave:     'text-sky-500 bg-sky-50',
    Conejo:  'text-pink-400 bg-pink-50',
    Roedor:  'text-orange-500 bg-orange-50',
    Hamster: 'text-orange-500 bg-orange-50',
    Cobaya:  'text-orange-500 bg-orange-50',
    Reptil:  'text-green-500 bg-green-50',
    default: 'text-gray-400 bg-gray-50',
};

function getEspecieSvg(especie) {
    return ESPECIE_SVG[especie] ?? ESPECIE_SVG.default;
}
function getEspecieColor(especie) {
    return ESPECIE_COLOR[especie] ?? ESPECIE_COLOR.default;
}

// ── Helpers ────────────────────────────────────────────────────────────────────
function escM(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&').replace(/</g,'<').replace(/>/g,'>').replace(/"/g,'"');
}

function calcularEdad(fechaNac) {
    if (!fechaNac) return null;
    const hoy  = new Date();
    const nac  = parseDateLocal(fechaNac) || new Date(fechaNac);
    const años = hoy.getFullYear() - nac.getFullYear();
    const m    = hoy.getMonth() - nac.getMonth();
    const total= m < 0 ? años - 1 : años;
    if (total < 1) {
        const meses = ((hoy.getFullYear() - nac.getFullYear()) * 12) + hoy.getMonth() - nac.getMonth();
        return `${meses} mes${meses !== 1 ? 'es' : ''}`;
    }
    return `${total} año${total !== 1 ? 's' : ''}`;
}

// ── Abrir modal mascotas ───────────────────────────────────────────────────────
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
                    <div class="w-16 h-16 text-gray-300">${ESPECIE_SVG.default}</div>
                    <p class="text-sm">Este cliente no tiene mascotas registradas.</p>
                </div>`;
            return;
        }

        contenedor.innerHTML = '';
        const grid = document.createElement('div');
        grid.className = 'grid grid-cols-1 xl:grid-cols-2 gap-6';
        data.mascotas.forEach(m => grid.appendChild(crearCardMascota(m)));
        contenedor.appendChild(grid);

        // Cargar todas las vacunas y enfermedades en paralelo
        const promises = data.mascotas.flatMap(m => [cargarVacunasCard(m.id), cargarEnfermedadesCard(m.id)]);
        await Promise.all(promises);
        lucide.createIcons();

    } catch {
        contenedor.innerHTML = `<div class="text-center text-red-400 text-sm py-8">Error al cargar mascotas.</div>`;
    }
}

function crearCardMascota(m) {
    const edad   = calcularEdad(m.fecha_nacimiento);
    const color  = getEspecieColor(m.especie);
    const svg    = getEspecieSvg(m.especie);

    const card = document.createElement('div');
    card.className = 'bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all';
    card.innerHTML = `
        <div class="flex items-center gap-4 p-5 border-b border-gray-50">
            <div class="w-16 h-16 rounded-xl ${color} flex items-center justify-center shrink-0">
                ${svg}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h4 class="font-bold text-gray-800 text-base truncate">${escM(m.nombre)}</h4>
                        <p class="text-sm text-gray-400 truncate">${escM(m.especie ?? '')}${m.raza ? ' · ' + escM(m.raza) : ''}</p>
                    </div>
                    ${m.sexo ? `<span class="shrink-0 text-xs font-medium bg-gray-100 text-gray-600 px-2.5 py-0.5 rounded-full">${escM(m.sexo)}</span>` : ''}
                </div>
            </div>
        </div>
        <div class="px-5 py-4 space-y-3">
            ${(edad || m.color || m.peso || m.numero_chip || m.esterilizado == 1) ? `
            <div class="flex flex-wrap gap-1.5">
                ${edad ? `<span class="inline-flex items-center gap-1 text-xs font-medium bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full"><i data-lucide="cake" class="w-3 h-3"></i> ${edad}</span>` : ''}
                ${m.color ? `<span class="inline-flex items-center gap-1 text-xs font-medium bg-purple-50 text-purple-700 px-2.5 py-1 rounded-full"><i data-lucide="palette" class="w-3 h-3"></i> ${escM(m.color)}</span>` : ''}
                ${m.peso ? `<span class="inline-flex items-center gap-1 text-xs font-medium bg-green-50 text-green-700 px-2.5 py-1 rounded-full"><i data-lucide="weight" class="w-3 h-3"></i> ${escM(m.peso)} kg</span>` : ''}
                ${m.numero_chip ? `<span class="inline-flex items-center gap-1 text-xs font-medium bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-full"><i data-lucide="fingerprint" class="w-3 h-3"></i> ${escM(m.numero_chip)}</span>` : ''}
                ${m.esterilizado == 1 ? `<span class="inline-flex items-center gap-1 text-xs font-medium bg-teal-50 text-teal-700 px-2.5 py-1 rounded-full"><i data-lucide="scissors" class="w-3 h-3"></i> Esterilizado</span>` : ''}
            </div>` : ''}
            ${m.alergias ? `<div class="flex gap-2.5 bg-red-50 border border-red-100 rounded-lg p-3"><div class="w-5 h-5 rounded-full bg-red-100 flex items-center justify-center shrink-0 mt-0.5"><i data-lucide="alert-triangle" class="w-3 h-3 text-red-500"></i></div><div><p class="text-xs font-semibold text-red-700 mb-0.5">Alergias</p><p class="text-xs text-red-600">${escM(m.alergias)}</p></div></div>` : ''}
            ${m.observaciones ? `<div class="flex gap-2.5 bg-amber-50 border border-amber-100 rounded-lg p-3"><div class="w-5 h-5 rounded-full bg-amber-100 flex items-center justify-center shrink-0 mt-0.5"><i data-lucide="stethoscope" class="w-3 h-3 text-amber-500"></i></div><div><p class="text-xs font-semibold text-amber-700 mb-0.5">Observaciones</p><p class="text-xs text-amber-600">${escM(m.observaciones)}</p></div></div>` : ''}
            <div id="vacunas-card-${m.id}" class="space-y-1.5"></div>
            <div id="enfermedades-card-${m.id}" class="space-y-1.5"></div>
        </div>
        <div class="flex border-t border-gray-100">
            <a href="/mascota/vista-estado-mascota.php?token=${m.token_publico || m.id}" target="_blank" class="flex-1 flex items-center justify-center gap-1.5 text-xs font-semibold text-emerald-600 hover:bg-emerald-50 py-3 transition border-r border-gray-100"><i data-lucide="external-link" class="w-3.5 h-3.5"></i> Perfil</a>
            <button data-id="${m.id}" data-action="editar-mascota" class="flex-1 flex items-center justify-center gap-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-50 py-3 transition border-r border-gray-100"><i data-lucide="pencil" class="w-3.5 h-3.5"></i> Editar</button>
            <button data-id="${m.id}" data-action="agregar-enfermedad" class="flex-1 flex items-center justify-center gap-1.5 text-xs font-semibold text-purple-600 hover:bg-purple-50 py-3 transition border-r border-gray-100"><i data-lucide="heart-pulse" class="w-3.5 h-3.5"></i> Enfermedad</button>
            <button data-id="${m.id}" data-nombre="${escM(m.nombre)}" data-action="eliminar-mascota" class="flex-1 flex items-center justify-center gap-1.5 text-xs font-semibold text-red-500 hover:bg-red-50 py-3 transition"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Eliminar</button>
        </div>`;

    return card;
}

// ── Cargar vacunas en cada card ─────────────────────────────────────────────────
async function cargarVacunasCard(mascotaId) {
    const contenedor = document.getElementById(`vacunas-card-${mascotaId}`);
    if (!contenedor) return;

    try {
        const res  = await fetch(`${BASE_MASCOTA}/listar_vacunas.php?mascota_id=${mascotaId}`);
        const data = await res.json();

        if (!data.success || data.vacunas.length === 0) {
            contenedor.innerHTML = `<div class="flex items-center gap-1.5 text-xs text-gray-400 bg-gray-50 rounded-lg px-3 py-2"><i data-lucide="syringe" class="w-3.5 h-3.5"></i> Sin vacunas registradas</div>`;
            lucide.createIcons();
            return;
        }

        contenedor.innerHTML = `
            <div class="bg-gradient-to-r from-green-50 to-white border border-green-100 rounded-xl p-3">
                <div class="flex items-center gap-1.5 text-xs font-semibold text-green-700 mb-2">
                    <i data-lucide="syringe" class="w-3.5 h-3.5"></i> Vacunas (${data.vacunas.length})
                </div>
                <div class="space-y-1.5">
                ${data.vacunas.map(v => `
                    <div class="flex items-center justify-between bg-white rounded-lg px-3 py-2 text-xs border border-green-100">
                        <div class="flex items-center gap-2 min-w-0">
                            <i data-lucide="shield-check" class="w-3 h-3 shrink-0 text-green-500"></i>
                            <span class="font-medium text-gray-800 truncate">${escM(v.nombre)}</span>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0 ml-2">
                            <span class="text-green-700 font-medium whitespace-nowrap">${formatFecha(v.fecha_aplicacion)}</span>
                            ${v.fecha_proxima ? `<span class="text-gray-300">→</span><span class="text-gray-500 whitespace-nowrap">${formatFecha(v.fecha_proxima)}</span>` : ''}
                        </div>
                    </div>
                `).join('')}
                </div>
            </div>`;
        lucide.createIcons();

    } catch {
        contenedor.innerHTML = `<div class="flex items-center gap-1.5 text-xs text-gray-400"><i data-lucide="alert-circle" class="w-3 h-3"></i> Error al cargar vacunas</div>`;
        lucide.createIcons();
    }
}

// ── Cargar enfermedades en cada card ────────────────────────────────────────────
async function cargarEnfermedadesCard(mascotaId) {
    const contenedor = document.getElementById(`enfermedades-card-${mascotaId}`);
    if (!contenedor) return;

    try {
        const res  = await fetch(`${BASE_MASCOTA}/listar_enfermedades.php?mascota_id=${mascotaId}`);
        const data = await res.json();

        if (!data.success || data.enfermedades.length === 0) {
            contenedor.innerHTML = '';
            return;
        }

        contenedor.innerHTML = `
            <div class="bg-gradient-to-r from-purple-50 to-white border border-purple-100 rounded-xl p-3">
                <div class="flex items-center gap-1.5 text-xs font-semibold text-purple-700 mb-2">
                    <i data-lucide="heart-pulse" class="w-3.5 h-3.5 text-purple-600"></i> Enfermedades (${data.enfermedades.length})
                </div>
                <div class="space-y-1.5">
                ${data.enfermedades.map(e => {
                    const prox = parseDateLocal(e.ultimo_control);
                    if (prox) prox.setDate(prox.getDate() + parseInt(e.recurrencia_dias));
                    const ahora = new Date();
                    const vencido = prox && prox <= ahora;
                    return `
                    <div class="flex items-center justify-between bg-white rounded-lg px-3 py-2 text-xs border border-purple-100 hover:border-purple-200 transition-colors">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-1.5 h-1.5 rounded-full ${vencido ? 'bg-red-500' : 'bg-purple-400'} shrink-0"></span>
                            <span class="font-semibold text-gray-800 truncate">${escM(e.enfermedad)}</span>
                            <span class="text-gray-400 shrink-0">· cada ${e.recurrencia_dias} días</span>
                            ${vencido ? `<span class="text-[10px] font-bold text-white bg-red-500 px-1.5 py-0.5 rounded shrink-0">VENCIDO</span>` : ''}
                        </div>
                        <div class="flex items-center gap-1 shrink-0 ml-2">
                            ${prox ? `<span class="text-gray-500 font-medium whitespace-nowrap">${prox.toLocaleDateString('es-CL', { day:'2-digit', month:'short' })}</span>` : ''}
                            <button data-eid="${e.id}" data-mid="${mascotaId}" data-enf="${escM(e.enfermedad)}" data-rec="${e.recurrencia_dias}" data-uc="${e.ultimo_control || ''}" data-action="editar-enfermedad" class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors" title="Editar enfermedad">
                                <i data-lucide="pencil" class="w-3 h-3"></i>
                            </button>
                            <button data-eid="${e.id}" data-mid="${mascotaId}" data-enf="${escM(e.enfermedad)}" data-action="contactar-enfermedad" class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700 transition-colors" title="Contactar para agendar cita">
                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                            </button>
                            <button data-eid="${e.id}" data-mid="${mascotaId}" data-action="eliminar-enfermedad" class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600 transition-colors" title="Eliminar enfermedad">
                                <i data-lucide="x" class="w-3 h-3"></i>
                            </button>
                        </div>
                    </div>`;
                }).join('')}
                </div>
            </div>`;
        lucide.createIcons();
    } catch {
        contenedor.innerHTML = '';
    }
}

function parseDateLocal(str) {
    if (!str) return null;
    const [y, m, d] = str.split(' ')[0].split('-').map(Number);
    return new Date(y, m - 1, d);
}

function formatFecha(fecha) {
    if (!fecha) return '';
    const d = parseDateLocal(fecha);
    if (!d) return '';
    return d.toLocaleDateString('es-CL', { day: '2-digit', month: 'short', year: 'numeric' });
}

// ── Form nueva mascota ─────────────────────────────────────────────────────────
function resetEspecieSelector() {
    document.querySelectorAll('#especie-selector input[type="radio"]').forEach(r => r.checked = false);
    actualizarAvatarPreview(null);
    const w = document.getElementById('especie-otro-wrapper');
    if (w) w.classList.add('hidden');
    const o = document.getElementById('m_especie_otro');
    if (o) o.value = '';
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

// Mostrar campo "Especificar especie" cuando se elige "Otro"
document.getElementById('especie-selector').addEventListener('change', (e) => {
    if (e.target.type === 'radio') actualizarAvatarPreview(e.target.value);
    const wrapper = document.getElementById('especie-otro-wrapper');
    if (e.target.type === 'radio' && e.target.value === 'Otro') {
        wrapper.classList.remove('hidden');
        setTimeout(() => document.getElementById('m_especie_otro').focus(), 50);
    } else if (e.target.type === 'radio') {
        wrapper.classList.add('hidden');
        document.getElementById('m_especie_otro').value = '';
    }
});

function abrirFormNuevaMascota() {
    document.getElementById('form-mascota').reset();
    document.getElementById('mascota_id').value         = '';
    document.getElementById('mascota_cliente_id').value = window.MascotaState.clienteId;
    document.getElementById('modal-form-mascota-titulo').textContent = 'Nueva Mascota';
    document.getElementById('btn-submit-mascota').textContent        = 'Guardar Mascota';
    document.getElementById('mascota-error').classList.add('hidden');
    resetEspecieSelector();
    resetVacunas();
    document.getElementById('modal-form-mascota').classList.remove('hidden');
    lucide.createIcons();
}

async function abrirFormEditarMascota(id) {
    try {
        const res  = await fetch(`${BASE_MASCOTA}/listar_mascotas.php?cliente_id=${window.MascotaState.clienteId}`);
        const data = await res.json();
        const m    = data.mascotas.find(x => x.id == id);
        if (!m) return;

        document.getElementById('mascota_id').value         = m.id;
        document.getElementById('mascota_cliente_id').value = window.MascotaState.clienteId;
        document.getElementById('m_nombre').value           = m.nombre      ?? '';
        document.getElementById('m_raza').value             = m.raza        ?? '';
        document.getElementById('m_fecha_nac').value        = m.fecha_nacimiento ?? '';
        document.getElementById('m_sexo').value             = m.sexo        ?? '';
        document.getElementById('m_color').value            = m.color       ?? '';
        document.getElementById('m_observaciones').value    = m.observaciones ?? '';
        if (document.getElementById('m_peso')) document.getElementById('m_peso').value = m.peso ?? '';
        if (document.getElementById('m_chip')) document.getElementById('m_chip').value = m.numero_chip ?? '';
        if (document.getElementById('m_esterilizado')) document.getElementById('m_esterilizado').checked = m.esterilizado == 1;
        if (document.getElementById('m_ultima_revision')) document.getElementById('m_ultima_revision').value = m.ultima_revision ?? '';
        if (document.getElementById('m_alergias')) document.getElementById('m_alergias').value = m.alergias ?? '';
        if (document.getElementById('m_notas_internas')) document.getElementById('m_notas_internas').value = m.notas_internas ?? '';

        // Marcar especie en el selector
        const matched = false;
        document.querySelectorAll('#especie-selector input[type="radio"]').forEach(r => {
            r.checked = (r.value === m.especie);
        });
        actualizarAvatarPreview(m.especie);

        // Si especie=Otro, mostrar campo custom y pre-llenar
        const wrapper = document.getElementById('especie-otro-wrapper');
        if (m.especie === 'Otro') {
            wrapper.classList.remove('hidden');
            document.getElementById('m_especie_otro').value = m.raza ?? '';
        } else {
            wrapper.classList.add('hidden');
            document.getElementById('m_especie_otro').value = '';
        }

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

// ── Submit form ────────────────────────────────────────────────────────────────
document.getElementById('form-mascota').addEventListener('submit', async (e) => {
    e.preventDefault();
    const errorDiv  = document.getElementById('mascota-error');
    const btnSubmit = document.getElementById('btn-submit-mascota');
    const esEdicion = !!document.getElementById('mascota_id').value;

    errorDiv.classList.add('hidden');
    btnSubmit.disabled = true;
    btnSubmit.textContent = 'Guardando...';

    // Si especie=Otro, reemplazar el valor del radio por la especie custom antes de enviar
    const especieRadio = document.querySelector('#especie-selector input[type="radio"]:checked');
    if (especieRadio && especieRadio.value === 'Otro') {
        const otro = document.getElementById('m_especie_otro').value.trim();
        if (otro) especieRadio.value = otro;
    }

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
        btnSubmit.disabled = false;
        btnSubmit.textContent = esEdicion ? 'Guardar Cambios' : 'Guardar Mascota';
    }
});

// ── Eliminar ───────────────────────────────────────────────────────────────────
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
    btn.disabled = true;
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
        btn.disabled = false;
        btn.textContent = 'Sí, eliminar';
    }
});

// ── Cerrar modales ─────────────────────────────────────────────────────────────
document.getElementById('btn-cerrar-modal-mascotas').addEventListener('click', () => {
    document.getElementById('modal-mascotas').classList.add('hidden');
});
['btn-cerrar-form-mascota', 'btn-cancelar-form-mascota'].forEach(id => {
    document.getElementById(id).addEventListener('click', () => {
        document.getElementById('modal-form-mascota').classList.add('hidden');
    });
});
document.getElementById('btn-agregar-mascota').addEventListener('click', abrirFormNuevaMascota);

if (document.getElementById('btn-agregar-vacuna')) {
    document.getElementById('btn-agregar-vacuna').addEventListener('click', () => {
        _vacunas.push({ id: null, nombre: '', fecha_aplicacion: '', fecha_proxima: '', veterinario: '', lote: '', notas: '' });
        renderVacunas();
        lucide.createIcons();
    });
}

// ── Delegación clicks en cards ─────────────────────────────────────────────────
document.getElementById('contenedor-mascotas').addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action]');
    if (!btn) return;
    const { id, action, nombre, mid, eid, enf } = btn.dataset;

    if (action === 'editar-mascota')        return abrirFormEditarMascota(id);
    if (action === 'eliminar-mascota')      return abrirModalEliminarMascota(id, nombre);

    // Agregar enfermedad
    if (action === 'agregar-enfermedad')    return abrirModalAgregarEnfermedad(id || mid);

    // Editar enfermedad
    if (action === 'editar-enfermedad')     return abrirModalEditarEnfermedad(eid, mid, enf, btn.dataset.rec, btn.dataset.uc);

    // Contactar para enfermedad
    if (action === 'contactar-enfermedad')  return contactarEnfermedad(mid, enf);

    // Eliminar enfermedad
    if (action === 'eliminar-enfermedad')   return eliminarEnfermedad(eid, mid);
});

// ── Enfermedades ──────────────────────────────────────────────────────────────
async function abrirModalAgregarEnfermedad(mascotaId) {
    const { value: formValues, isConfirmed } = await Swal.fire({
        title: 'Agregar Enfermedad',
        html: `
            <div class="text-left space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Enfermedad / condición</label>
                    <input id="swal-enfermedad" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Ej: Dermatitis atópica">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cada cuántos días debe controlarse</label>
                    <input id="swal-recurrencia" type="number" min="1" value="60" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Último control (opcional)</label>
                    <input id="swal-ultimo-control" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
        `,
        focusConfirm: false,
        confirmButtonText: 'Guardar',
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        customClass: { confirmButton: 'swal2-confirm swal2-styled', cancelButton: 'swal2-cancel swal2-styled' },
        preConfirm: () => {
            const enfermedad = document.getElementById('swal-enfermedad').value.trim();
            if (!enfermedad) { Swal.showValidationMessage('Ingrese el nombre de la enfermedad'); return false; }
            return {
                enfermedad: enfermedad,
                recurrencia_dias: parseInt(document.getElementById('swal-recurrencia').value) || 60,
                ultimo_control: document.getElementById('swal-ultimo-control').value || ''
            };
        }
    });

    if (!isConfirmed || !formValues) return;

    try {
        const fd = new FormData();
        fd.append('mascota_id', mascotaId);
        fd.append('enfermedad', formValues.enfermedad);
        fd.append('recurrencia_dias', formValues.recurrencia_dias);
        fd.append('ultimo_control', formValues.ultimo_control);

        const res = await fetch(`${BASE_MASCOTA}/guardar_enfermedad.php`, { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Enfermedad registrada', timer: 1500, showConfirmButton: false });
            cargarEnfermedadesCard(mascotaId);
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudo guardar.' });
        }
    } catch {
        Swal.fire({ icon: 'error', title: 'Error de conexión' });
    }
}

async function abrirModalEditarEnfermedad(enfermedadId, mascotaId, enfermedad, recurrencia, ultimoControl) {
    const { value: formValues, isConfirmed } = await Swal.fire({
        title: 'Editar Enfermedad',
        html: `
            <div class="text-left space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Enfermedad / condición</label>
                    <input id="swal-enf-edit" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" value="${escM(enfermedad)}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cada cuántos días debe controlarse</label>
                    <input id="swal-rec-edit" type="number" min="1" value="${recurrencia}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Último control (opcional)</label>
                    <input id="swal-uc-edit" type="date" value="${ultimoControl || ''}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
        `,
        focusConfirm: false,
        confirmButtonText: 'Guardar',
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const enf = document.getElementById('swal-enf-edit').value.trim();
            if (!enf) { Swal.showValidationMessage('Ingrese el nombre de la enfermedad'); return false; }
            return {
                enfermedad: enf,
                recurrencia_dias: parseInt(document.getElementById('swal-rec-edit').value) || 60,
                ultimo_control: document.getElementById('swal-uc-edit').value || ''
            };
        }
    });

    if (!isConfirmed || !formValues) return;

    try {
        const fd = new FormData();
        fd.append('id', enfermedadId);
        fd.append('mascota_id', mascotaId);
        fd.append('enfermedad', formValues.enfermedad);
        fd.append('recurrencia_dias', formValues.recurrencia_dias);
        fd.append('ultimo_control', formValues.ultimo_control);

        const res = await fetch(`${BASE_MASCOTA}/guardar_enfermedad.php`, { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Enfermedad actualizada', timer: 1500, showConfirmButton: false });
            cargarEnfermedadesCard(mascotaId);
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudo guardar.' });
        }
    } catch {
        Swal.fire({ icon: 'error', title: 'Error de conexión' });
    }
}

async function contactarEnfermedad(mascotaId, enfermedad) {
    // Obtener telefono del cliente desde las mascotas cargadas
    try {
        const res = await fetch(`${BASE_MASCOTA}/listar_mascotas.php?cliente_id=${window.MascotaState.clienteId}`);
        const data = await res.json();
        if (!data.success) throw new Error();
        const m = data.mascotas.find(x => x.id == mascotaId);
        if (!m || !m.cliente_telefono) {
            Swal.fire({ icon: 'warning', title: 'El cliente no tiene teléfono registrado' });
            return;
        }
        const telefonoRaw = (m.cliente_telefono || '').replace(/\D/g, '');
        const telefono = telefonoRaw.startsWith('56') ? telefonoRaw : '56' + telefonoRaw;
        if (!telefono || telefono.length < 8) {
            Swal.fire({ icon: 'warning', title: 'Número de teléfono inválido' });
            return;
        }
        const msg = `Hola, soy de VetWeb. Te escribo para agendar un control de seguimiento de ${enfermedad} para ${m.nombre}. ¿Cómo te acomoda agendar la cita?`;
        window.open(`https://wa.me/${telefono}?text=${encodeURIComponent(msg)}`, '_blank');
    } catch {
        Swal.fire({ icon: 'error', title: 'Error al obtener datos' });
    }
}

async function eliminarEnfermedad(enfermedadId, mascotaId) {
    const result = await Swal.fire({
        title: '¿Eliminar enfermedad?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    });
    if (!result.isConfirmed) return;

    try {
        const fd = new FormData();
        fd.append('id', enfermedadId);
        const res = await fetch(`${BASE_MASCOTA}/eliminar_enfermedad.php`, { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            cargarEnfermedadesCard(mascotaId);
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message });
        }
    } catch {
        Swal.fire({ icon: 'error', title: 'Error de conexión' });
    }
}
