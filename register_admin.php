<?php
define('HUINAGRO_SYSTEM', true);
session_start();
require 'database.php';
require 'functions.php';

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'admin'
) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden.";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Correo electrónico no válido.";
    } else {
        $stmt = $conn->prepare("SELECT id_user FROM Users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Ya existe una cuenta con ese correo electrónico.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'admin';
            $stmt = $conn->prepare("INSERT INTO Users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);

            if ($stmt->execute()) {
                
                $success_message = '
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Registro Exitoso - HuinAgro</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        body {
                            background-color: #e6f4ea;
                            padding: 20px;
                            font-family: "Segoe UI", sans-serif;
                        }
                        .success-container {
                            max-width: 600px;
                            margin: 50px auto;
                            background: white;
                            padding: 30px;
                            border-radius: 12px;
                            box-shadow: 0 0 20px rgba(0, 128, 0, 0.1);
                            border-left: 6px solid #2ecc71;
                        }
                        .alert-success {
                            background-color: #d4f9dd;
                            border-color: #2ecc71;
                            color: #155724;
                        }
                        h4 {
                            color: #2a9df4;
                        }
                        .btn-primary {
                            background-color: #2a9df4;
                            border-color: #2a9df4;
                        }
                        .btn-outline-primary {
                            border-color: #2a9df4;
                            color: #2a9df4;
                        }
                        .btn-outline-primary:hover {
                            background-color: #2a9df4;
                            color: white;
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="success-container text-center">
                            <div class="alert alert-success">
                                <h4 class="alert-heading">¡Registro Exitoso!</h4>
                                <p>Administrador <strong>' . htmlspecialchars($username) . '</strong> registrado.</p>
                                <hr>
                                <div class="d-flex justify-content-center gap-3">
                                    <a href="register_admin.php" class="btn btn-outline-primary">Registrar otro</a>
                                    <a href="dashboard_admin.php" class="btn btn-primary">Panel Admin</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </body>
                </html>';
                die($success_message);
            } else {
                $error = "Error al registrar el administrador.";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Registrar Administrador - HuinAgro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f1f5f9;
        }
        .form-container {
            max-width: 420px;
            margin: 3rem auto;
            background-color: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        h2 {
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .btn-block {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Registrar Nuevo Administrador</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Nombre de Usuario</label>
                    <input type="text" name="username" id="username" class="form-control" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" name="email" id="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" name="password" id="password" class="form-control" required minlength="6">
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="6">
                </div>
                <div class="d-grid gap-2 mb-2">
                    <button type="submit" class="btn btn-primary btn-block">Registrar Administrador</button>
                    <a href="dashboard_admin.php" class="btn btn-success btn-block">Volver</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

