<?php
// /mascota/vista-estado-mascota.php
require_once __DIR__ . '/../includes/db.php';

$token = trim($_GET['token'] ?? '');
if ($token === '') {
    // Fallback por ID solo si viene de admin (session activa)
    session_start();
    if (isset($_SESSION['user_id'])) {
        $id = intval($_GET['id'] ?? 0);
        if ($id === 0) {
            die("Token o ID de mascota no proporcionado.");
        }
        $stmt = $pdo->prepare("SELECT m.*, c.nombre_completo as cliente_nombres, c.telefono as cliente_telefono FROM mascota m LEFT JOIN cliente c ON m.cliente_id = c.id WHERE m.id = ?");
        $stmt->execute([$id]);
    } else {
        die("Token de mascota no proporcionado.");
    }
} else {
    $stmt = $pdo->prepare("SELECT m.*, c.nombre_completo as cliente_nombres, c.telefono as cliente_telefono FROM mascota m LEFT JOIN cliente c ON m.cliente_id = c.id WHERE m.token_publico = ?");
    $stmt->execute([$token]);
}
$mascota = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mascota) {
    $id = intval($_GET['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM mascota WHERE id = ?");
        $stmt->execute([$id]);
        $mascota = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    if (!$mascota) {
        die("Mascota no encontrada en el sistema.");
    }
}

$mascotaId = $mascota['id'];

// Obtener vacunas
$stmtVacunas = $pdo->prepare("SELECT * FROM vacuna WHERE mascota_id = ? ORDER BY fecha_aplicacion DESC");
$stmtVacunas->execute([$mascotaId]);
$vacunas = $stmtVacunas->fetchAll(PDO::FETCH_ASSOC);

// Obtener historial de citas
$stmtCitas = $pdo->prepare("SELECT c.id, c.fecha, c.tipo, c.estado, c.token_publico FROM citas c WHERE c.mascota_id = ? AND c.oculta = 0 ORDER BY c.fecha DESC LIMIT 10");
$stmtCitas->execute([$mascotaId]);
$citas = $stmtCitas->fetchAll(PDO::FETCH_ASSOC);

function calcularEdadStr($fechaNac) {
    if (!$fechaNac) return 'Desconocida';
    try {
        $nac = new DateTime($fechaNac);
        $hoy = new DateTime();
        $diferencia = $hoy->diff($nac);
        
        if ($diferencia->y == 0) {
            if ($diferencia->m == 0) {
                return $diferencia->d . ' días';
            }
            return $diferencia->m . ' meses';
        }
        return $diferencia->y . ' años y ' . $diferencia->m . ' meses';
    } catch (Exception $e) {
        return 'Desconocida';
    }
}
$edad = calcularEdadStr($mascota['fecha_nacimiento']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado Clínico de <?php echo htmlspecialchars($mascota['nombre'] ?? ''); ?> - VetWeb</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body class="bg-gray-50 min-h-screen font-sans text-gray-800">

<div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
    
    <!-- Encabezado -->
    <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                <i data-lucide="paw-print" class="w-8 h-8 text-blue-600"></i>
                Perfil Clínico: <?php echo htmlspecialchars($mascota['nombre'] ?? ''); ?>
            </h1>
            <p class="text-sm text-gray-500 mt-2">
                <strong>Dueño/a:</strong> <?php echo htmlspecialchars($mascota['cliente_nombres'] ?? 'Cliente'); ?> 
                <?php echo !empty($mascota['cliente_telefono']) ? ' | <strong>Tel:</strong> ' . htmlspecialchars($mascota['cliente_telefono']) : ''; ?>
            </p>
        </div>
        <div class="self-start sm:self-center">
            <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800 shadow-sm border border-emerald-200">
                <i data-lucide="check-circle-2" class="w-4 h-4"></i> Vista Cliente
            </span>
        </div>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Columna Izquierda: Info Básica -->
        <div class="md:col-span-1 space-y-6">
            <!-- Tarjeta Info -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-50 pb-2 flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4"></i> Datos de la Mascota
                </h3>
                <ul class="space-y-3 text-sm">
                    <li class="flex justify-between items-center"><span class="text-gray-500">Especie:</span> <span class="font-medium bg-gray-50 px-2 py-0.5 rounded"><?php echo htmlspecialchars($mascota['especie'] ?: '-'); ?></span></li>
                    <li class="flex justify-between items-center"><span class="text-gray-500">Raza:</span> <span class="font-medium text-right"><?php echo htmlspecialchars($mascota['raza'] ?: '-'); ?></span></li>
                    <li class="flex justify-between items-center"><span class="text-gray-500">Sexo:</span> <span class="font-medium"><?php echo htmlspecialchars($mascota['sexo'] ?: '-'); ?></span></li>
                    <li class="flex justify-between items-center"><span class="text-gray-500">Edad:</span> <span class="font-medium"><?php echo htmlspecialchars($edad); ?></span></li>
                    <li class="flex justify-between items-center"><span class="text-gray-500">Peso:</span> <span class="font-medium"><?php echo !empty($mascota['peso']) ? htmlspecialchars($mascota['peso']) . ' kg' : '-'; ?></span></li>
                    <li class="flex justify-between items-center"><span class="text-gray-500">Color:</span> <span class="font-medium"><?php echo htmlspecialchars($mascota['color'] ?: '-'); ?></span></li>
                    <li class="flex justify-between items-center"><span class="text-gray-500">Esterilizado/a:</span> <span class="font-medium <?php echo !empty($mascota['esterilizado']) ? 'text-teal-600' : ''; ?>"><?php echo !empty($mascota['esterilizado']) ? 'Sí' : 'No'; ?></span></li>
                    <li class="flex flex-col mt-2 pt-2 border-t border-gray-50">
                        <span class="text-gray-500 mb-1">Nº Microchip:</span> 
                        <span class="font-mono text-xs bg-indigo-50 text-indigo-700 px-2 py-1 rounded w-fit"><?php echo htmlspecialchars($mascota['numero_chip'] ?: 'No registrado'); ?></span>
                    </li>
                </ul>
            </div>
            
            <?php if (!empty($mascota['alergias'])): ?>
            <div class="bg-red-50 rounded-2xl p-5 shadow-sm border border-red-100">
                <h3 class="text-sm font-semibold text-red-800 uppercase tracking-wider mb-2 flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i> Alergias Conocidas
                </h3>
                <p class="text-red-700 text-sm italic"><?php echo nl2br(htmlspecialchars($mascota['alergias'])); ?></p>
            </div>
            <?php endif; ?>

            <?php if (count($citas) > 0): ?>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3 border-b border-gray-50 pb-2 flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4"></i> Historial de Citas
                </h3>
                <ul class="space-y-2">
                    <?php foreach ($citas as $c): 
                        $estadoCls = match($c['estado']) {
                            'pendiente'  => 'bg-yellow-100 text-yellow-700',
                            'confirmada' => 'bg-blue-100 text-blue-700',
                            'completada' => 'bg-green-100 text-green-700',
                            'cancelada'  => 'bg-red-100 text-red-600',
                            default      => 'bg-gray-100 text-gray-600'
                        };
                    ?>
                    <li class="flex items-center justify-between gap-2 p-2.5 rounded-xl hover:bg-gray-50 transition-colors border border-gray-50">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-400"><?php echo date('d/m/Y H:i', strtotime($c['fecha'])); ?></p>
                            <p class="text-sm font-medium text-gray-700 truncate"><?php echo htmlspecialchars($c['tipo']); ?></p>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full <?php echo $estadoCls; ?>"><?php echo ucfirst($c['estado']); ?></span>
                            <?php if (!empty($c['token_publico'])): ?>
                            <a href="/citas/seguimiento_cita.php?token=<?php echo urlencode($c['token_publico']); ?>" target="_blank" title="Ver seguimiento"
                                class="p-1 text-brand-600 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 rounded transition-colors">
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>

        <!-- Columna Derecha: Observaciones y Vacunas -->
        <div class="md:col-span-2 space-y-6">
            
            <!-- OBSERVACIONES (Destacado) -->
            <div class="bg-amber-50 rounded-2xl p-6 shadow-md border border-amber-200 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-amber-100 rounded-bl-full -z-0 opacity-50"></div>
                <h2 class="text-xl font-bold text-amber-900 flex items-center gap-2 mb-4 relative z-10">
                    <i data-lucide="stethoscope" class="w-6 h-6 text-amber-600"></i>
                    Estado Actual y Procedimientos
                </h2>
                
                <div class="relative z-10">
                    <?php if (!empty($mascota['observaciones'])): ?>
                        <div class="prose prose-amber prose-sm max-w-none text-amber-900 font-medium text-base bg-white/60 p-4 rounded-xl border border-amber-100 shadow-sm">
                            <?php echo nl2br(htmlspecialchars($mascota['observaciones'])); ?>
                        </div>
                    <?php else: ?>
                        <div class="bg-white/50 p-4 rounded-xl border border-amber-100">
                            <p class="text-amber-700/60 italic text-sm">El veterinario no ha reportado ninguna observación formal en el registro clínico en este momento.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mt-5 pt-4 border-t border-amber-200/50 flex flex-wrap items-center justify-between gap-2 text-xs text-amber-700 relative z-10">
                    <span class="bg-amber-100 px-2 py-1 rounded">Fecha de revisión médica: <?php echo !empty($mascota['ultima_revision']) ? date('d/m/Y', strtotime($mascota['ultima_revision'])) : 'No especificada'; ?></span>
                    <span class="opacity-75">Última actualización: <?php echo !empty($mascota['fecha_actualizacion']) ? date('d/m/Y H:i', strtotime($mascota['fecha_actualizacion'])) : 'N/A'; ?></span>
                </div>
            </div>

            <!-- VACUNAS -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-5 border-b border-gray-50 pb-3">
                    <i data-lucide="syringe" class="w-5 h-5 text-blue-500"></i>
                    Historial de Vacunas
                </h2>
                
                <?php if (count($vacunas) > 0): ?>
                    <div class="space-y-4">
                        <?php foreach($vacunas as $v): ?>
                            <div class="relative pl-5 border-l-2 border-blue-200 <?php echo (!empty($v['fecha_proxima']) && strtotime($v['fecha_proxima']) < time()) ? 'bg-orange-50/50 rounded-r-xl border-orange-300' : ''; ?> py-2 pr-2 transition hover:bg-gray-50">
                                <div class="absolute w-3 h-3 rounded-full <?php echo (!empty($v['fecha_proxima']) && strtotime($v['fecha_proxima']) < time()) ? 'bg-orange-500 shadow-[0_0_0_3px_#fff3cd]' : 'bg-blue-500 shadow-[0_0_0_3px_#eff6ff]'; ?> -left-[7px] top-3"></div>
                                
                                <h4 class="font-bold text-gray-800 text-base"><?php echo htmlspecialchars($v['nombre']); ?></h4>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 mt-2 text-sm text-gray-600">
                                    <p class="flex items-center gap-1"><i data-lucide="calendar" class="w-3.5 h-3.5 text-gray-400"></i> <span class="text-gray-500 mr-1">Aplicada el:</span> <?php echo !empty($v['fecha_aplicacion']) ? date('d/m/Y', strtotime($v['fecha_aplicacion'])) : '-'; ?></p>
                                    <p class="flex items-center gap-1">
                                        <i data-lucide="calendar-clock" class="w-3.5 h-3.5 text-gray-400"></i> 
                                        <span class="text-gray-500 mr-1">Próxima Dosis:</span> 
                                        <?php if (!empty($v['fecha_proxima'])): ?>
                                            <span class="px-2 py-0.5 rounded <?php echo (strtotime($v['fecha_proxima']) < time()) ? 'bg-red-100 text-red-700 font-bold' : 'bg-emerald-100 text-emerald-700 font-semibold'; ?>">
                                                <?php echo date('d/m/Y', strtotime($v['fecha_proxima'])); ?>
                                            </span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </p>
                                    
                                    <?php if (!empty($v['veterinario'])): ?>
                                    <p class="col-span-1 sm:col-span-2 flex items-center gap-1 mt-1">
                                        <i data-lucide="user" class="w-3.5 h-3.5 text-gray-400"></i> 
                                        <span class="text-gray-500">Vet. a cargo:</span> <?php echo htmlspecialchars($v['veterinario']); ?>
                                    </p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($v['lote'])): ?>
                                    <p class="col-span-1 sm:col-span-2 flex items-center gap-1">
                                        <i data-lucide="hash" class="w-3.5 h-3.5 text-gray-400"></i> 
                                        <span class="text-gray-500">Lote Producto:</span> <span class="font-mono text-xs bg-gray-100 px-1.5 py-0.5 rounded text-gray-700"><?php echo htmlspecialchars($v['lote']); ?></span>
                                    </p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($v['notas'])): ?>
                                    <div class="col-span-1 sm:col-span-2 mt-1 bg-gray-50 p-2 rounded text-gray-600 text-xs border border-gray-100">
                                        <strong>Notas: </strong>"<?php echo htmlspecialchars($v['notas']); ?>"
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-10 text-gray-400 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                        <i data-lucide="shield-off" class="w-12 h-12 mx-auto mb-3 text-gray-300"></i>
                        <p class="font-medium">Sin historial de vacunas</p>
                        <p class="text-xs mt-1">Todavía no se ha registrado ninguna aplicación para <?php echo htmlspecialchars($mascota['nombre'] ?? 'esta mascota'); ?>.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
    
    <footer class="mt-8 pt-6 border-t border-gray-200 text-center text-xs text-gray-400 space-y-1">
        <p>Este informe clínico fue generado automáticamente y está diseñado para mantener a los tutores informados sobre el estado de sus mascotas.</p>
        <p>&copy; <?php echo date('Y'); ?> VetWeb. La salud de tu mascota, primero.</p>
    </footer>

</div>

<script>
    // Inicializar iconos de lucide
    lucide.createIcons();
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    brand: { 50: '#eff6ff', 100: '#dbeafe', 500: '#3b82f6', 600: '#2563eb' }
                }
            }
        }
    }
</script>
</body>
</html>
