<?php
require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$cliente_id = intval($_POST['cliente_id'] ?? 0);
$cliente_telefono = trim($_POST['cliente_telefono'] ?? '');
$cliente_email = trim($_POST['cliente_email'] ?? '');
$mascota_id = intval($_POST['mascota_id'] ?? 0);
$cita_fecha = trim($_POST['cita_fecha'] ?? '');
$cita_tipo = trim($_POST['cita_tipo'] ?? '');
$cita_nota = trim($_POST['cita_nota'] ?? '');

$errores = [];
if ($cliente_id === 0)
    $errores[] = 'Debe seleccionar un cliente.';
if ($mascota_id === 0)
    $errores[] = 'Debe seleccionar una mascota.';
if ($cita_fecha === '')
    $errores[] = 'La fecha y hora son obligatorias.';
if ($cita_tipo === '')
    $errores[] = 'Debe seleccionar un tipo de servicio.';

if (!empty($errores)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errores)]);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Cliente: Validar y actualizar sus datos
    $stmt = $pdo->prepare("SELECT id FROM cliente WHERE id = ? LIMIT 1");
    $stmt->execute([$cliente_id]);
    if (!$stmt->fetch()) {
        throw new Exception("Cliente no encontrado en el sistema.");
    }
    
    $pdo->prepare("UPDATE cliente SET telefono = ?, email = ? WHERE id = ?")
        ->execute([$cliente_telefono, $cliente_email ?: null, $cliente_id]);

    // 2. Mascota: Validar
    $stmt = $pdo->prepare("SELECT id FROM mascota WHERE id = ? AND cliente_id = ?");
    $stmt->execute([$mascota_id, $cliente_id]);
    if (!$stmt->fetch()) {
        throw new Exception("Mascota no pertenece a este cliente.");
    }

    // 3. Generar token único
    $token = bin2hex(random_bytes(24)); // 48 chars, único y seguro

    // 4. Crear cita con token
    $ins = $pdo->prepare(
        "INSERT INTO citas (cliente_id, mascota_id, fecha, tipo, nota, estado, token_publico, created_at)
         VALUES (?, ?, ?, ?, ?, 'pendiente', ?, NOW())"
    );
    $ins->execute([$cliente_id, $mascota_id, $cita_fecha, $cita_tipo, $cita_nota ?: null, $token]);
    $cita_id = (int) $pdo->lastInsertId();

    // 5. Generar cotización automática
    // Obtener precios del servicio
    $stmtSvc = $pdo->prepare("SELECT precio_min, precio_max FROM servicios WHERE nombre = ? AND activo = 1 LIMIT 1");
    $stmtSvc->execute([$cita_tipo]);
    $servicio = $stmtSvc->fetch(PDO::FETCH_ASSOC);
    $precio_min = $servicio ? (float)$servicio['precio_min'] : 0;
    $precio_max = $servicio ? (float)$servicio['precio_max'] : 0;

    // Generar número de cotización: COT-YYYY-NNNN
    $year = date('Y');
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM cotizaciones WHERE YEAR(created_at) = ?");
    $stmtCount->execute([$year]);
    $count = (int)$stmtCount->fetchColumn();
    $numero = 'COT-' . $year . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

    $insCot = $pdo->prepare("
        INSERT INTO cotizaciones (numero_cotizacion, cita_id, cliente_id, mascota_id, servicio, precio_estimado_min, precio_estimado_max, nota, estado, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', NOW())
    ");
    $insCot->execute([$numero, $cita_id, $cliente_id, $mascota_id, $cita_tipo, $precio_min ?: null, $precio_max ?: null, $cita_nota ?: null]);
    $cotizacion_id = (int) $pdo->lastInsertId();

    // Insertar primer detalle de cotización
    $precio_detalle = max($precio_min, $precio_max);
    $insDet = $pdo->prepare("INSERT INTO cotizacion_detalles (cotizacion_id, descripcion, cantidad, precio_unitario, afecto_iva) VALUES (?, ?, 1, ?, 1)");
    $insDet->execute([$cotizacion_id, $cita_tipo, $precio_detalle > 0 ? $precio_detalle : 0]);

    $pdo->commit();

    // URL del link de seguimiento
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $link = "{$protocol}://{$host}/citas/seguimiento_cita.php?token={$token}";

    $link_cotizacion = "{$protocol}://{$host}/admin/cotizaciones/ver_cotizacion.php?id={$cotizacion_id}";

    echo json_encode([
        'success' => true,
        'message' => 'Cita agendada correctamente.',
        'cita_id' => $cita_id,
        'cliente_id' => $cliente_id,
        'mascota_id' => $mascota_id,
        'link_seguimiento' => $link,
        'link_cotizacion' => $link_cotizacion,
        'numero_cotizacion' => $numero,
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error crear_cita: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
}