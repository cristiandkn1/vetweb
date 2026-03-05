// admin/citas/scripts/crear-cita.js

document.addEventListener('DOMContentLoaded', () => {

    // ── Referencias DOM ────────────────────────────────────────────────────────
    const btnAbrir          = document.getElementById('btn-nueva-cita');
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

    // ── Modal ──────────────────────────────────────────────────────────────────
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
        // Volver a estado inicial de mascota
        formNuevaMascota.classList.add('hidden');
        containerSelect.classList.remove('hidden');
        mascotaSelect.innerHTML = '<option value="">-- Seleccione Cliente Primero --</option>';
        clearError();
    }

    if (btnAbrir)    btnAbrir.addEventListener('click', () => toggleModal(true));
    if (btnCerrarX)  btnCerrarX.addEventListener('click', () => toggleModal(false));
    if (btnCancelar) btnCancelar.addEventListener('click', () => toggleModal(false));

    // Cerrar al hacer click en el overlay
    modal.addEventListener('click', (e) => {
        if (e.target === modal.querySelector('.fixed.inset-0.bg-gray-900\\/50')) {
            toggleModal(false);
        }
    });

    // ── Mascota: toggle entre select y form nueva mascota ──────────────────────
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

    // ── Autocompletar mascotas al escribir nombre de cliente ───────────────────
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
                    // Rellenar teléfono y email si vienen del servidor
                    if (data.cliente.telefono) document.getElementById('cliente_telefono').value = data.cliente.telefono;
                    if (data.cliente.email)    document.getElementById('cliente_email').value    = data.cliente.email;

                    // Poblar select de mascotas
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
            } catch {
                // Silencioso; el usuario igual puede llenar manual
            }
        }, 350);
    });

    // ── Feedback de errores ────────────────────────────────────────────────────
    function showError(msg) {
        clearError();
        const div = document.createElement('div');
        div.id = 'cita-error';
        div.className = 'text-sm text-red-600 bg-red-50 border border-red-200 rounded p-3 -mt-2';
        div.textContent = msg;
        form.prepend(div);
    }

    function clearError() {
        document.getElementById('cita-error')?.remove();
    }

    function setLoading(loading) {
        btnSubmit.disabled = loading;
        btnSubmit.textContent = loading ? 'Guardando...' : 'Guardar Cita';
    }

    // ── Submit ─────────────────────────────────────────────────────────────────
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearError();

        const formData = new FormData(form);

        // Indicar si se está registrando una mascota nueva
        const esNuevaMascota = !formNuevaMascota.classList.contains('hidden');
        if (esNuevaMascota) {
            formData.set('nueva_mascota', '1');
        }

        setLoading(true);

        try {
            const res  = await fetch('/vetweb/admin/citas/api/crear_cita.php', {
                method: 'POST',
                body: formData,
            });

            const data = await res.json();

            if (data.success) {
                toggleModal(false);
                // Recargar la página o actualizar la vista de citas sin recargar
                window.location.reload();
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

});