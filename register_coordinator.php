<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
define('HUINAGRO_SYSTEM', true);

require 'database.php';
require 'functions.php';


check_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Error de seguridad (CSRF)";
        redirect('register_coordinator.php');
    }

    $username = clean_input($_POST['username']);
    $email = filter_var(clean_input($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

   
    if (!$email) {
        $_SESSION['error'] = "Correo electrónico inválido";
    } elseif ($password !== $confirm_password) {
        $_SESSION['error'] = "Las contraseñas no coinciden";
    } else {
        try {
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

          
            $stmt = $conn->prepare("INSERT INTO Users (username, email, password, role) VALUES (?, ?, ?, 'coordinator')");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            
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
                                <p>Coordinador <strong>' . htmlspecialchars($username) . '</strong> registrado.</p>
                                <hr>
                                <div class="d-flex justify-content-center gap-3">
                                    <a href="register_coordinator.php" class="btn btn-outline-primary">Registrar otro</a>
                                    <a href="dashboard_admin.php" class="btn btn-primary">Panel Admin</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </body>
                </html>';
                die($success_message);
            } else {
                throw new Exception("Error al registrar: " . $stmt->error);
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Coordinador - HuinAgro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e6f4ea;
            padding: 20px;
            font-family: "Segoe UI", sans-serif;
        }

        .form-container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(42, 157, 244, 0.1);
            border-left: 6px solid #2a9df4;
        }

        h1 {
            color: #2a9df4;
        }

        .form-label {
            color: #2c3e50;
        }

        .btn-danger {
            background-color: #2ecc71;
            border-color: #27ae60;
        }

        .btn-danger:hover {
            background-color: #27ae60;
        }

        .btn-secondary {
            background-color: #3498db;
            border-color: #2980b9;
        }

        .btn-secondary:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1 class="text-center mb-4">Registrar Nuevo Coordinador</h1>
            
            <?php display_session_error(); ?>
            
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <div class="mb-3">
                    <label for="username" class="form-label">Nombre de usuario:</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico:</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña:</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirmar contraseña:</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-danger">Registrar</button>
                    <a href="dashboard_admin.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
