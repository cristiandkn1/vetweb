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

                <!-- Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Clientes</h1>
                        <p class="text-gray-500 mt-1">Gestiona los clientes registrados en el sistema</p>
                    </div>
                    <button id="btn-nuevo-usuario"
                        class="flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white px-4 py-2.5 rounded-lg shadow-sm transition-colors font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Cliente
                    </button>
                </div>

                <!-- Buscador -->
                <div class="mb-5">
                    <div class="relative max-w-sm">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                        </svg>
                        <input type="text" id="buscador-usuario" placeholder="Buscar por nombre, RUT o teléfono..."
                            class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent">
                    </div>
                </div>

                <!-- Tabla -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="hidden sm:table-header-group">
                                <tr
                                    class="border-b border-gray-100 bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                                    <th class="px-5 py-3.5 font-semibold">#</th>
                                    <th class="px-5 py-3.5 font-semibold">Nombre</th>
                                    <th class="px-5 py-3.5 font-semibold">RUT</th>
                                    <th class="px-5 py-3.5 font-semibold">Teléfono</th>
                                    <th class="px-5 py-3.5 font-semibold">Email</th>
                                    <th class="px-5 py-3.5 font-semibold">Registro</th>
                                    <th class="px-5 py-3.5 font-semibold text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-usuarios-body">
                                <tr>
                                    <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                                        Cargando clientes...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="flex items-center justify-between px-5 py-3 border-t border-gray-100 text-xs text-gray-500"
                        id="paginacion-container">
                        <span id="paginacion-info"></span>
                        <div class="flex gap-1" id="paginacion-botones"></div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- ── Modal crear/editar usuario ─────────────────────────────────────── -->
    <div id="modal-usuario" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/50"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg">

                <!-- Header modal -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 id="modal-usuario-titulo" class="text-lg font-semibold text-gray-800">Nuevo Cliente</h3>
                    <button id="btn-cerrar-modal-usuario" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Form -->
                <form id="form-usuario" class="p-6 space-y-4">
                    <input type="hidden" id="usuario_id" name="id" value="">

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="nombre_completo" id="u_nombre" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">RUT</label>
                            <input type="text" name="rut" id="u_rut" placeholder="12.345.678-9"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono <span
                                    class="text-red-500">*</span></label>
                            <input type="tel" name="telefono" id="u_telefono" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" id="u_email"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                            <input type="text" name="direccion" id="u_direccion"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                    </div>

                    <!-- Error -->
                    <div id="usuario-error"
                        class="hidden text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg p-3"></div>

                    <!-- Botones -->
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" id="btn-cancelar-modal-usuario"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                        <button type="submit" id="btn-submit-usuario"
                            class="px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700 transition">
                            Guardar Cliente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'componentes/modal-mascotas.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../scripts/sidebar.js"></script>
    <!-- Orden importante: cargar → crear → editar → eliminar → guardar → mascotas → init -->
    <script src="scripts/cargar-usuarios.js"></script>
    <script src="scripts/crear-usuario.js"></script>
    <script src="scripts/editar-usuario.js"></script>
    <script src="scripts/eliminar-usuario.js"></script>
    <script src="scripts/guardar-usuario.js"></script>
    <script src="scripts/mascotas.js"></script>
    <script src="scripts/init-usuario.js"></script>
</body>

</html>