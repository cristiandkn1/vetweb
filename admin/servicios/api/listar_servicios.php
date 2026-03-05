<?php
// admin/servicios/api/listar_servicios.php
require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query(
        "SELECT id, nombre, descripcion, precio_min, precio_max, duracion_min, activo
         FROM servicios
         ORDER BY nombre ASC"
    );
    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'servicios' => $servicios]);

} catch (PDOException $e) {
    error_log("Error listar_servicios: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al obtener servicios.']);
}