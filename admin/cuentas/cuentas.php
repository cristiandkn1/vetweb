<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}
require_once __DIR__ . '/../../includes/db.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuentas - VetWeb</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include __DIR__ . '/../../includes/head.php'; ?>

    <div class="flex h-screen overflow-hidden">
        <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="max-w-7xl mx-auto space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Cuentas</h1>
                        <p class="text-sm text-gray-500">Registro de ingresos, gastos y pagos pendientes</p>
                    </div>
                    <button id="btn-agregar-cuenta" class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Nuevo registro
                    </button>
                </div>

                <!-- KPIs -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Ingresos (pagados)</p>
                        <p class="text-2xl font-bold text-emerald-600" id="kpi-ingresos">$0</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Gastos (pagados)</p>
                        <p class="text-2xl font-bold text-red-600" id="kpi-gastos">$0</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Saldo</p>
                        <p class="text-2xl font-bold text-emerald-600" id="kpi-saldo">$0</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Pendientes</p>
                        <p class="text-2xl font-bold text-yellow-600" id="kpi-pendientes">$0</p>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3 flex-wrap">
                        <select id="filtro-tipo" class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white text-gray-600 focus:outline-none focus:border-brand-400">
                            <option value="todos">Todos los tipos</option>
                            <option value="ingreso">Ingresos</option>
                            <option value="gasto">Gastos</option>
                        </select>
                        <select id="filtro-estado" class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white text-gray-600 focus:outline-none focus:border-brand-400">
                            <option value="todos">Todos los estados</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="pagado">Pagado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                        <input type="date" id="filtro-desde" class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white text-gray-600 focus:outline-none focus:border-brand-400">
                        <span class="text-gray-400 text-sm">a</span>
                        <input type="date" id="filtro-hasta" class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white text-gray-600 focus:outline-none focus:border-brand-400">
                        <input type="text" id="buscador-cuentas" placeholder="Buscar..."
                            class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white text-gray-600 focus:outline-none focus:border-brand-400 min-w-[200px]">
                        <button id="btn-limpiar-filtros" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 hover:bg-gray-50 rounded-lg transition-colors">
                            Limpiar
                        </button>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="px-3 py-3">Fecha</th>
                                    <th class="px-3 py-3">Tipo</th>
                                    <th class="px-3 py-3">Categoría</th>
                                    <th class="px-3 py-3">Descripción</th>
                                    <th class="px-3 py-3">Monto</th>
                                    <th class="px-3 py-3">Estado</th>
                                    <th class="px-3 py-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="lista-cuentas">
                                <tr><td colspan="7" class="text-center text-gray-400 py-12">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include __DIR__ . '/componentes/modal-cuenta.php'; ?>

    <!-- Modal Detalle -->
    <div id="modal-detalle-cuenta" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/50"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Detalle del Registro
                    </h3>
                    <button type="button" id="btn-cerrar-detalle-cuenta" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-1 max-h-[70vh] overflow-y-auto text-sm" id="detalle-cuenta-body">
                    <p class="text-center text-gray-400 py-8">Cargando...</p>
                </div>
                <div class="flex items-center justify-end px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                    <button type="button" onclick="document.getElementById('modal-detalle-cuenta').classList.add('hidden')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="base-cuentas" data-base="/admin/cuentas/api"></div>
    <script src="scripts/cargar-cuentas.js"></script>
    <script src="../scripts/sidebar.js"></script>
</body>
</html>
