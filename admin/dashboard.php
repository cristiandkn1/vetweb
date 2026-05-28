<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }
require_once '../includes/db.php';

$esAdmin = isset($_SESSION['user_role']) && (strtolower($_SESSION['user_role']) === 'admin');
$mes = date('m');
$anio = date('Y');

// Próximas citas de la semana (desde hoy hasta domingo)
$inicioSemana = date('Y-m-d');
$finSemana = date('Y-m-d', strtotime('sunday'));
$stmt = $pdo->prepare("
    SELECT c.id, c.fecha, c.tipo, c.estado, c.token_publico,
           cl.nombre_completo as cliente, cl.telefono as cliente_tel,
           m.nombre as mascota, m.especie
    FROM citas c
    JOIN cliente cl ON c.cliente_id = cl.id
    JOIN mascota m ON c.mascota_id = m.id
    WHERE c.estado IN ('pendiente','confirmada')
      AND DATE(c.fecha) BETWEEN ? AND ?
    ORDER BY c.fecha ASC
");
$stmt->execute([$inicioSemana, $finSemana]);
$citasSemana = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($esAdmin) {
    // Ganancias del mes (ingresos pagados)
    $stmt = $pdo->query("SELECT COALESCE(SUM(monto),0) FROM cuentas WHERE tipo='ingreso' AND estado='pagado' AND MONTH(fecha_contable)=$mes AND YEAR(fecha_contable)=$anio");
    $gananciaMes = $stmt->fetchColumn();

    // Ganancias últimos 6 meses
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(fecha_contable,'%Y-%m') as mes, SUM(monto) as total
        FROM cuentas WHERE tipo='ingreso' AND estado='pagado'
        AND fecha_contable >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY mes ORDER BY mes ASC
    ");
    $gananciasMeses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ganancias por día (este mes)
    $stmt = $pdo->query("
        SELECT DAY(fecha_contable) as dia, SUM(monto) as total
        FROM cuentas WHERE tipo='ingreso' AND estado='pagado'
        AND MONTH(fecha_contable)=$mes AND YEAR(fecha_contable)=$anio
        GROUP BY dia ORDER BY dia ASC
    ");
    $gananciasDias = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Servicios más ofrecidos (desde citas.tipo)
$stmt = $pdo->query("
    SELECT tipo, COUNT(*) as total FROM citas
    WHERE estado IN ('completada','confirmada')
    GROUP BY tipo ORDER BY total DESC LIMIT 8
");
$serviciosTop = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Clientes frecuentes
$stmt = $pdo->query("
    SELECT cl.nombre_completo, cl.telefono, COUNT(c.id) as total
    FROM citas c JOIN cliente cl ON c.cliente_id = cl.id
    WHERE c.estado IN ('completada','confirmada')
    GROUP BY cl.id ORDER BY total DESC LIMIT 5
");
$clientesFrecuentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mascotas frecuentes
$stmt = $pdo->query("
    SELECT m.nombre, m.especie, COUNT(c.id) as total
    FROM citas c JOIN mascota m ON c.mascota_id = m.id
    WHERE c.estado IN ('completada','confirmada')
    GROUP BY m.id ORDER BY total DESC LIMIT 5
");
$mascotasFrecuentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mascotas por especie
$stmt = $pdo->query("SELECT especie, COUNT(*) as total FROM mascota GROUP BY especie ORDER BY total DESC");
$mascotasEspecie = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Seguimientos activos sin interacción reciente (>2h sin interacción, solo pendiente/confirmada)
$pageS = max(1, (int)($_GET['page_seg'] ?? 1));
$perPageS = 10;
$offsetS = ($pageS - 1) * $perPageS;

$stmt = $pdo->prepare("
    SELECT c.id, c.fecha, c.tipo, c.estado, c.token_publico,
           cl.nombre_completo as cliente, m.nombre as mascota,
           GREATEST(
               COALESCE((SELECT MAX(created_at) FROM cita_bitacora WHERE cita_id = c.id), '1970-01-01 00:00:01'),
               COALESCE((SELECT MAX(created_at) FROM cita_notas_internas WHERE cita_id = c.id), '1970-01-01 00:00:01')
           ) as ultima_interaccion
    FROM citas c
    JOIN cliente cl ON c.cliente_id = cl.id
    JOIN mascota m ON c.mascota_id = m.id
    WHERE c.estado IN ('pendiente','confirmada')
      AND GREATEST(
          COALESCE((SELECT MAX(created_at) FROM cita_bitacora WHERE cita_id = c.id), '1970-01-01 00:00:01'),
          COALESCE((SELECT MAX(created_at) FROM cita_notas_internas WHERE cita_id = c.id), '1970-01-01 00:00:01')
      ) < DATE_SUB(NOW(), INTERVAL 2 HOUR)
    ORDER BY ultima_interaccion ASC
    LIMIT $perPageS OFFSET $offsetS
");
$stmt->execute();
$seguimientosPendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total para paginación
$stmt = $pdo->query("
    SELECT COUNT(*) FROM (
        SELECT c.id,
               GREATEST(
                   COALESCE((SELECT MAX(created_at) FROM cita_bitacora WHERE cita_id = c.id), '1970-01-01 00:00:01'),
                   COALESCE((SELECT MAX(created_at) FROM cita_notas_internas WHERE cita_id = c.id), '1970-01-01 00:00:01')
               ) as ultima_interaccion
        FROM citas c
        WHERE c.estado IN ('pendiente','confirmada')
        HAVING ultima_interaccion < DATE_SUB(NOW(), INTERVAL 2 HOUR)
    ) sub
");
$totalSeg = (int)$stmt->fetchColumn();
$totalPagesS = max(1, ceil($totalSeg / $perPageS));

// Controles de mascotas por enfermedad vencidos
$stmt = $pdo->query("
    SELECT me.id, me.enfermedad, me.recurrencia_dias, me.ultimo_control,
           m.id as mascota_id, m.nombre as mascota_nombre, m.especie,
           cl.id as cliente_id, cl.nombre_completo as cliente_nombre, cl.telefono as cliente_telefono,
           DATEDIFF(CURDATE(), DATE_ADD(me.ultimo_control, INTERVAL me.recurrencia_dias DAY)) as dias_vencido
    FROM mascota_enfermedades me
    JOIN mascota m ON me.mascota_id = m.id
    JOIN cliente cl ON m.cliente_id = cl.id
    WHERE me.activo = 1
      AND (me.ultimo_control IS NULL OR DATE_ADD(me.ultimo_control, INTERVAL me.recurrencia_dias DAY) <= CURDATE())
    ORDER BY
        CASE WHEN me.ultimo_control IS NULL THEN 0 ELSE 1 END,
        DATE_ADD(me.ultimo_control, INTERVAL me.recurrencia_dias DAY) ASC
    LIMIT 20
");
$controlesVencidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Totals rápidos
$totalClientes = $pdo->query("SELECT COUNT(*) FROM cliente")->fetchColumn();
$totalMascotas = $pdo->query("SELECT COUNT(*) FROM mascota")->fetchColumn();
$totalCitas = $pdo->query("SELECT COUNT(*) FROM citas")->fetchColumn();
$citasHoy = $pdo->query("SELECT COUNT(*) FROM citas WHERE DATE(fecha)=CURDATE() AND estado NOT IN ('cancelada')")->fetchColumn();
$diasSemana = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../includes/head.php'; ?>
<body class="bg-gray-100 font-sans">
    <?php include '../includes/mobile-header.php'; ?>
    <div class="flex h-screen overflow-hidden">
        <?php include '../includes/sidebar.php'; ?>
        <main class="flex-1 flex flex-col min-w-0 bg-gray-100 overflow-y-auto p-6 md:p-10">
            <div class="container mx-auto max-w-7xl">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Panel de Administración</h1>
                <p class="text-gray-500 mb-8">Resumen general del sistema</p>

                <!-- KPIs -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
                    <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
                        <div class="p-3 rounded-lg bg-blue-50 text-blue-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857"/></svg></div>
                        <div><p class="text-2xl font-bold text-gray-800"><?= $totalClientes ?></p><p class="text-sm text-gray-500">Clientes</p></div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
                        <div class="p-3 rounded-lg bg-amber-50 text-amber-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg></div>
                        <div><p class="text-2xl font-bold text-gray-800"><?= $totalMascotas ?></p><p class="text-sm text-gray-500">Mascotas</p></div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
                        <div class="p-3 rounded-lg bg-indigo-50 text-indigo-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                        <div><p class="text-2xl font-bold text-gray-800"><?= $totalCitas ?></p><p class="text-sm text-gray-500">Citas totales</p></div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
                        <div class="p-3 rounded-lg bg-emerald-50 text-emerald-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                        <div><p class="text-2xl font-bold text-gray-800"><?= $citasHoy ?></p><p class="text-sm text-gray-500">Citas hoy</p></div>
                    </div>
                </div>

                <!-- Próximas citas de la semana -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 mb-8">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Próximas Citas de la Semana</h3>
                    <?php if (count($citasSemana) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-500 uppercase text-xs tracking-wider border-b border-gray-100">
                                    <th class="py-3 pr-4 font-medium">Día</th>
                                    <th class="py-3 pr-4 font-medium">Cliente</th>
                                    <th class="py-3 pr-4 font-medium">Mascota</th>
                                    <th class="py-3 pr-4 font-medium">Tipo</th>
                                    <th class="py-3 font-medium">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($citasSemana as $c): $diaNombre = $diasSemana[(int)date('w', strtotime($c['fecha']))]; ?>
                                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                                    <td class="py-3 pr-4 whitespace-nowrap">
                                        <span class="font-medium text-gray-800"><?= $diaNombre ?></span>
                                        <span class="text-gray-400 ml-1"><?= date('d/m', strtotime($c['fecha'])) ?></span>
                                    </td>
                                    <td class="py-3 pr-4 text-gray-700"><?= htmlspecialchars($c['cliente']) ?></td>
                                    <td class="py-3 pr-4 text-gray-700"><?= htmlspecialchars($c['mascota']) ?></td>
                                    <td class="py-3 pr-4 text-gray-600"><?= htmlspecialchars($c['tipo']) ?></td>
                                    <td class="py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?= $c['estado'] === 'confirmada' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                                            <?= $c['estado'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-sm text-gray-400">No hay citas programadas para esta semana</p>
                    <?php endif; ?>
                </div>

                <!-- Controles de mascotas por enfermedad vencidos -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 mb-8">
                    <h3 class="text-lg font-bold text-gray-800 mb-1">Controles de Enfermedades Pendientes</h3>
                    <p class="text-xs text-gray-400 mb-4">Mascotas que requieren una cita de control por su enfermedad</p>
                    <?php if (count($controlesVencidos) > 0): ?>
                    <div class="space-y-2">
                        <?php foreach ($controlesVencidos as $cv):
                            $telefonoRaw = preg_replace('/\D/', '', $cv['cliente_telefono'] ?? '');
                            $telefono = (substr($telefonoRaw, 0, 2) === '56') ? $telefonoRaw : '56' . $telefonoRaw;
                            $msg = urlencode("Hola, soy de VetWeb. Te recordamos que " . $cv['mascota_nombre'] . " necesita un control de " . $cv['enfermedad'] . ". ¿Podemos agendar una cita?");
                            $sinControl = empty($cv['ultimo_control']);
                        ?>
                        <div class="flex items-center justify-between p-3 rounded-lg bg-red-50 border border-red-100 hover:border-red-200 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-2 h-2 rounded-full <?= $sinControl ? 'bg-amber-500' : 'bg-red-500' ?> shrink-0"></div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">
                                        <?= htmlspecialchars($cv['mascota_nombre']) ?>
                                        <span class="text-gray-400">·</span>
                                        <?= htmlspecialchars($cv['cliente_nombre']) ?>
                                    </p>
                                    <p class="text-xs text-gray-500 truncate">
                                        <?= htmlspecialchars($cv['enfermedad']) ?>
                                        <?php if ($sinControl): ?>
                                        <span class="text-amber-600 font-medium">· Sin control previo</span>
                                        <?php else: ?>
                                        <span class="text-red-600 font-medium">· <?= abs($cv['dias_vencido']) ?> día(s) vencido</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0 ml-3">
                                <button onclick='editarEnfermedad(<?= json_encode(['id' => $cv['id'], 'enfermedad' => $cv['enfermedad'], 'recurrencia_dias' => $cv['recurrencia_dias'], 'ultimo_control' => $cv['ultimo_control'], 'mascota_id' => $cv['mascota_id']]) ?>)' class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                    <i data-lucide="pencil" class="w-3 h-3"></i>
                                </button>
                                <a href="/admin/citas/citas.php" target="_blank" class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-brand-600 bg-brand-50 hover:bg-brand-100 rounded-lg transition">
                                    <i data-lucide="calendar-plus" class="w-3.5 h-3.5"></i> Agendar
                                </a>
                                <?php if (!empty($telefono) && strlen($telefono) >= 10): ?>
                                <a href="https://wa.me/<?= $telefono ?>?text=<?= $msg ?>" target="_blank" class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-emerald-600 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition">
                                    <i data-lucide="message-circle" class="w-3.5 h-3.5"></i> WhatsApp
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                        <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm">No hay controles de enfermedades pendientes</p>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($esAdmin): ?>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Ganancias del mes -->
                    <div class="bg-white rounded-xl border border-gray-200 p-6 lg:col-span-2">
                        <h3 class="text-lg font-bold text-gray-800 mb-1">Ganancias del Mes</h3>
                        <p class="text-3xl font-bold text-emerald-600 mb-4">$<?= number_format($gananciaMes, 0, ',', '.') ?></p>
                        <?php if (count($gananciasDias) > 0): ?>
                        <div class="flex items-end gap-1 h-24">
                            <?php $maxG = max(array_column($gananciasDias, 'total')); ?>
                            <?php for ($d = 1; $d <= (int)date('d'); $d++):
                                $found = current(array_filter($gananciasDias, fn($g) => (int)$g['dia'] === $d));
                                $val = $found ? (int)$found['total'] : 0;
                                $pct = $maxG > 0 ? ($val / $maxG) * 100 : 0;
                            ?>
                            <div class="flex-1 flex flex-col items-center gap-1">
                                <div class="w-full bg-emerald-100 rounded-t" style="height:<?= $pct ?>%"></div>
                                <span class="text-[10px] text-gray-400"><?= $d ?></span>
                            </div>
                            <?php endfor; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-sm text-gray-400">Sin ingresos este mes</p>
                        <?php endif; ?>
                    </div>

                    <!-- Ganancias últimos meses -->
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Últimos Meses</h3>
                        <div class="space-y-3">
                            <?php foreach (array_reverse($gananciasMeses) as $g): ?>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600"><?= date('M Y', strtotime($g['mes'].'-01')) ?></span>
                                <span class="text-sm font-semibold text-gray-800">$<?= number_format((int)$g['total'], 0, ',', '.') ?></span>
                            </div>
                            <?php endforeach; ?>
                            <?php if (empty($gananciasMeses)): ?>
                            <p class="text-sm text-gray-400">Sin datos</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Servicios más ofrecidos -->
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Servicios más Ofrecidos</h3>
                        <?php if (count($serviciosTop) > 0): ?>
                        <div class="space-y-3">
                            <?php $maxS = max(array_column($serviciosTop, 'total')); ?>
                            <?php foreach ($serviciosTop as $s): ?>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-700 font-medium"><?= htmlspecialchars($s['tipo']) ?></span>
                                    <span class="text-gray-500"><?= $s['total'] ?></span>
                                </div>
                                <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-brand-500 rounded-full" style="width:<?= $maxS > 0 ? ($s['total']/$maxS)*100 : 0 ?>%"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-sm text-gray-400">Sin datos</p>
                        <?php endif; ?>
                    </div>

                    <!-- Mascotas por especie -->
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Mascotas por Especie</h3>
                        <?php if (count($mascotasEspecie) > 0): ?>
                        <div class="space-y-3">
                            <?php $maxE = max(array_column($mascotasEspecie, 'total')); ?>
                            <?php foreach ($mascotasEspecie as $e): ?>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-700 font-medium"><?= htmlspecialchars($e['especie'] ?: 'Sin especificar') ?></span>
                                    <span class="text-gray-500"><?= $e['total'] ?></span>
                                </div>
                                <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-amber-500 rounded-full" style="width:<?= $maxE > 0 ? ($e['total']/$maxE)*100 : 0 ?>%"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-sm text-gray-400">Sin mascotas registradas</p>
                        <?php endif; ?>
                    </div>

                    <!-- Clientes frecuentes -->
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Clientes Frecuentes</h3>
                        <?php if (count($clientesFrecuentes) > 0): ?>
                        <div class="space-y-3">
                            <?php foreach ($clientesFrecuentes as $i => $cl): ?>
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full <?= ['bg-amber-100 text-amber-700','bg-blue-100 text-blue-700','bg-green-100 text-green-700','bg-purple-100 text-purple-700','bg-pink-100 text-pink-700'][$i] ?? 'bg-gray-100 text-gray-600' ?> flex items-center justify-center text-xs font-bold"><?= $i+1 ?></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate"><?= htmlspecialchars($cl['nombre_completo']) ?></p>
                                    <p class="text-xs text-gray-400"><?= htmlspecialchars($cl['telefono'] ?? '') ?></p>
                                </div>
                                <span class="text-sm font-semibold text-gray-600"><?= $cl['total'] ?> citas</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-sm text-gray-400">Sin datos</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Mascotas frecuentes -->
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Mascotas Frecuentes</h3>
                        <?php if (count($mascotasFrecuentes) > 0): ?>
                        <div class="space-y-3">
                            <?php foreach ($mascotasFrecuentes as $i => $m): ?>
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full <?= ['bg-amber-100 text-amber-700','bg-blue-100 text-blue-700','bg-green-100 text-green-700','bg-purple-100 text-purple-700','bg-pink-100 text-pink-700'][$i] ?? 'bg-gray-100 text-gray-600' ?> flex items-center justify-center text-xs font-bold"><?= $i+1 ?></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate"><?= htmlspecialchars($m['nombre']) ?></p>
                                    <p class="text-xs text-gray-400"><?= htmlspecialchars($m['especie'] ?? '') ?></p>
                                </div>
                                <span class="text-sm font-semibold text-gray-600"><?= $m['total'] ?> citas</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-sm text-gray-400">Sin datos</p>
                        <?php endif; ?>
                    </div>

                    <!-- Seguimientos sin interacción reciente -->
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Seguimientos sin Interacción</h3>
                        <p class="text-xs text-gray-400 mb-4">Citas activas sin interacción hace más de 2 horas</p>
                        <?php if (count($seguimientosPendientes) > 0): ?>
                        <div class="space-y-2">
                            <?php foreach ($seguimientosPendientes as $s):
                                $ultima = $s['ultima_interaccion'];
                                $sinInt = strpos($ultima, '1970') === 0 || $ultima === null;
                                $diff = $sinInt ? PHP_INT_MAX : time() - strtotime($ultima);
                                $horas = $sinInt ? -1 : floor($diff / 3600);
                                $minutos = $sinInt ? 0 : floor(($diff % 3600) / 60);
                            ?>
                            <a href="/citas/seguimiento_cita.php?token=<?= htmlspecialchars($s['token_publico']) ?>" target="_blank" class="flex items-center justify-between p-3 rounded-lg hover:bg-amber-50 transition-colors border border-transparent hover:border-amber-200">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate"><?= htmlspecialchars($s['cliente']) ?> · <?= htmlspecialchars($s['mascota']) ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($s['tipo']) ?> · <?= date('d/m', strtotime($s['fecha'])) ?></p>
                                </div>
                                <div class="text-right shrink-0 ml-4">
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold whitespace-nowrap <?= $sinInt ? 'text-red-600' : ($horas >= 8 ? 'text-red-600' : ($horas >= 4 ? 'text-amber-600' : 'text-gray-500')) ?>">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <?= $sinInt ? 'Sin interacción' : ($horas > 0 ? "$horas h $minutos min" : "$minutos min") ?>
                                    </span>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($totalPagesS > 1): ?>
                        <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
                            <p class="text-xs text-gray-400">Pág. <?= $pageS ?> de <?= $totalPagesS ?></p>
                            <div class="flex gap-1">
                                <?php if ($pageS > 1): ?>
                                <a href="?page_seg=<?= $pageS - 1 ?>" class="px-2.5 py-1 text-xs rounded border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">Anterior</a>
                                <?php endif; ?>
                                <?php if ($pageS < $totalPagesS): ?>
                                <a href="?page_seg=<?= $pageS + 1 ?>" class="px-2.5 py-1 text-xs rounded border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">Siguiente</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php else: ?>
                        <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                            <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-sm">Todos los seguimientos están al día</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script>
        lucide.createIcons();

        async function editarEnfermedad(data) {
            const { value: formValues, isConfirmed } = await Swal.fire({
                title: 'Editar Enfermedad',
                html: `
                    <div class="text-left space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Enfermedad</label>
                            <input id="swal-enf" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" value="${data.enfermedad}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cada cuántos días</label>
                            <input id="swal-rec" type="number" min="1" value="${data.recurrencia_dias}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Último control</label>
                            <input id="swal-uc" type="date" value="${data.ultimo_control || ''}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>
                `,
                focusConfirm: false,
                confirmButtonText: 'Guardar',
                showCancelButton: true,
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const enf = document.getElementById('swal-enf').value.trim();
                    if (!enf) { Swal.showValidationMessage('Ingrese la enfermedad'); return false; }
                    return {
                        enfermedad: enf,
                        recurrencia_dias: parseInt(document.getElementById('swal-rec').value) || 60,
                        ultimo_control: document.getElementById('swal-uc').value || ''
                    };
                }
            });

            if (!isConfirmed || !formValues) return;

            const fd = new FormData();
            fd.append('id', data.id);
            fd.append('mascota_id', data.mascota_id);
            fd.append('enfermedad', formValues.enfermedad);
            fd.append('recurrencia_dias', formValues.recurrencia_dias);
            fd.append('ultimo_control', formValues.ultimo_control);

            try {
                const res = await fetch('/admin/usuarios/api/guardar_enfermedad.php', { method: 'POST', body: fd });
                const r = await res.json();
                if (r.success) {
                    Swal.fire({ icon: 'success', title: 'Enfermedad actualizada', timer: 1500, showConfirmButton: false });
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: r.message });
                }
            } catch {
                Swal.fire({ icon: 'error', title: 'Error de conexión' });
            }
        }
    </script>
    <script src="scripts/sidebar.js"></script>
</body>
</html>
