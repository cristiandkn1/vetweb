// admin/citas/scripts/listar-citas.js

document.addEventListener('DOMContentLoaded', () => {

    const BASE       = '/admin/citas/api';
    const contenedor = document.getElementById('contenedor-citas');

    let filtroEstado = 'todos';

    // ── Obtener parámetros de filtro ──────────────────────────────────────────
    function obtenerParams() {
        const params = new URLSearchParams();
        const search = document.getElementById('buscador-clientes')?.value?.trim() || '';
        const desde  = document.getElementById('filtro-desde')?.value || '';
        const hasta  = document.getElementById('filtro-hasta')?.value || '';

        if (search)  params.set('search', search);
        if (desde)   params.set('desde',  desde);
        if (hasta)   params.set('hasta',  hasta);
        if (filtroEstado !== 'todos') params.set('estado', filtroEstado);

        return params;
    }

    // ── Cargar citas + KPIs desde la API ─────────────────────────────────────
    async function cargarCitas() {
        contenedor.innerHTML = `
            <div class="col-span-3 flex justify-center items-center h-32 text-gray-400">
                Cargando citas...
            </div>`;

        const params = obtenerParams();

        try {
            const res  = await fetch(`${BASE}/listar_citas.php?${params}`);
            const data = await res.json();

            if (!data.success) {
                mostrarVacio('Error al cargar las citas.');
                return;
            }

            renderKPI(data.kpi);
            renderizar(data.citas);

        } catch {
            mostrarVacio('Error de conexión.');
        }
    }

    // ── KPIs ──────────────────────────────────────────────────────────────────
    function renderKPI(kpi) {
        if (!kpi) return;
        document.querySelectorAll('[data-kpi]').forEach(el => {
            const key = el.dataset.kpi;
            el.textContent = kpi[key] !== undefined ? kpi[key] : '—';
        });
    }

    // ── Renderizar cards ─────────────────────────────────────────────────────
    function renderizar(citas) {
        if (!citas || citas.length === 0) {
            mostrarVacio('No hay citas para mostrar.');
            return;
        }

        const activas = citas.filter(c => c.estado === 'pendiente' || c.estado === 'confirmada');
        const cerradas = citas.filter(c => c.estado === 'completada' || c.estado === 'cancelada');

        contenedor.innerHTML = '';

        if (activas.length > 0) {
            const grid = document.createElement('div');
            grid.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6';
            activas.forEach(c => grid.appendChild(crearCard(c)));
            contenedor.appendChild(grid);
        }

        if (cerradas.length > 0) {
            const divider = document.createElement('div');
            divider.className = 'col-span-full my-6 flex items-center gap-3 text-gray-400 text-xs uppercase tracking-wider';
            divider.innerHTML = '<div class="flex-1 h-px bg-gray-200"></div><span>Completadas / Canceladas</span><div class="flex-1 h-px bg-gray-200"></div>';
            contenedor.appendChild(divider);

            const grid = document.createElement('div');
            grid.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3';
            cerradas.forEach(c => grid.appendChild(crearCard(c)));
            contenedor.appendChild(grid);
        }
    }

    function mostrarVacio(msg) {
        contenedor.innerHTML = `
            <div class="border-2 border-dashed border-gray-300 rounded-lg h-48
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

        const esCerrada = c.estado === 'completada' || c.estado === 'cancelada';
        const card = document.createElement('div');
        card.className = `bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col gap-3 ${esCerrada ? 'cita-cerrada hover:shadow-sm' : 'hover:shadow-md'} transition-shadow`;
        card.dataset.id = c.id;
        card.dataset.cotizacionTotal = Math.round(parseFloat(c.cotizacion_total || 0));
        card.dataset.waTelefono = c.cliente_telefono || '';
        card.dataset.waCliente  = c.cliente_nombre;
        card.dataset.waMascota  = c.mascota_nombre;
        card.dataset.waEspecie  = c.mascota_especie || '';
        card.dataset.waTipo     = c.tipo;
        card.dataset.waToken    = c.token_publico;
        card.dataset.waMid      = c.mascota_id;
        card.dataset.waMToken   = c.mascota_token || '';
        card.dataset.waNumCot   = c.numero_cotizacion || '';
        card.dataset.waCotEstado = c.cotizacion_estado || '';
        card.dataset.waDetalles = JSON.stringify(c.cotizacion_detalles || []);
        card.innerHTML = `
            <!-- Header: fecha + estado (siempre visible) -->
            <div class="flex items-start justify-between gap-2${esCerrada ? ' cursor-pointer select-none' : ''}" ${esCerrada ? 'onclick="this.closest(\'.cita-cerrada\').classList.toggle(\'expanded\')"' : ''}>
                <div class="flex items-center gap-2 min-w-0">
                    ${esCerrada ? `<svg class="w-4 h-4 text-gray-400 transition-transform collapse-chevron shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>` : ''}
                    <div class="min-w-0">
                        <p class="text-xs text-gray-400 uppercase tracking-wide">${fechaFmt}</p>
                        <p class="text-lg font-bold text-gray-800 ${esCerrada ? 'text-base' : ''}">${horaFmt}</p>
                    </div>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium shrink-0 ${estado.cls}">
                    ${estado.label}
                </span>
            </div>

            ${esCerrada ? `
            <!-- Resumen compacto -->
            <div class="cita-summary items-center gap-1.5 text-gray-500 truncate">
                <span class="font-medium text-gray-700">${escHtml(c.cliente_nombre)}</span>
                <span>·</span>
                <span>${escHtml(c.mascota_nombre)}</span>
                <span>·</span>
                <span class="text-gray-400">${escHtml(c.tipo)}</span>
            </div>` : ''}

            <!-- Cuerpo colapsable -->
            <div class="flex flex-col gap-3 ${esCerrada ? 'collapsible-body' : ''}">
                <!-- Servicio y Precio -->
                <div class="flex items-start justify-between gap-4 text-sm text-gray-700">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span class="font-medium">${escHtml(c.tipo)}</span>
                    </div>
                    ${c.precio_estimado_min && c.precio_estimado_min > 0 ? `
                    <div class="text-[11px] font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-100 whitespace-nowrap" title="El costo final puede variar por insumos o peso del paciente.">
                        Aprox. $${parseInt(c.precio_estimado_min).toLocaleString('es-CL')} ${c.precio_estimado_max > c.precio_estimado_min ? ' - $' + parseInt(c.precio_estimado_max).toLocaleString('es-CL') : ''}
                    </div>` : ''}
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
                <div class="flex flex-col gap-1.5 pt-2 mt-auto border-t border-gray-50">
                    <div class="flex items-center gap-1.5">
                        <select data-id="${c.id}" data-action="cambiar-estado"
                            class="flex-1 text-xs border border-gray-200 rounded px-2 py-1.5 bg-white text-gray-600 focus:outline-none focus:border-brand-400">
                            <option value="pendiente"  ${c.estado === 'pendiente'  ? 'selected' : ''}>Pendiente</option>
                            <option value="confirmada" ${c.estado === 'confirmada' ? 'selected' : ''}>Confirmada</option>
                            <option value="completada" ${c.estado === 'completada' ? 'selected' : ''}>Completada</option>
                            <option value="cancelada"  ${c.estado === 'cancelada'  ? 'selected' : ''}>Cancelada</option>
                        </select>

                        <a href="/citas/seguimiento_cita.php?token=${c.token_publico}" target="_blank" title="Página compartible"
                            class="p-1.5 text-brand-600 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 rounded transition-colors shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>

                        ${c.cliente_telefono ? `
                        <button data-action="whatsapp" title="Enviar por WhatsApp"
                            class="p-1.5 text-green-600 hover:text-green-800 bg-green-50 hover:bg-green-100 rounded transition-colors shrink-0">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </button>
                        ` : ''}
                    </div>

                    <div class="flex items-center gap-1.5 flex-wrap">
                        ${c.cotizacion_id ? `
                        <a href="/admin/cotizaciones/ver_cotizacion.php?id=${c.cotizacion_id}" target="_blank" title="Ver cotización"
                            class="px-2 py-1.5 text-[11px] font-medium text-emerald-600 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 rounded transition-colors flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            ${escHtml(c.numero_cotizacion || 'Cotización')}
                        </a>
                        ` : ''}

                        ${!esCerrada ? `
                        <button data-id="${c.id}" data-action="finalizar"
                            class="px-2 py-1.5 text-[11px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded transition-colors shadow-sm flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Finalizar
                        </button>
                        ` : ''}

                        <button data-id="${c.id}" data-action="editar-cita"
                            class="px-2 py-1.5 text-[11px] font-medium text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 rounded transition-colors flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                            Editar
                        </button>

                        <button data-id="${c.id}" data-recom="${escHtml(c.recomendaciones || '')}" data-action="notas"
                            class="px-2 py-1.5 text-[11px] font-medium text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 rounded transition-colors flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" /></svg>
                            Notas
                        </button>

                        <button data-id="${c.id}" data-action="eliminar"
                            class="px-2 py-1.5 text-[11px] font-medium text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 rounded transition-colors flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                            Eliminar
                        </button>
                    </div>
                </div>
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
                cargarCitas();
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
                cargarCitas();
            } else {
                alert(data.message || 'Error al eliminar.');
            }
        } catch {
            alert('Error de conexión.');
        }
    }

    // ── WhatsApp ───────────────────────────────────────────────────────────────
    function enviarWhatsApp(cardEl) {
        const ds = cardEl.dataset;
        const telefonoRaw = ds.waTelefono.replace(/\D/g, '');
        const telefono = telefonoRaw.startsWith('56') ? telefonoRaw : '56' + telefonoRaw;

        const fechaEl = cardEl.querySelector('.text-xs.text-gray-400.uppercase');
        const horaEl  = cardEl.querySelector('.text-lg.font-bold');
        const fecha   = fechaEl ? fechaEl.textContent.trim() : '';
        const hora    = horaEl  ? horaEl.textContent.trim() : '';

        let msg = `Hola ${ds.waCliente}!\n\n`;
        msg += `Te recordamos tu cita en nuestra clinica veterinaria:\n\n`;
        msg += `*Fecha:* ${fecha} a las ${hora}\n`;
        msg += `*Paciente:* ${ds.waMascota}${ds.waEspecie ? ` (${ds.waEspecie})` : ''}\n`;
        msg += `*Servicio:* ${ds.waTipo}\n\n`;

        msg += `*Seguimiento en vivo:*\n`;
        msg += `${window.location.origin}/citas/seguimiento_cita.php?token=${ds.waToken}\n\n`;

        msg += `*Estado de tu mascota:*\n`;
        msg += `${window.location.origin}/mascota/vista-estado-mascota.php?token=${ds.waMToken}\n`;

        // Cotización
        if (ds.waNumCot) {
            if (ds.waCotEstado === 'pendiente') {
                msg += `\n*Cotizacion ${ds.waNumCot}:* pendiente - el valor se confirmara al finalizar la atencion.\n`;
            } else {
                let detalles;
                try { detalles = JSON.parse(ds.waDetalles); } catch { detalles = []; }
                if (detalles.length > 0) {
                    let total = 0;
                    let lines = detalles.map(d => {
                        const subtotal = parseFloat(d.cantidad) * parseFloat(d.precio_unitario) * (d.afecto_iva ? 1.19 : 1);
                        total += subtotal;
                        const linea = `   ${d.descripcion}: ${d.cantidad} x $${parseFloat(d.precio_unitario).toLocaleString('es-CL')}`;
                        return d.afecto_iva ? `${linea} (+IVA)` : linea;
                    });
                    msg += `\n*Cotizacion ${ds.waNumCot}:*\n${lines.join('\n')}\n`;
                    msg += `   *Total: $${Math.round(total).toLocaleString('es-CL')}*\n`;
                }
            }
        }

        msg += `\nSaludos, VetWeb`;

        const url = `https://wa.me/${telefono}?text=${encodeURIComponent(msg)}`;
        window.open(url, '_blank');
    }

    // ── Delegación de eventos en cards ─────────────────────────────────────────
    contenedor.addEventListener('change', (e) => {
        const sel = e.target.closest('[data-action="cambiar-estado"]');
        if (sel) {
            if (sel.value === 'completada') {
                abrirModalFinalizar(sel.dataset.id);
            } else {
                cambiarEstado(sel.dataset.id, sel.value);
            }
        }
    });

    contenedor.addEventListener('click', (e) => {
        const btnElim = e.target.closest('[data-action="eliminar"]');
        if (btnElim) eliminarCita(btnElim.dataset.id);

        const btnFin = e.target.closest('[data-action="finalizar"]');
        if (btnFin) abrirModalFinalizar(btnFin.dataset.id);

        const btnNotas = e.target.closest('[data-action="notas"]');
        if (btnNotas) abrirModalNotas(btnNotas.dataset.id, btnNotas.dataset.recom);

        const btnEdit = e.target.closest('[data-action="editar-cita"]');
        if (btnEdit) abrirModalEditar(btnEdit.dataset.id);

        const btnWA = e.target.closest('[data-action="whatsapp"]');
        if (btnWA) enviarWhatsApp(btnWA.closest('[data-id]'));
    });

    // ── Modal de Finalizar Cita ────────────────────────────────────
    const modalFin = document.getElementById('modal-finalizar-cita');
    const formFin = document.getElementById('form-finalizar-cita');
    const inputCitaId = document.getElementById('finalizar_cita_id');
    const btnSubmitFin = document.getElementById('btn-submit-finalizar');

    function abrirModalFinalizar(id) {
        if (!modalFin) return;
        formFin.reset();
        inputCitaId.value = id;

        const kpiDiv = document.getElementById('kpi-cotizacion-total');
        const kpiValor = document.getElementById('kpi-cotizacion-total-valor');
        const card = document.querySelector(`[data-id="${id}"]`);
        const total = card ? parseInt(card.dataset.cotizacionTotal) : 0;
        if (kpiDiv && kpiValor && total > 0) {
            kpiValor.textContent = '$' + total.toLocaleString('es-CL');
            kpiDiv.classList.remove('hidden');
        } else if (kpiDiv) {
            kpiDiv.classList.add('hidden');
        }

        modalFin.classList.remove('hidden');
    }

    function cerrarModalFinalizar() {
        if (!modalFin) return;
        modalFin.classList.add('hidden');
    }

    document.getElementById('btn-cerrar-finalizar')?.addEventListener('click', cerrarModalFinalizar);
    document.getElementById('btn-cancelar-finalizar-2')?.addEventListener('click', cerrarModalFinalizar);
    modalFin?.addEventListener('click', (e) => {
        if (e.target === modalFin.querySelector('.fixed.inset-0.bg-gray-900\\/50')) cerrarModalFinalizar();
    });

    // ── Modal de Notas de Cita (Bitácora + Recomendaciones) ──────────
    const modalNotas = document.getElementById('modal-notas-cita');
    const bitacoraLista = document.getElementById('bitacora-lista');
    const inputHora = document.getElementById('bitacora-hora');
    const inputComentario = document.getElementById('bitacora-comentario');
    const btnAgregarBitacora = document.getElementById('btn-agregar-bitacora');
    const inputRecomendaciones = document.getElementById('recomendaciones');
    const btnSubmitNotas = document.getElementById('btn-submit-notas');
    let notasCitaId = null;

    // ── Renderizar una entrada de bitácora ───────────────────────────
    function crearEntradaBitacora(entry) {
        const div = document.createElement('div');
        div.className = 'flex items-start gap-3 p-3 bg-white rounded-lg border border-gray-200 shadow-sm hover:border-indigo-200 transition-colors group';
        div.dataset.id = entry.id;
        div.innerHTML = `
            <div class="w-14 shrink-0 text-center pt-0.5">
                <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md border border-indigo-100">${escHtml(entry.hora)}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">${escHtml(entry.comentario)}</p>
            </div>
            <div class="flex gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                <button type="button" data-action="editar-bitacora" data-id="${entry.id}"
                    class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors" title="Editar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/></svg>
                </button>
                <button type="button" data-action="eliminar-bitacora" data-id="${entry.id}"
                    class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition-colors" title="Eliminar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        `;
        return div;
    }

    // ── Cargar bitácora desde API ────────────────────────────────────
    async function cargarBitacora() {
        if (!bitacoraLista || !notasCitaId) return;
        bitacoraLista.innerHTML = '<div class="text-center text-sm text-gray-400 py-8">Cargando bitácora...</div>';

        try {
            const res = await fetch(`${BASE}/bitacora_listar.php?cita_id=${notasCitaId}`);
            const data = await res.json();

            if (!data.success || !data.entries.length) {
                bitacoraLista.innerHTML = '<div class="text-center text-sm text-gray-400 py-8 border-2 border-dashed border-gray-200 rounded-xl">Aún no hay registros. Agrega el primero arriba.</div>';
                return;
            }

            bitacoraLista.innerHTML = '';
            data.entries.forEach(e => bitacoraLista.appendChild(crearEntradaBitacora(e)));
        } catch {
            bitacoraLista.innerHTML = '<div class="text-center text-sm text-red-400 py-8">Error al cargar bitácora.</div>';
        }
    }

    function abrirModalNotas(id, recomendaciones) {
        if (!modalNotas) return;
        notasCitaId = id;
        if (inputRecomendaciones) inputRecomendaciones.value = recomendaciones || '';
        if (inputHora) {
            const ahora = new Date();
            inputHora.value = String(ahora.getHours()).padStart(2,'0') + ':' + String(ahora.getMinutes()).padStart(2,'0');
        }
        if (inputComentario) inputComentario.value = '';
        modalNotas.classList.remove('hidden');
        cargarBitacora();
    }

    function cerrarModalNotas() {
        if (!modalNotas) return;
        modalNotas.classList.add('hidden');
        notasCitaId = null;
    }

    document.getElementById('btn-cerrar-notas')?.addEventListener('click', cerrarModalNotas);
    document.getElementById('btn-cancelar-notas')?.addEventListener('click', cerrarModalNotas);
    modalNotas?.addEventListener('click', (e) => {
        if (e.target === modalNotas.querySelector('.fixed.inset-0.bg-gray-900\\/50')) cerrarModalNotas();
    });

    // ── Agregar entrada a bitácora ───────────────────────────────────
    btnAgregarBitacora?.addEventListener('click', async () => {
        const hora = inputHora?.value?.trim();
        const comentario = inputComentario?.value?.trim();
        if (!notasCitaId || !hora || !comentario) {
            alert('Completa la hora y el comentario.');
            return;
        }

        btnAgregarBitacora.disabled = true;
        btnAgregarBitacora.textContent = 'Agregando...';

        try {
            const fd = new FormData();
            fd.append('cita_id', notasCitaId);
            fd.append('hora', hora);
            fd.append('comentario', comentario);
            const res = await fetch(`${BASE}/bitacora_agregar.php`, { method: 'POST', body: fd });
            const data = await res.json();

            if (data.success) {
                if (inputComentario) inputComentario.value = '';
                cargarBitacora();
            } else {
                alert(data.message || 'Error al agregar entrada.');
            }
        } catch {
            alert('Error de red.');
        } finally {
            btnAgregarBitacora.disabled = false;
            btnAgregarBitacora.textContent = 'Agregar';
        }
    });

    // ── Delegación: editar / eliminar entradas de bitácora ───────────
    bitacoraLista?.addEventListener('click', async (e) => {
        const btnEditar = e.target.closest('[data-action="editar-bitacora"]');
        if (btnEditar) {
            const entryDiv = btnEditar.closest('[data-id]');
            const id = entryDiv?.dataset.id;
            if (!id) return;

            const horaEl = entryDiv.querySelector('.w-14 span');
            const textoEl = entryDiv.querySelector('.flex-1 p');
            const horaActual = horaEl?.textContent?.trim() || '';
            const textoActual = textoEl?.textContent?.trim() || '';

            const nuevaHora = prompt('Editar hora (HH:MM):', horaActual);
            if (!nuevaHora) return;
            const nuevoTexto = prompt('Editar comentario:', textoActual);
            if (!nuevoTexto) return;

            try {
                const fd = new FormData();
                fd.append('id', id);
                fd.append('hora', nuevaHora.trim());
                fd.append('comentario', nuevoTexto.trim());
                const res = await fetch(`${BASE}/bitacora_editar.php`, { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    cargarBitacora();
                } else {
                    alert(data.message || 'Error al editar.');
                }
            } catch {
                alert('Error de red.');
            }
            return;
        }

        const btnEliminar = e.target.closest('[data-action="eliminar-bitacora"]');
        if (btnEliminar) {
            const id = btnEliminar.dataset.id;
            if (!id || !confirm('¿Eliminar esta entrada de la bitácora?')) return;

            try {
                const fd = new FormData();
                fd.append('id', id);
                const res = await fetch(`${BASE}/bitacora_eliminar.php`, { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    cargarBitacora();
                } else {
                    alert(data.message || 'Error al eliminar.');
                }
            } catch {
                alert('Error de red.');
            }
        }
    });

    // ── Guardar Recomendaciones ──────────────────────────────────────
    btnSubmitNotas?.addEventListener('click', async () => {
        if (!notasCitaId) return;

        const recomendaciones = inputRecomendaciones?.value?.trim() || '';

        btnSubmitNotas.disabled = true;
        btnSubmitNotas.textContent = 'Guardando...';

        try {
            const fd = new FormData();
            fd.append('cita_id', notasCitaId);
            fd.append('recomendaciones', recomendaciones);
            fd.append('comentarios', '');
            const res = await fetch(`${BASE}/guardar_notas_cita.php`, { method: 'POST', body: fd });
            const data = await res.json();

            if (data.success) {
                cerrarModalNotas();
                cargarCitas();
            } else {
                alert(data.message || 'Error al guardar recomendaciones.');
            }
        } catch {
            alert('Error de red.');
        } finally {
            btnSubmitNotas.disabled = false;
            btnSubmitNotas.textContent = 'Guardar Recomendaciones';
        }
    });

    formFin?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const fd = new FormData(formFin);
        btnSubmitFin.disabled = true;
        btnSubmitFin.textContent = 'Procesando...';

        try {
            const res = await fetch(`${BASE}/finalizar_cita.php`, { method: 'POST', body: fd });
            const data = await res.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Cita completada',
                    text: data.message || 'La cita se finalizó correctamente.',
                    confirmButtonColor: '#0284c7',
                    confirmButtonText: 'OK',
                    timer: 3000,
                    timerProgressBar: true
                });
                cerrarModalFinalizar();
                cargarCitas();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Error al completar la cita',
                    confirmButtonColor: '#0284c7'
                });
            }
        } catch {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor.',
                confirmButtonColor: '#0284c7'
            });
        } finally {
            btnSubmitFin.disabled = false;
            btnSubmitFin.textContent = 'Guardar y Completar';
        }
    });

    // ── Filtros de estado ──────────────────────────────────────────────────────
    document.getElementById('filtros-estado').addEventListener('click', (e) => {
        const btn = e.target.closest('[data-estado]');
        if (!btn) return;

        filtroEstado = btn.dataset.estado;

        document.querySelectorAll('.filtro-btn').forEach(b => {
            b.classList.remove('bg-brand-600', 'text-white', 'activo');
            b.classList.add('bg-white', 'text-gray-600', 'border', 'border-gray-200');
        });
        btn.classList.add('bg-brand-600', 'text-white', 'activo');
        btn.classList.remove('bg-white', 'text-gray-600');

        cargarCitas();
    });

    // ── Búsqueda de texto ──────────────────────────────────────────────────────
    let timeoutBusqueda;
    document.getElementById('buscador-clientes')?.addEventListener('input', (e) => {
        clearTimeout(timeoutBusqueda);
        timeoutBusqueda = setTimeout(() => cargarCitas(), 300);
    });

    // ── Filtro de fechas ───────────────────────────────────────────────────────
    document.getElementById('filtro-desde')?.addEventListener('change', cargarCitas);
    document.getElementById('filtro-hasta')?.addEventListener('change', cargarCitas);
    document.getElementById('btn-limpiar-fechas')?.addEventListener('click', () => {
        document.getElementById('filtro-desde').value = '';
        document.getElementById('filtro-hasta').value = '';
        cargarCitas();
    });

    // ── Modal Editar Cita ─────────────────────────────────────────────
    const modalEdit = document.getElementById('modal-editar-cita');
    const formEdit = document.getElementById('form-editar-cita');

    window.abrirModalEditar = function (id) {
        if (!modalEdit) return;
        const card = document.querySelector(`[data-id="${id}"]`);
        if (!card) return;

        const fechaText = card.querySelector('.text-lg.font-bold')?.textContent || '';
        const tipoText = card.querySelector('.flex.items-center.gap-2 span.font-medium')?.textContent || '';
        const notaEl = card.querySelector('.bg-gray-50.rounded');
        const nota = notaEl ? notaEl.textContent.trim() : '';

        let fechaISO = '';
        const fechaCard = card.querySelector('.text-xs.text-gray-400.uppercase')?.textContent || '';
        try {
            const partes = fechaCard.split(' ');
            if (partes.length >= 3) {
                const meses = { ene: 0, feb: 1, mar: 2, abr: 3, may: 4, jun: 5, jul: 6, ago: 7, sep: 8, oct: 9, nov: 10, dic: 11 };
                const dia = parseInt(partes[0]);
                const mes = meses[partes[1]?.toLowerCase().substring(0, 3)] ?? 0;
                const anio = parseInt(partes[2]);
                const horaParts = fechaText.split(':');
                if (!isNaN(dia) && !isNaN(anio) && horaParts.length === 2) {
                    const d = new Date(anio, mes, dia, parseInt(horaParts[0]), parseInt(horaParts[1]));
                    if (!isNaN(d.getTime())) {
                        fechaISO = d.getFullYear() + '-' +
                            String(d.getMonth() + 1).padStart(2, '0') + '-' +
                            String(d.getDate()).padStart(2, '0') + 'T' +
                            String(d.getHours()).padStart(2, '0') + ':' +
                            String(d.getMinutes()).padStart(2, '0');
                    }
                }
            }
        } catch (e) {}

        document.getElementById('edit-cita-id').value = id;
        document.getElementById('edit-fecha').value = fechaISO;
        document.getElementById('edit-tipo').value = tipoText;
        document.getElementById('edit-nota').value = nota;

        modalEdit.classList.remove('hidden');
    };

    document.getElementById('btn-cerrar-modal-editar')?.addEventListener('click', cerrarModalEditar);
    document.getElementById('btn-cancelar-editar')?.addEventListener('click', cerrarModalEditar);
    modalEdit?.addEventListener('click', (e) => {
        if (e.target === modalEdit.querySelector('.fixed.inset-0.bg-gray-900\\/50')) cerrarModalEditar();
    });

    function cerrarModalEditar() {
        if (modalEdit) modalEdit.classList.add('hidden');
    }

    formEdit?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('edit-cita-id').value;
        const fecha = document.getElementById('edit-fecha').value;
        const tipo = document.getElementById('edit-tipo').value;
        const nota = document.getElementById('edit-nota').value.trim();

        if (!fecha || !tipo) {
            Swal.fire({ icon: 'warning', title: 'Campos requeridos', text: 'Fecha y tipo de servicio son obligatorios.', confirmButtonColor: '#0284c7', confirmButtonText: 'OK' });
            return;
        }

        try {
            const fd = new FormData();
            fd.append('id', id);
            fd.append('fecha', fecha.replace('T', ' ') + ':00');
            fd.append('tipo', tipo);
            fd.append('nota', nota);

            const res = await fetch(`${BASE}/editar_cita.php`, { method: 'POST', body: fd });
            const data = await res.json();

            if (data.success) {
                await Swal.fire({ icon: 'success', title: 'Cita actualizada', text: data.message, confirmButtonColor: '#0284c7', confirmButtonText: 'OK', timer: 2000, timerProgressBar: true });
                cerrarModalEditar();
                cargarCitas();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudo actualizar.', confirmButtonColor: '#0284c7', confirmButtonText: 'OK' });
            }
        } catch {
            Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor.', confirmButtonColor: '#0284c7', confirmButtonText: 'OK' });
        }
    });

    // ── Helper ─────────────────────────────────────────────────────────────────
    function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── Recargar al crear nueva cita ───────────────────────────────────────────
    window.addEventListener('citaCreada', cargarCitas);

    // Carga inicial
    cargarCitas();
});