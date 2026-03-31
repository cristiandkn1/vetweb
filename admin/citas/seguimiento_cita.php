<?php
// seguimiento_cita.php (en la raíz de vetweb/ o donde prefieras)
require_once __DIR__ . '/includes/db.php';

$token = trim((string) ($_GET['token'] ?? ''));

if ($token === '') {
    http_response_code(400);
    die(paginaError('⚠️', 'Token inválido', 'El enlace no es válido.'));
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            citas.id, citas.fecha, citas.tipo, citas.nota, citas.estado,
            citas.precio_estimado, citas.precio_final, citas.dias_estimados,
            citas.observaciones_vet, citas.created_at,
            cliente.nombre_completo AS cliente_nombre,
            cliente.telefono        AS cliente_telefono,
            mascota.nombre          AS mascota_nombre,
            mascota.especie         AS mascota_especie,
            mascota.raza            AS mascota_raza
        FROM citas
        JOIN cliente ON citas.cliente_id = cliente.id
        JOIN mascota ON citas.mascota_id = mascota.id
        WHERE citas.token_publico = ?
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $cita = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    die(paginaError('❌', 'Error', 'Ocurrió un problema al buscar la cita.'));
}

if (!$cita) {
    http_response_code(404);
    die(paginaError('🐾', 'Cita no encontrada', 'El enlace no es válido o ha expirado.'));
}

// ── Config de estado ───────────────────────────────────────────────────────────
function estadoConfig(string $estado): array
{
    return match ($estado) {
        'pendiente' => [
            'label' => 'Pendiente de confirmar',
            'class' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
            'icon' => '🕐',
            'mensaje' => 'Tu cita está registrada y pendiente de confirmación.',
        ],
        'confirmada' => [
            'label' => 'Cita confirmada',
            'class' => 'bg-blue-100 text-blue-800 border-blue-300',
            'icon' => '✅',
            'mensaje' => '¡Tu cita ha sido confirmada! Te esperamos.',
        ],
        'completada' => [
            'label' => 'Atención completada',
            'class' => 'bg-green-100 text-green-800 border-green-300',
            'icon' => '🎉',
            'mensaje' => 'Tu mascota fue atendida correctamente. ¡Gracias por confiar en nosotros!',
        ],
        'cancelada' => [
            'label' => 'Cita cancelada',
            'class' => 'bg-red-100 text-red-800 border-red-300',
            'icon' => '❌',
            'mensaje' => 'Esta cita fue cancelada. Contáctanos para reagendar.',
        ],
        default => [
            'label' => 'Sin estado',
            'class' => 'bg-gray-100 text-gray-700 border-gray-300',
            'icon' => '❓',
            'mensaje' => '',
        ],
    };
}

function paginaError(string $icon, string $titulo, string $msg): string
{
    return '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>' . $titulo . '</title>
    <script src="https://cdn.tailwindcss.com"></script></head>
    <body class="bg-slate-900 flex items-center justify-center min-h-screen">
    <div class="text-center text-white p-8">
        <div class="text-6xl mb-4">' . $icon . '</div>
        <h1 class="text-2xl font-bold mb-2">' . $titulo . '</h1>
        <p class="text-slate-400">' . $msg . '</p>
    </div></body></html>';
}

