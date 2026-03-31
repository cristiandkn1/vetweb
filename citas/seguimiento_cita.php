<?php
// vetweb/citas/seguimiento_cita.php
require_once __DIR__ . '/../includes/db.php';

$token = trim($_GET['token'] ?? '');

if (empty($token) || strlen($token) !== 48) {
    die("Enlace de seguimiento inválido o caducado.");
}

// Obtener datos de la cita, mascota y cliente
$stmt = $pdo->prepare("
    SELECT 
        c.id as cita_id, c.fecha, c.tipo, c.nota, c.estado, c.created_at, c.precio_final, c.observaciones_vet, c.pagado,
        m.nombre as mascota_nombre, m.especie as mascota_especie, m.raza as mascota_raza,
        cl.nombre_completo as cliente_nombre
    FROM citas c
    INNER JOIN mascota m ON c.mascota_id = m.id
    INNER JOIN cliente cl ON c.cliente_id = cl.id
    WHERE c.token_publico = ?
    LIMIT 1
");
$stmt->execute([$token]);
$cita = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cita) {
    die("Cita no encontrada. Por favor comuníquese con la clínica.");
}

// Configuración de visualización por estado
$estado_config = [
    'pendiente' =>  ['text' => 'Pendiente',   'color' => 'amber',    'icon' => 'clock',                 'step' => 1],
    'confirmada' => ['text' => 'En Atención', 'color' => 'blue',     'icon' => 'activity',              'step' => 2],
    'completada' => ['text' => 'Finalizada',  'color' => 'emerald',  'icon' => 'check-circle-2',        'step' => 3],
    'cancelada' =>  ['text' => 'Cancelada',   'color' => 'red',      'icon' => 'x-circle',              'step' => 0]
];

$estado_actual = strtolower($cita['estado']);
$conf = $estado_config[$estado_actual] ?? ['text' => 'Desconocido', 'color' => 'gray', 'icon' => 'help-circle', 'step' => 0];

$color = $conf['color'];

// Formatear datos
$fecha_obj = new DateTime($cita['fecha']);
$fecha_formateada = $fecha_obj->format('d/m/Y');
$hora_formateada = $fecha_obj->format('h:i A');

