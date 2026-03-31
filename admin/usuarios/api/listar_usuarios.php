<?php
// admin/usuario/api/listar_usuarios.php
require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query(
        "SELECT id, nombre_completo, rut, telefono, email, direccion, fecha_registro
         FROM cliente
         ORDER BY fecha_registro DESC"
    );
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'usuarios' => $usuarios]);
} catch (PDOException $e) {
    error_log("Error listar_usuarios: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al obtener clientes.']);
}