<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VetWeb - Seguimiento de Atención</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: { 50: '#f0f9ff', 100: '#e0f2fe', 500: '#0ea5e9', 600: '#0284c7', 900: '#0c4a6e' }
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans antialiased text-gray-800 bg-gray-50 min-h-screen flex flex-col">

    <?php
    require_once __DIR__ . '/includes/db.php';

    $error_msg = '';
    $buscado  = '';

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ticket'])) {
        $ticket = trim($_GET['ticket']);
        $ticket = ltrim($ticket, '#');
        $ticket_raw = $ticket;
        $ticket = preg_replace('/[^a-fA-F0-9]/', '', $ticket);
        $buscado = htmlspecialchars($ticket_raw);

        if (strlen($ticket) < 4) {
            $error_msg = 'El ticket debe tener al menos 4 caracteres.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT token_publico FROM citas WHERE token_publico LIKE ? AND oculta = 0 LIMIT 1");
                $stmt->execute([$ticket . '%']);
                $cita = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($cita) {
                    header("Location: citas/seguimiento_cita.php?token=" . urlencode($cita['token_publico']));
                    exit;
                } else {
                    $error_msg = 'No encontramos una cita con el ticket <strong>#' . $buscado . '</strong>. '
                               . 'Es posible que el ticket no exista, la cita haya sido eliminada o el código sea incorrecto.';
                }
            } catch (PDOException $e) {
                error_log("Error ticket: " . $e->getMessage());
                $error_msg = 'Error al buscar el ticket. Intenta de nuevo más tarde.';
            }
        }
    }
    ?>

    <nav class="bg-white shadow-sm">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="index.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-brand-600 transition-colors">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span class="font-medium">Volver al inicio</span>
                </a>
                <div class="flex items-center gap-2">
                    <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    <span class="text-lg font-bold text-brand-900">VetWeb</span>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-1 flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-lg">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 md:p-10">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-brand-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-ticket text-2xl text-brand-600"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Seguimiento de Atención</h1>
                    <p class="text-gray-500 mt-1">Ingresa tu Ticket de Atención para ver el estado de tu cita.</p>
                </div>

                <form method="GET" action="ticket.php" class="space-y-4">
                    <div>
                        <label for="ticket" class="block text-sm font-medium text-gray-700 mb-1.5">Ticket de Atención</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-gray-400 font-mono text-lg font-bold">#</span>
                            </div>
                            <input type="text" id="ticket" name="ticket" required
                                placeholder="2B43FC"
                                value="<?= $buscado ?>"
                                maxlength="10"
                                autofocus
                                class="w-full pl-10 pr-4 py-3.5 rounded-xl border-2 border-gray-200 bg-white text-gray-800 placeholder-gray-300 font-mono tracking-widest uppercase focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 transition-all text-lg">
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">Ingresa el código de 6 caracteres que aparece en tu comprobante (ej: 2B43FC).</p>
                    </div>

                    <?php if ($error_msg): ?>
                        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-4 text-sm flex items-start gap-3">
                            <i class="fa-solid fa-circle-exclamation mt-0.5 shrink-0"></i>
                            <span><?= $error_msg ?></span>
                        </div>
                    <?php endif; ?>

                    <button type="submit"
                        class="w-full py-3.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl transition-colors shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        Buscar Ticket
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                    <p class="text-sm text-gray-500">¿No tienes un ticket?</p>
                    <a href="index.php#services" class="text-sm font-medium text-brand-600 hover:text-brand-500 hover:underline">
                        Conoce nuestros servicios
                    </a>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
