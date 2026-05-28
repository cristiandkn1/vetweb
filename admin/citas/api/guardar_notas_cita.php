<?php
require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$cita_id = intval($_POST['cita_id'] ?? 0);
$recomendaciones = trim($_POST['recomendaciones'] ?? '');
$comentarios = trim($_POST['comentarios'] ?? '');

if ($cita_id === 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'ID de cita inválido.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE citas 
        SET recomendaciones = ?, comentarios = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([
        $recomendaciones ?: null,
        $comentarios ?: null,
        $cita_id
    ]);

    echo json_encode(['success' => true, 'message' => 'Notas guardadas correctamente.']);
} catch (PDOException $e) {
    error_log("Error guardar_notas_cita: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al guardar las notas.']);
}
