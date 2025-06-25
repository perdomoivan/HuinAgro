<?php
ob_start();
define('HUINAGRO_SYSTEM', true);
date_default_timezone_set('America/Bogota');

require 'database.php';
require 'functions.php';
require 'vendor/autoload.php';

check_auth();

if (!in_array($_SESSION['user_role'], ['coordinator', 'admin'])) {
    redirect('dashboard.php');
}

$date_filter = isset($_POST['date_filter']) ? trim($_POST['date_filter']) : '';

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

if ($_SESSION['user_role'] === 'coordinator') {
    $sql .= " AND fr.id_user = ?";
    $params[] = $_SESSION['user_id'];
    $types .= 'i';
}

if (!empty($date_filter)) {
    $sql .= " AND fr.date = ?";
    $params[] = $date_filter;
    $types .= 's';
}

$sql .= " ORDER BY fr.date DESC, fr.id_record ASC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$records = [];
while ($row = $result->fetch_assoc()) {
    $records[$row['id_record']]['date'] = $row['date'];
    $records[$row['id_record']]['client'] = $row['client'];
    $records[$row['id_record']]['lake'] = $row['lake'];
    $records[$row['id_record']]['details'][] = $row;
}


$user_id = $_SESSION['user_id'];
$report_date = !empty($date_filter) ? $date_filter : date('Y-m-d');
$report_type = 'PDF';
$stmt = $conn->prepare("INSERT INTO Reports (id_user, id_record, date, report_type) VALUES (?, NULL, ?, ?)");
$stmt->bind_param("iss", $user_id, $report_date, $report_type);
$stmt->execute();
$stmt->close();


$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('HuinAgro');
$pdf->SetAuthor('HuinAgro System');
$pdf->SetTitle('Historial de Ventas');
$pdf->SetSubject('Historial de Ventas');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->SetFont('helvetica', '', 10);
$pdf->AddPage();

$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Historial de Ventas', 0, 1, 'C');

