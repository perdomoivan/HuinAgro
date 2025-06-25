<?php
session_start();
include 'database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Por favor, ingresa todos los campos.";
    } else {
        $stmt = $conn->prepare("SELECT id_user, username, password, role FROM Users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id_user, $db_username, $db_password, $role);
            $stmt->fetch();

            if (password_verify($password, $db_password)) {
                $_SESSION['user_id'] = $id_user;
                $_SESSION['username'] = $db_username;
                $_SESSION['user_role'] = $role;

                if ($role === 'admin') {
                    header("Location: dashboard_admin.php");
                } else {
                    header("Location: dashboard_coordinator.php");
                }
                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "Usuario no encontrado.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio de Sesión - HuinAgro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e0f7fa; 
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow rounded-4 border-0">
                    <div class="card-header text-center bg-primary text-white rounded-top-4">
                        <h4>HuinAgro - Iniciar Sesión</h4>
                    </div>
                    <div class="text-center mt-3">
                        <img src="img/logo.jpg" alt="Logo de HuinAgro" style="max-width: 120px; border-radius: 10px;">
                    </div>
                    <div class="card-body px-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Usuario</label>
                                <input type="text" name="username" id="username" class="form-control form-control-lg" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" name="password" id="password" class="form-control form-control-lg" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 btn-lg">Ingresar</button>
                        </form>
                        <div class="mt-3 text-center">
                            <a href="forgot_password.php">¿Olvidaste tu contraseña?</a>
                        </div>
                    </div>
                    <div class="card-footer text-muted text-center rounded-bottom-4">
                        © HuinAgro <?php echo date('Y'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
