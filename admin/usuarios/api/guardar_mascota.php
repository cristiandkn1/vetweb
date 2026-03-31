<?php
// admin/usuarios/api/guardar_mascota.php
require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$cliente_id = intval($_POST['cliente_id'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$especie = trim($_POST['especie'] ?? '');
$raza = trim($_POST['raza'] ?? '');
$fecha_nac = trim($_POST['fecha_nacimiento'] ?? '');
$sexo = trim($_POST['sexo'] ?? '');
$color = trim($_POST['color'] ?? '');
$observaciones = trim($_POST['observaciones'] ?? '');

if ($nombre === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio.']);
    exit;
}
if ($cliente_id === 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Cliente inválido.']);
    exit;
}

try {
    if ($id > 0) {
        $stmt = $pdo->prepare(
            "UPDATE mascota
             SET nombre=?, especie=?, raza=?, fecha_nacimiento=?, sexo=?, color=?,
                 observaciones=?, fecha_actualizacion=NOW()
             WHERE id=? AND cliente_id=?"
        );
        $stmt->execute([
            $nombre,
            $especie ?: null,
            $raza ?: null,
            $fecha_nac ?: null,
            $sexo ?: null,
            $color ?: null,
            $observaciones ?: null,
            $id,
            $cliente_id
        ]);
        echo json_encode(['success' => true, 'message' => 'Mascota actualizada correctamente.']);
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO mascota
                (cliente_id, nombre, especie, raza, fecha_nacimiento, sexo, color, observaciones, fecha_registro, fecha_actualizacion)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
        );
        $stmt->execute([
            $cliente_id,
            $nombre,
            $especie ?: null,
            $raza ?: null,
            $fecha_nac ?: null,
            $sexo ?: null,
            $color ?: null,
            $observaciones ?: null
        ]);
        $nuevo_id = (int) $pdo->lastInsertId();
        echo json_encode(['success' => true, 'message' => 'Mascota registrada correctamente.', 'id' => $nuevo_id]);
    }
} catch (PDOException $e) {
    error_log("Error guardar_mascota: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno al guardar.']);
}