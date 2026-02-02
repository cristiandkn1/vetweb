<?php
session_start();

// Security check
/* if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.html');
    exit;
} */

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- Main Content Wrapper -->
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 h-screen w-full">
    <div class="container mx-auto px-6 py-8">
        <h3 class="text-gray-700 text-3xl font-medium">Panel de Cliente</h3>
        <p class="mt-4 text-gray-600">Bienvenido al área de clientes.</p>
    </div>
</main>

<?php include '../includes/footer.php'; ?>