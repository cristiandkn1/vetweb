<?php
require_once __DIR__ . '/../../../includes/db.php';
header('Content-Type: application/json');

$cita_id = intval($_GET['cita_id'] ?? 0);
if ($cita_id === 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'ID de cita inválido.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, hora, comentario, created_at FROM cita_bitacora WHERE cita_id = ? ORDER BY hora ASC, id ASC");
    $stmt->execute([$cita_id]);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatear hora a HH:MM
    foreach ($entries as &$e) {
        $e['hora'] = substr($e['hora'], 0, 5);
    }
    unset($e);

    echo json_encode(['success' => true, 'entries' => $entries]);
} catch (PDOException $e) {
    error_log("Error bitacora_listar: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al cargar bitácora.']);
}
