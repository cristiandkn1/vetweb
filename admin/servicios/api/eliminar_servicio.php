<?php
// admin/servicios/api/eliminar_servicio.php
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
    $stmt = $pdo->prepare("DELETE FROM servicios WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['success' => true, 'message' => 'Servicio eliminado.']);

} catch (PDOException $e) {
    error_log("Error eliminar_servicio: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el servicio.']);
}