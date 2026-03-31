<?php
// admin/usuarios/api/eliminar_mascota.php
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

    // Eliminar las vacunas asociadas antes de la mascota por si no hay CASCADE
    $stmt = $pdo->prepare("DELETE FROM vacuna WHERE mascota_id = ?");
    $stmt->execute([$id]);

    // Eliminar la mascota
    $stmt = $pdo->prepare("DELETE FROM mascota WHERE id = ?");
    $stmt->execute([$id]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Mascota eliminada correctamente.']);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error eliminar_mascota: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al eliminar la mascota.']);
}