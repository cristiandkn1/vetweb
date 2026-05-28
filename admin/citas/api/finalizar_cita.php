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

    // Crear cuenta de ingreso automática
    if ($precio_final > 0) {
        $stmtCita = $pdo->prepare("SELECT c.cliente_id, c.tipo, c.fecha FROM citas c WHERE c.id = ?");
        $stmtCita->execute([$cita_id]);
        $citaData = $stmtCita->fetch(PDO::FETCH_ASSOC);
        if ($citaData) {
            $estado_cuenta = $pagado ? 'pagado' : 'pendiente';
            $stmtCuenta = $pdo->prepare("INSERT INTO cuentas (tipo, categoria, descripcion, monto, estado, cita_id, cliente_id, metodo_pago, fecha_contable, created_at) VALUES ('ingreso', ?, ?, ?, ?, ?, ?, ?, CURDATE(), NOW())");
            $stmtCuenta->execute([$citaData['tipo'], "Cobro por {$citaData['tipo']}: " . substr($observaciones_vet, 0, 200), $precio_final, $estado_cuenta, $cita_id, $citaData['cliente_id'], $pagado ? 'efectivo' : null]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Cita finalizada correctamente.']);
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error al finalizar_cita: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno al procesar el cierre.']);
}
