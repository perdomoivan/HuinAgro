<?php
require_once 'database.php';
session_start();

$error = '';
$success = '';
$valid_token = false;
$email = '';


if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $stmt = $conn->prepare("SELECT email FROM Users WHERE reset_token = ? AND token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $valid_token = true;
        $email = $result->fetch_assoc()['email'];
    } else {
        $error = 'El enlace de recuperación es inválido o ha expirado.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($password) || empty($confirm_password)) {
        $error = 'Por favor, complete todos los campos.';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE Users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
        
        if ($stmt->execute()) {
            $success = 'Contraseña actualizada exitosamente. Ya puede iniciar sesión.';
        } else {
            $error = 'Error al actualizar la contraseña.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - HuinAgro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .reset-card {
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: none;
            border-radius: 15px;
        }
        .btn-custom {
            background-color: #42a5f5;
            color: white;
        }
        .btn-custom:hover {
            background-color: #1e88e5;
            color: white;
        }
        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card reset-card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-shield-lock text-primary" style="font-size: 3rem;"></i>
                        <h2 class="mt-2">Nueva Contraseña</h2>
                        <p class="text-muted">Ingresa tu nueva contraseña</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                            <div class="mt-3 text-center">
                                <a href="login.php" class="btn btn-success">
                                    <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
                                </a>
                            </div>
                        </div>
                    <?php elseif ($valid_token): ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="password" class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <small class="text-muted">Mínimo 6 caracteres</small>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>

                            <button type="submit" class="btn btn-custom w-100 mb-3">
                                <i class="bi bi-check-circle"></i> Actualizar Contraseña
                            </button>
                        </form>
                    <?php endif; ?>

                    <div class="text-center">
                        <a href="login.php" class="text-muted text-decoration-none">
                            <i class="bi bi-arrow-left"></i> Volver al login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
                 