if (empty($records)) {
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'No hay registros de ventas para mostrar.', 0, 1, 'C');
} else {
    foreach ($records as $recordId => $record) {

        if ($pdf->GetY() > 230) {
            $pdf->AddPage();
        }

        
        $username = $record['details'][0]['username'];

        
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(46, 204, 113);
        $header = 'Fecha: ' . $record['date'] . ' | Cliente: ' . ($record['client'] ?? 'Sin cliente') . ' | Lago: ' . ($record['lake'] ?? 'Sin lago') . ' | Registrado por: ' . $username;
        $pdf->Cell(0, 10, $header, 1, 1, 'L', true);

        
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(227, 252, 239);
        $pdf->Cell(40, 7, 'Tipo de Pescado', 1, 0, 'C', true);
        $pdf->Cell(40, 7, 'Presentación', 1, 0, 'C', true);
        $pdf->Cell(20, 7, 'Canastas', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'Peso (kg)', 1, 0, 'C', true);
        $pdf->Cell(30, 7, '$/kg', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'Total a pagar', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 10);
        $totalRecordBaskets = 0;
        $totalRecordWeight = 0;
        $totalRecordPrice = 0;
        foreach ($record['details'] as $row) {
            $subtotal = $row['gross_weight'] * $row['price_per_kilo'];
            $pdf->Cell(40, 6, $row['fish_type'], 1, 0, 'L');
            $pdf->Cell(40, 6, $row['weight_presentation'], 1, 0, 'L');
            $pdf->Cell(20, 6, $row['baskets'], 1, 0, 'C');
            $pdf->Cell(30, 6, number_format($row['gross_weight'], 1), 1, 0, 'C');
            $pdf->Cell(30, 6, number_format($row['price_per_kilo'], 3), 1, 0, 'C');
            $pdf->Cell(30, 6, number_format($subtotal, 3), 1, 1, 'C');
            $totalRecordBaskets += $row['baskets'];
            $totalRecordWeight += $row['gross_weight'];
            $totalRecordPrice += $subtotal;
        }

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(240, 248, 255);
        $pdf->Cell(80, 7, 'Totales', 1, 0, 'R', true);
        $pdf->Cell(20, 7, $totalRecordBaskets, 1, 0, 'C', true);
        $pdf->Cell(30, 7, number_format($totalRecordWeight, 1), 1, 0, 'C', true);
        $pdf->Cell(30, 7, '', 1, 0, 'C', true);
        $pdf->Cell(30, 7, number_format($totalRecordPrice, 3), 1, 1, 'C');

        
        $summary = [];
        $summaryOrder = [];
        foreach ($record['details'] as $row) {
            $key = $row['fish_type'] . ' - ' . $row['weight_presentation'] . ' - ' . number_format($row['price_per_kilo'], 3);
            if (!isset($summary[$key])) {
                $summary[$key] = ['baskets' => 0, 'weight' => 0, 'price_per_kilo' => $row['price_per_kilo'], 'subtotal' => 0];
                $summaryOrder[] = $key;
            }
            $summary[$key]['baskets'] += $row['baskets'];
            $summary[$key]['weight'] += $row['gross_weight'];
            $summary[$key]['subtotal'] += $row['gross_weight'] * $row['price_per_kilo'];
        }

        $grandTotal = 0;
        $grandBaskets = 0;
        $grandWeight = 0;
        foreach ($summaryOrder as $key) {
            $data = $summary[$key];
            $grandTotal += $data['subtotal'];
            $grandBaskets += $data['baskets'];
            $grandWeight += $data['weight'];
        }

        $startY = $pdf->GetY();
        $pdf->startTransaction();
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(46, 204, 113);
        $pdf->Cell(0, 10, 'Tipo de pez, presentación y precio', 1, 1, 'L', true);

        $requiredHeight = (count($summaryOrder) + 2) * 6 + 7;
        $endY = $pdf->GetY();

        if ($endY + $requiredHeight > ($pdf->getPageHeight() - 10)) {
            $pdf->rollbackTransaction(true);
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetFillColor(46, 204, 113);
            $pdf->Cell(0, 10, 'Tipo de pez, presentación y precio', 1, 1, 'L', true);
        } else {
            $pdf->commitTransaction();
        }

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(227, 252, 239);
        $pdf->Cell(40, 7, 'Tipo de Pescado', 1, 0, 'C', true);
        $pdf->Cell(40, 7, 'Presentación', 1, 0, 'C', true);
        $pdf->Cell(20, 7, 'Canastas', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'Peso (kg)', 1, 0, 'C', true);
        $pdf->Cell(30, 7, '$/kg', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'Total a pagar', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 10);
        foreach ($summaryOrder as $key) {
            list($fish, $presentation, $price) = explode(' - ', $key);
            $data = $summary[$key];
            $pdf->Cell(40, 6, $fish, 1, 0, 'L');
            $pdf->Cell(40, 6, $presentation, 1, 0, 'L');
            $pdf->Cell(20, 6, $data['baskets'], 1, 0, 'C');
            $pdf->Cell(30, 6, number_format($data['weight'], 1), 1, 0, 'C');
            $pdf->Cell(30, 6, number_format((float)$price, 3), 1, 0, 'C');
            $pdf->Cell(30, 6, number_format($data['subtotal'], 3), 1, 1, 'C');
        }

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(240, 248, 255);
        $pdf->Cell(80, 7, 'Totales', 1, 0, 'R', true);
        $pdf->Cell(20, 7, $grandBaskets, 1, 0, 'C', true);
        $pdf->Cell(30, 7, number_format($grandWeight, 1), 1, 0, 'C', true);
        $pdf->Cell(30, 7, '', 1, 0, 'C', true);
        $pdf->Cell(30, 7, number_format($grandTotal, 3), 1, 1, 'C');

        $pdf->Ln(10);
    }
}

ob_clean();
$filename_date = $date_filter ? str_replace(['/', '\\', ':'], '-', $date_filter) : 'Todos';
$filename = 'Reporte_Ventas_' . $filename_date . '.pdf';
$pdf->Output($filename, 'D');
?>
