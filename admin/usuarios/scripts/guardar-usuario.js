// admin/usuario/scripts/guardar-usuario.js
// Responsabilidad: submit del form (crear o editar según usuario_id)

const formUsuario = document.getElementById('form-usuario');

function cerrarModalUsuario() {
    document.getElementById('modal-usuario').classList.add('hidden');
}

// Cerrar con X y Cancelar
document.getElementById('btn-cerrar-modal-usuario').addEventListener('click',   cerrarModalUsuario);
document.getElementById('btn-cancelar-modal-usuario').addEventListener('click', cerrarModalUsuario);
document.getElementById('modal-usuario').addEventListener('click', (e) => {
    if (e.target === document.getElementById('modal-usuario').querySelector('.fixed.inset-0'))
        cerrarModalUsuario();
});

// Submit
formUsuario.addEventListener('submit', async (e) => {
    e.preventDefault();

    const errorDiv = document.getElementById('usuario-error');
    const btnSubmit = document.getElementById('btn-submit-usuario');
    const esEdicion = !!document.getElementById('usuario_id').value;

    errorDiv.classList.add('hidden');
    errorDiv.textContent = '';
    btnSubmit.disabled    = true;
    btnSubmit.textContent = 'Guardando...';

    try {
        const res  = await fetch(`${BASE_USUARIO}/guardar_usuario.php`, {
            method: 'POST',
            body:   new FormData(formUsuario),
        });
        const data = await res.json();

        if (data.success) {
            cerrarModalUsuario();
            cargarUsuarios();
        } else {
            errorDiv.textContent = data.message || 'Error al guardar.';
            errorDiv.classList.remove('hidden');
        }
    } catch {
        errorDiv.textContent = 'Error de conexión.';
        errorDiv.classList.remove('hidden');
    } finally {
        btnSubmit.disabled    = false;
        btnSubmit.textContent = esEdicion ? 'Guardar Cambios' : 'Guardar Cliente';
    }
});