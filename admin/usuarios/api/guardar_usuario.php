<?php
// admin/usuarios/api/guardar_usuario.php
require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$nombre_completo = trim($_POST['nombre_completo'] ?? '');
$rut = trim($_POST['rut'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$email = trim($_POST['email'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');

if ($nombre_completo === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio.']);
    exit;
}
if ($telefono === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'El teléfono es obligatorio.']);
    exit;
}

try {
    if ($id > 0) {
        $stmt = $pdo->prepare(
            "UPDATE cliente SET nombre_completo=?, rut=?, telefono=?, email=?, direccion=? WHERE id=?"
        );
        $stmt->execute([$nombre_completo, $rut ?: null, $telefono, $email ?: null, $direccion ?: null, $id]);
        echo json_encode(['success' => true, 'message' => 'Cliente actualizado correctamente.']);
    } else {
        // Verificar teléfono duplicado
        $check = $pdo->prepare("SELECT id FROM cliente WHERE telefono = ? LIMIT 1");
        $check->execute([$telefono]);
        if ($check->fetch()) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Ya existe un cliente con ese teléfono.']);
            exit;
        }
        $stmt = $pdo->prepare(
            "INSERT INTO cliente (nombre_completo, rut, telefono, email, direccion, fecha_registro)
             VALUES (?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([$nombre_completo, $rut ?: null, $telefono, $email ?: null, $direccion ?: null]);
        $id = (int) $pdo->lastInsertId();
        echo json_encode(['success' => true, 'message' => 'Cliente creado correctamente.', 'id' => $id]);
    }
} catch (PDOException $e) {
    error_log("Error guardar_usuario: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno al guardar.']);
}