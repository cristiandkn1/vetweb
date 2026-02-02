<?php
/**
 * Lógica de Registro Segura
 * Proyecto: Kuimera - VetApp
 */

session_start();
require_once dirname(__DIR__) . '/../includes/db.php';

// 1. Generar token CSRF para prevenir ataques de falsificación de peticiones
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 2. Verificación de Seguridad CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('HTTP/1.1 403 Forbidden');
        die('Error de validación de seguridad: La petición parece no ser auténtica.');
    }

    // 3. Sanitización de entradas
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 4. Validaciones
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Por favor, complete todos los campos obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El formato del correo electrónico no es válido.';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas introducidas no coinciden.';
    } elseif (strlen($password) < 8) {
        $error = 'La seguridad es prioridad: la contraseña debe tener al menos 8 caracteres.';
    } else {


        $stmt = $pdo->prepare("SELECT id FROM user WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'Este correo electrónico ya se encuentra registrado en nuestro sistema.';
        } else {


            $hashed_password = password_hash($password, PASSWORD_BCRYPT);


            $sql = "INSERT INTO user (name, email, password, role, active, createdAt) 
                    VALUES (?, ?, ?, 'CLIENTE', 0, NOW())";

            $stmt = $pdo->prepare($sql);

            try {
                if ($stmt->execute([$name, $email, $hashed_password])) {
                    $message = '¡Registro exitoso! Tu cuenta ha sido creada y está pendiente de activación por un administrador.';

                    // Limpiar variables para que no aparezcan en el formulario tras el éxito
                    $name = $email = '';

                    // Regenerar token CSRF tras una acción exitosa por seguridad
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                } else {
                    $error = 'No pudimos procesar tu registro en este momento.';
                }
            } catch (PDOException $e) {
                // 8. Registro de errores silencioso (No mostrar detalles técnicos al usuario)
                error_log("Error en registro: " . $e->getMessage());
                $error = 'Lo sentimos, ocurrió un error interno en el servidor. Inténtelo más tarde.';
            }
        }
    }
}
?>