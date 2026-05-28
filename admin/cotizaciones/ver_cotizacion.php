<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

require_once __DIR__ . '/../../includes/db.php';

// ============================================================
// DATOS DE LA EMPRESA — Edítalos directamente aquí
// ============================================================
$EMPRESA = [
    'nombre'       => 'VetWeb Clínica Veterinaria SpA.',
    'rut'          => '77.123.456-7',
    'giro'         => 'Servicios Veterinarios',
    'direccion'    => 'Av. Los Presidentes 1234, Local 5',
    'comuna'       => 'Providencia',
    'ciudad'       => 'Santiago',
    'telefono'     => '+56 2 2123 4567',
    'email'        => 'contacto@vetweb.cl',
    'web'          => 'www.vetweb.cl',
];

$id = intval($_GET['id'] ?? 0);
if ($id === 0) {
    die("ID de cotización inválido.");
}

$stmt = $pdo->prepare("
    SELECT 
        co.*,
        cl.nombre_completo AS cliente_nombre,
        cl.telefono AS cliente_telefono,
        cl.email AS cliente_email,
        cl.direccion AS cliente_direccion,
        m.nombre AS mascota_nombre,
        m.especie AS mascota_especie,
        m.raza AS mascota_raza,
        m.peso AS mascota_peso,
        ct.fecha AS cita_fecha
    FROM cotizaciones co
    JOIN cliente cl ON co.cliente_id = cl.id
    JOIN mascota m ON co.mascota_id = m.id
    LEFT JOIN citas ct ON co.cita_id = ct.id
    WHERE co.id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$cot = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cot) {
    die("Cotización no encontrada.");
}

$fecha_cot = new DateTime($cot['created_at']);
$fecha_venc = (clone $fecha_cot)->modify('+7 days');

$stmtDet = $pdo->prepare("SELECT * FROM cotizacion_detalles WHERE cotizacion_id = ? ORDER BY id ASC");
$stmtDet->execute([$id]);
$detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

if (empty($detalles) && !empty($cot['servicio'])) {
    $precio = max((float)($cot['precio_estimado_min'] ?? 0), (float)($cot['precio_estimado_max'] ?? 0));
    $detalles[] = ['descripcion' => $cot['servicio'], 'cantidad' => 1, 'precio_unitario' => $precio > 0 ? $precio : 0, 'afecto_iva' => 1];
}

$total = 0;
$IVA_PCT = 19;
foreach ($detalles as $d) {
    $sub_base = (int)$d['cantidad'] * (float)$d['precio_unitario'];
    $total += (int)($d['afecto_iva'] ?? 1) ? round($sub_base * (1 + $IVA_PCT / 100)) : $sub_base;
}

function fmtPeso($n) {
    return '$' . number_format($n, 0, ',', '.');
}

function esc($s) {
    return htmlspecialchars($s ?? '');
}

$estado_clases = [
    'pendiente' => 'bg-yellow-100 text-yellow-700',
    'aprobada'  => 'bg-green-100 text-green-700',
    'rechazada' => 'bg-red-100 text-red-600',
    'vencida'   => 'bg-gray-100 text-gray-500',
];
$estado_cls = $estado_clases[$cot['estado']] ?? 'bg-gray-100 text-gray-600';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotización <?php echo esc($cot['numero_cotizacion']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .print-page { break-inside: avoid; }
            @page { margin: 1cm; }
        }
    </style>
