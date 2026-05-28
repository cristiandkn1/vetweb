(function () {
    const BASE = document.getElementById('base-cuentas')?.dataset?.base || '/admin/cuentas/api';
    const contenedor = document.getElementById('lista-cuentas');
    const filtroTipo = document.getElementById('filtro-tipo');
    const filtroEstado = document.getElementById('filtro-estado');
    const filtroDesde = document.getElementById('filtro-desde');
    const filtroHasta = document.getElementById('filtro-hasta');
    const buscador = document.getElementById('buscador-cuentas');

    async function cargarCuentas() {
        const params = new URLSearchParams();
        if (filtroTipo?.value && filtroTipo.value !== 'todos') params.set('tipo', filtroTipo.value);
        if (filtroEstado?.value && filtroEstado.value !== 'todos') params.set('estado', filtroEstado.value);
        if (filtroDesde?.value) params.set('desde', filtroDesde.value);
        if (filtroHasta?.value) params.set('hasta', filtroHasta.value);
        if (buscador?.value) params.set('search', buscador.value);

        try {
            const res = await fetch(`${BASE}/listar_cuentas.php?${params}`);
            const data = await res.json();
            if (!data.success) { contenedor.innerHTML = '<tr><td colspan="7" class="text-center text-red-500 py-8">Error al cargar</td></tr>'; return; }

            const kpi = data.kpi || {};
            document.getElementById('kpi-ingresos').textContent = '$' + Math.round(kpi.total_ingresos || 0).toLocaleString('es-CL');
            document.getElementById('kpi-gastos').textContent = '$' + Math.round(kpi.total_gastos || 0).toLocaleString('es-CL');
            const saldo = (kpi.total_ingresos || 0) - (kpi.total_gastos || 0);
            const elSaldo = document.getElementById('kpi-saldo');
            elSaldo.textContent = '$' + Math.round(saldo).toLocaleString('es-CL');
            elSaldo.className = 'text-2xl font-bold ' + (saldo >= 0 ? 'text-emerald-600' : 'text-red-600');
            document.getElementById('kpi-pendientes').textContent = '$' + Math.round(kpi.total_pendientes || 0).toLocaleString('es-CL');

            if (!data.cuentas || data.cuentas.length === 0) {
                contenedor.innerHTML = '<tr><td colspan="7" class="text-center text-gray-400 py-12 bg-gray-50 rounded-lg">No hay registros</td></tr>';
                return;
            }

            contenedor.innerHTML = data.cuentas.map(c => {
                const tipoCls = c.tipo === 'ingreso' ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50';
                const estCls = { pendiente: 'bg-yellow-100 text-yellow-700', pagado: 'bg-green-100 text-green-700', cancelado: 'bg-gray-100 text-gray-500' }[c.estado] || 'bg-gray-100 text-gray-600';
                return `<tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-3 py-3 text-xs text-gray-500 whitespace-nowrap">${c.fecha_contable}</td>
                    <td class="px-3 py-3"><span class="text-[11px] font-semibold px-2 py-0.5 rounded-full ${tipoCls}">${c.tipo}</span></td>
                    <td class="px-3 py-3 text-sm text-gray-700 font-medium">${escHtml(c.categoria)}</td>
                    <td class="px-3 py-3 text-sm text-gray-500 max-w-[200px] truncate" title="${escHtml(c.descripcion || '')}">${escHtml(c.descripcion || '-')}</td>
                    <td class="px-3 py-3 text-sm font-semibold text-gray-800 whitespace-nowrap">$${Math.round(c.monto).toLocaleString('es-CL')}</td>
                    <td class="px-3 py-3"><span class="text-[11px] font-medium px-2 py-0.5 rounded-full ${estCls}">${c.estado}</span></td>
                    <td class="px-3 py-3">
                        <div class="flex items-center gap-1">
                            <button data-id="${c.id}" data-action="ver-detalle-cuenta" title="Ver detalle"
                                class="p-1.5 text-gray-500 hover:text-indigo-600 bg-gray-50 hover:bg-indigo-50 rounded transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </button>
                            <button data-id="${c.id}" data-action="editar-cuenta" title="Editar"
                                class="p-1.5 text-gray-500 hover:text-brand-600 bg-gray-50 hover:bg-brand-50 rounded transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                            </button>
                            ${c.estado === 'pendiente' && c.cliente_telefono ? `
                            <button data-id="${c.id}" data-action="whatsapp-cuenta" title="Recordar pago"
                                class="p-1.5 text-green-600 hover:text-green-800 bg-green-50 hover:bg-green-100 rounded transition-colors">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            </button>
                            ` : ''}
                            <button data-id="${c.id}" data-action="eliminar-cuenta" title="Eliminar"
                                class="p-1.5 text-gray-500 hover:text-red-600 bg-gray-50 hover:bg-red-50 rounded transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>`;
            }).join('');
        } catch {
            contenedor.innerHTML = '<tr><td colspan="7" class="text-center text-red-500 py-8">Error de conexión</td></tr>';
        }
    }

    function escHtml(s) { if (!s) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    // ── Filtros ──
    let timeoutBusq;
    filtroTipo?.addEventListener('change', cargarCuentas);
    filtroEstado?.addEventListener('change', cargarCuentas);
    filtroDesde?.addEventListener('change', cargarCuentas);
    filtroHasta?.addEventListener('change', cargarCuentas);
    buscador?.addEventListener('input', () => { clearTimeout(timeoutBusq); timeoutBusq = setTimeout(cargarCuentas, 300); });
    document.getElementById('btn-limpiar-filtros')?.addEventListener('click', () => {
        if (filtroTipo) filtroTipo.value = 'todos';
        if (filtroEstado) filtroEstado.value = 'todos';
        if (filtroDesde) filtroDesde.value = '';
        if (filtroHasta) filtroHasta.value = '';
        if (buscador) buscador.value = '';
        cargarCuentas();
    });

    // ── Modal ──
    const modal = document.getElementById('modal-cuenta');
    const form = document.getElementById('form-cuenta');
    const title = document.getElementById('modal-cuenta-title');
    let currentId = 0;

    function abrirModal(data) {
        currentId = data?.id || 0;
        title.textContent = currentId ? 'Editar registro' : 'Nuevo registro';
        document.getElementById('cuenta-id').value = currentId;
        document.getElementById('cuenta-tipo').value = data?.tipo || 'ingreso';
        document.getElementById('cuenta-estado').value = data?.estado || 'pendiente';
        document.getElementById('cuenta-categoria').value = data?.categoria || '';
        document.getElementById('cuenta-descripcion').value = data?.descripcion || '';
        document.getElementById('cuenta-monto').value = parseFloat(data?.monto || 0) || '';
        document.getElementById('cuenta-metodo').value = data?.metodo_pago || '';
        document.getElementById('cuenta-cliente').value = data?.cliente_id || '';
        document.getElementById('cuenta-fecha').value = data?.fecha_contable || new Date().toISOString().split('T')[0];
        modal.classList.remove('hidden');
    }

    function cerrarModal() { modal.classList.add('hidden'); }
    document.getElementById('btn-cerrar-cuenta')?.addEventListener('click', cerrarModal);
    document.getElementById('btn-cancelar-cuenta')?.addEventListener('click', cerrarModal);
    modal?.addEventListener('click', (e) => { if (e.target === modal.querySelector('.fixed.inset-0.bg-gray-900\\/50')) cerrarModal(); });

    // Cargar clientes en select
    async function cargarClientes() {
        try {
            const res = await fetch('/admin/usuarios/api/listar_usuarios.php');
            const data = await res.json();
            const sel = document.getElementById('cuenta-cliente');
            if (data.success && data.usuarios) {
                sel.innerHTML = '<option value="">Sin cliente</option>' + data.usuarios.map(cl =>
                    `<option value="${cl.id}">${escHtml(cl.nombre_completo)} ${cl.telefono ? '· ' + escHtml(cl.telefono) : ''}</option>`
                ).join('');
            }
        } catch {}
    }
    cargarClientes();

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-cuenta');
        btn.disabled = true; btn.textContent = 'Guardando...';
        try {
            const fd = new FormData(form);
            const res = await fetch(`${BASE}/guardar_cuenta.php`, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                await Swal.fire({ icon: 'success', title: 'Guardado', text: data.message, confirmButtonColor: '#0284c7', confirmButtonText: 'OK', timer: 1500, timerProgressBar: true });
                cerrarModal();
                cargarCuentas();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#0284c7', confirmButtonText: 'OK' });
            }
        } catch {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión.', confirmButtonColor: '#0284c7', confirmButtonText: 'OK' });
        }
        btn.disabled = false; btn.textContent = 'Guardar';
    });

    // ── Delegación de eventos ──
    document.querySelector('#lista-cuentas')?.addEventListener('click', async (e) => {
        const btnDetalle = e.target.closest('[data-action="ver-detalle-cuenta"]');
        if (btnDetalle) { abrirDetalleCuenta(btnDetalle.dataset.id); return; }

        const btnEditar = e.target.closest('[data-action="editar-cuenta"]');
        if (btnEditar) {
            const id = btnEditar.dataset.id;
            try {
                const res = await fetch(`${BASE}/listar_cuentas.php?id=${id}`);
                const data = await res.json();
                const c = data.cuentas?.[0];
                if (c) {
                    abrirModal(c);
                } else {
                    // Recargar y encontrar en la tabla
                    const row = btnEditar.closest('tr');
                    const cells = row.querySelectorAll('td');
                    abrirModal({
                        id: id,
                        tipo: row.querySelector('[class*="rounded-full"]')?.textContent?.trim() || 'ingreso',
                        categoria: cells[2]?.textContent?.trim() || '',
                        descripcion: cells[3]?.textContent?.trim() || '',
                        monto: parseFloat(cells[4]?.textContent?.replace(/[^0-9.-]/g, '') || 0),
                        estado: cells[5]?.textContent?.trim() || 'pendiente',
                        fecha_contable: cells[0]?.textContent?.trim() || ''
                    });
                }
            } catch {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el registro.', confirmButtonColor: '#0284c7' });
            }
        }

        const btnWA = e.target.closest('[data-action="whatsapp-cuenta"]');
        if (btnWA) {
            const row = btnWA.closest('tr');
            const cells = row.querySelectorAll('td');
            const monto = cells[4]?.textContent?.trim() || '';
            const categoria = cells[2]?.textContent?.trim() || '';
            const desc = cells[3]?.textContent?.trim() || categoria;
            // Buscar telefono del cliente desde el boton
            try {
                const res = await fetch(`${BASE}/listar_cuentas.php?id=${btnWA.dataset.id}`);
                const data = await res.json();
                const c = data.cuentas?.[0];
                if (c && c.cliente_telefono) {
                    const tel = c.cliente_telefono.replace(/\D/g, '');
                    const phone = tel.startsWith('56') ? tel : '56' + tel;
                    const msg = `Hola! Le recordamos que tiene un pago pendiente por ${monto} correspondiente a ${desc}. Por favor realice el pago a la brevedad. Gracias.`;
                    window.open(`https://wa.me/${phone}?text=${encodeURIComponent(msg)}`, '_blank');
                } else {
                    Swal.fire({ icon: 'info', title: 'Sin teléfono', text: 'Este registro no tiene un cliente con teléfono asociado.', confirmButtonColor: '#0284c7' });
                }
            } catch {}
        }

        const btnElim = e.target.closest('[data-action="eliminar-cuenta"]');
        if (btnElim) {
            const result = await Swal.fire({ title: 'Eliminar', text: 'Esta accion no se puede deshacer.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', confirmButtonText: 'Eliminar', cancelButtonText: 'Cancelar' });
            if (!result.isConfirmed) return;
            try {
                const fd = new FormData(); fd.append('id', btnElim.dataset.id);
                const res = await fetch(`${BASE}/eliminar_cuenta.php`, { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Eliminado', text: data.message, confirmButtonColor: '#0284c7', timer: 1500, timerProgressBar: true });
                    cargarCuentas();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#0284c7' });
                }
            } catch {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión.', confirmButtonColor: '#0284c7' });
            }
        }
    });

    document.getElementById('btn-agregar-cuenta')?.addEventListener('click', () => abrirModal(null));

    // ── Detalle ──
    async function abrirDetalleCuenta(id) {
        const body = document.getElementById('detalle-cuenta-body');
        body.innerHTML = '<p class="text-center text-gray-400 py-8">Cargando...</p>';
        document.getElementById('modal-detalle-cuenta').classList.remove('hidden');
        try {
            const res = await fetch(`/admin/cuentas/api/detalle_cuenta.php?id=${id}`);
            const data = await res.json();
            if (!data.success) { body.innerHTML = '<p class="text-center text-red-500 py-8">Error al cargar detalle.</p>'; return; }
            const c = data.cuenta;

            const tipoCls = c.tipo === 'ingreso' ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50';
            const estCls = { pendiente: 'bg-yellow-100 text-yellow-700', pagado: 'bg-green-100 text-green-700', cancelado: 'bg-gray-100 text-gray-500' }[c.estado] || 'bg-gray-100 text-gray-600';

            const montoFmt = '$' + Math.round(c.monto).toLocaleString('es-CL');

            body.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <span class="text-xs text-gray-400 uppercase tracking-wide">Monto</span>
                        <p class="text-2xl font-bold text-gray-800">${montoFmt}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 uppercase tracking-wide">Tipo</span>
                        <p><span class="text-xs font-semibold px-2 py-0.5 rounded-full ${tipoCls}">${escHtml(c.tipo)}</span></p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 uppercase tracking-wide">Estado</span>
                        <p><span class="text-xs font-medium px-2 py-0.5 rounded-full ${estCls}">${escHtml(c.estado)}</span></p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 uppercase tracking-wide">Categoría</span>
                        <p class="font-medium text-gray-800">${escHtml(c.categoria)}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 uppercase tracking-wide">Fecha</span>
                        <p class="font-medium text-gray-800">${c.fecha_contable}</p>
                    </div>
                    ${c.metodo_pago ? `<div>
                        <span class="text-xs text-gray-400 uppercase tracking-wide">Método de pago</span>
                        <p class="font-medium text-gray-800">${escHtml(c.metodo_pago)}</p>
                    </div>` : ''}
                </div>

                ${c.descripcion ? `<hr class="border-gray-100 my-4">
                <div>
                    <span class="text-xs text-gray-400 uppercase tracking-wide">Descripción</span>
                    <p class="text-sm text-gray-700 mt-1">${escHtml(c.descripcion)}</p>
                </div>` : ''}

                ${c.cliente_id ? `<hr class="border-gray-100 my-4">
                <div>
                    <span class="text-xs text-gray-400 uppercase tracking-wide">Cliente</span>
                    <p class="font-medium text-gray-800">${escHtml(c.cliente_nombre)}</p>
                    ${c.cliente_telefono ? `<p class="text-xs text-gray-500">${escHtml(c.cliente_telefono)}</p>` : ''}
                    ${c.cliente_email ? `<p class="text-xs text-gray-500">${escHtml(c.cliente_email)}</p>` : ''}
                </div>` : ''}

                ${c.cita_id ? `<hr class="border-gray-100 my-4">
                <div>
                    <span class="text-xs text-gray-400 uppercase tracking-wide">Cita asociada</span>
                    <div class="flex items-center justify-between mt-1">
                        <div>
                            <p class="font-medium text-gray-800">${escHtml(c.cita_tipo)}</p>
                            <p class="text-xs text-gray-500">${c.cita_fecha ? new Date(c.cita_fecha).toLocaleString('es-CL', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : ''} · <span class="${c.cita_estado === 'completada' ? 'text-emerald-600' : c.cita_estado === 'cancelada' ? 'text-red-500' : 'text-amber-600'}">${escHtml(c.cita_estado)}</span></p>
                        </div>
                        ${c.cita_token ? `<a href="/citas/seguimiento_cita.php?token=${c.cita_token}" target="_blank" class="text-brand-600 hover:text-brand-700 text-xs font-medium flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg> Ver seguimiento</a>` : ''}
                    </div>
                </div>` : ''}

                ${c.mascota_id ? `<hr class="border-gray-100 my-4">
                <div>
                    <span class="text-xs text-gray-400 uppercase tracking-wide">Mascota</span>
                    <div class="flex items-center justify-between mt-1">
                        <p class="font-medium text-gray-800">${escHtml(c.mascota_nombre)} ${c.mascota_especie ? '(' + escHtml(c.mascota_especie) + (c.mascota_raza ? ' · ' + escHtml(c.mascota_raza) : '') + ')' : ''}</p>
                        ${c.mascota_token ? `<a href="/mascota/vista-estado-mascota.php?token=${c.mascota_token}" target="_blank" class="text-brand-600 hover:text-brand-700 text-xs font-medium flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg> Perfil</a>` : ''}
                    </div>
                </div>` : ''}

                ${c.cotizacion_id ? `<hr class="border-gray-100 my-4">
                <div>
                    <span class="text-xs text-gray-400 uppercase tracking-wide">Cotización</span>
                    <div class="flex items-center justify-between mt-1">
                        <p class="font-medium text-gray-800">${escHtml(c.numero_cotizacion)} · ${escHtml(c.cotizacion_servicio)} ${c.cotizacion_total ? '($' + Math.round(c.cotizacion_total).toLocaleString('es-CL') + ')' : ''}</p>
                        <a href="/admin/cotizaciones/editar_cotizacion.php?id=${c.cotizacion_id}" target="_blank" class="text-brand-600 hover:text-brand-700 text-xs font-medium flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg> Ver cotización</a>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">Estado: <span class="${c.cotizacion_estado === 'aprobada' ? 'text-emerald-600' : c.cotizacion_estado === 'rechazada' ? 'text-red-500' : 'text-amber-600'}">${escHtml(c.cotizacion_estado)}</span></p>
                </div>` : ''}

                <hr class="border-gray-100 my-4">
                <div>
                    <span class="text-xs text-gray-400 uppercase tracking-wide">Creado</span>
                    <p class="text-xs text-gray-500">${new Date(c.created_at).toLocaleString('es-CL', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
                </div>
            `;
        } catch {
            body.innerHTML = '<p class="text-center text-red-500 py-8">Error de conexión.</p>';
        }
    }

    document.getElementById('btn-cerrar-detalle-cuenta')?.addEventListener('click', () => {
        document.getElementById('modal-detalle-cuenta').classList.add('hidden');
    });

    // Carga inicial
    cargarCuentas();
})();
