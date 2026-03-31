<div id="modal-finalizar-cita" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity"></div>
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b border-gray-100">
                    <div class="sm:flex sm:items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                                <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold leading-6 text-gray-900">Finalizar Cita</h3>
                        </div>
                        <button type="button" id="btn-cerrar-finalizar" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <form id="form-finalizar-cita" class="p-6">
                    <input type="hidden" name="cita_id" id="finalizar_cita_id">
                    
                    <div class="space-y-5">
                        <div class="bg-emerald-50 p-3 rounded-lg border border-emerald-100 mb-2">
                            <p class="text-sm text-emerald-800 font-medium">Estás a punto de marcar esta cita como Completada e ingresar la información de cierre clínico y cobro.</p>
                        </div>

                        <div>
                            <label for="precio_final" class="block text-sm font-medium text-gray-700">Cobro Generado (Precio Final) *</label>
                            <div class="relative mt-1">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" name="precio_final" id="precio_final" required min="0" step="0.01"
                                    class="block w-full rounded-md border-gray-300 pl-7 focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2"
                                    placeholder="0">
                            </div>
                        </div>

                        <div class="flex items-start bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <div class="flex h-5 items-center">
                                <input id="pagado" name="pagado" type="checkbox" value="1" checked
                                    class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-600">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="pagado" class="font-medium text-gray-700">El cliente ha pagado</label>
                                <p class="text-gray-500 text-xs">Marcar si el cobro ya fue efectuado en este instante.</p>
                            </div>
                        </div>

                        <div>
                            <label for="observaciones_vet" class="block text-sm font-medium text-gray-700">Observaciones Finales / Diagnóstico *</label>
                            <p class="text-[11px] text-gray-500 mb-1">El cliente verá esta observación en su seguimiento en línea. Se guardará también en el informe de la mascota.</p>
                            <textarea id="observaciones_vet" name="observaciones_vet" rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2"
                                placeholder="Escribe procedimientos realizados, recomendaciones y desglose de cobros extra si lo hay..." required></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-row-reverse gap-2">
                        <button type="submit" id="btn-submit-finalizar" class="inline-flex w-full justify-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 sm:ml-3 sm:w-auto transition-colors">
                            Guardar y Completar
                        </button>
                        <button type="button" id="btn-cancelar-finalizar-2" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
