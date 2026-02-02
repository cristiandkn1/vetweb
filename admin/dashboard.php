<?php
session_start();

// Control de acceso
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<?php include '../includes/head.php'; ?>

<body class="bg-gray-100 font-sans">
    <?php include '../includes/mobile-header.php'; ?>

    <div class="flex h-screen overflow-hidden">

        <div id="sidebar-overlay" class="fixed inset-0 bg-gray-900/50 z-40 hidden md:hidden"></div>

        <?php include '../includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col min-w-0 bg-gray-100 overflow-y-auto p-6 md:p-10">
            <div class="container mx-auto">
                <h3 class="text-gray-700 text-3xl font-medium">Panel de Administración</h3>

                <div class="mt-8">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div
                            class="flex items-center px-5 py-6 shadow-sm rounded-lg bg-white border border-gray-200 transition-all hover:shadow-md">
                            <div class="p-4 rounded-xl bg-indigo-50 text-indigo-600">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                            </div>
                            <div class="mx-5">
                                <h4 class="text-2xl font-semibold text-gray-700">8,282</h4>
                                <div class="text-gray-500 text-sm">Usuarios</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="scripts/sidebar.js"></script>
</body>

</html>