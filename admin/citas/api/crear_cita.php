<?php
require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

// ── Sanitizar entrada ──────────────────────────────────────────────────────────
$cliente_nombre = trim($_POST['cliente_nombre'] ?? '');
$cliente_telefono = trim($_POST['cliente_telefono'] ?? '');
$cliente_email = trim($_POST['cliente_email'] ?? '');
$mascota_id = intval($_POST['mascota_id'] ?? 0);
$cita_fecha = trim($_POST['cita_fecha'] ?? '');
$cita_tipo = trim($_POST['cita_tipo'] ?? '');
$cita_nota = trim($_POST['cita_nota'] ?? '');

// Campos nueva mascota
$nueva_mascota = !empty($_POST['nueva_mascota']);
$mascota_nombre = trim($_POST['mascota_nombre'] ?? '');
$mascota_especie = trim($_POST['mascota_especie'] ?? '');
$mascota_raza = trim($_POST['mascota_raza'] ?? '');
$mascota_sexo = trim($_POST['mascota_sexo'] ?? '');
$mascota_fecha_nac = trim($_POST['mascota_fecha_nac'] ?? '');
$mascota_observaciones = trim($_POST['mascota_observaciones'] ?? '');

// ── Validaciones ───────────────────────────────────────────────────────────────
$errores = [];

if ($cliente_nombre === '')
    $errores[] = 'El nombre del cliente es obligatorio.';
if ($cliente_telefono === '')
    $errores[] = 'El celular del cliente es obligatorio.';
if ($cita_fecha === '')
    $errores[] = 'La fecha y hora son obligatorias.';
if ($cita_tipo === '')
    $errores[] = 'Debe seleccionar un tipo de servicio.';

if (!$nueva_mascota && $mascota_id === 0) {
    $errores[] = 'Debe seleccionar o registrar una mascota.';
}
if ($nueva_mascota && $mascota_nombre === '') {
    $errores[] = 'El nombre de la nueva mascota es obligatorio.';
}

if (!empty($errores)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errores)]);
    exit;
}

// ── Transacción ────────────────────────────────────────────────────────────────
try {
    $pdo->beginTransaction();

    // 1. Buscar cliente por teléfono o crearlo
    // Columnas reales: id, nombre_completo, rut, email, password, telefono, direccion, fecha_registro
    $stmt = $pdo->prepare("SELECT id FROM cliente WHERE telefono = ? LIMIT 1");
    $stmt->execute([$cliente_telefono]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cliente) {
        $cliente_id = (int) $cliente['id'];
        $upd = $pdo->prepare("UPDATE cliente SET nombre_completo = ?, email = ? WHERE id = ?");
        $upd->execute([$cliente_nombre, $cliente_email ?: null, $cliente_id]);
    } else {
        $ins = $pdo->prepare(
            "INSERT INTO cliente (nombre_completo, telefono, email, fecha_registro)
             VALUES (?, ?, ?, NOW())"
        );
        $ins->execute([$cliente_nombre, $cliente_telefono, $cliente_email ?: null]);
        $cliente_id = (int) $pdo->lastInsertId();
    }

    // 2. Registrar nueva mascota si aplica
    if ($nueva_mascota) {
        $ins = $pdo->prepare(
            "INSERT INTO mascota
                (cliente_id, nombre, especie, raza, sexo, fecha_nacimiento, observaciones, fecha_registro, fecha_actualizacion)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
        );
        $ins->execute([
            $cliente_id,
            $mascota_nombre,
            $mascota_especie ?: null,
            $mascota_raza ?: null,
            $mascota_sexo ?: null,
            $mascota_fecha_nac ?: null,
            $mascota_observaciones ?: null,
        ]);
        $mascota_id = (int) $pdo->lastInsertId();
    }

    // 3. Crear la cita
    $ins = $pdo->prepare(
        "INSERT INTO citas (cliente_id, mascota_id, fecha, tipo, nota, estado, created_at)
         VALUES (?, ?, ?, ?, ?, 'pendiente', NOW())"
    );
    $ins->execute([
        $cliente_id,
        $mascota_id,
        $cita_fecha,
        $cita_tipo,
        $cita_nota ?: null,
    ]);
    $cita_id = (int) $pdo->lastInsertId();

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Cita agendada correctamente.',
        'cita_id' => $cita_id,
        'cliente_id' => $cliente_id,
        'mascota_id' => $mascota_id,
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error crear_cita: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
}