<?php
require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$id = intval($_POST['id'] ?? 0);

if ($id === 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'ID inválido.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Obtener email del cliente antes de eliminar
    $stmt = $pdo->prepare("SELECT email FROM cliente WHERE id = ?");
    $stmt->execute([$id]);
    $cliente = $stmt->fetch();

    // Eliminar cliente (cascada a mascotas y citas)
    $stmt = $pdo->prepare("DELETE FROM cliente WHERE id = ?");
    $stmt->execute([$id]);

    // Si había un email, eliminar también el user asociado
    if ($cliente && !empty($cliente['email'])) {
        $stmt = $pdo->prepare("DELETE FROM user WHERE email = ?");
        $stmt->execute([$cliente['email']]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Cliente eliminado correctamente.']);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error eliminar_usuario: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el cliente.']);
}
