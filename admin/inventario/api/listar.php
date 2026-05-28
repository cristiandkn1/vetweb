<?php
session_start();
require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autenticado.']);
    exit;
}

$esAdmin = ($_SESSION['user_role'] ?? '') === 'ADMIN';

$search    = trim($_GET['search'] ?? '');
$categoria = trim($_GET['categoria'] ?? '');
$bajo_stock = $_GET['bajo_stock'] ?? '';

$conditions = ['1=1'];
$params = [];

if ($search !== '') {
    $conditions[] = '(i.nombre LIKE ? OR i.descripcion LIKE ? OR i.proveedor LIKE ?)';
    $s = "%$search%";
    array_push($params, $s, $s, $s);
}

if ($categoria !== '' && $categoria !== 'todas') {
    $conditions[] = 'i.categoria = ?';
    $params[] = $categoria;
}

if ($bajo_stock === '1') {
    $conditions[] = 'i.cantidad <= 5';
}

$where = implode(' AND ', $conditions);

try {
    // KPIs
    $kpiStmt = $pdo->prepare("
        SELECT
            COUNT(*) AS total_items,
            SUM(i.cantidad <= 5) AS bajo_stock,
            SUM(i.fecha_vencimiento IS NOT NULL AND i.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND i.fecha_vencimiento >= CURDATE()) AS por_vencer,
            SUM(i.fecha_vencimiento IS NOT NULL AND i.fecha_vencimiento < CURDATE()) AS vencidos,
            SUM(i.cantidad * i.precio) AS valor_total
        FROM inventario i
        WHERE $where
    ");
    $kpiStmt->execute($params);
    $kpi = $kpiStmt->fetch(PDO::FETCH_ASSOC);

    // Items
    $selectFields = 'i.id, i.nombre, i.descripcion, i.categoria, i.cantidad, i.fecha_vencimiento, i.lote, i.proveedor, i.ubicacion, i.created_at, i.updated_at';
    if ($esAdmin) {
        $selectFields .= ', i.precio';
    }

    $stmt = $pdo->prepare("
        SELECT $selectFields
        FROM inventario i
        WHERE $where
        ORDER BY i.nombre ASC
    ");
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'items'   => $items,
        'kpi'     => $kpi,
        'es_admin' => $esAdmin
    ]);

} catch (PDOException $e) {
    error_log("Error listar inventario: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al cargar inventario.']);
}
