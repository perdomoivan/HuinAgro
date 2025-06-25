<?php
define('HUINAGRO_SYSTEM', true);
session_start();
date_default_timezone_set('America/Bogota');

require 'database.php';
require 'functions.php';

if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed: " . ($conn->connect_error ?? "Connection not initialized"));
}

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

$presentations = $conn->query("SELECT id_weight_presentation, description FROM WeightPresentations")->fetch_all(MYSQLI_ASSOC);
$fish_types = $conn->query("SELECT id_fish_type, name FROM FishTypes")->fetch_all(MYSQLI_ASSOC);

$errors = [];
$success = false;
$total_sale = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client = trim($_POST['client']);
    $lake = trim($_POST['lake']);
    $date = $_POST['date'];

    if (empty($client) || empty($lake) || empty($date)) {
        $errors[] = "Todos los campos generales son obligatorios.";
    } else {
        if (!isset($_POST['fish_type']) || count($_POST['fish_type']) === 0) {
            $errors[] = "Debe agregar al menos una entrada de pescado.";
        } else {
            $conn->begin_transaction();

            try {
                $dummy_fish_type = (int) $_POST['fish_type'][0];
                $stmt = $conn->prepare("INSERT INTO FishingRecords (id_user, id_fish_type, client, lake, date, total_amount) VALUES (?, ?, ?, ?, ?, 0)");
                $stmt->bind_param("iisss", $user_id, $dummy_fish_type, $client, $lake, $date);
                $stmt->execute();
                $record_id = $stmt->insert_id;
                $stmt->close();

                $total_sale = 0;

                foreach ($_POST['fish_type'] as $index => $fish_type) {
                    $fish_type = (int) $_POST['fish_type'][$index];
                    $presentation = (int) $_POST['presentation'][$index];
                    $baskets = (int) $_POST['baskets'][$index];
                    $gross_weight = (float) $_POST['gross_weight'][$index];
                    $price_per_kg = (float) $_POST['price_per_kg'][$index];

                    if ($fish_type > 0 && $presentation > 0 && $baskets >= 0 && $gross_weight >= 0 && $price_per_kg >= 0) {
                        $entry_total = $gross_weight * $price_per_kg;

                        $stmt = $conn->prepare("INSERT INTO FishingEntries (id_record, id_fish_type, id_weight_presentation, baskets, gross_weight, price) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("iiiidd", $record_id, $fish_type, $presentation, $baskets, $gross_weight, $price_per_kg);
                        $stmt->execute();
                        $stmt->close();

                        $total_sale += $entry_total;
                    }
                }

                $stmt = $conn->prepare("UPDATE FishingRecords SET total_amount = ? WHERE id_record = ?");
                $stmt->bind_param("di", $total_sale, $record_id);
                $stmt->execute();
                $stmt->close();

                $conn->commit();
                $success = true;

            } catch (Exception $e) {
                $conn->rollback();
                $errors[] = "Error al registrar la venta: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Registrar Venta - HuinAgro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<body class="bg-light pt-4 pb-5">
    <a href="dashboard_coordinator.php" class="btn btn-primary position-fixed top-0 end-0 m-3">
        <i class="bi bi-house-door-fill"></i> Inicio
    </a>

    <div class="container">
        <div class="card shadow mx-auto" style="max-width: 750px;">
            <div class="card-body">
                <h2 class="card-title text-center text-primary mb-4">Registrar Venta</h2>

                <?php if ($success): ?>
                    <div class="alert alert-success text-center">¡Venta registrada exitosamente!</div>
                    
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" id="salesForm" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Cliente</label>
                        <input type="text" class="form-control" name="client" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lago</label>
                        <input type="text" class="form-control" name="lake" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fecha</label>
                        <input type="date" class="form-control" name="date" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <h5 class="mt-4 text-secondary">Entradas de Pescado</h5>
                    <div id="entries">
                        <div class="row g-3 entry-group align-items-end mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Tipo de Pescado</label>
                                <select name="fish_type[]" class="form-select" required>
                                    <option value="">Seleccionar</option>
                                    <?php foreach ($fish_types as $f): ?>
                                        <option value="<?= $f['id_fish_type'] ?>"><?= htmlspecialchars($f['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Presentación</label>
                                <select name="presentation[]" class="form-select" required>
                                    <option value="">Seleccionar</option>
                                    <?php foreach ($presentations as $p): ?>
                                        <option value="<?= $p['id_weight_presentation'] ?>"><?= htmlspecialchars($p['description']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Canastas</label>
                                <input type="number" name="baskets[]" min="0" class="form-control" required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Peso Bruto (kg)</label>
                                <input type="number" name="gross_weight[]" step="0.01" min="0" class="form-control" required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Precio/kg</label>
                                <input type="number" name="price_per_kg[]" step="0.01" min="0" class="form-control" required>
                            </div>

                            <div class="col-md-1">
                                <button type="button" class="btn btn-primary btn-sm btn-remove-entry mt-4">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="addEntry" class="btn btn-success mb-3">
                        <i class="bi bi-plus-circle"></i> Agregar Entrada
                    </button>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Registrar Venta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('addEntry').addEventListener('click', function () {
            const entries = document.getElementById('entries');
            const newEntry = entries.firstElementChild.cloneNode(true);

            newEntry.querySelectorAll('input, select').forEach(el => el.value = '');
            entries.appendChild(newEntry);
        });

        document.getElementById('entries').addEventListener('click', function (e) {
            if (e.target.closest('.btn-remove-entry')) {
                const entry = e.target.closest('.entry-group');
                if (document.querySelectorAll('.entry-group').length > 1) {
                    entry.remove();
                } else {
                    alert('Debe haber al menos una entrada.');
                }
            }
        });
    </script>
</body>
</html>


