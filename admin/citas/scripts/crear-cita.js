// admin/citas/scripts/crear-cita.js

document.addEventListener('DOMContentLoaded', () => {
    // Referencias al DOM
    const btnAbrir = document.getElementById('btn-nueva-cita');
    const modal = document.getElementById('modal-nueva-cita');
    const btnCerrarX = document.getElementById('btn-cerrar-modal');
    const btnCancelar = document.getElementById('btn-cancelar-modal');
    
    // Referencias lógica mascota
    const btnNuevaMascota = document.getElementById('btn-nueva-mascota');
    const btnCancelarMascota = document.getElementById('btn-cancelar-mascota');
    const containerSelect = document.getElementById('container-select-mascota');
    const formNuevaMascota = document.getElementById('form-nueva-mascota');

    // --- LÓGICA DEL MODAL ---
    
    function toggleModal(show) {
        if (show) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
            // Opcional: limpiar formulario al cerrar
            // document.getElementById('form-crear-cita').reset();
        }
    }

    // Abrir modal
    if(btnAbrir) btnAbrir.addEventListener('click', () => toggleModal(true));

    // Cerrar modal
    if(btnCerrarX) btnCerrarX.addEventListener('click', () => toggleModal(false));
    if(btnCancelar) btnCancelar.addEventListener('click', () => toggleModal(false));

    // Cerrar al hacer click fuera (en el overlay)
    modal.addEventListener('click', (e) => {
        if (e.target === modal.querySelector('.bg-gray-900\\/50')) {
            toggleModal(false);
        }
    });

    // --- LÓGICA DE NUEVA MASCOTA ---

    // Mostrar campos de nueva mascota y ocultar el select
    btnNuevaMascota.addEventListener('click', () => {
        containerSelect.classList.add('hidden');
        formNuevaMascota.classList.remove('hidden');
        // Aquí podrías limpiar el valor del select para asegurar que el backend sepa que es nueva
        document.getElementById('mascota_id').value = "";
    });

    // Cancelar nueva mascota y volver al select
    btnCancelarMascota.addEventListener('click', () => {
        formNuevaMascota.classList.add('hidden');
        containerSelect.classList.remove('hidden');
    });

    // --- MANEJO DEL FORMULARIO (Placeholder para tu API) ---
    const form = document.getElementById('form-crear-cita');
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        console.log("Enviando datos a api/crear_cita.php...");
        
        // Aquí iría tu fetch a la carpeta api
        // const formData = new FormData(form);
        // fetch('api/crear_cita.php', { method: 'POST', body: formData })...
        
        alert("Funcionalidad de backend pendiente de conectar a /api");
        toggleModal(false);
    });
});