<?php
define('HUINAGRO_SYSTEM', true);
session_start();
date_default_timezone_set('America/Bogota');

require 'database.php';
require 'functions.php';

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'coordinator'
) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}


$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email FROM Users WHERE id_user = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Panel de Coordinador - HuinAgro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<body class="bg-light d-flex">

    <aside class="bg-primary text-white p-3 d-flex flex-column" style="width: 260px; min-height: 100vh;">
        <h4 class="text-center mb-4 d-flex justify-content-center align-items-center gap-2">
          <i class="bi bi-stars text-warning fs-4"></i>
          <span class="fw-bold text-uppercase">Menú del Coordinador</span>
          <i class="bi bi-person-badge-fill text-warning fs-4"></i>
        </h4>


        <nav class="nav flex-column">
            <a href="dashboard_coordinator.php" class="nav-link text-white fw-semibold d-flex align-items-center gap-2 bg-success rounded mb-2">
                <i class="bi bi-house-door-fill fs-5"></i> <span>Inicio</span>
            </a>
            <a href="fishing_records.php" class="nav-link text-white fw-semibold d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-basket-fill fs-5"></i> <span>Registrar venta</span>
            </a>
            <a href="user_history.php" class="nav-link text-white fw-semibold d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-clock-history fs-5"></i> <span>Historial de ventas</span>
            </a>
            <a href="reports.php" class="nav-link text-white fw-semibold d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-file-earmark-bar-graph-fill fs-5"></i> <span>Historial de descargas</span>
            </a>
        </nav>
    </aside>

    <main class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center bg-primary text-white p-3 rounded mb-4 shadow">
            <h2 class="m-0">HuinAgro - Panel de Coordinador</h2>
            <a href="logout.php?nocache=<?= time() ?>" class="btn btn-success">Cerrar Sesión</a>
        </div>

        <div class="bg-white p-4 rounded shadow border-start border-success border-5">
            <h1 class="fw-bold">Bienvenid@ <?= htmlspecialchars($user['username'] ?? 'Usuario') ?></h1>
            <p class="text-muted mb-0">Hora de acceso: <span id="hora-acceso"></span></p>
        </div>
    </main>

    <script>
        function actualizarHora() {
            const ahora = new Date();
            const opciones = {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            };
            const horaFormateada = ahora.toLocaleString('es-CO', opciones).replace(',', '');
            document.getElementById('hora-acceso').textContent = horaFormateada;
        }

        setInterval(actualizarHora, 1000);
        actualizarHora();
    </script>

</body>
</html>
