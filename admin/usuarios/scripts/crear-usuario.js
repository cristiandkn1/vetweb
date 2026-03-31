// admin/usuario/scripts/crear-usuario.js
// Responsabilidad: abrir modal vacío y enviar nuevo cliente a la API

function abrirModalCrearUsuario() {
    document.getElementById('form-usuario').reset();
    document.getElementById('usuario_id').value          = '';
    document.getElementById('modal-usuario-titulo').textContent = 'Nuevo Cliente';
    document.getElementById('btn-submit-usuario').textContent   = 'Guardar Cliente';
    document.getElementById('usuario-error').classList.add('hidden');
    document.getElementById('modal-usuario').classList.remove('hidden');
}

document.getElementById('btn-nuevo-usuario')
    .addEventListener('click', abrirModalCrearUsuario);