</head>
<body class="min-h-screen">

    <!-- Toolbar -->
    <div class="no-print max-w-4xl mx-auto pt-6 pb-3 px-4 flex justify-between items-center">
        <div class="flex items-center gap-2">
            <a href="cotizaciones.php" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver
            </a>
            <a href="editar_cotizacion.php?id=<?php echo $id; ?>"
               class="inline-flex items-center gap-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2.5 rounded-lg shadow-sm transition-colors text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Editar
            </a>
        </div>
        <button onclick="window.print()"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg shadow-sm transition-colors text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Imprimir
        </button>
    </div>

    <!-- Documento -->
    <div class="max-w-4xl mx-auto pb-6 px-4">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden print:shadow-none print:rounded-none">

            <!-- ─── HEADER: Empresa ─── -->
            <div class="px-8 pt-6 pb-4 print:px-6 print:pt-4 flex justify-between items-start border-b border-gray-200">
                <div class="flex items-center gap-4">
                    <!-- Logo placeholder -->
                    <div class="w-14 h-14 bg-gray-50 rounded-lg border border-gray-200 flex items-center justify-center text-gray-300 text-[10px] font-semibold uppercase shrink-0">
                        Logo
                    </div>
                    <div>
                        <h1 class="text-xl font-black text-gray-800 tracking-tight"><?php echo esc($EMPRESA['nombre']); ?></h1>
                        <div class="mt-1 text-sm text-gray-500 leading-snug">
                            <p><?php echo esc($EMPRESA['rut']); ?> · <?php echo esc($EMPRESA['direccion']); ?>, <?php echo esc($EMPRESA['comuna']); ?></p>
                            <p>Tel: <?php echo esc($EMPRESA['telefono']); ?> · Email: <?php echo esc($EMPRESA['email']); ?> · Giro: <?php echo esc($EMPRESA['giro']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="text-right shrink-0 ml-4">
                    <span class="inline-block text-xs font-bold text-gray-400 uppercase tracking-widest mb-0.5">Cotización</span>
                    <p class="text-2xl font-extrabold text-gray-800"><?php echo esc($cot['numero_cotizacion']); ?></p>
                    <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $estado_cls; ?>">
                        <?php echo esc(ucfirst($cot['estado'])); ?>
                    </span>
                </div>
            </div>

            <div class="px-8 py-5 print:px-6 space-y-5">

                <!-- ─── Emisión y Vencimiento ─── -->
                <div class="flex gap-8 text-sm print-page">
                    <div>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Emisión</span>
                        <p class="font-medium text-gray-800"><?php echo $fecha_cot->format('d/m/Y'); ?></p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Vencimiento</span>
                        <p class="font-medium text-gray-800"><?php echo $fecha_venc->format('d/m/Y'); ?></p>
                    </div>
                </div>

                <!-- ─── Cliente ─── -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100 print-page">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Cliente</span>
                            <p class="text-base font-bold text-gray-800 mt-0.5"><?php echo esc($cot['cliente_nombre']); ?></p>
                            <?php if (!empty($cot['cliente_telefono']) || !empty($cot['cliente_email']) || !empty($cot['cliente_direccion'])): ?>
                                <p class="text-sm text-gray-500 mt-0.5">
                                    <?php echo esc($cot['cliente_telefono']); ?>
                                    <?php if (!empty($cot['cliente_telefono']) && !empty($cot['cliente_email'])): echo ' · '; endif; ?>
                                    <?php echo esc($cot['cliente_email']); ?>
                                    <?php if ((!empty($cot['cliente_telefono']) || !empty($cot['cliente_email'])) && !empty($cot['cliente_direccion'])): echo ' · '; endif; ?>
                                    <?php echo esc($cot['cliente_direccion']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="text-right text-sm text-gray-500">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Paciente</span>
                            <p class="font-semibold text-gray-700 mt-0.5"><?php echo esc($cot['mascota_nombre']); ?></p>
                            <p><?php echo esc(ucfirst($cot['mascota_especie'] ?? '')); ?>
                            <?php if (!empty($cot['mascota_raza'])): echo ' · ' . esc($cot['mascota_raza']); endif; ?>
                            <?php if (!empty($cot['mascota_peso'])): echo ' · ' . esc($cot['mascota_peso']) . ' kg'; endif; ?>
                            <?php if (!empty($cot['cita_fecha'])): echo '<br>Atención: ' . (new DateTime($cot['cita_fecha']))->format('d/m/Y H:i'); endif; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ─── Tabla de Servicios ─── -->
                <div class="print-page">
                    <div class="rounded-lg border border-gray-200 overflow-hidden">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-500 uppercase tracking-wider w-8">#</th>
                                    <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Descripción</th>
                                    <th class="text-center px-4 py-2.5 text-xs font-bold text-gray-500 uppercase tracking-wider w-14">Cant.</th>
                                    <th class="text-right px-4 py-2.5 text-xs font-bold text-gray-500 uppercase tracking-wider">P. Unitario</th>
                                    <th class="text-center px-4 py-2.5 text-xs font-bold text-gray-500 uppercase tracking-wider w-16">IVA</th>
                                    <th class="text-right px-4 py-2.5 text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detalles as $i => $d):
                                    $sub_base = (int)$d['cantidad'] * (float)$d['precio_unitario'];
                                    $afecto = (int)($d['afecto_iva'] ?? 1);
                                    $sub_total = $afecto ? round($sub_base * (1 + $IVA_PCT / 100)) : $sub_base;
                                ?>
                                <tr class="border-b border-gray-100">
                                    <td class="px-4 py-3 text-center text-sm text-gray-400 font-mono"><?php echo $i + 1; ?></td>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-gray-800 text-sm"><?php echo esc($d['descripcion']); ?></p>
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-700 text-sm"><?php echo (int)$d['cantidad']; ?></td>
                                    <td class="px-4 py-3 text-right text-gray-700 text-sm"><?php echo fmtPeso((float)$d['precio_unitario']); ?></td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if ($afecto): ?>
                                            <span class="inline-block text-[11px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">Incluye</span>
                                        <?php else: ?>
                                            <span class="inline-block text-[11px] font-medium text-gray-400 bg-gray-50 px-2 py-0.5 rounded-full border border-gray-200">Exento</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-800 text-sm"><?php echo fmtPeso($sub_total); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- ─── Total ─── -->
                    <div class="flex justify-end mt-3">
                        <div class="text-right">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-medium">Total</p>
                            <p class="text-2xl font-black text-emerald-700"><?php echo fmtPeso($total); ?></p>
                            <p class="text-[11px] text-gray-400 mt-0.5">* Valores en pesos chilenos</p>
                        </div>
                    </div>
                </div>

                <!-- ─── Notas ─── -->
                <?php if (!empty($cot['nota'])): ?>
                <div class="bg-amber-50 rounded-lg p-3 border border-amber-100 print-page">
                    <h3 class="text-[11px] font-bold text-amber-500 uppercase tracking-wider mb-1">Notas</h3>
                    <p class="text-xs text-amber-900 leading-snug"><?php echo nl2br(esc($cot['nota'])); ?></p>
                </div>
                <?php endif; ?>

                <!-- ─── Términos ─── -->
                <div class="border-t border-gray-200 pt-3 print-page">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-2 text-xs text-gray-500 leading-snug">
                        <div>
                            <p class="font-semibold text-gray-700 mb-0.5">Vigencia</p>
                            <p>7 días corridos desde la emisión. Precios y condiciones pueden variar después de este período.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-700 mb-0.5">Formas de Pago</p>
                            <p>Efectivo, débito/crédito, transferencia, cheque al día.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-700 mb-0.5">Condiciones</p>
                            <p>Servicios previa confirmación del cliente. Adicionales se presupuestan por separado.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-700 mb-0.5">Contacto</p>
                            <p><?php echo esc($EMPRESA['telefono']); ?><br>
                               <?php echo esc($EMPRESA['email']); ?><br>
                               <?php echo esc($EMPRESA['web']); ?></p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ─── Footer ─── -->
            <div class="bg-gray-50 border-t border-gray-100 px-8 py-2.5 text-center print:px-6 print:py-2">
                <p class="text-[11px] text-gray-400">
                    <?php echo esc($EMPRESA['nombre']); ?> · RUT <?php echo esc($EMPRESA['rut']); ?> · <?php echo esc($cot['numero_cotizacion']); ?>
                </p>
            </div>
        </div>
    </div>

    <script>
        const params = new URLSearchParams(window.location.search);
        if (params.get('print') === '1') {
            window.addEventListener('load', () => setTimeout(() => window.print(), 500));
        }
    </script>
</body>
</html>
