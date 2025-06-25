<?php
define('HUINAGRO_SYSTEM', true);
session_start();

require 'database.php';
require 'functions.php';

check_auth();

$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;
$id_record = $_POST['id_record'] ?? null;


if (!$user_id) {
    die('⚠️ Acceso no autorizado: sesión no iniciada.');
}

if (!$id_record || !is_numeric($id_record)) {
    die('⚠️ ID inválido.');
}


if (!in_array($user_role, ['admin', 'coordinator'])) {
    die('🚫 No tienes permisos para eliminar registros.');
}


if ($user_role === 'coordinator') {
    $sqlCheck = "SELECT id_record FROM FishingRecords WHERE id_record = ? AND id_user = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    if (!$stmtCheck) {
        die('Error en la preparación de la consulta de verificación.');
    }
    $stmtCheck->bind_param("ii", $id_record, $user_id);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows === 0) {
        die('🚫 No tienes permiso para eliminar este registro o no existe.');
    }
}


$conn->begin_transaction();

try {
    
    $sqlDeleteEntries = "DELETE FROM FishingEntries WHERE id_record = ?";
    $stmtEntries = $conn->prepare($sqlDeleteEntries);
    if (!$stmtEntries) {
        throw new Exception('Error al preparar la consulta de eliminación de entradas.');
    }
    $stmtEntries->bind_param("i", $id_record);
    $stmtEntries->execute();

    
    $sqlDeleteRecord = "DELETE FROM FishingRecords WHERE id_record = ?";
    $stmtRecord = $conn->prepare($sqlDeleteRecord);
    if (!$stmtRecord) {
        throw new Exception('Error al preparar la consulta de eliminación del registro.');
    }
    $stmtRecord->bind_param("i", $id_record);
    $stmtRecord->execute();

    
    $conn->commit();

    
    $redirect_page = ($user_role === 'admin') ? 'historial_admin.php' : 'user_history.php';
    header("Location: $redirect_page?msg=Registro+eliminado+correctamente");
    exit;

} catch (Exception $e) {
    
    $conn->rollback();
    die("Error al eliminar: " . $e->getMessage());
} finally {
    
    if (isset($stmtCheck)) $stmtCheck->close();
    if (isset($stmtEntries)) $stmtEntries->close();
    if (isset($stmtRecord)) $stmtRecord->close();
    if ($conn) $conn->close();
}
?>