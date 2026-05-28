<?php
require_once __DIR__ . '/../../../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$tipo = trim($_POST['tipo'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$monto = floatval($_POST['monto'] ?? 0);
$estado = trim($_POST['estado'] ?? 'pendiente');
$cliente_id = !empty($_POST['cliente_id']) ? intval($_POST['cliente_id']) : null;
$metodo_pago = trim($_POST['metodo_pago'] ?? '');
$fecha_contable = trim($_POST['fecha_contable'] ?? date('Y-m-d'));

if (!in_array($tipo, ['ingreso', 'gasto'])) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Tipo inválido.']);
    exit;
}
if ($categoria === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'La categoría es obligatoria.']);
    exit;
}
if ($monto <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'El monto debe ser mayor a 0.']);
    exit;
}
$allowed_estados = ['pendiente', 'pagado', 'cancelado'];
if (!in_array($estado, $allowed_estados)) $estado = 'pendiente';

try {
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE cuentas SET tipo=?, categoria=?, descripcion=?, monto=?, estado=?, cliente_id=?, metodo_pago=?, fecha_contable=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$tipo, $categoria, $descripcion ?: null, $monto, $estado, $cliente_id, $metodo_pago ?: null, $fecha_contable, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cuentas (tipo, categoria, descripcion, monto, estado, cliente_id, metodo_pago, fecha_contable, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$tipo, $categoria, $descripcion ?: null, $monto, $estado, $cliente_id, $metodo_pago ?: null, $fecha_contable]);
        $id = (int)$pdo->lastInsertId();
    }
    echo json_encode(['success' => true, 'message' => 'Cuenta guardada correctamente.', 'id' => $id]);
} catch (PDOException $e) {
    error_log("Error guardar_cuenta: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al guardar cuenta.']);
}
