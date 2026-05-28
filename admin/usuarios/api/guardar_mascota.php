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
$peso = !empty($_POST['peso']) ? floatval($_POST['peso']) : null;
$esterilizado = isset($_POST['esterilizado']) ? 1 : 0;
$numero_chip = trim($_POST['numero_chip'] ?? '');
$ultima_revision = trim($_POST['ultima_revision'] ?? '');
$alergias = trim($_POST['alergias'] ?? '');
$notas_internas = trim($_POST['notas_internas'] ?? '');

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
    $pdo->beginTransaction();

    if ($id > 0) {
        $stmt = $pdo->prepare(
            "UPDATE mascota
             SET nombre=?, especie=?, raza=?, fecha_nacimiento=?, sexo=?, color=?,
                 peso=?, esterilizado=?, numero_chip=?, ultima_revision=?, alergias=?, notas_internas=?,
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
            $peso,
            $esterilizado,
            $numero_chip ?: null,
            $ultima_revision ?: null,
            $alergias ?: null,
            $notas_internas ?: null,
            $observaciones ?: null,
            $id,
            $cliente_id
        ]);
        $mascota_id = $id;
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO mascota
                (cliente_id, nombre, especie, raza, fecha_nacimiento, sexo, color,
                 peso, esterilizado, numero_chip, ultima_revision, alergias, notas_internas,
                 observaciones, token_publico, fecha_registro, fecha_actualizacion)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
        );
        $stmt->execute([
            $cliente_id,
            $nombre,
            $especie ?: null,
            $raza ?: null,
            $fecha_nac ?: null,
            $sexo ?: null,
            $color ?: null,
            $peso,
            $esterilizado,
            $numero_chip ?: null,
            $ultima_revision ?: null,
            $alergias ?: null,
            $notas_internas ?: null,
            $observaciones ?: null,
            bin2hex(random_bytes(24))
        ]);
        $mascota_id = (int) $pdo->lastInsertId();
    }

    // ── Guardar Vacunas ──────────────────────────────────────────────────────────
    $vacuna_ids_validos = [];
    $vacuna_nombres = $_POST['vacuna_nombre'] ?? [];
    $vacuna_ids     = $_POST['vacuna_id'] ?? [];
    $vacuna_f_ap    = $_POST['vacuna_fecha_aplicacion'] ?? [];
    $vacuna_f_pr    = $_POST['vacuna_fecha_proxima'] ?? [];
    $vacuna_vet     = $_POST['vacuna_veterinario'] ?? [];
    $vacuna_lote    = $_POST['vacuna_lote'] ?? [];
    $vacuna_notas   = $_POST['vacuna_notas'] ?? [];

    for ($i = 0; $i < count($vacuna_nombres); $i++) {
        $v_nombre = trim($vacuna_nombres[$i] ?? '');
        if ($v_nombre === '') continue;

        $v_id   = intval($vacuna_ids[$i] ?? 0);
        $v_f_ap = !empty($vacuna_f_ap[$i]) ? $vacuna_f_ap[$i] : null;
        $v_f_pr = !empty($vacuna_f_pr[$i]) ? $vacuna_f_pr[$i] : null;
        $v_vet  = trim($vacuna_vet[$i] ?? '');
        $v_lote = trim($vacuna_lote[$i] ?? '');
        $v_notas= trim($vacuna_notas[$i] ?? '');

        if ($v_id > 0) {
            $stmt = $pdo->prepare("UPDATE vacuna SET nombre=?, fecha_aplicacion=?, fecha_proxima=?, veterinario=?, lote=?, notas=? WHERE id=? AND mascota_id=?");
            $stmt->execute([$v_nombre, $v_f_ap, $v_f_pr, $v_vet, $v_lote, $v_notas, $v_id, $mascota_id]);
            $vacuna_ids_validos[] = $v_id;
        } else {
            $stmt = $pdo->prepare("INSERT INTO vacuna (mascota_id, nombre, fecha_aplicacion, fecha_proxima, veterinario, lote, notas) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$mascota_id, $v_nombre, $v_f_ap, $v_f_pr, $v_vet, $v_lote, $v_notas]);
            $vacuna_ids_validos[] = (int) $pdo->lastInsertId();
        }
    }

    if (count($vacuna_ids_validos) > 0) {
        $placeholders = implode(',', array_fill(0, count($vacuna_ids_validos), '?'));
        $stmt = $pdo->prepare("DELETE FROM vacuna WHERE mascota_id=? AND id NOT IN ($placeholders)");
        $params = array_merge([$mascota_id], $vacuna_ids_validos);
        $stmt->execute($params);
    } else {
        $stmt = $pdo->prepare("DELETE FROM vacuna WHERE mascota_id=?");
        $stmt->execute([$mascota_id]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Mascota y vacunas guardadas correctamente.', 'id' => $mascota_id]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error guardar_mascota: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno al guardar.']);
}