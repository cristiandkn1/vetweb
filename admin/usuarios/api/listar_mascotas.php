<?php
// admin/usuarios/api/listar_mascotas.php
require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

$cliente_id = intval($_GET['cliente_id'] ?? 0);

if ($cliente_id === 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Cliente inválido.']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "SELECT id, cliente_id, nombre, especie, raza, fecha_nacimiento, sexo, color, peso, ultima_revision, notas_internas, numero_chip, esterilizado, alergias, observaciones, fecha_registro, fecha_actualizacion
         FROM mascota
         WHERE cliente_id = ?
         ORDER BY nombre ASC"
    );
    $stmt->execute([$cliente_id]);
    $mascotas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'mascotas' => $mascotas]);
} catch (PDOException $e) {
    error_log("Error listar_mascotas: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al obtener mascotas.']);
}