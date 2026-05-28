<?php
require_once __DIR__ . '/../../../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$empleado_id = intval($_POST['empleado_id'] ?? 0);
$dia_semana = intval($_POST['dia_semana'] ?? 0);
$hora_inicio = trim($_POST['hora_inicio'] ?? '');
$hora_fin = trim($_POST['hora_fin'] ?? '');
$tipo = trim($_POST['tipo'] ?? 'trabajo');

if ($empleado_id === 0 || $hora_inicio === '' || $hora_fin === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios.']);
    exit;
}
if (!in_array($tipo, ['trabajo', 'colacion'])) $tipo = 'trabajo';

try {
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE horario_semanal SET empleado_id=?, dia_semana=?, hora_inicio=?, hora_fin=?, tipo=? WHERE id=?");
        $stmt->execute([$empleado_id, $dia_semana, $hora_inicio, $hora_fin, $tipo, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO horario_semanal (empleado_id, dia_semana, hora_inicio, hora_fin, tipo) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$empleado_id, $dia_semana, $hora_inicio, $hora_fin, $tipo]);
        $id = (int)$pdo->lastInsertId();
    }
    echo json_encode(['success' => true, 'horario_id' => $id]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
