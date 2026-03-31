<?php // admin/usuarios/componentes/modal-mascotas-eliminar.php ?>

<div id="modal-eliminar-mascota" class="fixed inset-0 z-[60] hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/60"></div>
    <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm p-6 text-center">
            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-3">
                <i data-lucide="trash-2" class="w-6 h-6 text-red-500"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">¿Eliminar mascota?</h3>
            <p class="text-sm text-gray-500 mb-1">Estás a punto de eliminar a:</p>
            <p id="eliminar-mascota-nombre" class="font-semibold text-gray-800 mb-3"></p>
            <p class="text-xs text-red-500 mb-5">Esta acción no se puede deshacer.</p>
            <div class="flex gap-3">
                <button id="btn-cancelar-eliminar-mascota"
                    class="flex-1 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button id="btn-confirmar-eliminar-mascota"
                    class="flex-1 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                    Sí, eliminar
                </button>
            </div>
        </div>
    </div>
</div>