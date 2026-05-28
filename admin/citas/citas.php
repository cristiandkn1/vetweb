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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <?php include '../../includes/mobile-header.php'; ?>

    <div class="flex h-screen overflow-hidden">

        <?php include '../../includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col min-w-0 bg-gray-100 overflow-y-auto">

            <div class="p-6 md:p-10">

                <!-- Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Gestión de Citas</h1>
                        <p class="text-gray-500 mt-1">Administra la agenda y pacientes</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto">
                        <!-- Buscador -->
                        <div class="relative w-full sm:w-64">
                            <input type="text" id="buscador-clientes" placeholder="Buscar cliente o paciente..." 
                                class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 shadow-sm transition-shadow">
                            <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        
                        <!-- Botón Nueva Cita -->
                        <button id="btn-nueva-cita"
                            class="flex items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 text-white px-4 py-2.5 rounded-lg shadow-sm transition-colors font-medium w-full sm:w-auto">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="whitespace-nowrap">Nueva Cita</span>
                        </button>
                    </div>
                </div>

                <!-- KPIs -->
                <div id="kpi-container" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
                    <div class="kpi-card bg-white rounded-xl border border-gray-100 p-4 text-center">
                        <div class="text-2xl font-bold text-brand-600" data-kpi="total">—</div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide mt-1">Total</div>
                    </div>
                    <div class="kpi-card bg-white rounded-xl border border-gray-100 p-4 text-center">
                        <div class="text-2xl font-bold text-amber-600" data-kpi="pendientes">—</div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide mt-1">Pendientes</div>
                    </div>
                    <div class="kpi-card bg-white rounded-xl border border-gray-100 p-4 text-center">
                        <div class="text-2xl font-bold text-blue-600" data-kpi="confirmadas">—</div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide mt-1">Confirmadas</div>
                    </div>
                    <div class="kpi-card bg-white rounded-xl border border-gray-100 p-4 text-center">
                        <div class="text-2xl font-bold text-emerald-600" data-kpi="completadas">—</div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide mt-1">Completadas</div>
                    </div>
                    <div class="kpi-card bg-white rounded-xl border border-gray-100 p-4 text-center">
                        <div class="text-2xl font-bold text-red-500" data-kpi="canceladas">—</div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide mt-1">Canceladas</div>
                    </div>
                    <div class="kpi-card bg-white rounded-xl border border-gray-100 p-4 text-center">
                        <div class="text-2xl font-bold text-indigo-600" data-kpi="hoy">—</div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide mt-1">Hoy</div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="flex flex-wrap items-center gap-3 mb-6">
                    <!-- Estado -->
                    <div class="flex flex-wrap gap-2" id="filtros-estado">
                        <button data-estado="todos"
                            class="filtro-btn activo px-4 py-1.5 rounded-full text-sm font-medium bg-brand-600 text-white transition-colors">
                            Todas
                        </button>
                        <button data-estado="pendiente"
                            class="filtro-btn px-4 py-1.5 rounded-full text-sm font-medium bg-white text-gray-600 border border-gray-200 hover:border-brand-400 transition-colors">
                            Pendientes
                        </button>
                        <button data-estado="confirmada"
                            class="filtro-btn px-4 py-1.5 rounded-full text-sm font-medium bg-white text-gray-600 border border-gray-200 hover:border-brand-400 transition-colors">
                            Confirmadas
                        </button>
                        <button data-estado="completada"
                            class="filtro-btn px-4 py-1.5 rounded-full text-sm font-medium bg-white text-gray-600 border border-gray-200 hover:border-brand-400 transition-colors">
                            Completadas
                        </button>
                        <button data-estado="cancelada"
                            class="filtro-btn px-4 py-1.5 rounded-full text-sm font-medium bg-white text-gray-600 border border-gray-200 hover:border-brand-400 transition-colors">
                            Canceladas
                        </button>
                    </div>

                    <!-- Filtro fechas -->
                    <div class="flex items-center gap-2 ml-auto">
                        <input type="date" id="filtro-desde"
                            class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        <span class="text-xs text-gray-400">–</span>
                        <input type="date" id="filtro-hasta"
                            class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        <button id="btn-limpiar-fechas"
                            class="px-3 py-1.5 text-xs font-medium text-gray-500 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            Limpiar
                        </button>
                    </div>
                </div>

                <!-- Grid de citas -->
                <div id="contenedor-citas" class="flex flex-col">
                    <div class="flex justify-center items-center h-32 text-gray-400">
                        Cargando citas...
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Modal selector: nuevo cliente vs cliente existente -->
    <div id="modal-selector-cita" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/50"></div>
        <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center">

                <!-- Cerrar -->
                <button id="btn-cerrar-selector" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="text-4xl mb-3">🐾</div>
                <h3 class="text-lg font-bold text-gray-800 mb-1">Agendar Nueva Cita</h3>
                <p class="text-sm text-gray-500 mb-6">¿El cliente ya está registrado en el sistema?</p>

                <div class="flex flex-col gap-3">
                    <!-- Cliente existente → abre modal de cita -->
                    <button id="btn-cliente-existente"
                        class="w-full flex items-center gap-3 px-4 py-3.5 rounded-xl border-2 border-brand-200 hover:border-brand-500 hover:bg-brand-50 transition-colors text-left group">
                        <span class="text-2xl">👤</span>
                        <div>
                            <div class="font-semibold text-gray-800 text-sm group-hover:text-brand-700">
                                Agendar para cliente existente
                            </div>
                            <div class="text-xs text-gray-400">El cliente ya está en la base de datos</div>
                        </div>
                    </button>

                    <!-- Nuevo cliente → redirige a /usuario/usuario.php -->
                    <a href="../usuarios/usuarios.php"
                        class="w-full flex items-center gap-3 px-4 py-3.5 rounded-xl border-2 border-gray-200 hover:border-gray-400 hover:bg-gray-50 transition-colors text-left group">
                        <span class="text-2xl">➕</span>
                        <div>
                            <div class="font-semibold text-gray-800 text-sm group-hover:text-gray-700">
                                Crear nuevo cliente
                            </div>
                            <div class="text-xs text-gray-400">Registrar cliente antes de agendar</div>
                        </div>
                    </a>
                </div>

            </div>
        </div>
    </div>

    <?php include 'componentes/modal-crear-cita.php'; ?>
    <?php include 'componentes/modal-finalizar-cita.php'; ?>
    <?php include 'componentes/modal-notas-cita.php'; ?>
    <?php include 'componentes/modal-editar-cita.php'; ?>

    <style>
        .cita-cerrada .collapsible-body { display: none; }
        .cita-cerrada:not(.expanded) { opacity: 0.65; padding: 0.5rem 0.75rem !important; gap: 0 !important; border-color: #e5e7eb !important; box-shadow: none !important; }
        .cita-cerrada:not(.expanded) .collapse-chevron { transform: rotate(-90deg); }
        .cita-cerrada:not(.expanded) .cita-summary { display: flex; }
        .cita-cerrada:not(.expanded) .text-lg { font-size: 0.875rem !important; }
        .cita-cerrada.expanded .collapsible-body { display: flex; }
        .cita-cerrada.expanded { opacity: 0.85; border-color: #d1d5db !important; }
        .cita-cerrada.expanded .collapse-chevron { transform: rotate(0deg); }
        .cita-summary { display: none; font-size: 0.75rem; color: #6b7280; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../scripts/sidebar.js"></script>
    <script src="scripts/crear-cita.js"></script>
    <script src="scripts/listar-citas.js"></script>

    <script>
        // Lógica del modal selector
        const btnNuevaCita = document.getElementById('btn-nueva-cita');
        const modalSelector = document.getElementById('modal-selector-cita');
        const btnCerrarSelector = document.getElementById('btn-cerrar-selector');
        const btnExistente = document.getElementById('btn-cliente-existente');

        btnNuevaCita.addEventListener('click', () => {
            modalSelector.classList.remove('hidden');
        });

        btnCerrarSelector.addEventListener('click', () => {
            modalSelector.classList.add('hidden');
        });

        // Click en overlay
        modalSelector.addEventListener('click', (e) => {
            if (e.target === modalSelector.querySelector('.fixed.inset-0.bg-gray-900\\/50')) {
                modalSelector.classList.add('hidden');
            }
        });

        // Cliente existente → cierra selector y abre modal de cita
        btnExistente.addEventListener('click', () => {
            modalSelector.classList.add('hidden');
            document.getElementById('modal-nueva-cita').classList.remove('hidden');
        });
    </script>
</body>

</html>