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
    $stmt = $pdo->prepare("
        SELECT
            cu.id, cu.tipo, cu.categoria, cu.descripcion, cu.monto, cu.estado,
            cu.metodo_pago, cu.fecha_contable, cu.created_at,
            cl.id AS cliente_id, cl.nombre_completo AS cliente_nombre,
            cl.telefono AS cliente_telefono, cl.email AS cliente_email,
            ci.id AS cita_id, ci.fecha AS cita_fecha, ci.tipo AS cita_tipo,
            ci.estado AS cita_estado, ci.token_publico AS cita_token,
            m.id AS mascota_id, m.nombre AS mascota_nombre,
            m.especie AS mascota_especie, m.raza AS mascota_raza,
            m.token_publico AS mascota_token,
            co.id AS cotizacion_id, co.numero_cotizacion,
            co.servicio AS cotizacion_servicio, co.estado AS cotizacion_estado,
            (SELECT SUM(cd.cantidad * cd.precio_unitario) FROM cotizacion_detalles cd WHERE cd.cotizacion_id = co.id) AS cotizacion_total
        FROM cuentas cu
        LEFT JOIN cliente cl ON cu.cliente_id = cl.id
        LEFT JOIN citas ci ON cu.cita_id = ci.id
        LEFT JOIN mascota m ON ci.mascota_id = m.id
        LEFT JOIN cotizaciones co ON co.cita_id = ci.id
        WHERE cu.id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cuenta) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Cuenta no encontrada.']);
        exit;
    }

    echo json_encode(['success' => true, 'cuenta' => $cuenta]);
} catch (PDOException $e) {
    error_log("Error detalle_cuenta: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al obtener detalle.']);
}
