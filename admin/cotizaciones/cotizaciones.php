<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}
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
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Cotizaciones</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Gestión y seguimiento de cotizaciones</p>
                </div>

                <!-- KPIs -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6" id="kpi-container">
                    <div class="bg-white rounded-xl border border-gray-100 p-4 flex flex-col">
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Ingresos</span>
                        <span class="text-xl font-bold text-emerald-600 mt-1" id="kpi-ingresos">$0</span>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 p-4 flex flex-col">
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Aprobadas</span>
                        <span class="text-xl font-bold text-green-600 mt-1" id="kpi-aprobadas">0</span>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 p-4 flex flex-col">
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Pendientes</span>
                        <span class="text-xl font-bold text-yellow-600 mt-1" id="kpi-pendientes">0</span>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 p-4 flex flex-col">
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Rechazadas</span>
                        <span class="text-xl font-bold text-red-600 mt-1" id="kpi-rechazadas">0</span>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 p-4 flex flex-col">
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Vencidas</span>
                        <span class="text-xl font-bold text-gray-500 mt-1" id="kpi-vencidas">0</span>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 p-4 flex flex-col">
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total</span>
                        <span class="text-xl font-bold text-gray-800 mt-1" id="kpi-total">0</span>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="bg-white rounded-xl border border-gray-100 p-4 mb-6">
                    <div class="flex flex-col md:flex-row gap-3 items-end">
                        <!-- Buscador -->
                        <div class="flex-1 w-full">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1 block">Buscar</label>
                            <input type="text" id="input-search" placeholder="Cliente, mascota, número, servicio..."
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                        <!-- Estado -->
                        <div class="w-full md:w-40">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1 block">Estado</label>
                            <select id="input-estado"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white">
                                <option value="todos">Todos</option>
                                <option value="pendiente">Pendientes</option>
                                <option value="aprobada">Aprobadas</option>
                                <option value="rechazada">Rechazadas</option>
                                <option value="vencida">Vencidas</option>
                            </select>
                        </div>
                        <!-- Desde -->
                        <div class="w-full md:w-44">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1 block">Desde</label>
                            <input type="date" id="input-desde"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                        <!-- Hasta -->
                        <div class="w-full md:w-44">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1 block">Hasta</label>
                            <input type="date" id="input-hasta"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                        <button id="btn-filtrar"
                            class="w-full md:w-auto px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Filtrar
                        </button>
                    </div>
                </div>

                <!-- Tabla -->
                <div id="tabla-container" class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                    <div class="p-6 text-center text-gray-400 text-sm">Cargando cotizaciones...</div>
                </div>

            </div>
        </main>
    </div>

    <script src="../scripts/sidebar.js"></script>
    <script>
        const BASE = '/admin/cotizaciones/api';

        const estadoConfig = {
            pendiente: { cls: 'bg-yellow-100 text-yellow-700', label: 'Pendiente' },
            aprobada:  { cls: 'bg-green-100 text-green-700',   label: 'Aprobada' },
            rechazada: { cls: 'bg-red-100 text-red-600',       label: 'Rechazada' },
            vencida:   { cls: 'bg-gray-100 text-gray-500',     label: 'Vencida' },
        };

        function escHtml(str) { if (!str) return ''; return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

        function fmtPeso(n) { return '$' + Math.round(Number(n)).toLocaleString('es-CL'); }

        function fmtFecha(iso) {
            const d = new Date(iso);
            return d.toLocaleDateString('es-CL', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        // ── Cargar datos ──────────────────────────────────────
        async function cargarDatos() {
            const tablaContainer = document.getElementById('tabla-container');
            tablaContainer.innerHTML = '<div class="p-6 text-center text-gray-400 text-sm">Cargando cotizaciones...</div>';

            const params = new URLSearchParams();
            const search = document.getElementById('input-search').value.trim();
            const estado = document.getElementById('input-estado').value;
            const desde  = document.getElementById('input-desde').value;
            const hasta  = document.getElementById('input-hasta').value;
            if (search) params.set('search', search);
            if (estado && estado !== 'todos') params.set('estado', estado);
            if (desde) params.set('desde', desde);
            if (hasta) params.set('hasta', hasta);

            try {
                const res = await fetch(`${BASE}/listar_cotizaciones.php?${params.toString()}`);
                const data = await res.json();
                if (!data.success) { tablaContainer.innerHTML = '<div class="p-6 text-center text-red-400 text-sm">Error al cargar.</div>'; return; }
                renderKpi(data.kpi);
                renderTabla(data.cotizaciones);
            } catch {
                tablaContainer.innerHTML = '<div class="p-6 text-center text-red-400 text-sm">Error de conexión.</div>';
            }
        }

        // ── KPIs ───────────────────────────────────────────────
        function renderKpi(kpi) {
            document.getElementById('kpi-ingresos').textContent = fmtPeso(kpi.ingresos || 0);
            document.getElementById('kpi-aprobadas').textContent = Number(kpi.aprobadas || 0).toLocaleString('es-CL');
            document.getElementById('kpi-pendientes').textContent = Number(kpi.pendientes || 0).toLocaleString('es-CL');
            document.getElementById('kpi-rechazadas').textContent = Number(kpi.rechazadas || 0).toLocaleString('es-CL');
            document.getElementById('kpi-vencidas').textContent = Number(kpi.vencidas || 0).toLocaleString('es-CL');
            document.getElementById('kpi-total').textContent = Number(kpi.total || 0).toLocaleString('es-CL');
        }

        // ── Tabla agrupada por cliente ────────────────────────
        function renderTabla(cotizaciones) {
            const container = document.getElementById('tabla-container');

            if (cotizaciones.length === 0) {
                container.innerHTML = '<div class="p-10 text-center text-gray-400 text-sm">No hay cotizaciones para los filtros seleccionados.</div>';
                return;
            }

            // Agrupar por cliente
            const grupos = {};
            cotizaciones.forEach(c => {
                const key = c.cliente_id;
                if (!grupos[key]) grupos[key] = { id: c.cliente_id, nombre: c.cliente_nombre, items: [] };
                grupos[key].items.push(c);
            });

            let html = '<div class="overflow-x-auto"><table class="w-full">';
            html += `
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Cotización</th>
                        <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Servicio</th>
                        <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Mascota</th>
                        <th class="text-right px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="text-center px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-4 py-3 w-44"></th>
                    </tr>
                </thead>
                <tbody>`;

            Object.keys(grupos).forEach(clienteId => {
                const g = grupos[clienteId];
                html += `
                    <tr class="bg-gray-100/60 border-b border-gray-200">
                        <td colspan="7" class="px-4 py-2.5 text-sm font-bold text-gray-700">
                            <span class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                ${escHtml(g.nombre)}
                            </span>
                            <span class="text-xs font-normal text-gray-400 ml-2">${g.items.length} cotización(es)</span>
                        </td>
                    </tr>`;

                g.items.forEach(c => {
                    const est = estadoConfig[c.estado] || { cls: 'bg-gray-100 text-gray-600', label: c.estado };
                    html += `
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-sm font-semibold text-brand-700">${escHtml(c.numero_cotizacion)}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">${fmtFecha(c.created_at)}</td>
                        <td class="px-4 py-3 text-sm text-gray-800">${escHtml(c.servicio)}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">${escHtml(c.mascota_nombre)}${c.mascota_especie ? ' (' + escHtml(c.mascota_especie) + ')' : ''}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-right text-gray-800">${c.total > 0 ? fmtPeso(c.total) : '-'}</td>
                        <td class="px-4 py-3 text-center"><span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium ${est.cls}">${est.label}</span></td>
                        <td class="px-4 py-3 text-right">
                            <a href="editar_cotizacion.php?id=${c.id}" class="inline-block px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors mr-1">Editar</a>
                            <a href="ver_cotizacion.php?id=${c.id}" class="inline-block px-3 py-1.5 text-xs font-medium text-brand-600 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 rounded-md transition-colors">Ver</a>
                        </td>
                    </tr>`;
                });
            });

            html += '</tbody></table></div>';
            container.innerHTML = html;
        }

        // ── Eventos ────────────────────────────────────────────
        document.getElementById('btn-filtrar').addEventListener('click', cargarDatos);

        // Enter en buscador
        document.getElementById('input-search').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') cargarDatos();
        });

        // Carga inicial
        cargarDatos();
    </script>
</body>
</html>
