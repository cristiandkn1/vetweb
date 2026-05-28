<?php
require_once __DIR__ . '/../../../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$color = trim($_POST['color'] ?? '#6366f1');
$telefono = trim($_POST['telefono'] ?? '');
$especialidad = trim($_POST['especialidad'] ?? '');
$activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;

if ($nombre === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio.']);
    exit;
}

try {
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE empleados SET nombre=?, color=?, telefono=?, especialidad=?, activo=? WHERE id=?");
        $stmt->execute([$nombre, $color, $telefono ?: null, $especialidad ?: null, $activo, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO empleados (nombre, color, telefono, especialidad, activo) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $color, $telefono ?: null, $especialidad ?: null, $activo]);
        $id = (int)$pdo->lastInsertId();
    }
    echo json_encode(['success' => true, 'empleado_id' => $id]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
