<?php
require_once __DIR__ . '/../../../includes/db.php';
header('Content-Type: application/json');

$search = trim($_GET['search'] ?? '');

try {
    $where = '';
    $params = [];
    if ($search !== '') {
        $where = 'WHERE (m.nombre LIKE ? OR m.especie LIKE ? OR cl.nombre_completo LIKE ? OR cl.telefono LIKE ?)';
        $s = "%$search%";
        $params = [$s, $s, $s, $s];
    }

    $stmt = $pdo->prepare("
        SELECT m.*, cl.nombre_completo AS dueno, cl.telefono AS dueno_telefono, cl.email AS dueno_email,
            (SELECT COUNT(*) FROM citas WHERE mascota_id = m.id AND oculta = 0) AS total_citas,
            (SELECT COUNT(*) FROM cotizaciones WHERE mascota_id = m.id) AS total_cotizaciones
        FROM mascota m
        LEFT JOIN cliente cl ON m.cliente_id = cl.id
        $where
        ORDER BY m.fecha_registro DESC
        LIMIT 200
    ");
    $stmt->execute($params);
    $mascotas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Auto-generar token_publico para mascotas sin uno
    foreach ($mascotas as &$m) {
        if (empty($m['token_publico'])) {
            $token = bin2hex(random_bytes(24));
            $upd = $pdo->prepare("UPDATE mascota SET token_publico = ? WHERE id = ?");
            $upd->execute([$token, $m['id']]);
            $m['token_publico'] = $token;
        }
    }
    unset($m);

    echo json_encode(['success' => true, 'mascotas' => $mascotas]);
} catch (PDOException $e) {
    error_log("Error listar_mascotas_admin: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al obtener mascotas.']);
}
