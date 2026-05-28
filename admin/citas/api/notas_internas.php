<?php
session_start();
require_once __DIR__ . '/../../../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $cita_id = intval($_GET['cita_id'] ?? 0);
    if ($cita_id === 0) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'cita_id inválido.']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("SELECT n.id, n.mensaje, n.created_at, n.user_id, u.name AS user_name
            FROM cita_notas_internas n
            JOIN user u ON n.user_id = u.id
            WHERE n.cita_id = ?
            ORDER BY n.created_at ASC, n.id ASC");
        $stmt->execute([$cita_id]);
        $notas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'notas' => $notas]);
    } catch (PDOException $e) {
        error_log("Error notas_internas GET: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al cargar notas.']);
    }
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $cita_id = intval($data['cita_id'] ?? 0);
    $mensaje = trim($data['mensaje'] ?? '');

    if ($cita_id === 0 || $mensaje === '') {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'cita_id y mensaje son requeridos.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO cita_notas_internas (cita_id, user_id, mensaje) VALUES (?, ?, ?)");
        $stmt->execute([$cita_id, $_SESSION['user_id'], $mensaje]);
        $id = (int) $pdo->lastInsertId();

        $stmt2 = $pdo->prepare("SELECT n.id, n.mensaje, n.created_at, n.user_id, u.name AS user_name
            FROM cita_notas_internas n JOIN user u ON n.user_id = u.id WHERE n.id = ?");
        $stmt2->execute([$id]);
        $nota = $stmt2->fetch(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'nota' => $nota]);
    } catch (PDOException $e) {
        error_log("Error notas_internas POST: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al guardar nota.']);
    }
    exit;
}

if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);
    $mensaje = trim($data['mensaje'] ?? '');

    if ($id === 0 || $mensaje === '') {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'id y mensaje son requeridos.']);
        exit;
    }

    try {
        // Solo el autor puede editar
        $stmt = $pdo->prepare("UPDATE cita_notas_internas SET mensaje = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$mensaje, $id, $_SESSION['user_id']]);
        if ($stmt->rowCount() === 0) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No puedes editar esta nota.']);
            exit;
        }
        $stmt2 = $pdo->prepare("SELECT n.id, n.mensaje, n.created_at, n.user_id, u.name AS user_name
            FROM cita_notas_internas n JOIN user u ON n.user_id = u.id WHERE n.id = ?");
        $stmt2->execute([$id]);
        echo json_encode(['success' => true, 'nota' => $stmt2->fetch(PDO::FETCH_ASSOC)]);
    } catch (PDOException $e) {
        error_log("Error notas_internas PUT: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al editar nota.']);
    }
    exit;
}

if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);

    if ($id === 0) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'id requerido.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM cita_notas_internas WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        if ($stmt->rowCount() === 0) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No puedes eliminar esta nota.']);
            exit;
        }
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        error_log("Error notas_internas DELETE: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al eliminar nota.']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
