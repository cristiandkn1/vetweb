<?php
require_once __DIR__ . '/../../../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$hora = trim($_POST['hora'] ?? '');
$comentario = trim($_POST['comentario'] ?? '');

if ($id === 0 || $hora === '' || $comentario === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE cita_bitacora SET hora = ?, comentario = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$hora, $comentario, $id]);

    echo json_encode(['success' => true, 'message' => 'Entrada actualizada.']);
} catch (PDOException $e) {
    error_log("Error bitacora_editar: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al editar entrada.']);
}
