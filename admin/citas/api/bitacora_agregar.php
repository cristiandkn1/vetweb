<?php
require_once __DIR__ . '/../../../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$cita_id = intval($_POST['cita_id'] ?? 0);
$hora = trim($_POST['hora'] ?? '');
$comentario = trim($_POST['comentario'] ?? '');

if ($cita_id === 0 || $comentario === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
    exit;
}

if ($hora === '') {
    $hora = date('H:i');
}

try {
    $stmt = $pdo->prepare("INSERT INTO cita_bitacora (cita_id, hora, comentario, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$cita_id, $hora, $comentario]);
    $id = (int) $pdo->lastInsertId();

    echo json_encode(['success' => true, 'message' => 'Entrada agregada.', 'entry' => [
        'id' => $id,
        'hora' => $hora,
        'comentario' => $comentario,
    ]]);
} catch (PDOException $e) {
    error_log("Error bitacora_agregar: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al agregar entrada.']);
}
