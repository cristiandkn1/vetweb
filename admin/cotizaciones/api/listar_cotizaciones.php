<?php
require_once __DIR__ . '/../../../includes/db.php';
header('Content-Type: application/json');

$search  = trim($_GET['search'] ?? '');
$estado  = trim($_GET['estado'] ?? '');
$desde   = trim($_GET['desde'] ?? '');
$hasta   = trim($_GET['hasta'] ?? '');

$where = [];
$params = [];

if ($estado !== '' && $estado !== 'todos') {
    $where[] = 'co.estado = ?';
    $params[] = $estado;
}
if ($search !== '') {
    $where[] = '(cl.nombre_completo LIKE ? OR m.nombre LIKE ? OR co.numero_cotizacion LIKE ? OR co.servicio LIKE ?)';
    $s = "%$search%";
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
}
if ($desde !== '') {
    $where[] = 'DATE(co.created_at) >= ?';
    $params[] = $desde;
}
if ($hasta !== '') {
    $where[] = 'DATE(co.created_at) <= ?';
    $params[] = $hasta;
}

$whereSql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

try {
    // KPIs
    $kpiSql = "SELECT
        COUNT(DISTINCT co.id) AS total,
        COUNT(DISTINCT CASE WHEN co.estado = 'aprobada' THEN co.id END) AS aprobadas,
        COUNT(DISTINCT CASE WHEN co.estado = 'pendiente' THEN co.id END) AS pendientes,
        COUNT(DISTINCT CASE WHEN co.estado = 'rechazada' THEN co.id END) AS rechazadas,
        COUNT(DISTINCT CASE WHEN co.estado = 'vencida' THEN co.id END) AS vencidas,
        COALESCE(SUM(cd.cantidad * cd.precio_unitario), 0) AS ingresos
    FROM cotizaciones co
    JOIN cliente cl ON co.cliente_id = cl.id
    JOIN mascota m ON co.mascota_id = m.id
    LEFT JOIN cotizacion_detalles cd ON cd.cotizacion_id = co.id
    $whereSql";
    $stmtKpi = $pdo->prepare($kpiSql);
    $stmtKpi->execute($params);
    $kpi = $stmtKpi->fetch(PDO::FETCH_ASSOC);

    // Cotizaciones
    $sql = "SELECT
            co.id,
            co.numero_cotizacion,
            co.cita_id,
            co.servicio,
            co.precio_estimado_min,
            co.precio_estimado_max,
            co.nota,
            co.estado,
            co.created_at,
            cl.id AS cliente_id,
            cl.nombre_completo AS cliente_nombre,
            cl.telefono AS cliente_telefono,
            cl.email AS cliente_email,
            m.id AS mascota_id,
            m.nombre AS mascota_nombre,
            m.especie AS mascota_especie,
            m.raza AS mascota_raza,
            COALESCE(SUM(cd.cantidad * cd.precio_unitario), 0) AS total
        FROM cotizaciones co
        JOIN cliente cl ON co.cliente_id = cl.id
        JOIN mascota m ON co.mascota_id = m.id
        LEFT JOIN cotizacion_detalles cd ON cd.cotizacion_id = co.id
        $whereSql
        GROUP BY co.id
        ORDER BY cl.nombre_completo ASC, co.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $cotizaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'kpi' => $kpi,
        'cotizaciones' => $cotizaciones,
    ]);
} catch (PDOException $e) {
    error_log("Error listar_cotizaciones: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al obtener cotizaciones.']);
}
