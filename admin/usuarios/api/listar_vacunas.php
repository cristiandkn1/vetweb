<?php
// admin/usuarios/api/listar_vacunas.php
require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

$mascota_id = intval($_GET['mascota_id'] ?? 0);

if ($mascota_id === 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Mascota inválida.']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "SELECT id, nombre, fecha_aplicacion, fecha_proxima, veterinario, lote, notas
         FROM vacuna
         WHERE mascota_id = ?
         ORDER BY fecha_aplicacion ASC"
    );
    $stmt->execute([$mascota_id]);
    $vacunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'vacunas' => $vacunas]);
} catch (PDOException $e) {
    error_log("Error listar_vacunas: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al obtener vacunas.']);
}
