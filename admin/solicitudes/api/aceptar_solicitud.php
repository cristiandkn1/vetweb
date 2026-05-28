<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'msg' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$solicitud_id = (int)($input['solicitud_id'] ?? 0);

if (!$solicitud_id) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'ID de solicitud no válido']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Obtener la solicitud con lock
    $stmt = $pdo->prepare("SELECT * FROM solicitudes WHERE id = ? AND estado = 'pendiente' FOR UPDATE");
    $stmt->execute([$solicitud_id]);
    $sol = $stmt->fetch();

    if (!$sol) {
        throw new Exception('La solicitud no existe o ya fue procesada');
    }

    // Usar valores del formulario (override) o los almacenados en la solicitud
    $nombre     = trim($input['nombre_completo'] ?? $sol['nombre_completo']);
    $email      = trim($input['email'] ?? $sol['email']);
    $rut        = trim($input['rut'] ?? $sol['rut'] ?? '');
    $telefono   = trim($input['telefono'] ?? $sol['telefono']);
    $mascota_nombre  = trim($input['mascota_nombre'] ?? $sol['mascota_nombre']);
    $mascota_especie = trim($input['mascota_especie'] ?? $sol['mascota_especie'] ?? '');
    $mascota_raza    = trim($input['mascota_raza'] ?? $sol['mascota_raza'] ?? '');
    $mascota_sexo    = trim($input['mascota_sexo'] ?? $sol['mascota_sexo'] ?? '');
    $servicio   = trim($input['servicio'] ?? $sol['servicio']);
    $fecha_cita = trim($input['fecha_cita'] ?? $sol['fecha_cita']);
    $nota       = trim($input['nota'] ?? $sol['nota'] ?? '');

    if (empty($nombre) || empty($email) || empty($mascota_nombre) || empty($servicio) || empty($fecha_cita)) {
        throw new Exception('Faltan campos obligatorios: nombre, email, mascota, servicio y fecha.');
    }

    // --- Crear o actualizar cliente ---
    $stmt = $pdo->prepare("SELECT id FROM cliente WHERE email = ?");
    $stmt->execute([$email]);
    $cliente_existente = $stmt->fetch();

    if ($cliente_existente) {
        $cliente_id = $cliente_existente['id'];
        $stmt = $pdo->prepare("UPDATE cliente SET nombre_completo = ?, rut = ?, telefono = ? WHERE id = ?");
        $stmt->execute([$nombre, $rut, $telefono, $cliente_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cliente (nombre_completo, rut, telefono, email, fecha_registro) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$nombre, $rut, $telefono, $email]);
        $cliente_id = $pdo->lastInsertId();
    }

    // --- Crear mascota ---
    $stmt = $pdo->prepare("INSERT INTO mascota (cliente_id, nombre, especie, raza, sexo, token_publico, fecha_registro, fecha_actualizacion) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([$cliente_id, $mascota_nombre, $mascota_especie, $mascota_raza, $mascota_sexo, bin2hex(random_bytes(24))]);
    $mascota_id = $pdo->lastInsertId();

    // --- Crear user si no existe ---
    $stmt = $pdo->prepare("SELECT id FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $usuario_existente = $stmt->fetch();
    $usuario_creado = false;

    if (!$usuario_existente) {
        $random_pass = bin2hex(random_bytes(16));
        $hash = password_hash($random_pass, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO user (name, email, password, role, active, createdAt) VALUES (?, ?, ?, 'CLIENTE', 1, NOW())");
        $stmt->execute([$nombre, $email, $hash]);
        $usuario_creado = true;
    }

    // --- Crear cita ---
    $token = bin2hex(random_bytes(24));
    $stmt = $pdo->prepare("INSERT INTO citas (cliente_id, mascota_id, fecha, tipo, nota, estado, token_publico, created_at) VALUES (?, ?, ?, ?, ?, 'confirmada', ?, NOW())");
    $stmt->execute([$cliente_id, $mascota_id, $fecha_cita, $servicio, $nota, $token]);
    $cita_id = (int) $pdo->lastInsertId();

    // --- Generar cotización ---
    $stmtSvc = $pdo->prepare("SELECT precio_min, precio_max FROM servicios WHERE nombre = ? AND activo = 1 LIMIT 1");
    $stmtSvc->execute([$servicio]);
    $svc = $stmtSvc->fetch(PDO::FETCH_ASSOC);
    $precio_min = $svc ? (float)$svc['precio_min'] : 0;
    $precio_max = $svc ? (float)$svc['precio_max'] : 0;

    $year = date('Y');
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM cotizaciones WHERE YEAR(created_at) = ?");
    $stmtCount->execute([$year]);
    $count = (int)$stmtCount->fetchColumn();
    $numero = 'COT-' . $year . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

    $stmt = $pdo->prepare("INSERT INTO cotizaciones (numero_cotizacion, cita_id, cliente_id, mascota_id, servicio, precio_estimado_min, precio_estimado_max, nota, estado, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', NOW())");
    $stmt->execute([$numero, $cita_id, $cliente_id, $mascota_id, $servicio, $precio_min ?: null, $precio_max ?: null, $nota ?: null]);
    $cotizacion_id = (int) $pdo->lastInsertId();

    $precio_detalle = max($precio_min, $precio_max);
    $stmt = $pdo->prepare("INSERT INTO cotizacion_detalles (cotizacion_id, descripcion, cantidad, precio_unitario, afecto_iva) VALUES (?, ?, 1, ?, 1)");
    $stmt->execute([$cotizacion_id, $servicio, $precio_detalle > 0 ? $precio_detalle : 0]);

    // --- Marcar solicitud como aceptada ---
    $stmt = $pdo->prepare("UPDATE solicitudes SET estado = 'aceptada' WHERE id = ?");
    $stmt->execute([$solicitud_id]);

    $pdo->commit();

    $parts = ['Cliente creado'];
    if ($usuario_creado) $parts[] = 'Cuenta de usuario creada';
    $parts[] = 'Mascota registrada';
    $parts[] = 'Cita confirmada';
    $parts[] = "Cotización {$numero} generada";

    echo json_encode(['ok' => true, 'msg' => 'Solicitud aceptada: ' . implode(', ', $parts) . '.']);

} catch (PDOException $e) {
    $pdo->rollBack();
    if ($e->getCode() == 23000 && strpos($e->getMessage(), 'uq_cliente_telefono') !== false) {
        echo json_encode(['ok' => false, 'msg' => 'El teléfono ya está registrado con otro cliente. Revisa los datos.']);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'msg' => 'Error al procesar la solicitud.']);
    }
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
