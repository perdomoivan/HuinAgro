<?php
$host = '127.0.0.1';
$dbname = 'huinagro';
$username = 'root';
$password = '';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8");
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}


if ($conn) {
    $stmt = $conn->prepare("SELECT id_user FROM Users WHERE username = ?");
    $stmt->bind_param("s", $adminUsername);
    $adminUsername = 'admin';
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $hashedPassword = password_hash('huinagro2025admin', PASSWORD_DEFAULT); 
        $stmtInsert = $conn->prepare("INSERT INTO Users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $username = 'admin';
        $email = 'admin@huinagro.com';
        $password = $hashedPassword;
        $role = 'admin';
        $stmtInsert->bind_param("ssss", $username, $email, $password, $role);
        $stmtInsert->execute();
        $stmtInsert->close();
    }

    $stmt->close();
}
?>
