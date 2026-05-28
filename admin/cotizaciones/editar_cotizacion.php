<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

require_once __DIR__ . '/../../includes/db.php';

$id = intval($_GET['id'] ?? 0);
if ($id === 0) {
    die("ID de cotización inválido.");
}

$stmt = $pdo->prepare("
    SELECT 
        co.*,
        cl.nombre_completo AS cliente_nombre,
        cl.telefono AS cliente_telefono,
        cl.email AS cliente_email,
        m.nombre AS mascota_nombre,
        m.especie AS mascota_especie,
        m.raza AS mascota_raza
    FROM cotizaciones co
    JOIN cliente cl ON co.cliente_id = cl.id
    JOIN mascota m ON co.mascota_id = m.id
    WHERE co.id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$cot = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cot) {
    die("Cotización no encontrada.");
}

$stmtDet = $pdo->prepare("SELECT * FROM cotizacion_detalles WHERE cotizacion_id = ? ORDER BY id ASC");
$stmtDet->execute([$id]);
$detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

if (empty($detalles) && !empty($cot['servicio'])) {
    $precio = max((float)($cot['precio_estimado_min'] ?? 0), (float)($cot['precio_estimado_max'] ?? 0));
    $detalles[] = [
        'id' => 0,
        'descripcion' => $cot['servicio'],
        'cantidad' => 1,
        'precio_unitario' => $precio > 0 ? $precio : 0,
        'afecto_iva' => 1,
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../includes/head.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<body class="bg-gray-100 font-sans">
    <?php include '../../includes/mobile-header.php'; ?>
    <div class="flex h-screen overflow-hidden">
        <?php include '../../includes/sidebar.php'; ?>
        <main class="flex-1 flex flex-col min-w-0 bg-gray-100 overflow-y-auto">
            <div class="p-6 md:p-10 max-w-5xl mx-auto w-full">

                <!-- Header -->
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <a href="cotizaciones.php" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5 mb-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            Volver
                        </a>
                        <h1 class="text-2xl font-bold text-gray-800">Editar Cotización</h1>
                        <p class="text-gray-500 text-sm"><?php echo htmlspecialchars($cot['numero_cotizacion']); ?></p>
                    </div>
                    <div class="flex gap-2">
                        <a href="ver_cotizacion.php?id=<?php echo $id; ?>"
                            class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            Vista Previa
                        </a>
                        <button id="btn-guardar"
                            class="px-5 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors">
                            Guardar Cambios
                        </button>
                    </div>
                </div>

                <!-- Info Cliente / Mascota -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Cliente</span>
                            <p class="font-semibold text-gray-800 mt-1"><?php echo htmlspecialchars($cot['cliente_nombre']); ?></p>
                            <p class="text-gray-500"><?php echo htmlspecialchars($cot['cliente_telefono'] ?? ''); ?></p>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Paciente</span>
                            <p class="font-semibold text-gray-800 mt-1"><?php echo htmlspecialchars($cot['mascota_nombre']); ?></p>
                            <p class="text-gray-500"><?php echo htmlspecialchars(ucfirst($cot['mascota_especie'] ?? '')); ?><?php echo !empty($cot['mascota_raza']) ? ' - ' . htmlspecialchars($cot['mascota_raza']) : ''; ?></p>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Estado</span>
                            <select id="estado-cot" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2 bg-white">
                                <option value="pendiente" <?php echo $cot['estado'] === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="aprobada" <?php echo $cot['estado'] === 'aprobada' ? 'selected' : ''; ?>>Aprobada</option>
                                <option value="rechazada" <?php echo $cot['estado'] === 'rechazada' ? 'selected' : ''; ?>>Rechazada</option>
                                <option value="vencida" <?php echo $cot['estado'] === 'vencida' ? 'selected' : ''; ?>>Vencida</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Editor de Items -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Servicios / Productos</h2>
                        <div class="flex gap-2">
                            <button id="btn-agregar-producto"
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-600 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                Agregar Producto
                            </button>
                            <button id="btn-agregar-fila"
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-brand-600 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 px-3 py-1.5 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Agregar Fila
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full" id="tabla-detalles">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-10">#</th>
                                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Descripción</th>
                                    <th class="text-center px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-20">Cant.</th>
                                    <th class="text-right px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-40">Precio <span class="font-normal normal-case">(sin IVA)</span></th>
                                    <th class="text-center px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-16">IVA</th>
                                    <th class="text-right px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-40">Subtotal</th>
                                    <th class="px-4 py-3 w-16"></th>
                                </tr>
                            </thead>
                            <tbody id="detalles-body">
                                <?php foreach ($detalles as $i => $d):
                                    $checked = (int)($d['afecto_iva'] ?? 1) ? 'checked' : '';
                                ?>
                                <tr class="border-b border-gray-100 detalle-row">
                                    <td class="px-4 py-2 text-center text-sm text-gray-400 font-mono"><?php echo $i + 1; ?></td>
                                    <td class="px-4 py-2">
                                        <input type="text" value="<?php echo htmlspecialchars($d['descripcion']); ?>"
                                            class="detalle-desc w-full border-0 bg-transparent focus:ring-0 focus:outline-none text-sm text-gray-800 placeholder-gray-300"
                                            placeholder="Ej: Consulta General, Vacuna, etc...">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" value="<?php echo (int)($d['cantidad']); ?>" min="1"
                                            class="detalle-cant w-full text-center border border-gray-200 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm p-1.5">
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                                            <input type="number" value="<?php echo (float)($d['precio_unitario']); ?>" min="0" step="1"
                                                class="detalle-precio w-full pl-7 pr-3 py-1.5 border border-gray-200 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm text-right">
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox"
                                                class="detalle-iva sr-only peer"
                                                <?php echo $checked; ?>>
                                            <span class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500 relative inline-block shrink-0"></span>
                                        </label>
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <span class="detalle-subtotal text-sm font-semibold text-gray-800">$0</span>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <button type="button" class="btn-eliminar-fila p-1.5 text-gray-300 hover:text-red-500 transition-colors" title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Total -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                        <div class="text-right">
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-1">Total</p>
                            <p class="text-3xl font-black text-gray-800" id="total-general">$0</p>
                        </div>
                    </div>
                </div>

                <!-- Notas -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-2">Notas / Observaciones</label>
                    <textarea id="nota-cot" rows="3"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2"
                        placeholder="Condiciones, detalles adicionales..."><?php echo htmlspecialchars($cot['nota'] ?? ''); ?></textarea>
                </div>

            </div>
        </main>
    </div>

    <!-- Modal seleccionar producto del inventario -->
    <div id="modal-productos" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-xl max-h-[80vh] flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                <h3 class="text-lg font-bold text-gray-800">Seleccionar Producto</h3>
                <button type="button" class="cerrar-modal-prod text-gray-300 hover:text-gray-500 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-6 py-3 border-b border-gray-50 shrink-0">
                <input type="text" id="buscador-inventario" placeholder="Buscar producto..."
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
            </div>
            <div class="flex-1 overflow-y-auto p-4" id="lista-productos">
                <div class="text-center text-sm text-gray-400 py-8">Cargando...</div>
            </div>
            <div class="px-6 py-3 border-t border-gray-100 shrink-0 text-right">
                <button type="button" class="cerrar-modal-prod px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Cancelar</button>
            </div>
        </div>
    </div>

    <script src="../scripts/sidebar.js"></script>
    <script>
        const COTIZACION_ID = <?php echo $id; ?>;

        function fmtPeso(n) {
            return '$' + Math.round(n).toLocaleString('es-CL');
        }

        function calcularFila(row) {
            const cant = parseInt(row.querySelector('.detalle-cant').value) || 1;
            const precio = parseFloat(row.querySelector('.detalle-precio').value) || 0;
            const ivaOn = row.querySelector('.detalle-iva').checked;
            const factor = ivaOn ? 1.19 : 1;
            const sub = Math.round(cant * precio * factor);
            row.querySelector('.detalle-subtotal').textContent = fmtPeso(sub);
            return sub;
        }

        function recalcularTodo() {
            const rows = document.querySelectorAll('.detalle-row');
            let total = 0;
            rows.forEach((row, i) => {
                row.querySelector('td:first-child').textContent = i + 1;
                total += calcularFila(row);
            });
            document.getElementById('total-general').textContent = fmtPeso(total);
        }

        // ── Agregar fila ─────────────────────────────────────────────────
        document.getElementById('btn-agregar-fila').addEventListener('click', () => {
            const tbody = document.getElementById('detalles-body');
            const tr = document.createElement('tr');
            tr.className = 'border-b border-gray-100 detalle-row';
            tr.innerHTML = `
                <td class="px-4 py-2 text-center text-sm text-gray-400 font-mono">${tbody.children.length + 1}</td>
                <td class="px-4 py-2">
                    <input type="text" value=""
                        class="detalle-desc w-full border-0 bg-transparent focus:ring-0 focus:outline-none text-sm text-gray-800 placeholder-gray-300"
                        placeholder="Ej: Consulta General, Vacuna, etc...">
                </td>
                <td class="px-4 py-2">
                    <input type="number" value="1" min="1"
                        class="detalle-cant w-full text-center border border-gray-200 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm p-1.5">
                </td>
                <td class="px-4 py-2">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                        <input type="number" value="0" min="0" step="1"
                            class="detalle-precio w-full pl-7 pr-3 py-1.5 border border-gray-200 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm text-right">
                    </div>
                </td>
                <td class="px-4 py-2 text-center">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" checked
                            class="detalle-iva sr-only peer">
                        <span class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500 relative inline-block shrink-0"></span>
                    </label>
                </td>
                <td class="px-4 py-2 text-right">
                    <span class="detalle-subtotal text-sm font-semibold text-gray-800">$0</span>
                </td>
                <td class="px-4 py-2 text-center">
                    <button type="button" class="btn-eliminar-fila p-1.5 text-gray-300 hover:text-red-500 transition-colors" title="Eliminar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
            tr.querySelector('.detalle-cant').addEventListener('input', recalcularTodo);
            tr.querySelector('.detalle-precio').addEventListener('input', recalcularTodo);
            tr.querySelector('.detalle-iva').addEventListener('change', recalcularTodo);
            tr.querySelector('.btn-eliminar-fila').addEventListener('click', async () => {
                if (document.querySelectorAll('.detalle-row').length <= 1) {
                    alert('Debe haber al menos un servicio.');
                    return;
                }
                const { isConfirmed: conf } = await Swal.fire({ title: '¿Eliminar?', text: '¿Eliminar este Servicio/Producto de la cotización?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280', confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar', reverseButtons: true }); if (conf) {
                    tr.remove();
                    recalcularTodo();
                }
            });
            recalcularTodo();
        });

        // ── Event listeners en filas existentes ──────────────────────────
        document.querySelectorAll('.detalle-row').forEach(row => {
            row.querySelector('.detalle-cant').addEventListener('input', recalcularTodo);
            row.querySelector('.detalle-precio').addEventListener('input', recalcularTodo);
            row.querySelector('.detalle-iva').addEventListener('change', recalcularTodo);
            row.querySelector('.btn-eliminar-fila').addEventListener('click', async () => {
                if (document.querySelectorAll('.detalle-row').length <= 1) {
                    alert('Debe haber al menos un servicio.');
                    return;
                }
                const { isConfirmed: conf } = await Swal.fire({ title: '¿Eliminar?', text: '¿Eliminar este Servicio/Producto de la cotización?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280', confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar', reverseButtons: true }); if (conf) {
                    row.remove();
                    recalcularTodo();
                }
            });
        });

        recalcularTodo();

        // ── Guardar ──────────────────────────────────────────────────────
        document.getElementById('btn-guardar').addEventListener('click', async () => {
            const detalles = [];
            document.querySelectorAll('.detalle-row').forEach(row => {
                const desc = row.querySelector('.detalle-desc').value.trim();
                if (!desc) return;
                detalles.push({
                    descripcion: desc,
                    cantidad: parseInt(row.querySelector('.detalle-cant').value) || 1,
                    precio_unitario: parseFloat(row.querySelector('.detalle-precio').value) || 0,
                    afecto_iva: row.querySelector('.detalle-iva').checked ? 1 : 0,
                });
            });

            if (detalles.length === 0) {
                alert('Agrega al menos un servicio.');
                return;
            }

            const btn = document.getElementById('btn-guardar');
            btn.disabled = true;
            btn.textContent = 'Guardando...';

            try {
                const fd = new FormData();
                fd.append('id', COTIZACION_ID);
                fd.append('estado', document.getElementById('estado-cot').value);
                fd.append('nota', document.getElementById('nota-cot').value);
                fd.append('detalles', JSON.stringify(detalles));

                const res = await fetch('api/guardar_cotizacion.php', { method: 'POST', body: fd });
                const data = await res.json();

                if (data.success) {
                    btn.textContent = '¡Guardado!';
                    btn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
                    btn.classList.add('bg-green-500');
                    setTimeout(() => {
                        btn.textContent = 'Guardar Cambios';
                        btn.classList.remove('bg-green-500');
                        btn.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
                    }, 2000);
                } else {
                    alert(data.message || 'Error al guardar.');
                }
            } catch {
                alert('Error de red.');
            } finally {
                btn.disabled = false;
                if (btn.textContent === 'Guardando...') btn.textContent = 'Guardar Cambios';
            }
        });

        // ── Modal Productos del Inventario ──────────────────────────────────
        async function cargarProductos(query) {
            const cont = document.getElementById('lista-productos');
            let url = '/admin/inventario/api/listar.php';
            if (query) url += '?search=' + encodeURIComponent(query);
            try {
                const r = await fetch(url);
                const d = await r.json();
                if (!d.success) {
                    cont.innerHTML = '<div class="text-center text-sm text-red-400 py-8">Error al cargar.</div>';
                    return;
                }
                const items = d.items.filter(i => i.cantidad > 0);
                if (items.length === 0) {
                    cont.innerHTML = '<div class="text-center text-sm text-gray-400 py-8">Sin productos con stock disponible.</div>';
                    return;
                }
                cont.innerHTML = items.map(i => {
                    const precio = i.precio ? '$' + Math.round(i.precio).toLocaleString('es-CL') : '—';
                    return `<div class="flex items-center justify-between px-4 py-3 rounded-lg hover:bg-brand-50 cursor-pointer transition-colors border border-transparent hover:border-brand-100 mb-1 producto-item"
                                data-nombre="${escHtml(i.nombre)}" data-precio="${i.precio || 0}">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800">${escHtml(i.nombre)}</p>
                                <p class="text-xs text-gray-400">Stock: ${i.cantidad} · ${CATEGORIA_LABELS[i.categoria] || i.categoria}</p>
                            </div>
                            <div class="text-sm font-semibold text-gray-700 ml-4">${precio}</div>
                        </div>`;
                }).join('');
                cont.querySelectorAll('.producto-item').forEach(el => {
                    el.addEventListener('click', () => {
                        agregarProductoACot(el.dataset.nombre, parseFloat(el.dataset.precio));
                    });
                });
            } catch {
                cont.innerHTML = '<div class="text-center text-sm text-red-400 py-8">Error de conexión.</div>';
            }
        }

        async function agregarProductoACot(nombre, precio) {
            document.getElementById('modal-productos').classList.add('hidden');
            const tbody = document.getElementById('detalles-body');
            const tr = document.createElement('tr');
            tr.className = 'border-b border-gray-100 detalle-row';
            const num = tbody.children.length + 1;
            tr.innerHTML = `
                <td class="px-4 py-2 text-center text-sm text-gray-400 font-mono">${num}</td>
                <td class="px-4 py-2">
                    <input type="text" value="${escHtml(nombre)}"
                        class="detalle-desc w-full border-0 bg-transparent focus:ring-0 focus:outline-none text-sm text-gray-800 placeholder-gray-300"
                        placeholder="Ej: Consulta General, Vacuna, etc...">
                </td>
                <td class="px-4 py-2">
                    <input type="number" value="1" min="1"
                        class="detalle-cant w-full text-center border border-gray-200 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm p-1.5">
                </td>
                <td class="px-4 py-2">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                        <input type="number" value="${Math.round(precio)}" min="0" step="1"
                            class="detalle-precio w-full pl-7 pr-3 py-1.5 border border-gray-200 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm text-right">
                    </div>
                </td>
                <td class="px-4 py-2 text-center">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" checked
                            class="detalle-iva sr-only peer">
                        <span class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500 relative inline-block shrink-0"></span>
                    </label>
                </td>
                <td class="px-4 py-2 text-right">
                    <span class="detalle-subtotal text-sm font-semibold text-gray-800">$0</span>
                </td>
                <td class="px-4 py-2 text-center">
                    <button type="button" class="btn-eliminar-fila p-1.5 text-gray-300 hover:text-red-500 transition-colors" title="Eliminar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
            tr.querySelector('.detalle-cant').addEventListener('input', recalcularTodo);
            tr.querySelector('.detalle-precio').addEventListener('input', recalcularTodo);
            tr.querySelector('.detalle-iva').addEventListener('change', recalcularTodo);
            tr.querySelector('.btn-eliminar-fila').addEventListener('click', async () => {
                if (document.querySelectorAll('.detalle-row').length <= 1) { alert('Debe haber al menos un servicio.'); return; }
                const { isConfirmed: conf } = await Swal.fire({ title: '¿Eliminar?', text: '¿Eliminar este Servicio/Producto de la cotización?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280', confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar', reverseButtons: true }); if (conf) { tr.remove(); recalcularTodo(); }
            });
            recalcularTodo();
        }

        document.getElementById('btn-agregar-producto').addEventListener('click', () => {
            document.getElementById('modal-productos').classList.remove('hidden');
            document.getElementById('buscador-inventario').value = '';
            cargarProductos('');
            setTimeout(() => document.getElementById('buscador-inventario').focus(), 100);
        });

        document.querySelectorAll('.cerrar-modal-prod').forEach(b => {
            b.addEventListener('click', () => document.getElementById('modal-productos').classList.add('hidden'));
        });
        document.getElementById('modal-productos').addEventListener('click', (e) => {
            if (e.target === e.currentTarget) e.currentTarget.classList.add('hidden');
        });

        let timeoutInvBusq;
        document.getElementById('buscador-inventario').addEventListener('input', function() {
            clearTimeout(timeoutInvBusq);
            timeoutInvBusq = setTimeout(() => cargarProductos(this.value.trim()), 250);
        });

        const CATEGORIA_LABELS = { vacuna: 'Vacuna', remedio: 'Remedio', equipo: 'Equipo', insumo: 'Insumo', otro: 'Otro' };
        function escHtml(s) { if (!s) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    </script>
</body>
</html>
