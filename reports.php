<?php
define('HUINAGRO_SYSTEM', true);
session_start();
date_default_timezone_set('America/Bogota');

require 'database.php';
require 'functions.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinator') {
    header("Location: login.php");
    exit;
}


$user_id = $_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_report'])) {
    $report_id = filter_input(INPUT_POST, 'report_id', FILTER_SANITIZE_NUMBER_INT);
    
    
    $check_stmt = $conn->prepare("SELECT id_user FROM Reports WHERE id_report = ?");
    $check_stmt->bind_param("i", $report_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 1) {
        $report_data = $check_result->fetch_assoc();
        if ($report_data['id_user'] == $user_id) {
            
            $delete_stmt = $conn->prepare("DELETE FROM Reports WHERE id_report = ?");
            $delete_stmt->bind_param("i", $report_id);
            $delete_stmt->execute();
            $delete_stmt->close();
            
            
            header("Location: reports.php");
            exit;
        }
    }
    $check_stmt->close();
}


$stmt = $conn->prepare("
    SELECT r.id_report, r.id_user, u.username as coordinator_name, r.date, r.report_type
    FROM Reports r
    JOIN Users u ON r.id_user = u.id_user
    WHERE r.id_user = ?
    ORDER BY r.date DESC, r.id_report DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$reports = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reportes - HuinAgro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root {
            --color-azul: #0d6efd;
            --color-verde: #198754;
        }
        
        .btn-delete {
            transition: transform 0.2s;
            background-color: var(--color-azul);
            border-color: var(--color-azul);
        }
        
        .btn-delete:hover {
            transform: scale(1.05);
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        
        .bg-custom-header {
            background-color: var(--color-verde) !important;
        }
        
        .border-custom {
            border-left-color: var(--color-verde) !important;
        }
        
        .table-custom thead {
            background-color: var(--color-azul);
            color: white;
        }
        
        .nav-link.active {
            background-color: var(--color-verde) !important;
        }
        
        .btn-logout {
            background-color: var(--color-azul);
            border-color: var(--color-azul);
        }
    </style>
</head>
<body class="bg-light d-flex">

<aside class="bg-primary text-white p-3 d-flex flex-column" style="width: 260px; min-height: 100vh;">
    <h4 class="text-center mb-4 d-flex justify-content-center align-items-center gap-2">
          <i class="bi bi-stars text-warning fs-4"></i>
          <span class="fw-bold text-uppercase">Menú del Coordinador</span>
          <i class="bi bi-person-badge-fill text-warning fs-4"></i>
        </h4>
     
     
    <nav class="nav flex-column">
        <a href="dashboard_coordinator.php" class="nav-link text-white fw-semibold d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-house-door-fill fs-5"></i> <span>Inicio</span>
        </a>
        <a href="fishing_records.php" class="nav-link text-white fw-semibold d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-basket-fill fs-5"></i> <span>Registrar venta</span>
        </a>
        <a href="user_history.php" class="nav-link text-white fw-semibold d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-clock-history fs-5"></i> <span>Historial</span>
        </a>
        <a href="reports.php" class="nav-link text-white fw-semibold d-flex align-items-center gap-2 active rounded mb-2">
            <i class="bi bi-file-earmark-bar-graph-fill fs-5"></i> <span>Historial de descargas</span>
        </a>
    </nav>
</aside>

<main class="flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center bg-custom-header text-white p-3 rounded mb-4 shadow">
        <h2 class="m-0">HuinAgro - Descargas</h2>
        <a href="logout.php?nocache=<?= time() ?>" class="btn btn-logout text-white">Cerrar Sesión</a>
    </div>

    <div class="bg-white p-4 rounded shadow border-start border-custom border-5">
        <h1 class="fw-bold">Historial de    Descargas</h1>

        <?php if (empty($reports)): ?>
            <div class="alert alert-info">No hay registros de reportes generados.</div>
        <?php else: ?>
            <table class="table table-custom table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Coordinador</th>
                        <th>Fecha</th>
                        <th>Tipo de Documento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): ?>
                        <tr>
                            <td><?= htmlspecialchars($report['id_report']) ?></td>
                            <td><?= htmlspecialchars($report['coordinator_name']) ?></td>
                            <td><?= htmlspecialchars($report['date']) ?></td>
                            <td><?= htmlspecialchars($report['report_type']) ?></td>
                            <td>
                                <form method="POST" action="reports.php" class="d-inline" id="form-delete-<?= $report['id_report'] ?>">
                                    <input type="hidden" name="report_id" value="<?= $report['id_report'] ?>">
                                    <input type="hidden" name="delete_report" value="1">
                                    <button type="button" class="btn btn-delete btn-sm text-white"
                                            onclick="confirmDelete(<?= $report['id_report'] ?>, '<?= htmlspecialchars($report['report_type']) ?>', '<?= htmlspecialchars($report['date']) ?>')">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmDelete(reportId, reportType, reportDate) {
    Swal.fire({
        title: '¿Eliminar Reporte?',
        html: `<div class="text-start">
                <p>Estás a punto de eliminar:</p>
                <div class="alert alert-warning p-2">
                    <strong>Tipo:</strong> ${reportType}<br>
                    <strong>Fecha:</strong> ${reportDate}
                </div>
                <p>Esta acción no se puede deshacer.</p>
               </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#198754',
        customClass: {
            confirmButton: 'btn btn-primary mx-2',
            cancelButton: 'btn btn-success mx-2'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(`form-delete-${reportId}`).submit();
        }
    });
}
</script>
</body>
</html>