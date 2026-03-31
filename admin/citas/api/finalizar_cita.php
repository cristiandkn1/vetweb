<?php
require_once __DIR__ . '/../../../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$cita_id = intval($_POST['cita_id'] ?? 0);
$precio_final = floatval($_POST['precio_final'] ?? 0);
$observaciones_vet = trim($_POST['observaciones_vet'] ?? '');
$pagado = isset($_POST['pagado']) && $_POST['pagado'] == '1' ? 1 : 0;

if ($cita_id === 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'ID de cita inválido.']);
    exit;
}

if ($observaciones_vet === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Las observaciones finales son obligatorias.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Actualizar cita
    $stmt = $pdo->prepare("
        UPDATE citas 
        SET estado = 'completada', precio_final = ?, observaciones_vet = ?, pagado = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$precio_final, $observaciones_vet, $pagado, $cita_id]);

    // Opcional: Agregar observación al registro genérico de la mascota
    $stmt2 = $pdo->prepare("SELECT mascota_id FROM citas WHERE id = ? LIMIT 1");
    $stmt2->execute([$cita_id]);
    $mascota_id = $stmt2->fetchColumn();

    if ($mascota_id) {
        $fecha = date('d/m/Y');
        $addObs = "\n---\nCobro ($precio_final) - Cierre de cita ($fecha):\n" . $observaciones_vet;
        
        $stmtMascota = $pdo->prepare("
            UPDATE mascota 
            SET observaciones = CONCAT(IFNULL(observaciones, ''), ?), fecha_actualizacion = NOW() 
            WHERE id = ?
        ");
        $stmtMascota->execute([$addObs, $mascota_id]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Cita finalizada correctamente.']);
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error al finalizar_cita: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno al procesar el cierre.']);
}
