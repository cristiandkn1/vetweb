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
    // Obtener foto para borrarla del disco
    $stmt = $pdo->prepare("SELECT foto_url FROM mascota WHERE id = ?");
    $stmt->execute([$id]);
    $m = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($m && $m['foto_url']) {
        $path = __DIR__ . '/../../../../' . ltrim($m['foto_url'], '/vetweb/');
        if (file_exists($path))
            unlink($path);
    }

    $stmt = $pdo->prepare("DELETE FROM mascota WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'Mascota eliminada correctamente.']);
} catch (PDOException $e) {
    error_log("Error eliminar_mascota: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al eliminar la mascota.']);
}