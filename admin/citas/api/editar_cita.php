<?php
require_once __DIR__ . '/../../../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$id    = intval($_POST['id'] ?? 0);
$fecha = trim($_POST['fecha'] ?? '');
$tipo  = trim($_POST['tipo'] ?? '');
$nota  = trim($_POST['nota'] ?? '');

if (!$id || !$fecha || !$tipo) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'ID, fecha y tipo son obligatorios.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE citas SET fecha = ?, tipo = ?, nota = ? WHERE id = ? AND oculta = 0");
    $stmt->execute([$fecha, $tipo, $nota, $id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('La cita no existe o no se puede editar.');
    }

    echo json_encode(['success' => true, 'message' => 'Cita actualizada correctamente.']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
