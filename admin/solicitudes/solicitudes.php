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
            <div class="p-6 md:p-10">

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Solicitudes de Cita</h1>
                        <p class="text-gray-500 mt-1">Revisa y aprueba las solicitudes enviadas por los clientes desde la página pública.</p>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="hidden sm:table-header-group">
                                <tr class="border-b border-gray-100 bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                                    <th class="px-5 py-3.5 font-semibold">#</th>
                                    <th class="px-5 py-3.5 font-semibold">Cliente</th>
                                    <th class="px-5 py-3.5 font-semibold">Contacto</th>
                                    <th class="px-5 py-3.5 font-semibold">Mascota</th>
                                    <th class="px-5 py-3.5 font-semibold">Servicio</th>
                                    <th class="px-5 py-3.5 font-semibold">Fecha Cita</th>
                                    <th class="px-5 py-3.5 font-semibold">Recibida</th>
                                    <th class="px-5 py-3.5 font-semibold text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-solicitudes-body">
                                <tr>
                                    <td colspan="8" class="px-5 py-12 text-center text-gray-400">
                                        <i class="fa-solid fa-spinner fa-spin text-xl"></i>
                                        <p class="mt-2">Cargando solicitudes...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Modal Revisar Solicitud -->
    <div id="modal-revisar" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/50"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fa-solid fa-pen-to-square text-brand-600 mr-2"></i>
                        Revisar Solicitud
                    </h3>
                    <button type="button" onclick="cerrarModalRevisar()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="form-revisar" class="p-6 space-y-5" onsubmit="return false">
                    <input type="hidden" id="rv-solicitud-id">

                    <!-- Cliente -->
                    <fieldset>
                        <legend class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-user text-brand-500"></i> Datos del Cliente
                        </legend>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Nombre Completo</label>
                                <input type="text" id="rv-nombre"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                                <input type="email" id="rv-email"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">RUT o Pasaporte</label>
                                <input type="text" id="rv-rut"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Celular</label>
                                <div class="flex">
                                    <span class="inline-flex items-center px-3 py-2 border border-r-0 border-gray-300 rounded-l-lg bg-gray-50 text-gray-500 text-xs select-none">+569</span>
                                    <input type="tel" id="rv-telefono" maxlength="8"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Mascota -->
                    <fieldset>
                        <legend class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-paw text-brand-500"></i> Datos de la Mascota
                        </legend>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Nombre</label>
                                <input type="text" id="rv-mascota-nombre"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Especie</label>
                                <input type="text" id="rv-mascota-especie"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Raza</label>
                                <input type="text" id="rv-mascota-raza"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Sexo</label>
                                <select id="rv-mascota-sexo"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                                    <option value="">—</option>
                                    <option>Macho</option>
                                    <option>Hembra</option>
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Cita -->
                    <fieldset>
                        <legend class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            <i class="fa-regular fa-calendar text-brand-500"></i> Detalles de la Cita
                        </legend>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Servicio</label>
                                <input type="text" id="rv-servicio"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Fecha y Hora</label>
                                <input type="datetime-local" id="rv-fecha"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Nota adicional</label>
                                <textarea id="rv-nota" rows="2"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition resize-none"></textarea>
                            </div>
                        </div>
                    </fieldset>
                </form>

                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                    <button type="button" onclick="cerrarModalRevisar()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                    <button type="button" id="btn-aceptar-desde-modal"
                        class="px-5 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition shadow-sm">
                        <i class="fa-solid fa-check mr-1"></i>
                        Aceptar y Generar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../scripts/sidebar.js"></script>
    <script>
    let solicitudesData = [];
    let solicitudActual = null;

    document.addEventListener('DOMContentLoaded', cargarSolicitudes);

    async function cargarSolicitudes() {
        const tbody = document.getElementById('tabla-solicitudes-body');
        try {
            const res = await fetch('api/listar_solicitudes.php');
            const json = await res.json();

            if (!json.ok || !json.data.length) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-gray-400">
                            <i class="fa-solid fa-inbox text-3xl mb-2"></i>
                            <p>No hay solicitudes pendientes.</p>
                        </td>
                    </tr>`;
                return;
            }

            solicitudesData = json.data;

            tbody.innerHTML = solicitudesData.map((s, i) => `
                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors" data-id="${s.id}">
                    <td class="px-5 py-4 text-gray-500 font-mono text-xs align-top hidden sm:table-cell">${i + 1}</td>
                    <td class="px-5 py-4 align-top">
                        <div class="font-medium text-gray-800">${escHtml(s.nombre_completo)}</div>
                        <div class="text-xs text-gray-400 mt-0.5">${escHtml(s.email)}</div>
                    </td>
                    <td class="px-5 py-4 align-top">
                        <div class="flex items-center gap-1.5 text-gray-600">
                            <i class="fa-solid fa-phone text-xs"></i>
                            <span>${escHtml(s.telefono || '—')}</span>
                        </div>
                    </td>
                    <td class="px-5 py-4 align-top">
                        <div class="font-medium text-gray-800">${escHtml(s.mascota_nombre)}</div>
                        <div class="text-xs text-gray-400">${escHtml(s.mascota_especie || '')}${s.mascota_raza ? ' · ' + escHtml(s.mascota_raza) : ''}</div>
                    </td>
                    <td class="px-5 py-4 align-top">
                        <span class="inline-block bg-brand-50 text-brand-700 text-xs font-medium px-2.5 py-1 rounded-full">
                            ${escHtml(s.servicio)}
                        </span>
                    </td>
                    <td class="px-5 py-4 align-top text-gray-600 whitespace-nowrap">
                        <i class="fa-regular fa-calendar text-xs mr-1"></i>
                        ${formatearFecha(s.fecha_cita)}
                    </td>
                    <td class="px-5 py-4 align-top text-gray-500 text-xs whitespace-nowrap">
                        ${formatearFecha(s.created_at)}
                    </td>
                    <td class="px-5 py-4 align-top text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <button onclick="abrirModalRevisar(${s.id})"
                                class="inline-flex items-center gap-1 bg-green-600 hover:bg-green-700 text-white text-xs font-medium px-3 py-2 rounded-lg transition-colors shadow-sm">
                                <i class="fa-solid fa-check"></i>
                                Aceptar
                            </button>
                            <button onclick="rechazarSolicitud(${s.id}, '${escHtml(s.nombre_completo)}')"
                                class="inline-flex items-center gap-1 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium px-3 py-2 rounded-lg transition-colors">
                                <i class="fa-solid fa-xmark"></i>
                                Rechazar
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');

        } catch (e) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="px-5 py-12 text-center text-red-400">
                        <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                        <p class="mt-2">Error al cargar solicitudes.</p>
                    </td>
                </tr>`;
        }
    }

    function escHtml(str) {
        if (!str) return '';
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function formatearFecha(f) {
        if (!f) return '—';
        const iso = f.includes('T') ? f : f.replace(' ', 'T');
        const d = new Date(iso);
        if (isNaN(d.getTime())) return '—';
        return d.toLocaleDateString('es-CL', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function toDatetimeLocal(mysql) {
        if (!mysql) return '';
        return mysql.replace(' ', 'T').substring(0, 16);
    }

    function toMysql(dt) {
        if (!dt) return '';
        return dt.replace('T', ' ') + ':00';
    }

    // ── Modal Revisar ──────────────────────────────────────────────────────

    function abrirModalRevisar(id) {
        const s = solicitudesData.find(x => x.id == id);
        if (!s) return;
        solicitudActual = s;

        document.getElementById('rv-solicitud-id').value = s.id;
        document.getElementById('rv-nombre').value = s.nombre_completo || '';
        document.getElementById('rv-email').value = s.email || '';
        document.getElementById('rv-rut').value = s.rut || '';
        document.getElementById('rv-telefono').value = s.telefono || '';
        document.getElementById('rv-mascota-nombre').value = s.mascota_nombre || '';
        document.getElementById('rv-mascota-especie').value = s.mascota_especie || '';
        document.getElementById('rv-mascota-raza').value = s.mascota_raza || '';
        document.getElementById('rv-mascota-sexo').value = s.mascota_sexo || '';
        document.getElementById('rv-servicio').value = s.servicio || '';
        document.getElementById('rv-fecha').value = toDatetimeLocal(s.fecha_cita);
        document.getElementById('rv-nota').value = s.nota || '';

        document.getElementById('modal-revisar').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function cerrarModalRevisar() {
        document.getElementById('modal-revisar').classList.add('hidden');
        document.body.style.overflow = '';
        solicitudActual = null;
    }

    document.getElementById('btn-aceptar-desde-modal').addEventListener('click', async function () {
        const id = document.getElementById('rv-solicitud-id').value;
        if (!id) return;

        const data = {
            solicitud_id: parseInt(id),
            nombre_completo: document.getElementById('rv-nombre').value.trim(),
            email: document.getElementById('rv-email').value.trim(),
            rut: document.getElementById('rv-rut').value.trim(),
            telefono: document.getElementById('rv-telefono').value.trim(),
            mascota_nombre: document.getElementById('rv-mascota-nombre').value.trim(),
            mascota_especie: document.getElementById('rv-mascota-especie').value.trim(),
            mascota_raza: document.getElementById('rv-mascota-raza').value.trim(),
            mascota_sexo: document.getElementById('rv-mascota-sexo').value,
            servicio: document.getElementById('rv-servicio').value.trim(),
            fecha_cita: toMysql(document.getElementById('rv-fecha').value),
            nota: document.getElementById('rv-nota').value.trim()
        };

        if (!data.nombre_completo || !data.email || !data.mascota_nombre || !data.servicio || !data.fecha_cita) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos requeridos',
                text: 'Nombre, email, mascota, servicio y fecha son obligatorios.',
                confirmButtonColor: '#0284c7',
                confirmButtonText: 'OK'
            });
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Procesando...';

        try {
            const res = await fetch('api/aceptar_solicitud.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const json = await res.json();

            if (json.ok) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Solicitud aceptada',
                    text: json.msg,
                    confirmButtonColor: '#0284c7',
                    confirmButtonText: 'OK'
                });
                cerrarModalRevisar();
                cargarSolicitudes();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: json.msg || 'No se pudo procesar la solicitud.',
                    confirmButtonColor: '#0284c7',
                    confirmButtonText: 'OK'
                });
            }
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor.',
                confirmButtonColor: '#0284c7',
                confirmButtonText: 'OK'
            });
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check mr-1"></i> Aceptar y Generar';
        }
    });

    // ── Rechazar ───────────────────────────────────────────────────────────

    async function rechazarSolicitud(solicitudId, nombre) {
        const { value: motivo } = await Swal.fire({
            title: '¿Rechazar solicitud?',
            html: `Se rechazará la solicitud de <strong>${escHtml(nombre)}</strong>.`,
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Opcional: motivo del rechazo...',
            inputAttributes: { rows: '3' },
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fa-solid fa-xmark mr-1"></i>Sí, rechazar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        });

        if (motivo === undefined) return;

        try {
            const res = await fetch('api/rechazar_solicitud.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ solicitud_id: solicitudId, motivo: motivo || '' })
            });
            const json = await res.json();

            if (json.ok) {
                await Swal.fire({
                    icon: 'info',
                    title: 'Solicitud rechazada',
                    text: json.msg,
                    confirmButtonColor: '#0284c7',
                    confirmButtonText: 'OK'
                });
                cargarSolicitudes();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: json.msg || 'No se pudo rechazar la solicitud.',
                    confirmButtonColor: '#0284c7',
                    confirmButtonText: 'OK'
                });
            }
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor.',
                confirmButtonColor: '#0284c7',
                confirmButtonText: 'OK'
            });
        }
    }

    // Cerrar modal con Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !document.getElementById('modal-revisar').classList.contains('hidden')) {
            cerrarModalRevisar();
        }
    });
    </script>
</body>
</html>
