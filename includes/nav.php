<nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1">
    <?php
    $current_uri = $_SERVER['REQUEST_URI'];
    $base_url = "/admin/";

    function isActive($url) {
        global $current_uri;
        return strpos($current_uri, $url) !== false;
    }

    function navItem($label, $url, $icon) {
        $active = isActive($url);
        $cls = $active ? "bg-brand-50 text-brand-700 font-bold" : "text-gray-600 hover:bg-gray-50 hover:text-gray-900";
        $icn = $active ? 'text-brand-500' : 'text-gray-400 group-hover:text-gray-500';
        return "<a href='$url' class='flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-colors group $cls'>
                <svg class='mr-3 h-5 w-5 $icn' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='$icon'></path>
                </svg>
                $label
              </a>";
    }

    function category($label, $icon, $items, $open = false) {
        global $current_uri;
        $anyActive = false;
        foreach ($items as $item) {
            if (strpos($current_uri, $item[1]) !== false) { $anyActive = true; break; }
        }
        $open = $open || $anyActive;
        $catId = 'cat-' . preg_replace('/[^a-z]/', '', strtolower($label));
        $arrowCls = $anyActive ? 'text-brand-500' : 'text-gray-400';
        $arrowRot = $open ? '' : '-rotate-90';
        echo "<div class='mb-1'>
            <button onclick='toggleCat(\"$catId\")' class='w-full flex items-center px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-50 transition-colors group'>
                <svg class='mr-3 h-4 w-4 $arrowCls transition-transform $arrowRot' id='{$catId}-arrow' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/>
                </svg>
                <span>$label</span>
            </button>
            <div id='$catId' class='ml-2 space-y-0.5 " . ($open ? '' : 'hidden') . "'>
                ";
        foreach ($items as $item) {
            echo navItem($item[0], $item[1], $item[2]);
        }
        echo "  </div>
        </div>";
    }
    ?>

    <?= navItem('Inicio', $base_url . 'dashboard.php', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6') ?>

    <?php category('Agenda', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', [
        ['Citas',      $base_url . 'citas/citas.php',                  'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['Solicitudes',$base_url . 'solicitudes/solicitudes.php',      'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4'],
    ]) ?>

    <?php category('Pacientes', 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z', [
        ['Clientes',   $base_url . 'usuarios/usuarios.php',            'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ['Mascotas',   $base_url . 'mascotas/mascotas.php',           'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
    ], true) ?>

    <?php category('Finanzas', 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z', [
        ['Cuentas',    $base_url . 'cuentas/cuentas.php',             'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Cotizaciones',$base_url . 'cotizaciones/cotizaciones.php',  'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
    ]) ?>

    <?php category('Gestión', 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4', [
        ['Horarios',   $base_url . 'horarios/',                       'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Servicios',  $base_url . 'servicios/servicios.php',         'M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'],
        ['Inventario', $base_url . 'inventario/',                     'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
    ]) ?>
</nav>

<script>
function toggleCat(id) {
    const el = document.getElementById(id);
    const arrow = document.getElementById(id + '-arrow');
    if (!el) return;
    el.classList.toggle('hidden');
    arrow.classList.toggle('rotate-0');
    arrow.classList.toggle('-rotate-90');
}
</script>