<?php
// admin/citas/api/listar_citas.php
require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query(
        "SELECT 
            citas.id,
            citas.fecha,
            citas.tipo,
            citas.nota,
            citas.estado,
            citas.token_publico,
            s.precio_min AS precio_estimado_min,
            s.precio_max AS precio_estimado_max,
            cliente.id        AS cliente_id,
            cliente.nombre_completo AS cliente_nombre,
            cliente.telefono  AS cliente_telefono,
            mascota.id        AS mascota_id,
            mascota.nombre    AS mascota_nombre,
            mascota.especie   AS mascota_especie
         FROM citas
         JOIN cliente ON citas.cliente_id = cliente.id
         JOIN mascota ON citas.mascota_id = mascota.id
         LEFT JOIN servicios s ON citas.tipo = s.nombre
         WHERE citas.oculta = 0
         ORDER BY citas.fecha DESC"
    );
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'citas' => $citas]);

} catch (PDOException $e) {
    error_log("Error listar_citas: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al obtener citas.']);
}