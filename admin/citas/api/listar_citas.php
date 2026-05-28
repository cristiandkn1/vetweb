<?php
require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

$search = trim($_GET['search'] ?? '');
$desde  = trim($_GET['desde'] ?? '');
$hasta  = trim($_GET['hasta'] ?? '');
$estado = trim($_GET['estado'] ?? '');

$conditions = ['c.oculta = 0'];
$params = [];

if ($search !== '') {
    $conditions[] = '(cl.nombre_completo LIKE ? OR m.nombre LIKE ? OR cl.telefono LIKE ? OR c.tipo LIKE ?)';
    $s = "%$search%";
    array_push($params, $s, $s, $s, $s);
}

if ($desde !== '') {
    $conditions[] = 'DATE(c.fecha) >= ?';
    $params[] = $desde;
}

if ($hasta !== '') {
    $conditions[] = 'DATE(c.fecha) <= ?';
    $params[] = $hasta;
}

if ($estado !== '' && $estado !== 'todos') {
    $conditions[] = 'c.estado = ?';
    $params[] = $estado;
}

$where = implode(' AND ', $conditions);

try {
    // KPIs
    $kpiStmt = $pdo->prepare("
        SELECT
            COUNT(*) AS total,
            SUM(c.estado = 'pendiente')  AS pendientes,
            SUM(c.estado = 'confirmada') AS confirmadas,
            SUM(c.estado = 'completada') AS completadas,
            SUM(c.estado = 'cancelada')  AS canceladas,
            SUM(DATE(c.fecha) = CURDATE()) AS hoy
        FROM citas c
        JOIN cliente cl ON c.cliente_id = cl.id
        JOIN mascota m  ON c.mascota_id = m.id
        WHERE $where
    ");
    $kpiStmt->execute($params);
    $kpi = $kpiStmt->fetch(PDO::FETCH_ASSOC);

    // Citas
    $stmt = $pdo->prepare("
        SELECT
            c.id,
            c.fecha,
            c.tipo,
            c.nota,
            c.recomendaciones,
            c.estado,
            c.token_publico,
            c.precio_final,
            s.precio_min AS precio_estimado_min,
            s.precio_max AS precio_estimado_max,
            cl.id        AS cliente_id,
            cl.nombre_completo AS cliente_nombre,
            cl.telefono  AS cliente_telefono,
            m.id         AS mascota_id,
            m.nombre     AS mascota_nombre,
            m.especie    AS mascota_especie,
            m.token_publico AS mascota_token,
            co.id        AS cotizacion_id,
            co.numero_cotizacion,
            co.estado    AS cotizacion_estado,
            (SELECT SUM(d.precio_unitario * d.cantidad * IF(d.afecto_iva, 1.19, 1))
             FROM cotizacion_detalles d WHERE d.cotizacion_id = co.id) AS cotizacion_total
        FROM citas c
        JOIN cliente cl ON c.cliente_id = cl.id
        JOIN mascota m  ON c.mascota_id = m.id
        LEFT JOIN servicios s ON c.tipo = s.nombre
        LEFT JOIN cotizaciones co ON c.id = co.cita_id
        WHERE $where
        ORDER BY c.fecha DESC
    ");
    $stmt->execute($params);
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cotización detalles
    $detallesMap = [];
    $cotizacionIds = array_filter(array_column($citas, 'cotizacion_id'));
    if (!empty($cotizacionIds)) {
        $placeholders = implode(',', array_fill(0, count($cotizacionIds), '?'));
        $detStmt = $pdo->prepare("SELECT cotizacion_id, descripcion, cantidad, precio_unitario, afecto_iva FROM cotizacion_detalles WHERE cotizacion_id IN ($placeholders) ORDER BY id");
        $detStmt->execute(array_values($cotizacionIds));
        while ($row = $detStmt->fetch(PDO::FETCH_ASSOC)) {
            $detallesMap[$row['cotizacion_id']][] = $row;
        }
    }
    foreach ($citas as &$cita) {
        $cita['cotizacion_detalles'] = $detallesMap[$cita['cotizacion_id']] ?? [];
    }
    unset($cita);

    echo json_encode([
        'success' => true,
        'citas'   => $citas,
        'kpi'     => $kpi
    ]);

} catch (PDOException $e) {
    error_log("Error listar_citas: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al obtener citas.']);
}
