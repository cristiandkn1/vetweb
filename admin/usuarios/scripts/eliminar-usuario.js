// admin/usuario/scripts/eliminar-usuario.js
// Responsabilidad: mostrar confirmación y ejecutar el DELETE

let _eliminarUsuarioId = null;

function abrirModalEliminarUsuario(id, nombre) {
    _eliminarUsuarioId = id;
    document.getElementById('eliminar-usuario-nombre').textContent = nombre;
    document.getElementById('modal-eliminar-usuario').classList.remove('hidden');
}

document.getElementById('btn-cancelar-eliminar').addEventListener('click', () => {
    document.getElementById('modal-eliminar-usuario').classList.add('hidden');
    _eliminarUsuarioId = null;
});

document.getElementById('btn-confirmar-eliminar').addEventListener('click', async () => {
    if (!_eliminarUsuarioId) return;

    const btn = document.getElementById('btn-confirmar-eliminar');
    btn.disabled    = true;
    btn.textContent = 'Eliminando...';

    try {
        const fd = new FormData();
        fd.append('id', _eliminarUsuarioId);
        const res  = await fetch(`${BASE_USUARIO}/eliminar_usuario.php`, { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
            document.getElementById('modal-eliminar-usuario').classList.add('hidden');
            _eliminarUsuarioId = null;
            cargarUsuarios();
        } else {
            alert(data.message || 'Error al eliminar.');
        }
    } catch {
        alert('Error de conexión.');
    } finally {
        btn.disabled    = false;
        btn.textContent = 'Sí, eliminar';
    }
});