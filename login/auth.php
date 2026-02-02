<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo "<script>alert('Por favor complete todos los campos.'); window.location.href='../index.html';</script>";
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, name, password, role, active FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['active'] == 1) {
                // Login exitoso
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                // Redirección basada en rol
                if ($user['role'] === 'admin' || $user['role'] === 'ADMIN') { // Check for both just in case
                    header("Location: ../admin/dashboard.php");
                } else {
                    // Clientes y otros roles van a /sesion/
                    header("Location: ../sesion/dashboard.php");
                }
                exit;
            } else {
                echo "<script>alert('Tu cuenta está inactiva. Contacta al administrador.'); window.location.href='../index.html';</script>";
                exit;
            }
        } else {
            echo "<script>alert('Correo o contraseña incorrectos.'); window.location.href='../index.html';</script>";
            exit;
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo "<script>alert('Error del sistema. Intente más tarde.'); window.location.href='../index.html';</script>";
        exit;
    }
} else {
    // Si intentan acceder directo al archivo
    header("Location: ../index.html");
    exit;
}
?>