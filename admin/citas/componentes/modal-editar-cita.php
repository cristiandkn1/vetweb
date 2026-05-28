<?php
require_once __DIR__ . '/../../../includes/db.php';

try {
    $stmt = $pdo->query("SELECT id, nombre FROM servicios WHERE activo = 1 ORDER BY nombre ASC");
    $tipos_cita = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tipos_cita = [];
}
?>
<div id="modal-editar-cita" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/50"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fa-solid fa-pen-to-square text-brand-600 mr-2"></i>
                    Editar Cita
                </h3>
                <button type="button" id="btn-cerrar-modal-editar" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="form-editar-cita" class="p-6 space-y-4">
                <input type="hidden" id="edit-cita-id" name="id">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha y Hora <span class="text-red-500">*</span></label>
                    <input type="datetime-local" id="edit-fecha" name="fecha" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Servicio <span class="text-red-500">*</span></label>
                    <select id="edit-tipo" name="tipo" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                        <option value="">Seleccionar...</option>
                        <?php foreach ($tipos_cita as $t): ?>
                        <option value="<?= htmlspecialchars($t['nombre']) ?>"><?= htmlspecialchars($t['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nota</label>
                    <textarea id="edit-nota" name="nota" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition resize-none"></textarea>
                </div>
            </form>

            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                <button type="button" id="btn-cancelar-editar"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button type="submit" form="form-editar-cita"
                    class="px-5 py-2 text-sm font-semibold text-white bg-brand-600 rounded-lg hover:bg-brand-700 transition shadow-sm">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>
