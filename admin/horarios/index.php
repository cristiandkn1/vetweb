<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

$DIAS = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
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
                        <h1 class="text-2xl font-bold text-gray-800">Horarios</h1>
                        <p class="text-sm text-gray-500 mt-0.5">Gestión de turnos y horarios del personal</p>
                    </div>
                    <button id="btn-nuevo-empleado"
                        class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        Nuevo Empleado
                    </button>
                </div>

                <!-- Employee Selector + Schedule Editor -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

                    <!-- Employee List -->
                    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Empleados</h2>
                        </div>
                        <div id="lista-empleados" class="divide-y divide-gray-50">
                            <div class="p-4 text-center text-sm text-gray-400">Cargando...</div>
                        </div>
                    </div>

                    <!-- Schedule Editor -->
                    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide" id="editor-titulo">Selecciona un empleado</h2>
                            <span id="editor-empleado-color" class="w-4 h-4 rounded-full inline-block bg-gray-200"></span>
                        </div>
                        <div id="editor-contenido" class="p-4 text-center text-sm text-gray-400">
                            Selecciona un empleado de la lista para editar sus horarios.
                        </div>
                    </div>
                </div>

                <!-- Weekly Calendar View -->
                <div class="bg-white rounded-xl border border-gray-100 overflow-hidden mb-6">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Vista Semanal — Horarios</h2>
                    </div>
                    <div id="calendario-contenedor" class="overflow-x-auto p-4">
                        <div class="text-center text-sm text-gray-400">Cargando...</div>
                    </div>
                </div>

                <!-- Weekly Appointments Calendar -->
                <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between flex-wrap gap-2">
                        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Agenda de Citas</h2>
                        <div class="flex items-center gap-2 text-sm">
                            <button id="btn-semana-ant" class="px-2.5 py-1 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded transition-colors font-medium" title="Semana anterior">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <span id="semana-label" class="text-gray-600 font-medium min-w-[200px] text-center">—</span>
                            <button id="btn-semana-sig" class="px-2.5 py-1 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded transition-colors font-medium" title="Semana siguiente">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                            <button id="btn-semana-hoy" class="px-3 py-1 text-xs font-medium text-brand-600 bg-brand-50 hover:bg-brand-100 rounded transition-colors ml-1">Hoy</button>
                        </div>
                    </div>
                    <div id="calendario-citas" class="overflow-x-auto p-4">
                        <div class="text-center text-sm text-gray-400">Cargando...</div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Modal Empleado -->
    <div id="modal-empleado" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800" id="modal-title">Nuevo Empleado</h3>
                <button type="button" class="cerrar-modal text-gray-300 hover:text-gray-500 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="form-empleado" class="p-6 space-y-4">
                <input type="hidden" name="id" id="emp-id" value="0">
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Nombre *</label>
                    <input type="text" name="nombre" id="emp-nombre" required
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="color" id="emp-color" value="#6366f1"
                            class="w-10 h-10 rounded border border-gray-200 cursor-pointer p-0.5">
                        <span class="text-sm text-gray-400" id="emp-color-text">#6366f1</span>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Teléfono</label>
                    <input type="text" name="telefono" id="emp-telefono"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Especialidad</label>
                    <input type="text" name="especialidad" id="emp-especialidad"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        placeholder="Ej: Veterinaria, Peluquería, Recepción...">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="activo" id="emp-activo" value="1" checked
                        class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    <label for="emp-activo" class="text-sm text-gray-600">Empleado activo</label>
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
        const DIAS = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        const API = '/admin/horarios/api';

        let empleados = [];
        let empleadoSeleccionado = null;

        // ── Empleados ────────────────────────────────────────
        async function cargarEmpleados() {
            const r = await fetch(`${API}/listar_empleados.php`);
            const d = await r.json();
            if (!d.success) return;
            empleados = d.empleados;
            renderEmpleados();
        }

        function renderEmpleados() {
            const c = document.getElementById('lista-empleados');
            if (empleados.length === 0) {
                c.innerHTML = '<div class="p-4 text-center text-sm text-gray-400">Sin empleados registrados.</div>';
                return;
            }
            c.innerHTML = empleados.map(e => `
                <div class="px-4 py-3 flex items-center gap-3 hover:bg-gray-50 cursor-pointer transition-colors empleado-item ${empleadoSeleccionado === e.id ? 'bg-brand-50' : ''}"
                     data-id="${e.id}">
                    <span class="w-3 h-3 rounded-full shrink-0" style="background:${e.color}"></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">${escHtml(e.nombre)}</p>
                        <p class="text-[11px] text-gray-400">${e.especialidad ? escHtml(e.especialidad) : 'Sin especialidad'}${e.activo ? '' : ' · Inactivo'}</p>
                    </div>
                    <div class="flex gap-1 shrink-0">
                        <button class="btn-editar-emp p-1.5 text-gray-300 hover:text-brand-600 transition-colors" data-id="${e.id}" title="Editar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button class="btn-eliminar-emp p-1.5 text-gray-300 hover:text-red-500 transition-colors" data-id="${e.id}" title="Eliminar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            `).join('');

            // Select employee
            c.querySelectorAll('.empleado-item').forEach(el => {
                el.addEventListener('click', (e) => {
                    if (e.target.closest('button')) return;
                    seleccionarEmpleado(parseInt(el.dataset.id));
                });
            });
            // Edit/delete buttons
            c.querySelectorAll('.btn-editar-emp').forEach(b => {
                b.addEventListener('click', () => abrirModalEmpleado(parseInt(b.dataset.id)));
            });
            c.querySelectorAll('.btn-eliminar-emp').forEach(b => {
                b.addEventListener('click', async () => {
                    if (!confirm('¿Eliminar este empleado?')) return;
                    const fd = new FormData(); fd.append('id', b.dataset.id);
                    const r = await fetch(`${API}/eliminar_empleado.php`, { method: 'POST', body: fd });
                    const d = await r.json();
                    if (d.success) {
                        if (empleadoSeleccionado === parseInt(b.dataset.id)) empleadoSeleccionado = null;
                        cargarEmpleados();
                        cargarCalendario();
                    }
                });
            });
        }

        function escHtml(s) { if (!s) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

        // ── Modal Empleado ────────────────────────────────────
        function abrirModalEmpleado(id) {
            const esNuevo = !id;
            document.getElementById('modal-title').textContent = esNuevo ? 'Nuevo Empleado' : 'Editar Empleado';
            document.getElementById('emp-id').value = esNuevo ? 0 : id;
            document.getElementById('emp-nombre').value = esNuevo ? '' : empleados.find(e => e.id === id).nombre || '';
            document.getElementById('emp-color').value = esNuevo ? '#6366f1' : empleados.find(e => e.id === id).color || '#6366f1';
            document.getElementById('emp-color-text').textContent = document.getElementById('emp-color').value;
            document.getElementById('emp-telefono').value = esNuevo ? '' : empleados.find(e => e.id === id).telefono || '';
            document.getElementById('emp-especialidad').value = esNuevo ? '' : empleados.find(e => e.id === id).especialidad || '';
            document.getElementById('emp-activo').checked = esNuevo ? true : !!parseInt(empleados.find(e => e.id === id).activo || 1);
            document.getElementById('modal-empleado').classList.remove('hidden');
        }

        document.getElementById('btn-nuevo-empleado').addEventListener('click', () => abrirModalEmpleado(0));

        document.querySelectorAll('.cerrar-modal').forEach(b => {
            b.addEventListener('click', () => document.getElementById('modal-empleado').classList.add('hidden'));
        });
        document.getElementById('modal-empleado').addEventListener('click', (e) => {
            if (e.target === e.currentTarget) e.currentTarget.classList.add('hidden');
        });

        document.getElementById('emp-color').addEventListener('input', function() {
            document.getElementById('emp-color-text').textContent = this.value;
        });

        document.getElementById('form-empleado').addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            if (!fd.get('activo')) fd.set('activo', '0');
            const r = await fetch(`${API}/guardar_empleado.php`, { method: 'POST', body: fd });
            const d = await r.json();
            if (d.success) {
                document.getElementById('modal-empleado').classList.add('hidden');
                await cargarEmpleados();
                if (empleadoSeleccionado) await cargarEditor(empleadoSeleccionado);
                await cargarCalendario();
            } else {
                alert(d.message || 'Error al guardar.');
            }
        });

        // ── Seleccionar empleado para editar horario ─────────
        function seleccionarEmpleado(id) {
            empleadoSeleccionado = id;
            renderEmpleados();
            cargarEditor(id);
        }

        async function cargarEditor(empleadoId) {
            const emp = empleados.find(e => e.id === empleadoId);
            const cont = document.getElementById('editor-contenido');
            if (!emp) {
                document.getElementById('editor-titulo').textContent = 'Selecciona un empleado';
                document.getElementById('editor-empleado-color').style.background = '#d1d5db';
                cont.innerHTML = '<div class="text-center text-sm text-gray-400">Selecciona un empleado de la lista.</div>';
                return;
            }

            document.getElementById('editor-titulo').textContent = emp.nombre;
            document.getElementById('editor-empleado-color').style.background = emp.color;

            const r = await fetch(`${API}/obtener_horarios.php?empleado_id=${empleadoId}`);
            const d = await r.json();
            const horarios = d.success ? d.horarios : [];

            // Horarios agrupados por día
            const porDia = {};
            DIAS.forEach((_, i) => porDia[i] = []);
            horarios.forEach(h => porDia[h.dia_semana].push(h));

            let html = '<div class="space-y-4">';
            DIAS.forEach((dia, i) => {
                const blocks = porDia[i];
                html += `
                    <div class="flex items-start gap-3">
                        <div class="w-20 shrink-0 pt-1.5">
                            <span class="text-xs font-bold text-gray-500 uppercase">${dia.substring(0,3)}</span>
                        </div>
                        <div class="flex-1 flex flex-wrap gap-2" id="bloques-${i}">`;
                if (blocks.length === 0) {
                    html += `<span class="text-xs text-gray-300 italic">Sin horario</span>`;
                } else {
                    blocks.forEach(b => {
                        const esCol = b.tipo === 'colacion';
                        html += `
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium ${esCol ? 'bg-orange-50 text-orange-600 border border-orange-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'}"
                                  title="${esCol ? 'Colación' : 'Trabajo'}">
                                ${b.hora_inicio.substring(0,5)}–${b.hora_fin.substring(0,5)}
                                ${esCol ? '🍴' : ''}
                                <button class="btn-del-horario text-gray-300 hover:text-red-500 ml-0.5" data-id="${b.id}" title="Eliminar">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </span>`;
                    });
                }
                html += `
                        </div>
                        <button class="btn-agregar-bloque shrink-0 px-2 py-1 text-xs font-medium text-brand-600 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 rounded transition-colors"
                                data-dia="${i}">
                            + Agregar
                        </button>
                    </div>`;
            });
            html += '</div>';
            cont.innerHTML = html;

            // Event listeners: delete block
            cont.querySelectorAll('.btn-del-horario').forEach(b => {
                b.addEventListener('click', async () => {
                    const fd = new FormData(); fd.append('id', b.dataset.id);
                    const r = await fetch(`${API}/eliminar_horario.php`, { method: 'POST', body: fd });
                    const d = await r.json();
                    if (d.success) { cargarEditor(empleadoId); cargarCalendario(); }
                });
            });

            // Event listeners: add block
            cont.querySelectorAll('.btn-agregar-bloque').forEach(b => {
                b.addEventListener('click', () => agregarBloque(empleadoId, parseInt(b.dataset.dia)));
            });
        }

        function agregarBloque(empleadoId, dia) {
            const cont = document.getElementById('editor-contenido');
            const existing = cont.querySelector(`#bloques-${dia}`);

            const div = document.createElement('div');
            div.className = 'flex items-center gap-1.5 mt-1';
            div.innerHTML = `
                <input type="time" value="09:00" class="h-inicio w-24 px-1.5 py-1 text-xs border border-gray-200 rounded focus:border-brand-500 focus:ring-brand-500">
                <span class="text-xs text-gray-400">–</span>
                <input type="time" value="18:00" class="h-fin w-24 px-1.5 py-1 text-xs border border-gray-200 rounded focus:border-brand-500 focus:ring-brand-500">
                <select class="h-tipo text-xs border border-gray-200 rounded px-1 py-1 bg-white">
                    <option value="trabajo">Trabajo</option>
                    <option value="colacion">Colación</option>
                </select>
                <button class="btn-guardar-bloque px-2 py-1 text-xs font-medium text-white bg-brand-600 hover:bg-brand-700 rounded transition-colors">Guardar</button>
                <button class="btn-cancelar-bloque px-2 py-1 text-xs font-medium text-gray-500 bg-gray-100 hover:bg-gray-200 rounded transition-colors">Cancelar</button>
            `;

            if (existing) {
                existing.appendChild(div);
            } else {
                // If no block container, create one
                const parent = cont.querySelector(`.flex.items-start.gap-3:nth-child(${dia + 1}) .flex-1`);
                if (parent) {
                    // Remove "Sin horario" text if present
                    const italic = parent.querySelector('span.text-gray-300');
                    if (italic) italic.remove();
                    parent.appendChild(div);
                } else {
                    cont.querySelector('.space-y-4').children[dia].querySelector('.flex-1').appendChild(div);
                }
            }

            div.querySelector('.btn-guardar-bloque').addEventListener('click', async () => {
                const fd = new FormData();
                fd.append('empleado_id', empleadoId);
                fd.append('dia_semana', dia);
                fd.append('hora_inicio', div.querySelector('.h-inicio').value);
                fd.append('hora_fin', div.querySelector('.h-fin').value);
                fd.append('tipo', div.querySelector('.h-tipo').value);
                const r = await fetch(`${API}/guardar_horario.php`, { method: 'POST', body: fd });
                const d = await r.json();
                if (d.success) { cargarEditor(empleadoId); cargarCalendario(); }
                else { alert(d.message || 'Error'); }
            });

            div.querySelector('.btn-cancelar-bloque').addEventListener('click', () => div.remove());
        }

        // ── Calendario Semanal ────────────────────────────────
        async function cargarCalendario() {
            const cont = document.getElementById('calendario-contenedor');
            const r = await fetch(`${API}/obtener_horarios.php`);
            const d = await r.json();
            if (!d.success) { cont.innerHTML = '<div class="text-center text-sm text-gray-400">Error al cargar.</div>'; return; }

            const horarios = d.horarios;

            // Agrupar: empleado_id -> dia_semana -> [horarios]
            const empData = {};
            horarios.forEach(h => {
                if (!empData[h.empleado_id]) empData[h.empleado_id] = { nombre: h.empleado_nombre, color: h.empleado_color, dias: {} };
                if (!empData[h.empleado_id].dias[h.dia_semana]) empData[h.empleado_id].dias[h.dia_semana] = [];
                empData[h.empleado_id].dias[h.dia_semana].push(h);
            });

            const empIds = Object.keys(empData);
            if (empIds.length === 0) {
                cont.innerHTML = '<div class="text-center text-sm text-gray-400">Sin horarios registrados. Agrega empleados y asigna sus horarios.</div>';
                return;
            }

            let html = '<table class="w-full min-w-[700px]"><thead><tr class="bg-gray-50 border-b border-gray-200">';
            html += '<th class="text-left px-3 py-2 text-xs font-bold text-gray-500 uppercase tracking-wider w-36">Empleado</th>';
            DIAS.forEach(d => {
                html += `<th class="text-center px-2 py-2 text-xs font-bold text-gray-500 uppercase tracking-wider">${d.substring(0,3)}</th>`;
            });
            html += '</tr></thead><tbody>';

            empIds.forEach(eid => {
                const ed = empData[eid];
                html += `<tr class="border-b border-gray-100 hover:bg-gray-50/50">`;
                html += `<td class="px-3 py-3 text-sm font-medium text-gray-700 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:${ed.color}"></span>
                            ${escHtml(ed.nombre)}
                         </td>`;
                DIAS.forEach((_, dia) => {
                    const blocks = ed.dias[dia] || [];
                    html += `<td class="px-2 py-3 text-center align-top">`;
                    if (blocks.length === 0) {
                        html += `<span class="text-[11px] text-gray-200">–</span>`;
                    } else {
                        const workBlocks = blocks.filter(b => b.tipo === 'trabajo');
                        const breakBlocks = blocks.filter(b => b.tipo === 'colacion');
                        workBlocks.forEach(b => {
                            html += `<span class="inline-block text-[11px] font-medium text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded mb-0.5 border border-emerald-100">${b.hora_inicio.substring(0,5)}–${b.hora_fin.substring(0,5)}</span>`;
                            if (workBlocks.length > 1 || breakBlocks.length > 0) html += `<br>`;
                        });
                        breakBlocks.forEach(b => {
                            html += `<span class="inline-block text-[11px] font-medium text-orange-600 bg-orange-50 px-1.5 py-0.5 rounded mb-0.5 border border-orange-100">🍴 ${b.hora_inicio.substring(0,5)}–${b.hora_fin.substring(0,5)}</span>`;
                            if (breakBlocks.length > 1) html += `<br>`;
                        });
                    }
                    html += `</td>`;
                });
                html += `</tr>`;
            });

            html += '</tbody></table>';
            cont.innerHTML = html;
        }

        // ── Agenda de Citas ──────────────────────────────────
        const ESTADO_COLORS = {
            pendiente: 'bg-amber-50 text-amber-700 border-amber-200',
            confirmada: 'bg-blue-50 text-blue-700 border-blue-200',
            completada: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            cancelada: 'bg-gray-50 text-gray-400 border-gray-200'
        };
        const ESTADO_LABELS = {
            pendiente: 'Pendiente',
            confirmada: 'Confirmada',
            completada: 'Completada',
            cancelada: 'Cancelada'
        };

        let semanaActual = (() => {
            const d = new Date();
            const dia = d.getDay();
            const diff = d.getDate() - dia + (dia === 0 ? -6 : 1);
            d.setDate(diff);
            return d;
        })();

        function formatearFecha(d) {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const dia = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${dia}`;
        }

        function formatearFechaLegible(d) {
            const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            return `${d.getDate()} ${meses[d.getMonth()]} ${d.getFullYear()}`;
        }

        function obtenerLunes(d) {
            const dia = d.getDay();
            const diff = d.getDate() - dia + (dia === 0 ? -6 : 1);
            const lunes = new Date(d);
            lunes.setDate(diff);
            lunes.setHours(0,0,0,0);
            return lunes;
        }

        function sumarDias(d, n) {
            const r = new Date(d);
            r.setDate(r.getDate() + n);
            return r;
        }

        async function cargarCitasSemana() {
            const lunes = obtenerLunes(semanaActual);

            document.getElementById('semana-label').textContent =
                `Semana del ${formatearFechaLegible(lunes)}`;

            const r = await fetch(`/admin/horarios/api/listar_citas_semana.php?semana_inicio=${formatearFecha(lunes)}`);
            const d = await r.json();

            if (!d.success) {
                document.getElementById('calendario-citas').innerHTML = '<div class="text-center text-sm text-gray-400">Error al cargar citas.</div>';
                return;
            }

            renderizarCitasSemana(d.citas, lunes);
        }

        function renderizarCitasSemana(citas, lunes) {
            const cont = document.getElementById('calendario-citas');

            const diasFecha = [];
            for (let i = 0; i < 7; i++) {
                diasFecha.push(formatearFecha(sumarDias(lunes, i)));
            }

            // Agrupar citas por día (0 = lunes, 6 = domingo)
            const porDia = {};
            for (let i = 0; i < 7; i++) porDia[i] = [];

            citas.forEach(c => {
                const fechaCita = c.fecha.substring(0, 10);
                const idx = diasFecha.indexOf(fechaCita);
                if (idx >= 0) porDia[idx].push(c);
            });

            const hoy = new Date();
            hoy.setHours(0,0,0,0);

            let html = '<div class="grid grid-cols-7 gap-2 min-w-[700px]">';
            const diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

            diasSemana.forEach((nombre, i) => {
                const fecha = sumarDias(lunes, i);
                const esHoy = formatearFecha(fecha) === formatearFecha(hoy);
                const esFinde = i >= 5;
                const cells = porDia[i];

                html += `<div class="${esFinde ? 'bg-gray-50/50' : 'bg-white'} rounded-lg border ${esHoy ? 'border-brand-400 ring-2 ring-brand-100' : 'border-gray-100'} overflow-hidden">`;
                html += `<div class="px-2 py-1.5 text-center border-b ${esHoy ? 'bg-brand-500 text-white' : 'bg-gray-50 text-gray-500'} text-[11px] font-bold uppercase tracking-wider">`;
                html += `${nombre}<br><span class="${esHoy ? 'text-white' : 'text-gray-400'} text-[10px] font-normal">${formatearFechaLegible(fecha)}</span>`;
                html += `</div>`;
                html += `<div class="p-1.5 space-y-1 min-h-[80px]">`;

                if (cells.length === 0) {
                    html += `<div class="text-[10px] text-gray-200 text-center py-2">—</div>`;
                } else {
                    cells.forEach(c => {
                        const hora = c.fecha.substring(11, 16);
                        const estadoClass = ESTADO_COLORS[c.estado] || ESTADO_COLORS.pendiente;
                        const estadoLabel = ESTADO_LABELS[c.estado] || c.estado;
                        html += `
                            <div class="text-[11px] bg-white border border-gray-100 rounded-md px-2 py-1.5 hover:shadow-sm transition-shadow cursor-default">
                                <div class="font-bold text-gray-700">${hora}</div>
                                <div class="text-gray-800 truncate">${escHtml(c.cliente_nombre)}</div>
                                <div class="text-gray-400 truncate">${escHtml(c.mascota_nombre)} ${c.mascota_especie ? '· '+escHtml(c.mascota_especie) : ''}</div>
                                <div class="text-gray-400 truncate">${escHtml(c.tipo)}</div>
                                <div class="inline-block px-1.5 py-0.5 rounded text-[10px] font-medium border ${estadoClass} mt-1">
                                    ${estadoLabel}
                                </div>
                            </div>`;
                    });
                }

                html += `</div></div>`;
            });

            html += '</div>';
            cont.innerHTML = html;
        }

        document.getElementById('btn-semana-ant').addEventListener('click', () => {
            semanaActual.setDate(semanaActual.getDate() - 7);
            cargarCitasSemana();
        });

        document.getElementById('btn-semana-sig').addEventListener('click', () => {
            semanaActual.setDate(semanaActual.getDate() + 7);
            cargarCitasSemana();
        });

        document.getElementById('btn-semana-hoy').addEventListener('click', () => {
            semanaActual = obtenerLunes(new Date());
            cargarCitasSemana();
        });

        // ── Init ──────────────────────────────────────────────
        cargarEmpleados();
        cargarCalendario();
        cargarCitasSemana();
    </script>
</body>
</html>
