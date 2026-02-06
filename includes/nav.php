<nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1">
    <?php
    // Obtenemos la ruta actual para comparar (ej: /vetweb/admin/citas/citas.php)
    $current_uri = $_SERVER['REQUEST_URI'];

    // Definimos la base de la URL para evitar duplicados
    $base_url = "/vetweb/admin/";

    $menu_items = [
        ['label' => 'Inicio', 'url' => $base_url . 'dashboard.php', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['label' => 'Citas', 'url' => $base_url . 'citas/citas.php', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['label' => 'Usuarios', 'url' => $base_url . 'usuarios/usuarios.php', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ['label' => 'Horarios', 'url' => $base_url . 'horarios/horarios.php', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => 'Servicios', 'url' => $base_url . 'servicios/servicios.php', 'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'],
    ];

    foreach ($menu_items as $item) {
        // Verificamos si la URL del item está contenida en la URI actual
        $isActive = (strpos($current_uri, $item['url']) !== false);

        $class = $isActive ? "bg-brand-50 text-brand-700 font-bold" : "text-gray-600 hover:bg-gray-50 hover:text-gray-900";

        echo "<a href='{$item['url']}' class='flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors group $class'>
                <svg class='mr-3 h-5 w-5 " . ($isActive ? 'text-brand-500' : 'text-gray-400 group-hover:text-gray-500') . "' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='{$item['icon']}'></path>
                </svg>
                {$item['label']}
              </a>";
    }
    ?>
</nav>