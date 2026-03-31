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
    const clienteNombreInput= document.getElementById('cliente_nombre');
    const mascotaSelect     = document.getElementById('mascota_id');

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
        formNuevaMascota.classList.add('hidden');
        containerSelect.classList.remove('hidden');
        mascotaSelect.innerHTML = '<option value="">-- Seleccione Cliente Primero --</option>';
        clearError();
    }


    if (btnCerrarX)  btnCerrarX.addEventListener('click', () => toggleModal(false));
    if (btnCancelar) btnCancelar.addEventListener('click', () => toggleModal(false));

    modal.addEventListener('click', (e) => {
        if (e.target === modal.querySelector('.fixed.inset-0.bg-gray-900\\/50')) toggleModal(false);
    });

    // ── Mascota ────────────────────────────────────────────────────────────────
    btnNuevaMascota.addEventListener('click', () => {
        containerSelect.classList.add('hidden');
        formNuevaMascota.classList.remove('hidden');
        mascotaSelect.value = '';
    });

    btnCancelarMascota.addEventListener('click', () => {
        formNuevaMascota.classList.add('hidden');
        containerSelect.classList.remove('hidden');
        formNuevaMascota.querySelectorAll('input, select, textarea').forEach(el => el.value = '');
    });

    // ── Autocompletar cliente ──────────────────────────────────────────────────
    let busquedaTimer;
    clienteNombreInput.addEventListener('input', () => {
        clearTimeout(busquedaTimer);
        const q = clienteNombreInput.value.trim();
        if (q.length < 2) {
            mascotaSelect.innerHTML = '<option value="">-- Seleccione Cliente Primero --</option>';
            return;
        }
        busquedaTimer = setTimeout(async () => {
            try {
                const res  = await fetch(`/vetweb/admin/citas/api/buscar_cliente.php?q=${encodeURIComponent(q)}`);
                const data = await res.json();
                if (data.success && data.cliente) {
                    if (data.cliente.telefono) document.getElementById('cliente_telefono').value = data.cliente.telefono;
                    if (data.cliente.email)    document.getElementById('cliente_email').value    = data.cliente.email;
                    if (data.mascotas && data.mascotas.length > 0) {
                        mascotaSelect.innerHTML = '<option value="">-- Seleccione una mascota --</option>';
                        data.mascotas.forEach(m => {
                            const opt = document.createElement('option');
                            opt.value       = m.id;
                            opt.textContent = `${m.nombre} (${m.especie || 'Sin especie'})`;
                            mascotaSelect.appendChild(opt);
                        });
                    } else {
                        mascotaSelect.innerHTML = '<option value="">-- Sin mascotas registradas --</option>';
                    }
                } else {
                    mascotaSelect.innerHTML = '<option value="">-- Cliente nuevo --</option>';
                }
            } catch { /* silencioso */ }
        }, 350);
    });

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
        if (!formNuevaMascota.classList.contains('hidden')) {
            formData.set('nueva_mascota', '1');
        }

        setLoading(true);
        try {
            const res  = await fetch('/vetweb/admin/citas/api/crear_cita.php', { method: 'POST', body: formData });
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