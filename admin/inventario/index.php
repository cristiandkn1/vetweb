<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}
$esAdmin = ($_SESSION['user_role'] ?? '') === 'ADMIN';
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../includes/head.php'; ?>
<body class="bg-gray-100 font-sans">
    <?php include '../../includes/mobile-header.php'; ?>
    <div class="flex h-screen overflow-hidden">
        <?php include '../../includes/sidebar.php'; ?>
        <main class="flex-1 flex flex-col min-w-0 bg-gray-100 overflow-y-auto">
            <div class="p-6 md:p-8">

                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Inventario</h1>
                        <p class="text-sm text-gray-500 mt-0.5">Vacunas, remedios, equipos e insumos</p>
                    </div>
                    <button id="btn-nuevo-item"
                        class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Nuevo Item
                    </button>
                </div>

                <!-- KPIs -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
                    <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
                        <div class="text-2xl font-bold text-brand-600" data-kpi="total_items">—</div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide mt-1">Total Items</div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
                        <div class="text-2xl font-bold text-amber-600" data-kpi="bajo_stock">—</div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide mt-1">Stock Bajo</div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
                        <div class="text-2xl font-bold text-yellow-600" data-kpi="por_vencer">—</div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide mt-1">Por Vencer</div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
                        <div class="text-2xl font-bold text-red-500" data-kpi="vencidos">—</div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide mt-1">Vencidos</div>
                    </div>
                    <?php if ($esAdmin): ?>
                    <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
                        <div class="text-2xl font-bold text-emerald-600" data-kpi="valor_total">—</div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide mt-1">Valor Total</div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-3 mb-6">
                    <div class="relative flex-1 min-w-[200px] max-w-xs">
                        <input type="text" id="buscador" placeholder="Buscar nombre, descripción, proveedor..."
                            class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <select id="filtro-categoria"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        <option value="todas">Todas las categorías</option>
                        <option value="vacuna">Vacunas</option>
                        <option value="remedio">Remedios</option>
                        <option value="equipo">Equipos</option>
                        <option value="insumo">Insumos</option>
                        <option value="otro">Otros</option>
                    </select>
                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer select-none">
                        <input type="checkbox" id="filtro-bajo-stock" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        Stock bajo
                    </label>
                </div>

                <!-- Table -->
                <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Categoría</th>
                                    <th class="text-center px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Stock</th>
                                    <th class="text-right px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider <?= $esAdmin ? '' : 'hidden' ?>">Precio</th>
                                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Vencimiento</th>
                                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Proveedor</th>
                                    <th class="text-center px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-body" class="divide-y divide-gray-50">
                                <tr><td colspan="7" class="text-center py-12 text-sm text-gray-400">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Modal -->
    <div id="modal-item" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <h3 class="text-lg font-bold text-gray-800" id="modal-title">Nuevo Item</h3>
                <button type="button" class="cerrar-modal text-gray-300 hover:text-gray-500 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="form-item" class="p-6 space-y-4">
                <input type="hidden" name="id" id="item-id" value="0">

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Nombre *</label>
                        <input type="text" name="nombre" id="item-nombre" required
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Descripción</label>
                        <textarea name="descripcion" id="item-descripcion" rows="2"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Categoría</label>
                        <select name="categoria" id="item-categoria"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            <option value="vacuna">Vacuna</option>
                            <option value="remedio">Remedio</option>
                            <option value="equipo">Equipo</option>
                            <option value="insumo">Insumo</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Cantidad</label>
                        <input type="number" name="cantidad" id="item-cantidad" value="0" min="0"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                    <?php if ($esAdmin): ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Precio $</label>
                        <input type="number" name="precio" id="item-precio" step="1" min="0"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                            placeholder="0">
                    </div>
                    <?php endif; ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Lote</label>
                        <input type="text" name="lote" id="item-lote"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Fecha Vencimiento</label>
                        <input type="date" name="fecha_vencimiento" id="item-fecha-vencimiento"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Proveedor</label>
                        <input type="text" name="proveedor" id="item-proveedor"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Ubicación</label>
                        <input type="text" name="ubicacion" id="item-ubicacion"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                            placeholder="Ej: Estante A3, Refrigerador...">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="cerrar-modal px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Cancelar</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg transition-colors">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../scripts/sidebar.js"></script>
    <script>
        const API = '/admin/inventario/api';
        const esAdmin = <?= json_encode($esAdmin) ?>;
        const CATEGORIA_LABELS = { vacuna: 'Vacuna', remedio: 'Remedio', equipo: 'Equipo', insumo: 'Insumo', otro: 'Otro' };
        const CATEGORIA_COLORS = {
            vacuna: 'bg-blue-50 text-blue-700 border-blue-200',
            remedio: 'bg-amber-50 text-amber-700 border-amber-200',
            equipo: 'bg-purple-50 text-purple-700 border-purple-200',
            insumo: 'bg-teal-50 text-teal-700 border-teal-200',
            otro: 'bg-gray-50 text-gray-600 border-gray-200'
        };

        let items = [];

        // ── Cargar ────────────────────────────────────────────
        async function cargarItems() {
            const params = new URLSearchParams();
            const search = document.getElementById('buscador').value.trim();
            const categoria = document.getElementById('filtro-categoria').value;
            const bajoStock = document.getElementById('filtro-bajo-stock').checked;

            if (search) params.set('search', search);
            if (categoria && categoria !== 'todas') params.set('categoria', categoria);
            if (bajoStock) params.set('bajo_stock', '1');

            const r = await fetch(`${API}/listar.php?${params}`);
            const d = await r.json();
            if (!d.success) return;

            items = d.items;
            renderKPI(d.kpi);
            renderTabla();
        }

        // ── KPIs ──────────────────────────────────────────────
        function renderKPI(kpi) {
            if (!kpi) return;
            document.querySelectorAll('[data-kpi]').forEach(el => {
                const key = el.dataset.kpi;
                if (key === 'valor_total') {
                    const val = parseFloat(kpi[key]);
                    el.textContent = !isNaN(val) && val > 0 ? '$' + Math.round(val).toLocaleString('es-CL') : '$0';
                } else {
                    el.textContent = kpi[key] !== undefined ? kpi[key] : '—';
                }
            });
        }

        // ── Tabla ─────────────────────────────────────────────
        function renderTabla() {
            const tbody = document.getElementById('tabla-body');
            if (items.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-12 text-sm text-gray-400">Sin resultados.</td></tr>';
                return;
            }

            tbody.innerHTML = items.map(i => {
                const catLabel = CATEGORIA_LABELS[i.categoria] || i.categoria;
                const catColor = CATEGORIA_COLORS[i.categoria] || CATEGORIA_COLORS.otro;

                const stockColor = i.cantidad <= 0 ? 'text-red-600 font-bold' :
                                   i.cantidad <= 5 ? 'text-amber-600 font-bold' :
                                   'text-gray-700';

                let vencHtml = '<span class="text-gray-300">—</span>';
                if (i.fecha_vencimiento) {
                    const hoy = new Date(); hoy.setHours(0,0,0,0);
                    const vence = new Date(i.fecha_vencimiento + 'T00:00:00');
                    const diff = Math.ceil((vence - hoy) / (1000*60*60*24));
                    if (diff < 0) {
                        vencHtml = `<span class="text-red-600 font-medium">${i.fecha_vencimiento} (vencido)</span>`;
                    } else if (diff <= 30) {
                        vencHtml = `<span class="text-amber-600 font-medium">${i.fecha_vencimiento} (${diff}d)</span>`;
                    } else {
                        vencHtml = `<span class="text-gray-600">${i.fecha_vencimiento}</span>`;
                    }
                }

                const precioHtml = esAdmin && i.precio !== null && i.precio !== undefined
                    ? `<span class="text-gray-700 font-medium">$${Math.round(i.precio).toLocaleString('es-CL')}</span>`
                    : '<span class="text-gray-300">—</span>';

                const proveedor = i.proveedor ? escHtml(i.proveedor) : '<span class="text-gray-300">—</span>';
                const ubicacion = i.ubicacion ? escHtml(i.ubicacion) : '';
                const descripcion = i.descripcion ? escHtml(i.descripcion) : '';
                const lote = i.lote ? escHtml(i.lote) : '';

                return `
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-800">${escHtml(i.nombre)}</div>
                            ${descripcion ? `<div class="text-xs text-gray-400 mt-0.5 line-clamp-1">${descripcion}</div>` : ''}
                            ${lote ? `<div class="text-[10px] text-gray-300 mt-0.5">Lote: ${lote}</div>` : ''}
                            ${ubicacion ? `<div class="text-[10px] text-gray-300">${ubicacion}</div>` : ''}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded text-[11px] font-medium border ${catColor}">${catLabel}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-sm ${stockColor}">${i.cantidad}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm ${esAdmin ? '' : 'hidden'}">
                            ${precioHtml}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            ${vencHtml}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            ${proveedor}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button class="btn-editar p-1.5 text-gray-300 hover:text-brand-600 transition-colors" data-id="${i.id}" title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button class="btn-eliminar p-1.5 text-gray-300 hover:text-red-500 transition-colors" data-id="${i.id}" title="Eliminar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');

            // Eventos
            tbody.querySelectorAll('.btn-editar').forEach(b => {
                b.addEventListener('click', () => abrirModal(parseInt(b.dataset.id)));
            });
            tbody.querySelectorAll('.btn-eliminar').forEach(b => {
                b.addEventListener('click', async () => {
                    if (!confirm('¿Eliminar este item del inventario?')) return;
                    const fd = new FormData(); fd.append('id', b.dataset.id);
                    const r = await fetch(`${API}/eliminar.php`, { method: 'POST', body: fd });
                    const d = await r.json();
                    if (d.success) cargarItems();
                });
            });
        }

        function escHtml(s) { if (!s) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

        // ── Borrador en sessionStorage ─────────────────────────
        function guardarBorrador() {
            const data = {};
            document.querySelectorAll('#form-item [name]').forEach(el => {
                if (el.type === 'checkbox') {
                    data[el.name] = el.checked ? '1' : '0';
                } else {
                    data[el.name] = el.value;
                }
            });
            sessionStorage.setItem('inventario_draft', JSON.stringify(data));
        }

        function restaurarBorrador() {
            const raw = sessionStorage.getItem('inventario_draft');
            if (!raw) return false;
            try {
                const data = JSON.parse(raw);
                if (!data.nombre || !data.nombre.trim()) return false;
                Object.entries(data).forEach(([name, val]) => {
                    const el = document.querySelector(`#form-item [name="${name}"]`);
                    if (el) {
                        if (el.type === 'checkbox') {
                            el.checked = val === '1';
                        } else {
                            el.value = val;
                        }
                    }
                });
                return true;
            } catch { return false; }
        }

        function limpiarBorrador() {
            sessionStorage.removeItem('inventario_draft');
        }

        // Modal save on input change
        document.getElementById('form-item').addEventListener('input', () => {
            const id = document.getElementById('item-id').value;
            if (id === '0') guardarBorrador();
        });

        // ── Modal ─────────────────────────────────────────────
        function abrirModal(id) {
            const esNuevo = !id;
            document.getElementById('modal-title').textContent = esNuevo ? 'Nuevo Item' : 'Editar Item';
            document.getElementById('item-id').value = esNuevo ? 0 : id;

            if (esNuevo) {
                if (!restaurarBorrador()) {
                    document.getElementById('item-nombre').value = '';
                    document.getElementById('item-descripcion').value = '';
                    document.getElementById('item-categoria').value = 'otro';
                    document.getElementById('item-cantidad').value = 0;
                    if (document.getElementById('item-precio')) document.getElementById('item-precio').value = '';
                    document.getElementById('item-lote').value = '';
                    document.getElementById('item-fecha-vencimiento').value = '';
                    document.getElementById('item-proveedor').value = '';
                    document.getElementById('item-ubicacion').value = '';
                }
            } else {
                const i = items.find(x => x.id === id);
                if (!i) return;
                document.getElementById('item-nombre').value = i.nombre || '';
                document.getElementById('item-descripcion').value = i.descripcion || '';
                document.getElementById('item-categoria').value = i.categoria || 'otro';
                document.getElementById('item-cantidad').value = i.cantidad || 0;
                if (document.getElementById('item-precio')) document.getElementById('item-precio').value = i.precio !== null ? i.precio : '';
                document.getElementById('item-lote').value = i.lote || '';
                document.getElementById('item-fecha-vencimiento').value = i.fecha_vencimiento || '';
                document.getElementById('item-proveedor').value = i.proveedor || '';
                document.getElementById('item-ubicacion').value = i.ubicacion || '';
            }

            document.getElementById('modal-item').classList.remove('hidden');
        }

        document.getElementById('btn-nuevo-item').addEventListener('click', () => abrirModal(0));

        document.querySelectorAll('.cerrar-modal').forEach(b => {
            b.addEventListener('click', () => document.getElementById('modal-item').classList.add('hidden'));
        });
        document.getElementById('modal-item').addEventListener('click', (e) => {
            if (e.target === e.currentTarget) e.currentTarget.classList.add('hidden');
        });

        document.getElementById('form-item').addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const r = await fetch(`${API}/guardar.php`, { method: 'POST', body: fd });
            const d = await r.json();
            if (d.success) {
                limpiarBorrador();
                document.getElementById('modal-item').classList.add('hidden');
                cargarItems();
            } else {
                alert(d.message || 'Error al guardar.');
            }
        });

        // ── Filtros ───────────────────────────────────────────
        let timeoutBusqueda;
        document.getElementById('buscador').addEventListener('input', () => {
            clearTimeout(timeoutBusqueda);
            timeoutBusqueda = setTimeout(cargarItems, 300);
        });
        document.getElementById('filtro-categoria').addEventListener('change', cargarItems);
        document.getElementById('filtro-bajo-stock').addEventListener('change', cargarItems);

        // ── Init ──────────────────────────────────────────────
        cargarItems();
    </script>
</body>
</html>
