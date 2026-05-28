<?php
require_once __DIR__ . '/../../../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$estado = trim($_POST['estado'] ?? 'pendiente');
$nota = trim($_POST['nota'] ?? '');
$detalles_json = trim($_POST['detalles'] ?? '[]');

$allowed_estados = ['pendiente', 'aprobada', 'rechazada', 'vencida'];
if (!in_array($estado, $allowed_estados)) {
    $estado = 'pendiente';
}

if ($id === 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'ID de cotización inválido.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Actualizar encabezado
    $stmt = $pdo->prepare("UPDATE cotizaciones SET estado = ?, nota = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$estado, $nota ?: null, $id]);

    // Procesar detalles: eliminar existentes e insertar nuevos
    $detalles = json_decode($detalles_json, true);
    if (!is_array($detalles)) {
        $detalles = [];
    }

    // Eliminar detalles anteriores
    $pdo->prepare("DELETE FROM cotizacion_detalles WHERE cotizacion_id = ?")->execute([$id]);

    // Insertar nuevos detalles
    if (!empty($detalles)) {
        $insDet = $pdo->prepare("INSERT INTO cotizacion_detalles (cotizacion_id, descripcion, cantidad, precio_unitario, afecto_iva, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        foreach ($detalles as $d) {
            $desc = trim($d['descripcion'] ?? '');
            $cant = max(1, intval($d['cantidad'] ?? 1));
            $precio = max(0, floatval($d['precio_unitario'] ?? 0));
            $afecto = isset($d['afecto_iva']) ? (int)$d['afecto_iva'] : 1;
            if ($desc !== '') {
                $insDet->execute([$id, $desc, $cant, $precio, $afecto]);
            }
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Cotización guardada correctamente.']);
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error guardar_cotizacion: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al guardar cotización.']);
}
