<?php
session_start(); // Necesario para CSRF
require_once dirname(__DIR__) . '/../includes/db.php';

// 1. Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. Validar Token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Error de validación de seguridad (CSRF).');
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Por favor complete todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // 3. Validar email
        $error = 'El formato del correo no es válido.';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($password) < 8) { // Recomendado subir a 8
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM user WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'El correo electrónico ya está registrado.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Ajustado a tu lógica de 'active' según lo que hablamos antes
            $sql = "INSERT INTO user (name, email, password, role, active, createdAt) VALUES (?, ?, ?, 'VETERINARIO', 0, NOW())";
            $stmt = $pdo->prepare($sql);

            try {
                if ($stmt->execute([$name, $email, $hashed_password])) {
                    $message = '¡Registro exitoso! Espera a que un admin active tu cuenta.';
                    $name = $email = '';
                    // 4. Cambiar el token después de un éxito
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
            } catch (PDOException $e) {
                // 5. No exponer $e->getMessage() en producción
                error_log($e->getMessage()); // Guarda el error en el log del servidor
                $error = 'Ocurrió un error interno. Inténtelo más tarde.';
            }
        }
    }
}
?>