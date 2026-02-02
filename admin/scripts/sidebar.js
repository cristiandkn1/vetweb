// /admin/scripts/sidebar.js
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const btnOpen = document.getElementById('btn-toggle-sidebar');
    const btnClose = document.getElementById('btn-close-sidebar');

    function toggle() {
        if (!sidebar || !overlay) return; // Seguridad por si los elementos no existen
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }

    // El operador ?. asegura que si el botón no existe en una página específica, no rompa el código
    [btnOpen, btnClose, overlay].forEach(el => el?.addEventListener('click', toggle));
});