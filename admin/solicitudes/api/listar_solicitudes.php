<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'msg' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../../includes/db.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT *
        FROM solicitudes
        WHERE estado = 'pendiente'
        ORDER BY created_at DESC
    ");
    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'data' => $solicitudes]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Error al cargar solicitudes']);
}
