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
                        <h1 class="text-3xl font-bold text-gray-800">Gestión de Citas</h1>
                        <p class="text-gray-500 mt-1">Administra la agenda y pacientes</p>
                    </div>

                    <button id="btn-nueva-cita"
                        class="flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white px-4 py-2.5 rounded-lg shadow-sm transition-colors font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nueva Cita
                    </button>
                </div>

                <div id="contenedor-citas" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        class="border-2 border-dashed border-gray-300 rounded-lg h-48 flex items-center justify-center text-gray-400">
                        Aquí se cargarán las cards de citas...
                    </div>
                </div>

            </div>
        </main>
    </div>

    <?php include 'componentes/modal-crear-cita.php'; ?>

    <script src="../scripts/sidebar.js"></script>
    <script src="scripts/crear-cita.js"></script>
</body>

</html>