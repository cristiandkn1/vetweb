<?php // admin/usuarios/componentes/modal-mascotas-form.php ?>

<!-- z-[60] para quedar encima del modal de listado -->
<div id="modal-form-mascota" class="fixed inset-0 z-[60] hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/60"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
                <h3 id="modal-form-mascota-titulo" class="text-lg font-semibold text-gray-800">Nueva Mascota</h3>
                <button id="btn-cerrar-form-mascota" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="form-mascota" class="p-6 space-y-5 overflow-y-auto max-h-[75vh]">
                <input type="hidden" id="mascota_id" name="id" value="">
                <input type="hidden" id="mascota_cliente_id" name="cliente_id" value="">

                <!-- Avatar especie -->
                <div class="flex justify-center">
                    <div id="mascota-avatar-preview"
                        class="w-20 h-20 rounded-2xl bg-amber-50 border-2 border-amber-100 flex items-center justify-center text-amber-500 p-3">
                        <i data-lucide="paw-print" class="w-10 h-10"></i>
                    </div>
                </div>

                <!-- ── Información básica ──────────────────────────────── -->
                <div>
                    <h4
                        class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3 flex items-center gap-2">
                        <i data-lucide="info" class="w-3.5 h-3.5"></i> Información básica
                    </h4>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="nombre" id="m_nombre" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Especie</label>
                            <div class="grid grid-cols-3 gap-2" id="especie-selector">

                                <label class="especie-opcion cursor-pointer">
                                    <input type="radio" name="especie" value="Perro" class="sr-only">
                                    <div
                                        class="especie-card flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-gray-200 hover:border-brand-400 transition text-center">
                                        <i data-lucide="dog" class="w-7 h-7 text-gray-500"></i>
                                        <span class="text-xs font-medium text-gray-600">Perro</span>
                                    </div>
                                </label>

                                <label class="especie-opcion cursor-pointer">
                                    <input type="radio" name="especie" value="Gato" class="sr-only">
                                    <div
                                        class="especie-card flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-gray-200 hover:border-brand-400 transition text-center">
                                        <i data-lucide="cat" class="w-7 h-7 text-gray-500"></i>
                                        <span class="text-xs font-medium text-gray-600">Gato</span>
                                    </div>
                                </label>

                                <label class="especie-opcion cursor-pointer">
                                    <input type="radio" name="especie" value="Ave" class="sr-only">
                                    <div
                                        class="especie-card flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-gray-200 hover:border-brand-400 transition text-center">
                                        <i data-lucide="bird" class="w-7 h-7 text-gray-500"></i>
                                        <span class="text-xs font-medium text-gray-600">Ave</span>
                                    </div>
                                </label>

                                <label class="especie-opcion cursor-pointer">
                                    <input type="radio" name="especie" value="Roedor" class="sr-only">
                                    <div
                                        class="especie-card flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-gray-200 hover:border-brand-400 transition text-center">
                                        <i data-lucide="squirrel" class="w-7 h-7 text-gray-500"></i>
                                        <span class="text-xs font-medium text-gray-600">Roedor</span>
                                    </div>
                                </label>

                                <label class="especie-opcion cursor-pointer">
                                    <input type="radio" name="especie" value="Reptil" class="sr-only">
                                    <div
                                        class="especie-card flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-gray-200 hover:border-brand-400 transition text-center">
                                        <i data-lucide="turtle" class="w-7 h-7 text-gray-500"></i>
                                        <span class="text-xs font-medium text-gray-600">Reptil</span>
                                    </div>
                                </label>

                                <label class="especie-opcion cursor-pointer">
                                    <input type="radio" name="especie" value="Otro" class="sr-only">
                                    <div
                                        class="especie-card flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-gray-200 hover:border-brand-400 transition text-center">
                                        <i data-lucide="help-circle" class="w-7 h-7 text-gray-500"></i>
                                        <span class="text-xs font-medium text-gray-600">Otro</span>
                                    </div>
                                </label>

                            </div><!-- /especie-selector -->

                            <div id="especie-otro-wrapper" class="hidden mt-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Especificar especie</label>
                                <input type="text" name="especie_otro" id="m_especie_otro"
                                    placeholder="Ej: Hurón, Cerdo vietnamita, Equino..."
                                    class="w-full border border-brand-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 bg-brand-50">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Raza</label>
                            <input type="text" name="raza" id="m_raza"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de nacimiento</label>
                            <input type="date" name="fecha_nacimiento" id="m_fecha_nac"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sexo</label>
                            <select name="sexo" id="m_sexo"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-400">
                                <option value="">-- Seleccionar --</option>
                                <option value="Macho">Macho</option>
                                <option value="Hembra">Hembra</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                            <input type="text" name="color" id="m_color"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                    </div>
                </div>

                <!-- ── Datos físicos ───────────────────────────────────── -->
                <div class="border-t border-gray-100 pt-4">
                    <h4
                        class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3 flex items-center gap-2">
                        <i data-lucide="weight" class="w-3.5 h-3.5"></i> Datos físicos
                    </h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Peso <span
                                    class="text-gray-400 font-normal">(kg)</span></label>
                            <input type="number" name="peso" id="m_peso" step="0.01" min="0" placeholder="0.00"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                        <div class="flex items-end pb-1">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <div class="relative">
                                    <input type="checkbox" name="esterilizado" id="m_esterilizado" value="1"
                                        class="sr-only peer">
                                    <div
                                        class="w-10 h-6 bg-gray-200 rounded-full peer-checked:bg-brand-600 transition-colors">
                                    </div>
                                    <div
                                        class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4">
                                    </div>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Esterilizado/a</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- ── Identificación ─────────────────────────────────── -->
                <div class="border-t border-gray-100 pt-4">
                    <h4
                        class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3 flex items-center gap-2">
                        <i data-lucide="fingerprint" class="w-3.5 h-3.5"></i> Identificación
                    </h4>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nº de microchip</label>
                        <input type="text" name="numero_chip" id="m_chip"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                    </div>
                </div>

                <!-- ── Salud ──────────────────────────────────────────── -->
                <div class="border-t border-gray-100 pt-4">
                    <h4
                        class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3 flex items-center gap-2">
                        <i data-lucide="stethoscope" class="w-3.5 h-3.5"></i> Salud
                    </h4>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Última revisión</label>
                            <input type="date" name="ultima_revision" id="m_ultima_revision"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alergias conocidas</label>
                            <input type="text" name="alergias" id="m_alergias" placeholder="Ej: Polen, Pollo"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                            <textarea name="observaciones" id="m_observaciones" rows="2"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 resize-none"></textarea>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Notas internas
                                <span class="text-xs font-normal text-gray-400 ml-1">Solo visible para el equipo</span>
                            </label>
                            <textarea name="notas_internas" id="m_notas_internas" rows="2"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 resize-none bg-yellow-50"></textarea>
                        </div>
                    </div>
                </div>

                <!-- ── Vacunas ─────────────────────────────────────────── -->
                <div class="border-t border-gray-100 pt-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wide flex items-center gap-2">
                            <i data-lucide="syringe" class="w-3.5 h-3.5"></i> Vacunas
                        </h4>
                        <button type="button" id="btn-agregar-vacuna"
                            class="text-xs font-semibold text-brand-600 hover:text-brand-700 flex items-center gap-1">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Agregar
                        </button>
                    </div>
                    <div id="lista-vacunas" class="space-y-2"></div>
                    <p id="vacunas-vacio" class="text-xs text-gray-400 text-center py-2">Sin vacunas registradas</p>
                </div>

                <div id="mascota-error"
                    class="hidden text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg p-3"></div>

                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                    <button type="button" id="btn-cancelar-form-mascota"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                    <button type="submit" id="btn-submit-mascota"
                        class="px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700 transition">
                        Guardar Mascota
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>