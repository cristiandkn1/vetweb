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

$data = json_decode(file_get_contents('php://input'), true);
$solicitud_id = (int)($data['solicitud_id'] ?? 0);
$motivo = trim($data['motivo'] ?? '');

if (!$solicitud_id) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'ID de solicitud no válido']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE solicitudes SET estado = 'rechazada', nota = CASE WHEN ? != '' THEN ? ELSE nota END WHERE id = ? AND estado = 'pendiente'");
    $stmt->execute([$motivo, $motivo, $solicitud_id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('La solicitud no existe o ya fue procesada');
    }

    echo json_encode(['ok' => true, 'msg' => 'Solicitud rechazada.']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
