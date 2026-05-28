<?php
require_once __DIR__ . '/../../../includes/db.php';
header('Content-Type: application/json');

$semana_inicio = $_GET['semana_inicio'] ?? null;

if (!$semana_inicio) {
    $semana_inicio = date('Y-m-d', strtotime('monday this week'));
}

$semana_fin = date('Y-m-d', strtotime($semana_inicio . ' +7 days'));

try {
    $stmt = $pdo->prepare("
        SELECT
            c.id,
            c.fecha,
            c.tipo,
            c.estado,
            cl.nombre_completo AS cliente_nombre,
            m.nombre AS mascota_nombre,
            m.especie AS mascota_especie
        FROM citas c
        JOIN cliente cl ON c.cliente_id = cl.id
        JOIN mascota m ON c.mascota_id = m.id
        WHERE c.oculta = 0
          AND DATE(c.fecha) >= ?
          AND DATE(c.fecha) < ?
        ORDER BY c.fecha ASC
    ");
    $stmt->execute([$semana_inicio, $semana_fin]);
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'citas' => $citas,
        'semana_inicio' => $semana_inicio,
        'semana_fin' => $semana_fin
    ]);
} catch (PDOException $e) {
    error_log("Error listar_citas_semana: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al obtener citas.']);
}
