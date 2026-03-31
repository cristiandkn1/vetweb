// admin/usuario/scripts/init-usuario.js
// Responsabilidad: punto de entrada, delega eventos de la tabla a los módulos

document.addEventListener('DOMContentLoaded', () => {

    // Buscador
    document.getElementById('buscador-usuario').addEventListener('input', () => {
        window.UsuarioState.paginaActual = 1;
        renderTablaUsuarios();
    });

    // Delegación de clicks en la tabla
    document.getElementById('tabla-usuarios-body').addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;

        const { id, action, nombre } = btn.dataset;

        if (action === 'editar') {
            const usuario = window.UsuarioState.todos.find(u => u.id == id);
            if (usuario) abrirModalEditarUsuario(usuario);
        }

        if (action === 'eliminar') {
            abrirModalEliminarUsuario(id, nombre);
        }

        if (action === 'mascotas') {
            const usuario = window.UsuarioState.todos.find(u => u.id == id);
            if (usuario) abrirModalMascotas(id, usuario.nombre_completo);
        }
    });

    // Carga inicial
    cargarUsuarios();
});