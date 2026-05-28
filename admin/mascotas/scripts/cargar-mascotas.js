let mascotasData = [];

document.addEventListener('DOMContentLoaded', () => {
    cargarMascotas();

    let timeout;
    document.getElementById('buscador-mascotas')?.addEventListener('input', () => {
        clearTimeout(timeout);
        timeout = setTimeout(cargarMascotas, 300);
    });

    document.getElementById('btn-cerrar-detalle')?.addEventListener('click', cerrarDetalle);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !document.getElementById('modal-detalle-mascota').classList.contains('hidden')) {
            cerrarDetalle();
        }
    });
});

function escHtml(str) {
    if (!str) return '';
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

function calcularEdad(fn) {
    if (!fn) return '?';
    try {
        const nac = new Date(fn + 'T00:00:00');
        const hoy = new Date();
        const dif = hoy - nac;
        const años = Math.floor(dif / (365.25 * 86400000));
        if (años > 0) return años + ' año(s)';
        const meses = Math.floor(dif / (30.44 * 86400000));
        if (meses > 0) return meses + ' mes(es)';
        const días = Math.floor(dif / 86400000);
        return días + ' día(s)';
    } catch { return '?'; }
}

async function cargarMascotas() {
    const tbody = document.getElementById('lista-mascotas');
    const search = document.getElementById('buscador-mascotas')?.value || '';
    const params = search ? '?search=' + encodeURIComponent(search) : '';

    try {
        const res = await fetch(`api/listar_mascotas.php${params}`);
        const data = await res.json();

        if (!data.success) {
            tbody.innerHTML = `<tr><td colspan="7" class="px-5 py-12 text-center text-red-400"><i class="fa-solid fa-triangle-exclamation text-xl"></i><p class="mt-2">Error al cargar mascotas.</p></td></tr>`;
            return;
        }

        mascotasData = data.mascotas || [];

        if (mascotasData.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="px-5 py-12 text-center text-gray-400"><i class="fa-solid fa-paw text-3xl mb-2"></i><p>No hay mascotas registradas.</p></td></tr>`;
            actualizarKPIs([]);
            return;
        }

        actualizarKPIs(mascotasData);

        tbody.innerHTML = mascotasData.map(m => {
            const edad = calcularEdad(m.fecha_nacimiento);
            const especieIcon = m.especie?.toLowerCase() === 'perro' ? 'fa-dog' : m.especie?.toLowerCase() === 'gato' ? 'fa-cat' : 'fa-paw';
            return `<tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                <td class="px-5 py-4 align-top">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-brand-50 flex items-center justify-center text-brand-500">
                            <i class="fa-solid ${especieIcon}"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-800">${escHtml(m.nombre)}</div>
                            <div class="text-xs text-gray-400">${edad}${m.sexo ? ' · ' + escHtml(m.sexo) : ''}</div>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-4 align-top">
                    <div class="font-medium text-gray-800">${escHtml(m.dueno || '—')}</div>
                    <div class="text-xs text-gray-400">${escHtml(m.dueno_telefono || '')}</div>
                </td>
                <td class="px-5 py-4 align-top text-gray-600">
                    <span class="inline-block bg-brand-50 text-brand-700 text-xs font-medium px-2.5 py-1 rounded-full">
                        ${escHtml(m.especie || '—')}
                    </span>
                    ${m.raza ? '<span class="text-xs text-gray-400 ml-1.5">· ' + escHtml(m.raza) + '</span>' : ''}
                </td>
                <td class="px-5 py-4 align-top text-gray-600 text-sm">${edad}</td>
                <td class="px-5 py-4 align-top text-center">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full ${(m.total_citas || 0) > 0 ? 'bg-blue-50 text-blue-600' : 'bg-gray-50 text-gray-400'} text-xs font-semibold">${m.total_citas || 0}</span>
                </td>
                <td class="px-5 py-4 align-top text-center">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full ${(m.total_cotizaciones || 0) > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-50 text-gray-400'} text-xs font-semibold">${m.total_cotizaciones || 0}</span>
                </td>
                <td class="px-5 py-4 align-top text-center">
                    <div class="flex items-center justify-center gap-1.5">
                        <button onclick="verDetalle(${m.id})"
                            class="inline-flex items-center gap-1 bg-brand-600 hover:bg-brand-700 text-white text-xs font-medium px-3 py-2 rounded-lg transition-colors shadow-sm">
                            <i class="fa-solid fa-eye"></i>
                            Detalle
                        </button>
                        <a href="/mascota/vista-estado-mascota.php?token=${m.token_publico || m.id}" target="_blank"
                            class="inline-flex items-center gap-1 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 text-xs font-medium px-3 py-2 rounded-lg transition-colors">
                            <i class="fa-solid fa-external-link-alt"></i>
                            Perfil
                        </a>
                    </div>
                </td>
            </tr>`;
        }).join('');

    } catch {
        tbody.innerHTML = `<tr><td colspan="7" class="px-5 py-12 text-center text-red-400"><i class="fa-solid fa-triangle-exclamation text-xl"></i><p class="mt-2">Error de conexión.</p></td></tr>`;
    }
}

function actualizarKPIs(mascotas) {
    document.getElementById('kpi-total').textContent = mascotas.length;
    const duenos = new Set(mascotas.filter(m => m.dueno).map(m => m.dueno));
    document.getElementById('kpi-duenos').textContent = duenos.size;
    const especies = new Set(mascotas.filter(m => m.especie).map(m => m.especie.toLowerCase()));
    document.getElementById('kpi-especies').textContent = especies.size;
    const totalCitas = mascotas.reduce((sum, m) => sum + (parseInt(m.total_citas) || 0), 0);
    document.getElementById('kpi-citas').textContent = totalCitas;
}

// ── Modal Detalle ─────────────────────────────────────────────────────

async function verDetalle(id) {
    try {
        const res = await fetch(`api/detalle_mascota.php?id=${id}`);
        const data = await res.json();
        if (!data.success) {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#0284c7' });
            return;
        }
        const m = data.mascota;
        document.getElementById('detalle-titulo').innerHTML = `<i class="fa-solid fa-paw text-brand-600 mr-2"></i>${escHtml(m.nombre)} — Detalle completo`;

        document.getElementById('detalle-dueno').textContent = m.dueno || '—';
        document.getElementById('detalle-telefono').textContent = m.dueno_telefono || '—';
        document.getElementById('detalle-email').textContent = m.dueno_email || '—';
        document.getElementById('detalle-especie').textContent = m.especie || '—';
        document.getElementById('detalle-raza').textContent = m.raza || '—';
        document.getElementById('detalle-sexo').textContent = m.sexo || '—';
        document.getElementById('detalle-color').textContent = m.color || '—';
        document.getElementById('detalle-peso').textContent = m.peso ? m.peso + ' kg' : '—';
        document.getElementById('detalle-chip').textContent = m.numero_chip || '—';

        // Citas
        const detCitas = document.getElementById('detalle-citas');
        if (data.citas && data.citas.length > 0) {
            detCitas.innerHTML = data.citas.map(c => {
                const cls = { pendiente: 'bg-yellow-100 text-yellow-700', confirmada: 'bg-blue-100 text-blue-700', completada: 'bg-green-100 text-green-700', cancelada: 'bg-red-100 text-red-600' }[c.estado] || 'bg-gray-100 text-gray-600';
                const fecha = (c.fecha || '').substring(0, 16).replace('T', ' ');
                return `<div class="flex items-center justify-between gap-2 p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <i class="fa-regular fa-calendar text-gray-400"></i>
                        <div>
                            <p class="text-xs text-gray-400">${fecha}</p>
                            <p class="text-sm font-medium text-gray-800">${escHtml(c.tipo)}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full ${cls}">${c.estado}</span>
                        ${c.token_publico ? `<a href="/citas/seguimiento_cita.php?token=${c.token_publico}" target="_blank" class="p-1.5 text-brand-500 hover:bg-brand-100 rounded-lg transition-colors" title="Ver seguimiento"><i class="fa-solid fa-up-right-from-square"></i></a>` : ''}
                    </div>
                </div>`;
            }).join('');
        } else {
            detCitas.innerHTML = '<p class="text-sm text-gray-400 italic py-3"><i class="fa-regular fa-calendar mr-1"></i> Sin citas registradas.</p>';
        }

        // Cotizaciones
        const detCots = document.getElementById('detalle-cotizaciones');
        if (data.cotizaciones && data.cotizaciones.length > 0) {
            detCots.innerHTML = data.cotizaciones.map(co => {
                const cls = { pendiente: 'bg-yellow-100 text-yellow-700', aprobada: 'bg-green-100 text-green-700', rechazada: 'bg-red-100 text-red-600', vencida: 'bg-gray-100 text-gray-500' }[co.estado] || 'bg-gray-100 text-gray-600';
                return `<div class="flex items-center justify-between gap-2 p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-file-invoice text-gray-400"></i>
                        <div>
                            <p class="text-xs text-gray-400">${(co.created_at || '').substring(0, 10)}</p>
                            <p class="text-sm font-medium text-gray-800">${escHtml(co.numero_cotizacion)} · ${escHtml(co.servicio)}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="/admin/cotizaciones/editar_cotizacion.php?id=${co.id}" target="_blank" class="p-1.5 text-orange-500 hover:bg-orange-100 rounded-lg transition-colors" title="Editar cotización"><i class="fa-solid fa-pen-to-square"></i></a>
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full ${cls}">${co.estado}</span>
                    </div>
                </div>`;
            }).join('');
        } else {
            detCots.innerHTML = '<p class="text-sm text-gray-400 italic py-3"><i class="fa-solid fa-file-invoice mr-1"></i> Sin cotizaciones.</p>';
        }

        document.getElementById('modal-detalle-mascota').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

    } catch {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión.', confirmButtonColor: '#0284c7' });
    }
}

function cerrarDetalle() {
    document.getElementById('modal-detalle-mascota').classList.add('hidden');
    document.body.style.overflow = '';
}
