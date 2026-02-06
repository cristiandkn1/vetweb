<?php
require_once __DIR__ . '/../../../includes/db.php';

// Fetch appointment types
try {
    $stmt = $pdo->query("SELECT nombre, valor_aprox FROM cita_tipos WHERE activo = 1 ORDER BY nombre ASC");
    $tipos_cita = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback if table doesn't exist or error
    $tipos_cita = [];
    error_log("Error fetching cita_tipos: " . $e->getMessage());
}
?>
<div id="modal-nueva-cita" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

            <div
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">

                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b border-gray-100">
                    <div class="sm:flex sm:items-start justify-between">
                        <h3 class="text-xl font-semibold leading-6 text-gray-900" id="modal-title">Agendar Nueva Cita
                        </h3>
                        <button type="button" id="btn-cerrar-modal" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <form id="form-crear-cita" class="p-6 space-y-6">

                    <div>
                        <h4 class="text-sm font-bold text-brand-600 uppercase tracking-wide mb-3">1. Datos del Cliente
                        </h4>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="col-span-2">
                                <label for="cliente_busqueda" class="block text-sm font-medium text-gray-700">Buscar o
                                    Crear Cliente</label>
                                <input type="text" name="cliente_nombre" id="cliente_nombre"
                                    placeholder="Nombre del cliente..."
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2">
                                <p class="text-xs text-gray-500 mt-1">Escribe para buscar. Si no existe, llena los
                                    campos abajo.</p>
                            </div>

                            <div>
                                <label for="cliente_telefono"
                                    class="block text-sm font-medium text-gray-700">Teléfono</label>
                                <input type="tel" name="cliente_telefono" id="cliente_telefono"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2">
                            </div>
                            <div>
                                <label for="cliente_email" class="block text-sm font-medium text-gray-700">Email
                                    (Opcional)</label>
                                <input type="email" name="cliente_email" id="cliente_email"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2">
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="text-sm font-bold text-brand-600 uppercase tracking-wide">2. Mascota</h4>
                            <button type="button" id="btn-nueva-mascota"
                                class="text-xs text-brand-600 hover:text-brand-800 font-medium flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                Nueva Mascota
                            </button>
                        </div>

                        <div id="container-select-mascota">
                            <label for="mascota_id" class="block text-sm font-medium text-gray-700">Seleccionar
                                Mascota</label>
                            <select id="mascota_id" name="mascota_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2 bg-white">
                                <option value="">-- Seleccione Cliente Primero --</option>
                            </select>
                        </div>

                        <?php include 'form-nueva-mascota.php'; ?>
                    </div>

                    <hr class="border-gray-100">

                    <div>
                        <h4 class="text-sm font-bold text-brand-600 uppercase tracking-wide mb-3">3. Detalles de la Cita
                        </h4>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="cita_fecha" class="block text-sm font-medium text-gray-700">Fecha y
                                    Hora</label>
                                <input type="datetime-local" name="cita_fecha" id="cita_fecha" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2">
                            </div>
                            <div>
                                <label for="cita_tipo" class="block text-sm font-medium text-gray-700">Tipo de
                                    Servicio</label>
                                <select id="cita_tipo" name="cita_tipo"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2 bg-white">
                                    <option value="">-- Seleccione un servicio --</option>
                                    <?php if (!empty($tipos_cita)): ?>
                                        <?php foreach ($tipos_cita as $tipo): ?>
                                            <option value="<?php echo htmlspecialchars($tipo['nombre']); ?>"
                                                data-precio="<?php echo htmlspecialchars($tipo['valor_aprox']); ?>">
                                                <?php echo htmlspecialchars($tipo['nombre']); ?>
                                                (≈$<?php echo htmlspecialchars($tipo['valor_aprox']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="">No hay tipos de citas disponibles</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label for="cita_nota" class="block text-sm font-medium text-gray-700">Notas
                                    Adicionales</label>
                                <textarea id="cita_nota" name="cita_nota" rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 -mx-6 -mb-6 mt-4 rounded-b-lg">
                        <button type="submit"
                            class="inline-flex w-full justify-center rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 sm:ml-3 sm:w-auto">
                            Guardar Cita
                        </button>
                        <button type="button" id="btn-cancelar-modal"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>