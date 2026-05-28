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
                        <h1 class="text-3xl font-bold text-gray-800">Mascotas</h1>
                        <p class="text-gray-500 mt-1">Todas las mascotas registradas en el sistema</p>
                    </div>
                    <div class="relative w-full sm:w-64">
                        <input type="text" id="buscador-mascotas" placeholder="Buscar mascota, dueño, especie..."
                            class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 shadow-sm transition-shadow">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- KPIs -->
                <div id="kpi-container" class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                    <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
                        <div class="text-2xl font-bold text-brand-600" id="kpi-total">—</div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide mt-1">Total</div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
                        <div class="text-2xl font-bold text-emerald-600" id="kpi-duenos">—</div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide mt-1">Dueños</div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
                        <div class="text-2xl font-bold text-amber-600" id="kpi-especies">—</div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide mt-1">Especies</div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
                        <div class="text-2xl font-bold text-blue-600" id="kpi-citas">—</div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide mt-1">Citas totales</div>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="hidden sm:table-header-group">
                                <tr class="border-b border-gray-100 bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                                    <th class="px-5 py-3.5 font-semibold">Mascota</th>
                                    <th class="px-5 py-3.5 font-semibold">Dueño</th>
                                    <th class="px-5 py-3.5 font-semibold">Especie / Raza</th>
                                    <th class="px-5 py-3.5 font-semibold">Edad</th>
                                    <th class="px-5 py-3.5 font-semibold text-center">Citas</th>
                                    <th class="px-5 py-3.5 font-semibold text-center">Cotizaciones</th>
                                    <th class="px-5 py-3.5 font-semibold text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="lista-mascotas">
                                <tr>
                                    <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                                        <i class="fa-solid fa-spinner fa-spin text-xl"></i>
                                        <p class="mt-2">Cargando mascotas...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Modal Detalle -->
    <div id="modal-detalle-mascota" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/50"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800" id="detalle-titulo">
                        <i class="fa-solid fa-paw text-brand-600 mr-2"></i>
                        Detalle de Mascota
                    </h3>
                    <button type="button" id="btn-cerrar-detalle" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="text-gray-400 text-xs uppercase tracking-wide">Dueño</span>
                            <p class="font-medium text-gray-800" id="detalle-dueno">-</p>
                        </div>
                        <div>
                            <span class="text-gray-400 text-xs uppercase tracking-wide">Teléfono</span>
                            <p class="font-medium text-gray-800" id="detalle-telefono">-</p>
                        </div>
                        <div>
                            <span class="text-gray-400 text-xs uppercase tracking-wide">Email</span>
                            <p class="font-medium text-gray-800" id="detalle-email">-</p>
                        </div>
                        <div>
                            <span class="text-gray-400 text-xs uppercase tracking-wide">Sexo</span>
                            <p class="font-medium text-gray-800" id="detalle-sexo">-</p>
                        </div>
                        <div>
                            <span class="text-gray-400 text-xs uppercase tracking-wide">Especie</span>
                            <p class="font-medium text-gray-800" id="detalle-especie">-</p>
                        </div>
                        <div>
                            <span class="text-gray-400 text-xs uppercase tracking-wide">Raza</span>
                            <p class="font-medium text-gray-800" id="detalle-raza">-</p>
                        </div>
                        <div>
                            <span class="text-gray-400 text-xs uppercase tracking-wide">Color</span>
                            <p class="font-medium text-gray-800" id="detalle-color">-</p>
                        </div>
                        <div>
                            <span class="text-gray-400 text-xs uppercase tracking-wide">Peso</span>
                            <p class="font-medium text-gray-800" id="detalle-peso">-</p>
                        </div>
                        <div class="col-span-2">
                            <span class="text-gray-400 text-xs uppercase tracking-wide">N° Microchip</span>
                            <p class="font-medium text-gray-800 font-mono text-xs" id="detalle-chip">-</p>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            <i class="fa-regular fa-calendar text-brand-500"></i> Citas
                        </h4>
                        <div id="detalle-citas" class="space-y-2"></div>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-file-invoice text-brand-500"></i> Cotizaciones
                        </h4>
                        <div id="detalle-cotizaciones" class="space-y-2"></div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                    <button type="button" onclick="cerrarDetalle()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../scripts/sidebar.js"></script>
    <script src="scripts/cargar-mascotas.js"></script>
</body>
</html>
