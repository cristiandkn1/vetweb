<?php
// admin/citas/api/cambiar_estado_cita.php
require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$estado = trim($_POST['estado'] ?? '');
$permitidos = ['pendiente', 'confirmada', 'completada', 'cancelada'];

if ($id === 0 || !in_array($estado, $permitidos)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE citas SET estado = ? WHERE id = ?");
    $stmt->execute([$estado, $id]);

    echo json_encode(['success' => true, 'message' => 'Estado actualizado.']);

} catch (PDOException $e) {
    error_log("Error cambiar_estado_cita: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado.']);
}