$cfg = estadoConfig($cita['estado']);
$fecha = new DateTime($cita['fecha']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Cita ·
        <?= htmlspecialchars($cita['mascota_nombre']) ?>
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 min-h-screen">

    <div class="container mx-auto px-4 py-10 max-w-xl">

        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-700 mb-4 text-3xl">
                🐾
            </div>
            <h1 class="text-3xl font-bold text-white mb-1">Seguimiento de Cita</h1>
            <p class="text-slate-400 text-sm">Consulta el estado de la atención de tu mascota</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">

            <!-- Banner estado -->
            <div class="p-6 <?= $cfg['class'] ?> border-b-4 text-center">
                <div class="text-4xl mb-2">
                    <?= $cfg['icon'] ?>
                </div>
                <div class="text-xs font-medium opacity-70 uppercase tracking-wide mb-1">Estado actual</div>
                <div class="text-2xl font-bold">
                    <?= htmlspecialchars($cfg['label']) ?>
                </div>
                <?php if ($cfg['mensaje']): ?>
                    <p class="text-sm mt-2 opacity-80">
                        <?= htmlspecialchars($cfg['mensaje']) ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Fecha y servicio -->
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between gap-4">
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wide">Fecha de la cita</div>
                    <div class="text-lg font-bold text-gray-800">
                        <?= $fecha->format('d \d\e F \d\e Y') ?>
                    </div>
                    <div class="text-sm text-gray-500">
                        <?= $fecha->format('H:i') ?> hrs
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-400 uppercase tracking-wide">Servicio</div>
                    <div class="text-base font-semibold text-gray-800">
                        <?= htmlspecialchars($cita['tipo']) ?>
                    </div>
                </div>
            </div>

            <!-- Mascota -->
            <div class="px-6 py-5 border-b border-gray-100">
                <div class="text-xs text-gray-400 uppercase tracking-wide mb-3 font-medium">Paciente</div>
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-full bg-brand-100 flex items-center justify-center text-2xl bg-amber-50">
                        🐶
                    </div>
                    <div>
                        <div class="font-bold text-gray-800 text-lg">
                            <?= htmlspecialchars($cita['mascota_nombre']) ?>
                        </div>
                        <div class="text-sm text-gray-500">
                            <?= htmlspecialchars($cita['mascota_especie'] ?? '') ?>
                            <?php if ($cita['mascota_raza']): ?>
                                ·
                                <?= htmlspecialchars($cita['mascota_raza']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dueño -->
            <div class="px-6 py-4 bg-slate-50 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center text-slate-500">👤
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">Propietario</div>
                        <div class="font-semibold text-slate-800">
                            <?= htmlspecialchars($cita['cliente_nombre']) ?>
                        </div>
                        <?php if ($cita['cliente_telefono']): ?>
                            <div class="text-xs text-slate-500">
                                <?= htmlspecialchars($cita['cliente_telefono']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Estimados (solo si hay datos) -->
            <?php if ($cita['precio_estimado'] || $cita['precio_final'] || $cita['dias_estimados']): ?>
                <div class="px-6 py-5 border-b border-gray-100">
                    <div class="text-xs text-gray-400 uppercase tracking-wide mb-3 font-medium">Estimados</div>
                    <div class="grid grid-cols-<?= ($cita['dias_estimados'] ? '3' : '2') ?> gap-3 text-center">

                        <?php if ($cita['precio_estimado']): ?>
                            <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                                <div class="text-xs text-gray-400 mb-1">Precio estimado</div>
                                <div class="font-bold text-gray-800">
                                    $
                                    <?= number_format($cita['precio_estimado'], 0, ',', '.') ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($cita['precio_final']): ?>
                            <div class="bg-green-50 rounded-xl p-3 border border-green-100">
                                <div class="text-xs text-green-600 mb-1">Precio final</div>
                                <div class="font-bold text-green-700">
                                    $
                                    <?= number_format($cita['precio_final'], 0, ',', '.') ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($cita['dias_estimados']): ?>
                            <div class="bg-blue-50 rounded-xl p-3 border border-blue-100">
                                <div class="text-xs text-blue-500 mb-1">Días est.</div>
                                <div class="font-bold text-blue-700">
                                    <?= $cita['dias_estimados'] ?>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endif; ?>

            <!-- Observaciones del veterinario -->
            <?php if (!empty($cita['observaciones_vet'])): ?>
                <div class="px-6 py-5 border-b border-gray-100">
                    <div class="text-xs text-gray-400 uppercase tracking-wide mb-2 font-medium">Observaciones del
                        veterinario</div>
                    <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-sm text-gray-700 leading-relaxed">
                        <?= nl2br(htmlspecialchars($cita['observaciones_vet'])) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Nota original -->
            <?php if (!empty($cita['nota'])): ?>
                <div class="px-6 py-5 border-b border-gray-100">
                    <div class="text-xs text-gray-400 uppercase tracking-wide mb-2 font-medium">Nota de la consulta</div>
                    <p class="text-sm text-gray-600">
                        <?= nl2br(htmlspecialchars($cita['nota'])) ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Footer -->
            <div class="px-6 py-4 bg-slate-50 text-center">
                <p class="text-xs text-slate-400">
                    🔄 Esta página se actualiza automáticamente cada 30 segundos
                </p>
            </div>
        </div>

        <!-- Botón recargar -->
        <div class="mt-6 text-center">
            <button onclick="location.reload()"
                class="inline-flex items-center gap-2 px-6 py-3 bg-white text-slate-900 rounded-xl font-semibold hover:bg-slate-100 transition shadow-lg text-sm">
                🔄 Actualizar estado
            </button>
        </div>

        <p class="text-center text-slate-600 text-xs mt-6">
            Cita registrada el
            <?= (new DateTime($cita['created_at']))->format('d/m/Y') ?>
        </p>

    </div>

    <script>setTimeout(() => location.reload(), 30000);</script>
</body>

</html>