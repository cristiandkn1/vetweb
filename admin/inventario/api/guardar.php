<?php
session_start();
require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autenticado.']);
    exit;
}

$id              = (int) ($_POST['id'] ?? 0);
$nombre          = trim($_POST['nombre'] ?? '');
$descripcion     = trim($_POST['descripcion'] ?? '');
$categoria       = trim($_POST['categoria'] ?? 'otro');
$cantidad        = (int) ($_POST['cantidad'] ?? 0);
$precio          = $_POST['precio'] !== '' ? (float) $_POST['precio'] : null;
$fecha_vencimiento = trim($_POST['fecha_vencimiento'] ?? '');
$lote            = trim($_POST['lote'] ?? '');
$proveedor       = trim($_POST['proveedor'] ?? '');
$ubicacion       = trim($_POST['ubicacion'] ?? '');

if ($nombre === '') {
    echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio.']);
    exit;
}

$validCats = ['vacuna', 'remedio', 'equipo', 'insumo', 'otro'];
if (!in_array($categoria, $validCats)) {
    $categoria = 'otro';
}

try {
    if ($id > 0) {
        $stmt = $pdo->prepare("
            UPDATE inventario SET
                nombre = ?, descripcion = ?, categoria = ?, cantidad = ?,
                precio = ?, fecha_vencimiento = ?, lote = ?, proveedor = ?, ubicacion = ?
            WHERE id = ?
        ");
        $stmt->execute([$nombre, $descripcion, $categoria, $cantidad, $precio,
            $fecha_vencimiento ?: null, $lote ?: null, $proveedor ?: null, $ubicacion ?: null, $id]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO inventario (nombre, descripcion, categoria, cantidad, precio, fecha_vencimiento, lote, proveedor, ubicacion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nombre, $descripcion, $categoria, $cantidad, $precio,
            $fecha_vencimiento ?: null, $lote ?: null, $proveedor ?: null, $ubicacion ?: null]);
        $id = $pdo->lastInsertId();
    }

    echo json_encode(['success' => true, 'id' => $id]);

} catch (PDOException $e) {
    error_log("Error guardar inventario: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al guardar.']);
}
