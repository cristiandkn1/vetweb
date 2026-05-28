<?php
require_once __DIR__ . '/../../../includes/db.php';
header('Content-Type: application/json');

$mascota_id = intval($_GET['mascota_id'] ?? 0);
if ($mascota_id === 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Mascota inválida.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, mascota_id, enfermedad, recurrencia_dias, ultimo_control, activo, created_at
        FROM mascota_enfermedades
        WHERE mascota_id = ? AND activo = 1
        ORDER BY created_at DESC
    ");
    $stmt->execute([$mascota_id]);
    $enfermedades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'enfermedades' => $enfermedades]);
} catch (PDOException $e) {
    error_log("Error listar_enfermedades: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al obtener enfermedades.']);
}
