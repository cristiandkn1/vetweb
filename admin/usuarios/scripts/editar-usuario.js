
// admin/usuario/scripts/editar-usuario.js
// Responsabilidad: abrir modal con datos del cliente seleccionado
function abrirModalEditarUsuario(usuario) {
    document.getElementById('usuario_id').value                  = usuario.id;
    document.getElementById('u_nombre').value                    = usuario.nombre_completo ?? '';
    document.getElementById('u_rut').value                       = usuario.rut      ?? '';
    document.getElementById('u_telefono').value                  = usuario.telefono ?? '';
    document.getElementById('u_email').value                     = usuario.email    ?? '';
    document.getElementById('u_direccion').value                 = usuario.direccion ?? '';
    document.getElementById('modal-usuario-titulo').textContent  = 'Editar Cliente';
    document.getElementById('btn-submit-usuario').textContent    = 'Guardar Cambios';
    document.getElementById('usuario-error').classList.add('hidden');
    document.getElementById('modal-usuario').classList.remove('hidden');
}
