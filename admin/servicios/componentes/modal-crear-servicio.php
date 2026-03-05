<!-- admin/servicios/componentes/modal-crear-servicio.php -->
<div id="modal-nuevo-servicio" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-servicio-title" role="dialog"
    aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

            <div
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">

                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b border-gray-100">
                    <div class="sm:flex sm:items-start justify-between">
                        <h3 class="text-xl font-semibold leading-6 text-gray-900" id="modal-servicio-title">
                            Nuevo Servicio
                        </h3>
                        <button type="button" id="btn-cerrar-modal-servicio" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <form id="form-crear-servicio" class="p-6 space-y-5">
                    <input type="hidden" id="servicio_id" name="servicio_id" value="">

                    <div>
                        <label for="servicio_nombre" class="block text-sm font-medium text-gray-700">Nombre del
                            Servicio</label>
                        <input type="text" id="servicio_nombre" name="nombre" required
                            placeholder="Ej: Vacunación, Revisión general..."
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2">
                    </div>

                    <div>
                        <label for="servicio_descripcion" class="block text-sm font-medium text-gray-700">Descripción
                            <span class="text-gray-400 font-normal">(opcional)</span></label>
                        <textarea id="servicio_descripcion" name="descripcion" rows="2"
                            placeholder="Describe brevemente el servicio..."
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="servicio_precio_min" class="block text-sm font-medium text-gray-700">Precio
                                Mínimo ($)</label>
                            <input type="number" id="servicio_precio_min" name="precio_min" min="0" step="100"
                                placeholder="0"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2">
                            <p class="text-xs text-gray-400 mt-1">Caso más sencillo</p>
                        </div>
                        <div>
                            <label for="servicio_precio_max" class="block text-sm font-medium text-gray-700">Precio
                                Máximo ($)</label>
                            <input type="number" id="servicio_precio_max" name="precio_max" min="0" step="100"
                                placeholder="0"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2">
                            <p class="text-xs text-gray-400 mt-1">Caso más complejo</p>
                        </div>
                    </div>

                    <div>
                        <label for="servicio_duracion" class="block text-sm font-medium text-gray-700">Duración estimada
                            <span class="text-gray-400 font-normal">(minutos)</span></label>
                        <input type="number" id="servicio_duracion" name="duracion_min" min="5" step="5"
                            placeholder="30"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2">
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="servicio_activo" name="activo" value="1" checked
                            class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <label for="servicio_activo" class="text-sm font-medium text-gray-700">Servicio activo (visible
                            al agendar citas)</label>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 -mx-6 -mb-6 mt-4 rounded-b-lg">
                        <button type="submit" id="btn-submit-servicio"
                            class="inline-flex w-full justify-center rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 sm:ml-3 sm:w-auto">
                            Guardar Servicio
                        </button>
                        <button type="button" id="btn-cancelar-modal-servicio"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>