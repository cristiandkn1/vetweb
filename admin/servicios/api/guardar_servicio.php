<?php
// admin/servicios/api/guardar_servicio.php
require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$id = intval($_POST['servicio_id'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$precio_min = floatval($_POST['precio_min'] ?? 0);
$precio_max = floatval($_POST['precio_max'] ?? 0);
$duracion = intval($_POST['duracion_min'] ?? 30);
$activo = isset($_POST['activo']) ? 1 : 0;

// Validaciones
if ($nombre === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'El nombre del servicio es obligatorio.']);
    exit;
}
if ($precio_max > 0 && $precio_min > $precio_max) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'El precio mínimo no puede ser mayor al máximo.']);
    exit;
}

try {
    if ($id > 0) {
        // Editar servicio existente
        $stmt = $pdo->prepare(
            "UPDATE servicios
             SET nombre = ?, descripcion = ?, precio_min = ?, precio_max = ?,
                 duracion_min = ?, activo = ?
             WHERE id = ?"
        );
        $stmt->execute([$nombre, $descripcion ?: null, $precio_min, $precio_max, $duracion, $activo, $id]);
        $mensaje = 'Servicio actualizado correctamente.';
    } else {
        // Crear nuevo servicio
        $stmt = $pdo->prepare(
            "INSERT INTO servicios (nombre, descripcion, precio_min, precio_max, duracion_min, activo)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$nombre, $descripcion ?: null, $precio_min, $precio_max, $duracion, $activo]);
        $id = (int) $pdo->lastInsertId();
        $mensaje = 'Servicio creado correctamente.';
    }

    echo json_encode(['success' => true, 'message' => $mensaje, 'id' => $id]);

} catch (PDOException $e) {
    error_log("Error guardar_servicio: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno al guardar el servicio.']);
}