<?php 
define('HUINAGRO_SYSTEM', true);
date_default_timezone_set('America/Bogota');

require 'database.php';
require 'functions.php';

check_auth();

if ($_SESSION['user_role'] !== 'admin') {
    redirect('dashboard_coordinator.php');
}

$date_filter = $_GET['date_filter'] ?? '';

$sql = "
    SELECT fr.id_record, fr.date, fr.client, fr.lake,
           fe.baskets, fe.gross_weight, fe.price AS price_per_kilo,
           ft.name AS fish_type, wp.description AS weight_presentation,
           u.username
    FROM FishingRecords fr
    JOIN FishingEntries fe ON fr.id_record = fe.id_record
    JOIN FishTypes ft ON fe.id_fish_type = ft.id_fish_type
    JOIN WeightPresentations wp ON fe.id_weight_presentation = wp.id_weight_presentation
    JOIN Users u ON fr.id_user = u.id_user
    WHERE 1=1
";

$params = [];
$types = '';

if (!empty($date_filter)) {
    $sql .= " AND fr.date = ?";
    $params[] = $date_filter;
    $types .= 's';
}

$sql .= " ORDER BY fr.date DESC, fr.id_record DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$grouped = [];
while ($row = $result->fetch_assoc()) {
    $id = $row['id_record'];
    if (!isset($grouped[$id])) {
        $grouped[$id]['info'] = [
            'date' => $row['date'],
            'client' => $row['client'],
            'lake' => $row['lake'],
            'username' => $row['username']
        ];
        $grouped[$id]['entries'] = [];
        $grouped[$id]['totals'] = [
            'baskets' => 0, 
            'gross_weight' => 0,
            'total_value' => 0
        ];
        $grouped[$id]['by_fish_presentation'] = [];
    }

    $subtotal = $row['gross_weight'] * $row['price_per_kilo'];
    $grouped[$id]['entries'][] = [
        'fish_type' => $row['fish_type'],
        'weight_presentation' => $row['weight_presentation'],
        'baskets' => $row['baskets'],
        'gross_weight' => $row['gross_weight'],
        'price_per_kilo' => $row['price_per_kilo'],
        'subtotal' => $subtotal
    ];

    $grouped[$id]['totals']['baskets'] += $row['baskets'];
    $grouped[$id]['totals']['gross_weight'] += $row['gross_weight'];
    $grouped[$id]['totals']['total_value'] += $subtotal;

    $key = $row['fish_type'] . ' - ' . $row['weight_presentation'] . ' - ' . number_format($row['price_per_kilo'], 3);
    if (!isset($grouped[$id]['by_fish_presentation'][$key])) {
        $grouped[$id]['by_fish_presentation'][$key] = [
            'baskets' => 0, 
            'gross_weight' => 0,
            'price_per_kilo' => $row['price_per_kilo'],
            'subtotal' => 0
        ];
    }
    $grouped[$id]['by_fish_presentation'][$key]['baskets'] += $row['baskets'];
    $grouped[$id]['by_fish_presentation'][$key]['gross_weight'] += $row['gross_weight'];
    $grouped[$id]['by_fish_presentation'][$key]['subtotal'] += $subtotal;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Ventas - Administrador</title>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #e8fdf5;
            margin: 0;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #4da6ff, #34d5a3);
            color: #ffffff;
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            box-shadow: 3px 0 8px rgba(0,0,0,0.15);
        }

        .sidebar h3 {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 2rem;
            text-align: center;
            letter-spacing: 1px;
        }

        .sidebar nav {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.75rem 1rem;
            color: #ffffff;
            text-decoration: none;
            font-size: 1.1rem;
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .sidebar a::before {
            content: attr(data-icon);
            font-size: 1.3rem;
            display: inline-block;
        }

        .sidebar a:hover {
            background-color: #34d5a3;
        }

        .sidebar a.active {
            background-color: #ffffff;
            color: #198754;
            font-weight: bold;
        }

        .sidebar .logout-btn {
            margin-top: auto;
            background-color: #198754;
            color: white;
            text-align: center;
            padding: 0.75rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .sidebar .logout-btn:hover {
            background-color: #146c43;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
        }

        h2 {
            margin-bottom: 1rem;
            color: #1b6ca8;
        }

        .filter-form {
            margin-bottom: 1.5rem;
        }

        .filter-form input[type="date"],
        .filter-form button {
            padding: 0.5rem;
            border-radius: 4px;
        }

        .filter-form input {
            border: 1px solid #ccc;
        }

        .filter-form button {
            background: #1b9cfc;
            color: white;
            border: none;
            margin-left: 0.5rem;
            cursor: pointer;
        }

        .record {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            padding: 1rem;
        }

        .record-header {
            background: #2ecc71;
            color: white;
            padding: 0.75rem;
            border-radius: 6px 6px 0 0;
            font-weight: bold;
        }

        .record table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0.5rem;
        }

        .record th, .record td {
            padding: 0.5rem;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        .record th {
            background: #e3fcef;
            color: #117a65;
        }

        .record tfoot td {
            font-weight: bold;
            background: #f0f8ff;
        }

        .no-data {
            background: #d1f7e3;
            padding: 1rem;
            border-radius: 6px;
            color: #117a65;
            border: 1px solid #a7e9c2;
        }

        h4 {
            margin-top: 1rem;
            color: #198754;
        }

        .record-subheader {
            background: #2ecc71;
            color: white;
            padding: 0.75rem;
            border-radius: 6px 6px 0 0;
            font-weight: bold;
            margin-top: 1rem;
        }

        
        .record-buttons {
            margin-top: 1rem;
            display: flex;
            gap: 1rem;
        }

        .record-buttons form button {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
            font-weight: 500;
        }

        .delete-btn {
            background-color: rgb(53, 220, 84);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>👨‍💼 Administrador</h3>
        <nav>
            <a href="dashboard_admin.php" data-icon="🏠">Inicio</a>
            <a href="historial_admin.php" data-icon="📜" class="active">Historial Ventas</a>
        </nav>
        <a href="logout.php" class="logout-btn" data-icon="🚪">Cerrar sesión</a>
    </div>

    <div class="main-content">
        <h2>Historial de Ventas</h2>

        <form method="post" action="generar_reporte_pdf.php" style="margin-bottom: 1rem;">
            <input type="hidden" name="date_filter" value="<?= htmlspecialchars($date_filter) ?>">
            <button type="submit" style="background-color: #28a745; color: white; border: none; padding: 0.6rem 1rem; border-radius: 5px; cursor: pointer; font-weight: bold;">
                📄 Generar Reporte PDF
            </button>
        </form>

        <form method="get" class="filter-form">
            <label>Fecha:
                <input type="date" name="date_filter" value="<?= htmlspecialchars($date_filter) ?>">
            </label>
            <button type="submit">Filtrar</button>
        </form>

        <?php if (empty($grouped)): ?>
            <div class="no-data">No hay registros de ventas para mostrar.</div>
        <?php else: ?>
            <?php foreach ($grouped as $id_record => $data): ?>
                <div class="record">
                    <div class="record-header">
                        Fecha: <?= htmlspecialchars($data['info']['date']) ?> 
                        - Cliente: <?= htmlspecialchars($data['info']['client']) ?>
                        - Lago: <?= htmlspecialchars($data['info']['lake']) ?>
                        - Usuario: <?= htmlspecialchars($data['info']['username']) ?>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Tipo de Pez</th>
                                <th>Presentación de Peso</th>
                                <th>Cestas</th>
                                <th>Peso Bruto (kg)</th>
                                <th>Precio/kg</th>
                                <th>Total a pagar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['entries'] as $entry): ?>
                                <tr>
                                    <td><?= htmlspecialchars($entry['fish_type']) ?></td>
                                    <td><?= htmlspecialchars($entry['weight_presentation']) ?></td>
                                    <td><?= htmlspecialchars($entry['baskets']) ?></td>
                                    <td><?= htmlspecialchars(number_format($entry['gross_weight'], 2)) ?></td>
                                    <td><?= htmlspecialchars(number_format($entry['price_per_kilo'], 3)) ?></td>
                                    <td><?= htmlspecialchars(number_format($entry['subtotal'], 3)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2"><strong>TOTAL</strong></td>
                                <td><strong><?= $data['totals']['baskets'] ?></strong></td>
                                <td><strong><?= number_format($data['totals']['gross_weight'], 2) ?></strong></td>
                                <td></td>
                                <td><strong><?= number_format($data['totals']['total_value'], 3) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>

                    <h4 class="record-subheader">Totales por Tipo de Pez, Presentación y Precio:</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>Tipo de Pez - Presentación</th>
                                <th>Cestas</th>
                                <th>Peso Bruto (kg)</th>
                                <th>Precio/kg</th>
                                <th>Total a pagar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['by_fish_presentation'] as $key => $tot): 
                                list($fish, $presentation, $price) = explode(' - ', $key);
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($fish) ?> - <?= htmlspecialchars($presentation) ?></td>
                                    <td><?= htmlspecialchars($tot['baskets']) ?></td>
                                    <td><?= htmlspecialchars(number_format($tot['gross_weight'], 2)) ?></td>
                                    <td><?= htmlspecialchars(number_format($tot['price_per_kilo'], 3)) ?></td>
                                    <td><?= htmlspecialchars(number_format($tot['subtotal'], 3)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td><strong>TOTAL</strong></td>
                                <td><strong><?= $data['totals']['baskets'] ?></strong></td>
                                <td><strong><?= number_format($data['totals']['gross_weight'], 2) ?></strong></td>
                                <td></td>
                                <td><strong><?= number_format($data['totals']['total_value'], 3) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>

                    
                    <div class="record-buttons">
                        <form action="eliminar_registro.php" method="post" class="delete-form">
                            <input type="hidden" name="id_record" value="<?= htmlspecialchars($id_record) ?>">
                            <button type="submit" class="delete-btn">Eliminar</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault(); 

                Swal.fire({
                    title: '🗑️ ¿Eliminar este registro?',
                    text: '¡Cuidado! Esta acción no se puede deshacer. ¿Estás seguro?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#007bff',
                    cancelButtonColor: '#2ecc71',
                    confirmButtonText: '¡Sí, eliminar!',
                    cancelButtonText: 'No, cancelar',
                    background: '#fff',
                    backdrop: `
                        rgba(0,0,123,0.4)
                        url("https://www.pngall.com/wp-content/uploads/2/Fish-PNG-Image.png")
                        center center
                        no-repeat
                    `,
                    customClass: {
                        popup: 'animated shake',
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-success'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit(); 
                    }
                });
            });
        });
    </script>
</body>
</html>