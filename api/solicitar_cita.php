<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Datos no recibidos']);
    exit;
}

$nombre = trim($data['nombre_completo'] ?? '');
$email = trim($data['email'] ?? '');
$rut = trim($data['rut'] ?? '');
$telefono = trim($data['telefono'] ?? '');
$mascota_nombre = trim($data['mascota_nombre'] ?? '');
$mascota_especie = trim($data['mascota_especie'] ?? '');
$mascota_raza = trim($data['mascota_raza'] ?? '');
$mascota_sexo = trim($data['mascota_sexo'] ?? '');
$servicio = trim($data['servicio'] ?? '');
$fecha = trim($data['fecha'] ?? '');
$nota = trim($data['nota'] ?? '');

if (empty($nombre) || empty($email) || empty($telefono) || empty($mascota_nombre) || empty($servicio) || empty($fecha)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Todos los campos obligatorios deben ser completados.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO solicitudes (nombre_completo, email, rut, telefono, mascota_nombre, mascota_especie, mascota_raza, mascota_sexo, servicio, fecha_cita, nota, estado, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', NOW())");
    $stmt->execute([$nombre, $email, $rut, $telefono, $mascota_nombre, $mascota_especie, $mascota_raza, $mascota_sexo, $servicio, $fecha, $nota]);

    echo json_encode(['ok' => true, 'msg' => 'La cita se creó con éxito. Será contactado por celular o correo electrónico para confirmar la fecha.']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Error al procesar la solicitud.']);
}
