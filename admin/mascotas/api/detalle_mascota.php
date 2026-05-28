<?php
require_once __DIR__ . '/../../../includes/db.php';
header('Content-Type: application/json');

$id = intval($_GET['id'] ?? 0);
if ($id === 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'ID inválido.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT m.*, cl.nombre_completo AS dueno, cl.telefono AS dueno_telefono, cl.email AS dueno_email FROM mascota m LEFT JOIN cliente cl ON m.cliente_id = cl.id WHERE m.id = ?");
    $stmt->execute([$id]);
    $mascota = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$mascota) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Mascota no encontrada.']);
        exit;
    }

    $stmtC = $pdo->prepare("SELECT c.id, c.fecha, c.tipo, c.estado, c.token_publico FROM citas c WHERE c.mascota_id = ? AND c.oculta = 0 ORDER BY c.fecha DESC LIMIT 20");
    $stmtC->execute([$id]);
    $citas = $stmtC->fetchAll(PDO::FETCH_ASSOC);

    $stmtCot = $pdo->prepare("SELECT co.id, co.numero_cotizacion, co.servicio, co.estado, co.created_at FROM cotizaciones co WHERE co.mascota_id = ? ORDER BY co.created_at DESC LIMIT 10");
    $stmtCot->execute([$id]);
    $cotizaciones = $stmtCot->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'mascota' => $mascota, 'citas' => $citas, 'cotizaciones' => $cotizaciones]);
} catch (PDOException $e) {
    error_log("Error detalle_mascota: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al obtener detalle.']);
}
