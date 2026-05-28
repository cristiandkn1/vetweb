<?php
require_once __DIR__ . '/../../../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$id              = intval($_POST['id'] ?? 0);
$mascota_id      = intval($_POST['mascota_id'] ?? 0);
$enfermedad      = trim($_POST['enfermedad'] ?? '');
$recurrencia_dias = intval($_POST['recurrencia_dias'] ?? 60);
$ultimo_control  = trim($_POST['ultimo_control'] ?? '');

if ($mascota_id === 0 || $enfermedad === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Mascota y enfermedad son requeridos.']);
    exit;
}

if ($recurrencia_dias < 1) $recurrencia_dias = 60;

try {
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE mascota_enfermedades SET enfermedad = ?, recurrencia_dias = ?, ultimo_control = ? WHERE id = ? AND mascota_id = ?");
        $stmt->execute([$enfermedad, $recurrencia_dias, $ultimo_control ?: null, $id, $mascota_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO mascota_enfermedades (mascota_id, enfermedad, recurrencia_dias, ultimo_control) VALUES (?, ?, ?, ?)");
        $stmt->execute([$mascota_id, $enfermedad, $recurrencia_dias, $ultimo_control ?: null]);
        $id = $pdo->lastInsertId();
    }

    echo json_encode(['success' => true, 'id' => $id]);
} catch (PDOException $e) {
    error_log("Error guardar_enfermedad: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al guardar enfermedad.']);
}
