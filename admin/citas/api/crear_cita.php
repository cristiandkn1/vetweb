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

    $pdo->commit();

    // URL del link de seguimiento
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $link = "{$protocol}://{$host}/citas/seguimiento_cita.php?token={$token}";

    echo json_encode([
        'success' => true,
        'message' => 'Cita agendada correctamente.',
        'cita_id' => $cita_id,
        'cliente_id' => $cliente_id,
        'mascota_id' => $mascota_id,
        'link_seguimiento' => $link,
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error crear_cita: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
}