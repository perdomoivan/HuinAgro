<?php
define('HUINAGRO_SYSTEM', true);
require 'database.php';
require 'functions.php';

if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: $url");
        exit;
    }
}

check_auth();

if (!isset($_GET['id_record']) || !is_numeric($_GET['id_record'])) {
    exit('ID inválido');
}

$id_record = (int) $_GET['id_record'];
$user_id = $_SESSION['user_id'];


$sql = "SELECT * FROM FishingRecords WHERE id_record = ? AND id_user = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $id_record, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    exit('Registro no encontrado o no autorizado');
}

$record = $result->fetch_assoc();


$sql = "SELECT * FROM FishingEntries WHERE id_record = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_record);
$stmt->execute();
$entries = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);


$fishTypes = $conn->query("SELECT id_fish_type, name FROM FishTypes")->fetch_all(MYSQLI_ASSOC);
$presentations = $conn->query("SELECT id_weight_presentation, description FROM WeightPresentations")->fetch_all(MYSQLI_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client = $_POST['client'] ?? '';
    $lake = $_POST['lake'] ?? '';
    $date = $_POST['date'] ?? '';

    
    $stmt = $conn->prepare("UPDATE FishingRecords SET client = ?, lake = ?, date = ? WHERE id_record = ? AND id_user = ?");
    $stmt->bind_param('sssii', $client, $lake, $date, $id_record, $user_id);
    $stmt->execute();

    
    $stmt = $conn->prepare("DELETE FROM FishingEntries WHERE id_record = ?");
    $stmt->bind_param('i', $id_record);
    $stmt->execute();

    
    if (isset($_POST['entries']) && is_array($_POST['entries'])) {
        foreach ($_POST['entries'] as $entry) {
            $id_fish_type = (int) $entry['id_fish_type'];
            $id_weight_presentation = (int) $entry['id_weight_presentation'];
            $baskets = (int) $entry['baskets'];
            $gross_weight = isset($entry['gross_weight']) ? (float) $entry['gross_weight'] : 0;
            $price = isset($entry['price']) ? (float) $entry['price'] : 0;

            if ($baskets > 0) {
                $stmt = $conn->prepare("INSERT INTO FishingEntries (id_record, id_fish_type, id_weight_presentation, baskets, gross_weight, price) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('iiiidd', $id_record, $id_fish_type, $id_weight_presentation, $baskets, $gross_weight, $price);
                $stmt->execute();
            }
        }
    }

    redirect('user_history.php');
}
?>

<!DOCTYPE html>
<html lang="es"> 
<head>
    <meta charset="UTF-8">
    <title>Editar Registro de Venta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4>Editar Registro de Venta</h4>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <label for="client" class="form-label">Cliente</label>
                    <input type="text" class="form-control" id="client" name="client" value="<?= htmlspecialchars($record['client']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="lake" class="form-label">Lago</label>
                    <input type="text" class="form-control" id="lake" name="lake" value="<?= htmlspecialchars($record['lake']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="date" class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="date" name="date" value="<?= htmlspecialchars($record['date']) ?>" required>
                </div>

                <h5 class="mt-4">Detalles del Registro</h5>
                <div id="entries-container">
                    <?php foreach ($entries as $i => $entry): ?>
                        <div class="border rounded p-3 mb-3">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <label class="form-label">Tipo de Pescado</label>
                                    <select name="entries[<?= $i ?>][id_fish_type]" class="form-select" required>
                                        <?php foreach ($fishTypes as $type): ?>
                                            <option value="<?= $type['id_fish_type'] ?>" <?= $type['id_fish_type'] == $entry['id_fish_type'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($type['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Presentación</label>
                                    <select name="entries[<?= $i ?>][id_weight_presentation]" class="form-select" required>
                                        <?php foreach ($presentations as $presentation): ?>
                                            <option value="<?= $presentation['id_weight_presentation'] ?>" <?= $presentation['id_weight_presentation'] == $entry['id_weight_presentation'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($presentation['description']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Canastas</label>
                                    <input type="number" name="entries[<?= $i ?>][baskets]" class="form-control" value="<?= $entry['baskets'] ?>" min="1" required>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Peso Bruto (kg)</label>
                                    <input type="number" step="0.1" name="entries[<?= $i ?>][gross_weight]" class="form-control" value="<?= number_format($entry['gross_weight'], 1, '.', '') ?>" min="0" required>
                                </div>


                                <div class="col-md-2">
                                    <label class="form-label">Precio por Kilo ($)</label>
                                    <input type="number" step="0.001" name="entries[<?= $i ?>][price]" class="form-control" value="<?= number_format($entry['price'], 3, '.', '') ?>" min="0" required>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Guardar Cambios</button>
                    <a href="user_history.php" class="btn btn-primary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
