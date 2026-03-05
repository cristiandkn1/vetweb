<?php
require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');

if (strlen($q) < 2) {
    echo json_encode(['success' => false, 'cliente' => null, 'mascota' => []]);
    exit;
}

try {
    // Buscar cliente por nombre_completo o teléfono
    $stmt = $pdo->prepare(
        "SELECT id, nombre_completo, telefono, email
         FROM cliente
         WHERE nombre_completo LIKE ? OR telefono LIKE ?
         ORDER BY nombre_completo ASC
         LIMIT 1"
    );
    $like = '%' . $q . '%';
    $stmt->execute([$like, $like]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        echo json_encode(['success' => false, 'cliente' => null, 'mascota' => []]);
        exit;
    }

    // Buscar mascotas de ese cliente
    $stmt2 = $pdo->prepare(
        "SELECT id, nombre, especie, raza
         FROM mascota
         WHERE cliente_id = ?
         ORDER BY nombre ASC"
    );
    $stmt2->execute([$cliente['id']]);
    $mascota = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'cliente' => $cliente,
        'mascota' => $mascota,
    ]);

} catch (PDOException $e) {
    error_log("Error buscar_cliente: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno.']);
}