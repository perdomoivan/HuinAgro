<?php
define('HUINAGRO_SYSTEM', true);
session_start();
date_default_timezone_set('America/Bogota');

require 'database.php';
require 'functions.php';

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'admin'
) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

$welcome_message = isset($_SESSION['is_developer_admin']) 
    ? "Bienvenido Administrador Maestro" 
    : "Bienvenido Administrador";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Panel de Administración - HuinAgro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #2a9df4;
            --secondary-color: #28a745;
            --danger-color: #dc3545;
            --light-bg: #f8f9fa;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--primary-color);
            color: white;
            position: fixed;
            height: 100vh;
            z-index: 1000;
        }
        .sidebar-header {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        .sidebar-nav .nav-item {
            color: white;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            text-decoration: none;
            border-radius: 5px;
            margin: 0.5rem 0;
        }
        .sidebar-nav .nav-item:hover,
        .sidebar-nav .nav-item.active {
            background-color: rgba(255, 255, 255, 0.2);
        }
        .nav-item i {
            margin-right: 10px;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            padding: 2rem;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--primary-color);
            color: white;
            padding: 1rem 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        #clock {
            font-size: 0.9rem;
            color: #f1f1f1;
            margin-top: 0.3rem;
            text-align: right;
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-header text-center">
        <h4 class="d-flex flex-column align-items-center m-0">
            <i class="bi bi-fish text-success fs-2 mb-1"></i>
            <span class="fw-bold fs-5 text-white">🐟 Huin<span class="text-success">Agro</span></span>
            
        </h4>
    </div>

    <nav class="sidebar-nav">
        <a href="dashboard_admin.php" class="nav-item active">
            <i class="bi bi-speedometer2"></i><strong><em><span>Panel Principal</span></em></strong>
        </a>
        <a href="reports_admin.php" class="nav-item">
            <i class="bi bi-file-earmark-bar-graph"></i><span>Historial de descargas</span>
        </a>
        <a href="historial_admin.php" class="nav-item">
            <i class="bi bi-receipt"></i><span>Historial de Ventas</span>
        </a>
        <a href="register_coordinator.php" class="nav-item">
            <i class="bi bi-person-plus"></i><span>Registrar Coordinador</span>
        </a>
        <a href="register_admin.php" class="nav-item">
            <i class="bi bi-person-plus-fill"></i><span>Registrar Administrador</span>
        </a>
    </nav>
</aside>

<main class="main-content">
    <header class="admin-header">
        <div>
            <h2>Panel de  Administrador</h2>
            <div id="clock"></div>
        </div>
        <div>
            <span class="me-3"><?= htmlspecialchars($_SESSION['username']) ?></span>
            <a href="logout.php" class="btn btn-success btn-sm">
                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
            </a>
        </div>
    </header>

    <div class="bg-white p-4 rounded shadow-sm">
        <h4><?= $welcome_message ?></h4>
        <p>Este es el panel principal de administración de HuinAgro.</p>
    </div>
</main>

<script>
    function updateClock() {
        const now = new Date();
        const options = {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
            hour: '2-digit', minute: '2-digit', second: '2-digit',
            hour12: true,
            timeZone: 'America/Bogota'
        };
        const formatted = now.toLocaleString('es-CO', options);
        document.getElementById('clock').textContent = formatted;
    }

    setInterval(updateClock, 1000);
    updateClock();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
