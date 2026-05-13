// admin/citas/scripts/crear-cita.js

document.addEventListener('DOMContentLoaded', () => {

    // btn-nueva-cita lo maneja el modal selector en citas.php
    const modal             = document.getElementById('modal-nueva-cita');
    const btnCerrarX        = document.getElementById('btn-cerrar-modal');
    const btnCancelar       = document.getElementById('btn-cancelar-modal');
    const btnNuevaMascota   = document.getElementById('btn-nueva-mascota');
    const btnCancelarMascota= document.getElementById('btn-cancelar-mascota');
    const containerSelect   = document.getElementById('container-select-mascota');
    const formNuevaMascota  = document.getElementById('form-nueva-mascota');
    const form              = document.getElementById('form-crear-cita');
    const btnSubmit         = form.querySelector('[type="submit"]');
    const clienteIdSelect   = document.getElementById('cliente_id');
    const mascotaSelect     = document.getElementById('mascota_id');
    
    // Inicializar Select2
    if (window.jQuery && window.jQuery.fn.select2) {
        window.jQuery(clienteIdSelect).select2({ 
            width: '100%', 
            dropdownParent: window.jQuery('#modal-nueva-cita')
        });
        window.jQuery(mascotaSelect).select2({ 
            width: '100%', 
            dropdownParent: window.jQuery('#modal-nueva-cita')
        });
    }

    // ── Modal principal ────────────────────────────────────────────────────────
    function toggleModal(show) {
        if (show) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
            resetForm();
        }
    }

    function resetForm() {
        form.reset();
        if (window.jQuery && window.jQuery.fn.select2) {
            window.jQuery(clienteIdSelect).val('').trigger('change.select2');
            window.jQuery(mascotaSelect).empty().append('<option value="">-- Seleccione Cliente Primero --</option>').trigger('change.select2');
        }
        clearError();
    }


    if (btnCerrarX)  btnCerrarX.addEventListener('click', () => toggleModal(false));
    if (btnCancelar) btnCancelar.addEventListener('click', () => toggleModal(false));

    modal.addEventListener('click', (e) => {
        if (e.target === modal.querySelector('.fixed.inset-0.bg-gray-900\\/50')) toggleModal(false);
    });

    // ── Al cambiar de Cliente ──────────────────────────────────────────────────
    if (window.jQuery) {
        window.jQuery(clienteIdSelect).on('change', async function() {
            const selectedOption = window.jQuery(this).find(':selected');
            const tel = selectedOption.data('tel') || '';
            const email = selectedOption.data('email') || '';
            const c_id = window.jQuery(this).val();

            document.getElementById('cliente_telefono').value = tel;
            document.getElementById('cliente_email').value    = email;

            const ms = window.jQuery(mascotaSelect);
            ms.empty();

            if (!c_id) {
                ms.append('<option value="">-- Seleccione Cliente Primero --</option>').trigger('change');
                return;
            }

            try {
                const res  = await fetch(`/admin/citas/api/buscar_cliente.php?q=C_${c_id}`);
                const data = await res.json();
                
                if (data.success && data.mascota && data.mascota.length > 0) {
                    ms.append('<option value="">-- Seleccione una mascota --</option>');
                    data.mascota.forEach(m => {
                        ms.append(new Option(`${m.nombre} (${m.especie || 'Sin especie'})`, m.id));
                    });
                } else {
                    ms.append('<option value="">-- Sin mascotas registradas --</option>');
                }
            } catch { 
                ms.append('<option value="">-- Error de conexión --</option>');
            }
            ms.trigger('change');
        });
    }

    // ── Errores ────────────────────────────────────────────────────────────────
    function showError(msg) {
        clearError();
        const div = document.createElement('div');
        div.id = 'cita-error';
        div.className = 'text-sm text-red-600 bg-red-50 border border-red-200 rounded p-3 -mt-2';
        div.textContent = msg;
        form.prepend(div);
    }
    function clearError() { document.getElementById('cita-error')?.remove(); }
    function setLoading(loading) {
        btnSubmit.disabled    = loading;
        btnSubmit.textContent = loading ? 'Guardando...' : 'Guardar Cita';
    }

    // ── Submit ─────────────────────────────────────────────────────────────────
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearError();

        const formData = new FormData(form);

        setLoading(true);
        try {
            const res  = await fetch('/admin/citas/api/crear_cita.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.success) {
                toggleModal(false);
                mostrarModalLink(data.link_seguimiento);
            } else {
                showError(data.message || 'Ocurrió un error al guardar la cita.');
            }
        } catch (err) {
            showError('Error de conexión. Intenta de nuevo.');
            console.error(err);
        } finally {
            setLoading(false);
        }
    });

    // ── Modal de link de seguimiento ───────────────────────────────────────────
    function mostrarModalLink(link) {
        // Crear modal si no existe
        let ml = document.getElementById('modal-link-seguimiento');
        if (!ml) {
            ml = document.createElement('div');
            ml.id = 'modal-link-seguimiento';
            ml.className = 'fixed inset-0 z-50 flex items-center justify-center p-4';
            ml.innerHTML = `
                <div class="fixed inset-0 bg-gray-900/60"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full text-center z-10">
                    <div class="text-5xl mb-3">🎉</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-1">¡Cita agendada!</h3>
                    <p class="text-gray-500 text-sm mb-5">
                        Comparte este enlace con el cliente para que pueda seguir el estado de su cita.
                    </p>

                    <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-xl p-3 mb-4">
                        <input id="input-link-seguimiento" type="text" readonly
                            class="flex-1 text-xs text-gray-700 bg-transparent outline-none truncate">
                        <button id="btn-copiar-link"
                            class="shrink-0 bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                            Copiar
                        </button>
                    </div>

                    <a id="btn-abrir-link" href="#" target="_blank"
                        class="block text-xs text-brand-600 hover:underline mb-6">
                        Abrir enlace ↗
                    </a>

                    <button id="btn-cerrar-link"
                        class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 rounded-xl transition text-sm">
                        Cerrar y recargar
                    </button>
                </div>`;
            document.body.appendChild(ml);

            document.getElementById('btn-copiar-link').addEventListener('click', () => {
                const inp = document.getElementById('input-link-seguimiento');
                navigator.clipboard.writeText(inp.value).then(() => {
                    document.getElementById('btn-copiar-link').textContent = '¡Copiado!';
                    setTimeout(() => document.getElementById('btn-copiar-link').textContent = 'Copiar', 2000);
                });
            });

            document.getElementById('btn-cerrar-link').addEventListener('click', () => {
                ml.remove();
                window.location.reload();
            });
        }

        document.getElementById('input-link-seguimiento').value = link;
        document.getElementById('btn-abrir-link').href = link;
        ml.classList.remove('hidden');
    }

});