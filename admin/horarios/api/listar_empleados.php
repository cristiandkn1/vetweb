<?php
require_once __DIR__ . '/../../../includes/db.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT * FROM empleados ORDER BY activo DESC, nombre ASC");
    echo json_encode(['success' => true, 'empleados' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