// Formato chileno de moneda: $ 15.000 sin decimales
$precio_formateado = '$' . number_format($cita['precio_final'], 0, ',', '.');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Cita - VetWeb</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        .pulse-anim { animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite; }
        @keyframes pulse-ring {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.4); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(79, 70, 229, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(79, 70, 229, 0); }
        }
        .gradient-text {
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 relative selection:bg-indigo-100 selection:text-indigo-900">

<!-- Background Decorativo -->
<div class="fixed top-0 inset-x-0 h-[40vh] bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-800 -z-10 blur-[1px]">
    <!-- Patrón superpuesto opcional -->
    <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9IiNmZmYiLz48L3N2Zz4=')]"></div>
</div>

<main class="max-w-2xl mx-auto pt-10 pb-16 px-4 sm:px-6 w-full">

    <!-- Header Clinica -->
    <div class="text-center mb-8 flex flex-col items-center">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-white/10 backdrop-blur-md rounded-2xl shadow-xl border border-white/20 mb-4 text-white">
            <i data-lucide="paw-print" class="w-8 h-8"></i>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">VetWeb Clinics</h1>
        <p class="text-indigo-200 text-sm font-medium mt-2 uppercase tracking-widest">Seguimiento en Vivo y Facturación</p>
    </div>

    <!-- Main Card -->
    <div class="glass-panel rounded-[2rem] shadow-2xl overflow-hidden relative border-t-2 border-white">
        
        <!-- Estado Hero -->
        <div class="p-8 sm:p-10 text-center border-b border-slate-100 relative overflow-hidden bg-white/60">
            <!-- Circulo de fondo según estado -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-<?php echo $color; ?>-200 rounded-full blur-3xl opacity-40 -z-10"></div>
            
            <div class="inline-flex items-center justify-center w-20 h-20 bg-<?php echo $color; ?>-100 text-<?php echo $color; ?>-600 rounded-full shadow-inner mb-5 <?php echo ($estado_actual==='confirmada') ? 'pulse-anim' : ''; ?>">
                <i data-lucide="<?php echo $conf['icon']; ?>" class="w-10 h-10"></i>
            </div>
            
            <h2 class="text-3xl font-extrabold text-slate-900 mb-2"><?php echo $conf['text']; ?></h2>
            <p class="text-sm text-slate-500 font-medium">Ticket de Atención: <span class="font-mono text-slate-900 bg-slate-100 px-2 py-0.5 rounded ml-1">#<?php echo strtoupper(substr($token, 0, 6)); ?></span></p>

            <?php if ($conf['step'] > 0): ?>
            <!-- Barra de progreso larga -->
            <div class="mt-8 flex items-center justify-center gap-3 max-w-sm mx-auto">
                <div class="h-1.5 flex-1 rounded-full <?php echo $conf['step'] >= 1 ? 'bg-indigo-500' : 'bg-slate-200'; ?>"></div>
                <div class="h-1.5 flex-1 rounded-full <?php echo $conf['step'] >= 2 ? 'bg-indigo-500' : 'bg-slate-200'; ?>"></div>
                <div class="h-1.5 flex-1 rounded-full <?php echo $conf['step'] >= 3 ? 'bg-emerald-500' : 'bg-slate-200'; ?>"></div>
            </div>
            <div class="mt-2 text-[10px] text-slate-400 uppercase font-bold tracking-widest flex justify-between px-1">
                <span>Espera</span>
                <span>Atención</span>
                <span>Listo</span>
            </div>
            <?php endif; ?>
        </div>

        <div class="p-8 sm:p-10">
            <!-- Detalles del Paciente y Cita -->
            <div class="bg-slate-50 rounded-2xl p-6 mb-8 border border-slate-100 shadow-sm relative overflow-hidden">
                <div class="absolute right-0 top-0 w-32 h-32 bg-indigo-50 rounded-bl-[120px] -z-0"></div>
                <div class="relative z-10 grid grid-cols-1 sm:grid-cols-2 gap-8 items-center">
                    <!-- Paciente -->
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-16 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0 shadow-sm border border-indigo-200/50">
                            <i data-lucide="dog" class="w-8 h-8"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-1">Paciente</p>
                            <p class="text-xl font-extrabold text-slate-800 leading-none mb-1"><?php echo htmlspecialchars($cita['mascota_nombre']); ?></p>
                            <p class="text-sm text-slate-500 font-medium"><?php echo htmlspecialchars($cita['mascota_especie'] . (!empty($cita['mascota_raza']) ? ' - ' . $cita['mascota_raza'] : '')); ?></p>
                        </div>
                    </div>

                    <!-- Divisor en móvil, Línea en desktop -->
                    <div class="hidden sm:block absolute left-1/2 top-4 bottom-4 w-px bg-slate-200 border-dashed"></div>
                    <hr class="sm:hidden border-slate-200 border-dashed">

                    <!-- Fecha y Motivo -->
                    <div class="grid grid-cols-2 gap-6 sm:pl-4">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5 flex items-center gap-1.5"><i data-lucide="calendar" class="w-3.5 h-3.5"></i> Horario</p>
                            <p class="text-base font-bold text-slate-800"><?php echo $fecha_formateada; ?></p>
                            <p class="text-sm font-semibold text-indigo-600"><?php echo $hora_formateada; ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5 flex items-center gap-1.5"><i data-lucide="stethoscope" class="w-3.5 h-3.5"></i> Motivo</p>
                            <p class="text-sm font-bold text-slate-800 leading-snug"><?php echo htmlspecialchars($cita['tipo']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($cita['nota'])): ?>
            <div class="mb-8 bg-amber-50 rounded-2xl p-5 border border-amber-100/60 shadow-sm">
                <p class="text-xs font-bold text-amber-500 uppercase tracking-wider flex items-center gap-1.5 mb-2"><i data-lucide="message-square" class="w-4 h-4"></i> Notas Ingreso / Recepción</p>
                <p class="text-base font-medium text-amber-900 leading-relaxed italic border-l-4 border-amber-300 pl-4 ml-1">"<?php echo nl2br(htmlspecialchars($cita['nota'])); ?>"</p>
            </div>
            <?php endif; ?>

            <!-- Cierre: Facturación y Observaciones (Sólo si completada) -->
            <?php if ($estado_actual === 'completada'): ?>
            <div class="mt-10">
                <div class="flex items-center gap-4 mb-6">
                    <div class="h-px bg-slate-200 flex-1"></div>
                    <span class="text-sm font-bold text-slate-400 uppercase tracking-widest text-center">Facturación y Cierre Mëdico</span>
                    <div class="h-px bg-slate-200 flex-1"></div>
                </div>
                
                <div class="bg-gradient-to-br from-white to-slate-50 rounded-[2rem] p-6 sm:p-8 shadow-[0_8px_30px_rgba(0,0,0,0.04)] border border-slate-100 ring-1 ring-slate-900/5 flex flex-col md:flex-row gap-6 sm:gap-8 items-center md:items-stretch">
                    
                    <?php if ($cita['precio_final'] > 0): ?>
                    <div class="flex flex-col justify-center items-center md:items-start shrink-0 md:pr-8 md:border-r border-slate-100">
                        <span class="text-xs font-extrabold text-slate-400 uppercase tracking-widest mb-2 text-center md:text-left w-full">Total Honorarios</span>
                        <!-- 'pr-2' soluciona el corte en algunos navegadores al aplicar background-clip: text -->
                        <span class="text-4xl sm:text-5xl font-black bg-gradient-to-r from-emerald-500 to-teal-400 gradient-text tracking-tight pr-2 pb-1"><?php echo $precio_formateado; ?></span>
                        
                        <?php if (isset($cita['pagado']) && $cita['pagado'] == 1): ?>
                            <p class="text-xs text-emerald-600 font-bold mt-2 flex items-center gap-1.5 bg-emerald-50 px-3 py-1.5 rounded-full border border-emerald-100">
                                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-500"></i> Pagado / Facturado
                            </p>
                        <?php else: ?>
                            <p class="text-xs text-amber-600 font-bold mt-2 flex items-center gap-1.5 bg-amber-50 px-3 py-1.5 rounded-full border border-amber-100">
                                <i data-lucide="alert-circle" class="w-4 h-4 text-amber-500"></i> Pendiente de Pago
                            </p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Contenedor flex-1 para que el diagnóstico tome todo el espacio restante -->
                    <?php if (!empty($cita['observaciones_vet'])): ?>
                    <div class="flex-1 w-full bg-indigo-50/50 rounded-2xl p-5 relative border border-indigo-100 mt-2 md:mt-0 shadow-inner">
                        <div class="absolute -top-3 left-5 bg-indigo-600 px-3 py-1 rounded-lg text-[10px] font-bold text-white uppercase tracking-wider shadow-md flex items-center gap-1.5 border border-indigo-500">
                            <i data-lucide="file-text" class="w-3 h-3 text-indigo-200"></i> Diagnóstico Veterinario
                        </div>
                        <div class="text-sm text-slate-700 leading-relaxed mt-4 font-medium">
                            <?php echo nl2br(htmlspecialchars($cita['observaciones_vet'])); ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="flex-1 w-full flex items-center justify-center p-4">
                        <p class="text-sm text-slate-400 italic">"Sin indicaciones extra."</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Mensaje Personalizado -->
            <p class="text-center text-sm font-semibold text-slate-600 mt-8 mb-2">
                ¡Hola <span class="text-indigo-600"><?php echo htmlspecialchars(explode(' ', $cita['cliente_nombre'])[0]); ?></span>!
            </p>
            <p class="text-center text-xs text-slate-500 leading-relaxed mx-4">
                <?php if ($estado_actual === 'pendiente'): ?>
                    Por favor acércate a recepción y toma asiento. Te llamaremos brevemente.
                <?php elseif ($estado_actual === 'confirmada'): ?>
                    ¡Tu mascota está con el doctor en este instante! Te avisaremos cuando salgan.
                <?php else: ?>
                    Gracias por confiar en nuestros servicios. ¡Esperamos que <?php echo htmlspecialchars($cita['mascota_nombre']); ?> se recupere súper bien!
                <?php endif; ?>
            </p>
            
        </div>
        
        <div class="bg-indigo-50/50 px-6 py-3 flex justify-center border-t border-indigo-100">
            <p class="text-[11px] font-medium text-indigo-400 flex items-center gap-1.5">
                <i data-lucide="refresh-cw" class="w-3 h-3"></i> Refresco automático de estado
            </p>
        </div>
    </div>
    
    <!-- Reload Manual y Branding -->
    <div class="flex justify-between items-center mt-6 px-4">
        <p class="text-indigo-200/60 text-xs font-medium">Powered by VetWeb</p>
        <button onclick="window.location.reload()" class="flex items-center gap-1.5 text-white/80 hover:text-white font-medium text-xs transition-colors bg-white/10 px-3 py-1.5 rounded-full hover:bg-white/20 backdrop-blur-sm border border-white/10">
            <i data-lucide="rotate-cw" class="w-3 h-3"></i> Actualizar
        </button>
    </div>

</main>

<script>
    lucide.createIcons();
    setTimeout(() => window.location.reload(), 30000);
</script>
</body>
</html>
