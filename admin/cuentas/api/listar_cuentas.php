<?php
require_once __DIR__ . '/../../../includes/db.php';
header('Content-Type: application/json');

$tipo   = trim($_GET['tipo'] ?? '');
$estado = trim($_GET['estado'] ?? '');
$desde  = trim($_GET['desde'] ?? '');
$hasta  = trim($_GET['hasta'] ?? '');
$search = trim($_GET['search'] ?? '');
$id     = intval($_GET['id'] ?? 0);

$conditions = [];
$params = [];

if ($id > 0) {
    $conditions[] = 'c.id = ?';
    $params[] = $id;
}
if ($tipo !== '' && $tipo !== 'todos') {
    $conditions[] = 'c.tipo = ?';
    $params[] = $tipo;
}
if ($estado !== '' && $estado !== 'todos') {
    $conditions[] = 'c.estado = ?';
    $params[] = $estado;
}
if ($desde !== '') {
    $conditions[] = 'c.fecha_contable >= ?';
    $params[] = $desde;
}
if ($hasta !== '') {
    $conditions[] = 'c.fecha_contable <= ?';
    $params[] = $hasta;
}
if ($search !== '') {
    $conditions[] = '(c.descripcion LIKE ? OR c.categoria LIKE ? OR cl.nombre_completo LIKE ?)';
    $s = "%$search%";
    array_push($params, $s, $s, $s);
}

$where = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
$limit = $id > 0 ? '' : 'LIMIT 200';

try {
    $kpi = $pdo->query("
        SELECT
            COALESCE(SUM(CASE WHEN tipo='ingreso' AND estado='pagado' THEN monto ELSE 0 END), 0) AS total_ingresos,
            COALESCE(SUM(CASE WHEN tipo='gasto'   AND estado='pagado' THEN monto ELSE 0 END), 0) AS total_gastos,
            COALESCE(SUM(CASE WHEN estado='pendiente' THEN monto ELSE 0 END), 0) AS total_pendientes,
            COUNT(*) AS total,
            SUM(estado = 'pendiente') AS pendientes
        FROM cuentas
    ")->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT c.*, cl.nombre_completo AS cliente_nombre, cl.telefono AS cliente_telefono
        FROM cuentas c
        LEFT JOIN cliente cl ON c.cliente_id = cl.id
        $where
        ORDER BY c.fecha_contable DESC, c.created_at DESC
        $limit
    ");
    $stmt->execute($params);
    $cuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'cuentas' => $cuentas, 'kpi' => $kpi]);
} catch (PDOException $e) {
    error_log("Error listar_cuentas: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al obtener cuentas.']);
}
