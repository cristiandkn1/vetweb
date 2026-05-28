// admin/usuarios/scripts/eliminar-usuario.js
// SweetAlert2 delete confirmation

async function abrirModalEliminarUsuario(id, nombre) {
    const { isConfirmed } = await Swal.fire({
        title: '¿Eliminar cliente?',
        html: `Estás a punto de eliminar a <strong>${escHtml(nombre)}</strong>.<br>Se eliminarán también sus mascotas, citas y cuenta de usuario.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fa-solid fa-trash-can mr-1"></i>Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    });

    if (!isConfirmed) return;

    try {
        const fd = new FormData();
        fd.append('id', id);
        const res = await fetch(`${BASE_USUARIO}/eliminar_usuario.php`, { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
            await Swal.fire({
                icon: 'success',
                title: 'Eliminado',
                text: data.message || 'Cliente eliminado correctamente.',
                confirmButtonColor: '#0284c7',
                confirmButtonText: 'OK',
                timer: 2500,
                timerProgressBar: true
            });
            cargarUsuarios();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'No se pudo eliminar el cliente.',
                confirmButtonColor: '#0284c7',
                confirmButtonText: 'OK'
            });
        }
    } catch {
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo conectar con el servidor.',
            confirmButtonColor: '#0284c7',
            confirmButtonText: 'OK'
        });
    }
}
