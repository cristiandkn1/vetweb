<?php
// admin/usuarios/componentes/modal-mascotas.php
// Punto de entrada — incluye los 3 modales
?>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

<?php require_once __DIR__ . '/modal-mascotas-listado.php'; ?>
<?php require_once __DIR__ . '/modal-mascotas-form.php'; ?>
<?php require_once __DIR__ . '/modal-mascotas-eliminar.php'; ?>

<style>
    .especie-opcion input:checked+.especie-card {
        border-color: #2563eb;
        background-color: #eff6ff;
        color: #2563eb;
    }

    .especie-opcion input:checked+.especie-card svg,
    .especie-opcion input:checked+.especie-card i {
        color: #2563eb;
    }

    .especie-opcion input:checked+.especie-card span {
        color: #1d4ed8;
        font-weight: 600;
    }
</style>