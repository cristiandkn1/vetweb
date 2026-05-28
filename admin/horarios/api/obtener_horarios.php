<?php
require_once __DIR__ . '/../../../includes/db.php';
header('Content-Type: application/json');

$empleado_id = intval($_GET['empleado_id'] ?? 0);

try {
    if ($empleado_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM horario_semanal WHERE empleado_id = ? ORDER BY dia_semana, hora_inicio");
        $stmt->execute([$empleado_id]);
    } else {
        $stmt = $pdo->query("SELECT hs.*, e.nombre AS empleado_nombre, e.color AS empleado_color
            FROM horario_semanal hs
            JOIN empleados e ON e.id = hs.empleado_id
            ORDER BY hs.empleado_id, hs.dia_semana, hs.hora_inicio");
    }
    echo json_encode(['success' => true, 'horarios' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
