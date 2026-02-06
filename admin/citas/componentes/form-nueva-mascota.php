<div id="form-nueva-mascota" class="hidden bg-gray-50 p-4 rounded-lg border border-gray-200 mt-2">
    <div class="flex justify-between items-center mb-3">
        <span class="text-sm font-bold text-brand-600 uppercase tracking-wide">Registrar Mascota Nueva</span>
        <button type="button" id="btn-cancelar-mascota" class="text-xs text-red-500 hover:text-red-700 font-medium">
            &times; Cancelar
        </button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
        <!-- Nombre -->
        <div class="col-span-1 sm:col-span-2 md:col-span-1">
            <label class="block text-xs font-medium text-gray-700 mb-1">Nombre *</label>
            <input type="text" name="mascota_nombre" required
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2">
        </div>

        <!-- Especie -->
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Especie *</label>
            <select name="mascota_especie" required
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2 bg-white">
                <option value="" disabled selected>Selecciona una especie</option>

                <option value="Perro">Perro</option>
                <option value="Gato">Gato</option>

                <option value="Conejo">Conejo</option>
                <option value="Hamster">Hamster</option>
                <option value="Cobaya">Cobaya / Cuy</option>
                <option value="Huron">Hurón</option>
                <option value="Erizo">Erizo de Tierra</option>

                <option value="Ave">Ave</option>
                <option value="Reptil">Reptil (Tortuga, Iguana, etc.)</option>
                <option value="Pequeño Roedor">Otro Roedor (Ratón, Rata, Gerbo)</option>

                <option value="Exotico">Otro / Exótico</option>
            </select>
        </div>

        <!-- Raza -->
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Raza</label>
            <input type="text" name="mascota_raza" placeholder="Ej: Mestizo"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2">
        </div>

        <!-- Sexo -->
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Sexo *</label>
            <select name="mascota_sexo" required
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2 bg-white">
                <option value="Macho">Macho</option>
                <option value="Hembra">Hembra</option>
            </select>
        </div>

        <!-- Fecha de Nacimiento -->
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Fecha Nacimiento</label>
            <input type="date" name="mascota_fecha_nacimiento"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2">
        </div>

        <!-- Color -->
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Color</label>
            <input type="text" name="mascota_color"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2">
        </div>

        <!-- Peso -->
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Peso (kg)</label>
            <input type="number" step="0.01" name="mascota_peso" placeholder="0.00"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2">
        </div>

        <!-- Chip -->
        <div class="col-span-1 sm:col-span-2 md:col-span-1">
            <label class="block text-xs font-medium text-gray-700 mb-1">N° Chip</label>
            <input type="text" name="mascota_chip"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2">
        </div>

        <!-- Esterilizado Checkbox -->
        <div class="flex items-end pb-2">
            <div class="flex items-center h-full">
                <input id="mascota_esterilizado" name="mascota_esterilizado" type="checkbox" value="1"
                    class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                <label for="mascota_esterilizado" class="ml-2 block text-sm text-gray-700">¿Esterilizado?</label>
            </div>
        </div>

        <!-- Alergias -->
        <div class="col-span-1 sm:col-span-3">
            <label class="block text-xs font-medium text-gray-700 mb-1">Alergias</label>
            <textarea name="mascota_alergias" rows="1" placeholder="Ninguna conocida"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2"></textarea>
        </div>

        <!-- Observaciones -->
        <div class="col-span-1 sm:col-span-3">
            <label class="block text-xs font-medium text-gray-700 mb-1">Observaciones</label>
            <textarea name="mascota_observaciones" rows="2" placeholder="Comportamiento, miedos, etc."
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm border p-2"></textarea>
        </div>
    </div>
</div>