<?php
// admin/citas/api/eliminar_cita.php
require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$id = intval($_POST['id'] ?? 0);

if ($id === 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'ID inválido.']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM citas WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['success' => true, 'message' => 'Cita eliminada correctamente.']);

} catch (PDOException $e) {
    error_log("Error eliminar_cita: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al eliminar la cita.']);
}