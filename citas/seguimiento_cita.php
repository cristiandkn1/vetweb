<?php
// vetweb/citas/seguimiento_cita.php
session_start();
require_once __DIR__ . '/../includes/db.php';

$isLoggedIn = isset($_SESSION['user_id']);

$token = trim($_GET['token'] ?? '');

if (empty($token) || strlen($token) !== 48) {
    die("Enlace de seguimiento inválido o caducado.");
}

// Obtener datos de la cita, mascota y cliente
$stmt = $pdo->prepare("
    SELECT 
        c.id as cita_id, c.fecha, c.tipo, c.nota, c.recomendaciones, c.estado, c.created_at, c.precio_final, c.observaciones_vet, c.pagado,
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

// Obtener bitácora de la cita
$stmtBitacora = $pdo->prepare("SELECT id, hora, comentario FROM cita_bitacora WHERE cita_id = ? ORDER BY hora ASC, id ASC");
$stmtBitacora->execute([$cita['cita_id']]);
$bitacora = $stmtBitacora->fetchAll(PDO::FETCH_ASSOC);

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
$precio_final = $cita['precio_final'] ?? null;
$precio_formateado = $precio_final !== null ? '$' . number_format((float) $precio_final, 0, ',', '.') : '—';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Cita - VetWeb</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

            <?php if (!empty($bitacora)): ?>
            <div class="mb-8">
                <div class="flex items-center gap-4 mb-4">
                    <div class="h-px bg-violet-200 flex-1"></div>
                    <span class="text-xs font-bold text-violet-500 uppercase tracking-widest text-center flex items-center gap-1.5"><i data-lucide="clock" class="w-3.5 h-3.5"></i> Bitácora de la Cita</span>
                    <div class="h-px bg-violet-200 flex-1"></div>
                </div>
                <div class="space-y-3">
                    <?php foreach ($bitacora as $b): ?>
                    <div class="flex items-start gap-4 bg-white rounded-xl p-4 border border-violet-100 shadow-sm">
                        <div class="w-16 shrink-0 text-center">
                            <span class="text-xs font-bold text-violet-600 bg-violet-50 px-2 py-1 rounded-md border border-violet-200"><?php echo htmlspecialchars(substr($b['hora'], 0, 5)); ?></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($b['comentario'])); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($cita['recomendaciones'])): ?>
            <div class="mb-8 bg-sky-50 rounded-2xl p-5 border border-sky-100/60 shadow-sm">
                <p class="text-xs font-bold text-sky-500 uppercase tracking-wider flex items-center gap-1.5 mb-2"><i data-lucide="clipboard-list" class="w-4 h-4"></i> Recomendaciones / Pasos a Seguir</p>
                <p class="text-base font-medium text-sky-900 leading-relaxed italic border-l-4 border-sky-300 pl-4 ml-1">"<?php echo nl2br(htmlspecialchars($cita['recomendaciones'])); ?>"</p>
            </div>
            <?php endif; ?>

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

            <?php if ($isLoggedIn): ?>
            <!-- Notas Internas del Equipo (solo staff) -->
            <div class="mt-10" id="seccion-notas-internas">
                <div class="flex items-center gap-4 mb-6">
                    <div class="h-px bg-slate-200 flex-1"></div>
                    <span class="text-sm font-bold text-slate-400 uppercase tracking-widest text-center flex items-center gap-1.5">
                        <i data-lucide="message-circle" class="w-4 h-4"></i> Notas Internas del Equipo
                    </span>
                    <div class="h-px bg-slate-200 flex-1"></div>
                </div>

                <div id="lista-notas-internas" class="space-y-3 mb-4">
                    <div class="text-center text-sm text-slate-400 py-4">Cargando notas...</div>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                    <textarea id="input-nota-interna" rows="2" placeholder="Escribe una nota para el equipo (procedimientos, observaciones, recomendaciones)..."
                        class="w-full text-sm border border-slate-200 rounded-lg p-3 resize-none focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent"></textarea>
                    <div class="flex justify-end mt-2">
                        <button id="btn-enviar-nota" onclick="enviarNotaInterna()"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                            <i data-lucide="send" class="w-4 h-4"></i> Enviar
                        </button>
                    </div>
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

    // Auto-refresh inteligente: se pausa si el usuario está escribiendo
    let refreshTimer = setTimeout(() => window.location.reload(), 30000);
    function resetRefresh() {
        clearTimeout(refreshTimer);
        refreshTimer = setTimeout(() => window.location.reload(), 30000);
    }
    document.addEventListener('focusin', (e) => {
        if (e.target.tagName === 'TEXTAREA' || e.target.tagName === 'INPUT') {
            clearTimeout(refreshTimer);
        }
    });
    document.addEventListener('focusout', (e) => {
        if (e.target.tagName === 'TEXTAREA' || e.target.tagName === 'INPUT') {
            resetRefresh();
        }
    });

    <?php if ($isLoggedIn): ?>
    const CITA_ID = <?php echo json_encode($cita['cita_id']); ?>;
    const USER_ID = <?php echo json_encode($_SESSION['user_id']); ?>;

    async function cargarNotasInternas() {
        const container = document.getElementById('lista-notas-internas');
        try {
            const res = await fetch(`/admin/citas/api/notas_internas.php?cita_id=${CITA_ID}`);
            const data = await res.json();
            if (!data.success) { container.innerHTML = '<div class="text-center text-sm text-red-400 py-4">Error al cargar notas.</div>'; return; }
            if (!data.notas || data.notas.length === 0) {
                container.innerHTML = '<div class="text-center text-sm text-slate-400 py-4 italic">Sin notas internas aún.</div>';
                return;
            }
            container.innerHTML = data.notas.map(n => `
                <div class="flex items-start gap-3 bg-white rounded-xl p-4 border border-slate-100 shadow-sm nota-item" data-id="${n.id}">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0 text-xs font-bold">${n.user_name.charAt(0).toUpperCase()}</div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-indigo-600">${escHtml(n.user_name)}</span>
                                <span class="text-[10px] text-slate-400">${new Date(n.created_at).toLocaleString('es-CL', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' })}</span>
                            </div>
                            ${parseInt(n.user_id) === USER_ID ? `<div class="flex items-center gap-1 nota-acciones">
                                <button onclick="editarNota(${n.id}, this)" class="p-1 text-slate-400 hover:text-indigo-600 transition-colors" title="Editar"><i data-lucide="pencil" class="w-3.5 h-3.5"></i></button>
                                <button onclick="eliminarNota(${n.id}, this)" class="p-1 text-slate-400 hover:text-red-500 transition-colors" title="Eliminar"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                            </div>` : ''}
                        </div>
                        <div class="nota-contenido" data-id="${n.id}">
                            <p class="text-sm text-slate-700 leading-relaxed">${escHtml(n.mensaje)}</p>
                        </div>
                        <div class="nota-editor hidden" data-id="${n.id}">
                            <textarea class="w-full text-sm border border-indigo-300 rounded-lg p-2 resize-none focus:outline-none focus:ring-2 focus:ring-indigo-400" rows="2">${escHtml(n.mensaje)}</textarea>
                            <div class="flex justify-end gap-2 mt-1.5">
                                <button onclick="cancelarEditarNota(${n.id}, this)" class="px-2.5 py-1 text-xs font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-md transition-colors">Cancelar</button>
                                <button onclick="guardarEditarNota(${n.id}, this)" class="px-2.5 py-1 text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md transition-colors">Guardar</button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
            lucide.createIcons();
        } catch {
            container.innerHTML = '<div class="text-center text-sm text-red-400 py-4">Error de conexión.</div>';
        }
    }

    async function enviarNotaInterna() {
        const input = document.getElementById('input-nota-interna');
        const mensaje = input.value.trim();
        if (!mensaje) return;

        const btn = document.getElementById('btn-enviar-nota');
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Enviando...';

        try {
            const res = await fetch('/admin/citas/api/notas_internas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cita_id: CITA_ID, mensaje })
            });
            const data = await res.json();
            if (data.success) {
                input.value = '';
                cargarNotasInternas();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Error al enviar nota.', confirmButtonColor: '#4f46e5' });
            }
        } catch {
            Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor.', confirmButtonColor: '#4f46e5' });
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="send" class="w-4 h-4"></i> Enviar';
            lucide.createIcons();
        }
    }

    function editarNota(id, btn) {
        const nota = btn.closest('.nota-item');
        nota.querySelector('.nota-contenido').classList.add('hidden');
        nota.querySelector('.nota-editor').classList.remove('hidden');
        nota.querySelector('.nota-editor textarea').focus();
    }

    function cancelarEditarNota(id, btn) {
        const nota = btn.closest('.nota-item');
        nota.querySelector('.nota-editor').classList.add('hidden');
        nota.querySelector('.nota-contenido').classList.remove('hidden');
    }

    async function guardarEditarNota(id, btn) {
        const nota = btn.closest('.nota-item');
        const textarea = nota.querySelector('.nota-editor textarea');
        const mensaje = textarea.value.trim();
        if (!mensaje) return;

        const guardarBtn = btn;
        guardarBtn.disabled = true;
        guardarBtn.textContent = 'Guardando...';

        try {
            const res = await fetch('/admin/citas/api/notas_internas.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, mensaje })
            });
            const data = await res.json();
            if (data.success) {
                cargarNotasInternas();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudo editar.', confirmButtonColor: '#4f46e5' });
                guardarBtn.disabled = false;
                guardarBtn.textContent = 'Guardar';
            }
        } catch {
            Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar.', confirmButtonColor: '#4f46e5' });
            guardarBtn.disabled = false;
            guardarBtn.textContent = 'Guardar';
        }
    }

    async function eliminarNota(id, btn) {
        const result = await Swal.fire({
            title: '¿Eliminar nota?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        });
        if (!result.isConfirmed) return;

        try {
            const res = await fetch('/admin/citas/api/notas_internas.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Eliminada', text: 'La nota se eliminó correctamente.', timer: 2000, timerProgressBar: true, showConfirmButton: false });
                cargarNotasInternas();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudo eliminar.', confirmButtonColor: '#4f46e5' });
            }
        } catch {
            Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar.', confirmButtonColor: '#4f46e5' });
        }
    }

    function escHtml(str) { if (!str) return ''; return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    document.addEventListener('DOMContentLoaded', cargarNotasInternas);
    <?php endif; ?>
</script>
</body>
</html>
