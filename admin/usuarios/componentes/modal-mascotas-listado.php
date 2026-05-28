<?php // admin/usuarios/componentes/modal-mascotas-listado.php ?>

<div id="modal-mascotas" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/50"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-5xl max-h-[90vh] flex flex-col">

            <div class="flex items-center justify-between px-8 py-5 border-b border-gray-100 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600">
                        <i data-lucide="paw-print" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 id="modal-mascotas-titulo" class="text-lg font-semibold text-gray-800">Mascotas</h3>
                        <p id="modal-mascotas-subtitulo" class="text-xs text-gray-400"></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button id="btn-agregar-mascota"
                        class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold bg-brand-600 hover:bg-brand-700 text-white rounded-lg transition shadow-sm">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Nueva Mascota
                    </button>
                    <button id="btn-cerrar-modal-mascotas" class="text-gray-400 hover:text-gray-600 ml-2 p-1.5 rounded-lg hover:bg-gray-100 transition">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <div id="contenedor-mascotas" class="overflow-y-auto p-8">
                <div class="flex justify-center items-center h-32 text-gray-400 text-sm">Cargando mascotas...</div>
            </div>

        </div>
    </div>
</div>