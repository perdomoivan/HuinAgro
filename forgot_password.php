<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'database.php';
require_once 'vendor/autoload.php';
require_once 'email_config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

date_default_timezone_set('America/Bogota');

session_start();

$message = '';
$error = '';


function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['PHP_SELF']);
    return $protocol . $host . $path . '/';
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo']);
    
    if (empty($correo)) {
        $error = 'Por favor, ingrese su correo.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'Correo electrónico no válido.';
    } else {
        $stmt = $conn->prepare("SELECT id_user, username FROM Users WHERE email = ?");
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            
            if (empty($token)) {
                die("Token generation failed!");
            }
           
            $stmt = $conn->prepare("UPDATE Users SET reset_token = ?, token_expiry = ? WHERE email = ?");
            if ($stmt === false) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("sss", $token, $expires, $correo);
            $success = $stmt->execute();
            if ($success === false) {
                die("Execute failed: " . $stmt->error);
            }
            $affected_rows = $stmt->affected_rows;
            

            if ($affected_rows > 0) {
                
                try {
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = EMAIL_HOST;
                    $mail->SMTPAuth = true;
                    $mail->Username = EMAIL_USERNAME;
                    $mail->Password = EMAIL_PASSWORD;
                    $mail->SMTPSecure = EMAIL_SMTPSecure;
                    $mail->Port = EMAIL_PORT;

                    $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
                    $mail->addAddress($correo);
                    $mail->Subject = 'Restablecer Contraseña - HuinAgro';
                    $mail->isHTML(true);
                    
                    
                    $baseUrl = getBaseUrl();
                    $resetUrl = $baseUrl . 'reset_password.php?token=' . urlencode($token);
                    
                    
                    $emailBody = "
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                            <div style='text-align: center; margin-bottom: 30px;'>
                                <h2 style='color: #333; margin-bottom: 10px;'>Restablecer Contraseña</h2>
                                <p style='color: #666;'>HuinAgro</p>
                            </div>
                            
                            <div style='margin-bottom: 30px;'>
                                <p style='color: #333; font-size: 16px;'>Hola <strong>{$user['username']}</strong>,</p>
                                <p style='color: #666; line-height: 1.6;'>
                                    Hemos recibido una solicitud para restablecer la contraseña de tu cuenta. 
                                    Si no realizaste esta solicitud, puedes ignorar este correo.
                                </p>
                                <p style='color: #666; line-height: 1.6;'>
                                    Para restablecer tu contraseña, haz clic en el siguiente botón:
                                </p>
                            </div>
                            
                            <div style='text-align: center; margin: 30px 0;'>
                                <a href='{$resetUrl}' 
                                   style='background-color: #42a5f5; color: white; padding: 12px 30px; 
                                          text-decoration: none; border-radius: 5px; font-weight: bold; 
                                          display: inline-block;'>
                                    Restablecer Contraseña
                                </a>
                            </div>
                            
                            <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;'>
                                <p style='color: #999; font-size: 14px;'>
                                    <strong>Importante:</strong> Este enlace expirará en 1 hora por seguridad.
                                </p>
                                <p style='color: #999; font-size: 12px;'>
                                    Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
                                    <span style='word-break: break-all;'>{$resetUrl}</span>
                                </p>
                            </div>
                        </div>";
                    
                    

                    $mail->Body = $emailBody;
                    $mail->AltBody = "Hola {$user['username']},\n\nHemos recibido una solicitud para restablecer la contraseña de tu cuenta.\n\nPara restablecer tu contraseña, visita el siguiente enlace:\n{$resetUrl}\n\nEste enlace expirará en 1 hora.\n\nSi no realizaste esta solicitud, puedes ignorar este correo.\n\nSaludos,\nEquipo HuinAgro";

                    

                    if ($mail->send()) {
                        $message = "✅ Se ha enviado un enlace de recuperación a su correo electrónico.";
                    } else {
                        $error = 'Error al enviar el correo: ' . $mail->ErrorInfo;
                    }
                } catch (Exception $e) {
                    $error = 'Error del sistema de correo: ' . $e->getMessage();
                }
            } else {
                $error = 'Error al procesar la solicitud. Inténtelo nuevamente.';
            }
        } else {
            $error = 'No se encontró una cuenta con ese correo electrónico.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - HuinAgro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .forgot-card {
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
                <div class="card forgot-card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-key text-primary" style="font-size: 3rem;"></i>
                            <h2 class="mt-2">Recuperar Contraseña</h2>
                            <p class="text-muted">Ingresa tu correo para recibir un enlace de recuperación</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($message): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="bi bi-check-circle"></i> <?php echo $message; ?>
                                <hr>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> 
                                    Si no recibes el correo en unos minutos, revisa tu carpeta de spam.
                                </small>
                            </div>
                            <div class="text-center mt-3">
                                <a href="login.php" class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-left"></i> Volver al Login
                                </a>
                            </div>
                        <?php else: ?>
                            <form method="POST">
                                <div class="mb-4">
                                    <label for="correo" class="form-label">
                                        <i class="bi bi-envelope"></i> Correo Electrónico
                                    </label>
                                    <input type="email" class="form-control" id="correo" name="correo" 
                                           placeholder="ejemplo@correo.com" required>
                                </div>

                                <button type="submit" class="btn btn-custom w-100 mb-3">
                                    <i class="bi bi-send"></i> Enviar Enlace de Recuperación
                                </button>
                            </form>
                        <?php endif; ?>

                        <div class="text-center">
                            <p class="mb-0">¿Recordaste tu contraseña? 
                                <a href="login.php" class="text-decoration-none">Inicia sesión</a>
                            </p>
                            <a href="index.php" class="text-muted text-decoration-none">
                                <i class="bi bi-arrow-left"></i> Volver al inicio
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