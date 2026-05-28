<div id="modal-notas-cita" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity"></div>
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b border-gray-100">
                    <div class="sm:flex sm:items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold leading-6 text-gray-900">Notas de la Cita</h3>
                        </div>
                        <button type="button" id="btn-cerrar-notas" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-6 space-y-6 overflow-y-auto max-h-[70vh]">

                    <!-- === BITÁCORA === -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-indigo-600 uppercase tracking-wide flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Bitácora de la Cita
                            </h4>
                            <span class="text-xs text-gray-400">Registros con hora</span>
                        </div>
                        <p class="text-xs text-gray-500 mb-4">Agrega eventos, procedimientos, medicaciones y observaciones con su hora exacta.</p>

                        <!-- Formulario agregar entrada -->
                        <div id="form-agregar-bitacora" class="bg-gray-50 rounded-xl p-4 border border-gray-200 mb-4">
                            <div class="flex gap-3 mb-3">
                                <div class="w-24 shrink-0">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Hora</label>
                                    <input type="time" id="bitacora-hora" value="<?php echo date('H:i'); ?>"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2">
                                </div>
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Comentario / Procedimiento</label>
                                    <textarea id="bitacora-comentario" rows="2"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2"
                                        placeholder="Ej: Se administró antibiótico, la temperatura bajó..."></textarea>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" id="btn-agregar-bitacora"
                                    class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Agregar
                                </button>
                            </div>
                        </div>

                        <!-- Lista de entradas de bitácora -->
                        <div id="bitacora-lista" class="space-y-2">
                            <div class="text-center text-sm text-gray-400 py-8">Cargando bitácora...</div>
                        </div>
                    </div>

                    <hr class="border-gray-200">

                    <!-- === RECOMENDACIONES FINALES === -->
                    <div>
                        <h4 class="text-sm font-bold text-emerald-600 uppercase tracking-wide flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Recomendaciones / Pasos a Seguir
                        </h4>
                        <p class="text-xs text-gray-500 mb-2">Indicaciones finales, cuidados posteriores y pasos que debe seguir el cliente.</p>
                        <textarea id="recomendaciones" name="recomendaciones" rows="3"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2"
                            placeholder="Ej: Reposo por 24 hrs, aplicar crema 2 veces al día, control en 7 días..."></textarea>
                    </div>

                </div>

                <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-2 border-t border-gray-100 rounded-b-lg">
                    <button type="button" id="btn-submit-notas"
                        class="inline-flex w-full justify-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 sm:ml-3 sm:w-auto transition-colors">
                        Guardar Recomendaciones
                    </button>
                    <button type="button" id="btn-cancelar-notas"
                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
