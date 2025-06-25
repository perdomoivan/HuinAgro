<?php

function ensure_session_started(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}


function redirect(string $url): void {
    header("Location: " . filter_var($url, FILTER_SANITIZE_URL));
    exit;
}


function check_auth(): void {
    ensure_session_started();
    
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Debes iniciar sesión primero";
        redirect('login.php');
    }
}


function check_admin(): void {
    check_auth();

    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        $_SESSION['error'] = "Acceso restringido a administradores";
        redirect('dashboard_user.php');
    }
}


function clean_input($data): string {
    if (!isset($data)) return '';

    $data = trim($data);
    $data = stripslashes($data);
    $data = strip_tags($data); 
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}


function display_session_error(): void {
    ensure_session_started();

    if (!empty($_SESSION['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        echo $_SESSION['error'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['error']);
    }
}


function display_session_success(): void {
    ensure_session_started();

    if (!empty($_SESSION['success'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo $_SESSION['success'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['success']);
    }
}


function generate_csrf_token(): string {
    ensure_session_started();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}


 
function verify_csrf_token(string $token): bool {
    ensure_session_started();

    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}


function user_exists(string $username): bool {
    global $conn;

    $stmt = $conn->prepare("SELECT id_user FROM Users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0;
}


function verify_credentials(string $username, string $password): ?array {
    global $conn;

    $stmt = $conn->prepare("SELECT id_user, username, password, role FROM Users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            return $user;
        } else {
            error_log("Contraseña incorrecta para el usuario $username");
        }
    } else {
        error_log("Usuario no encontrado: $username");
    }

    return null;
}


 
function logout(): void {
    ensure_session_started();
    session_unset();
    session_destroy();
    redirect('login.php');
}


function require_role(string $role): void {
    ensure_session_started();

    
    if (!isset($_SESSION['user_role'])) {
        $_SESSION['error'] = "Debes iniciar sesión primero.";
        redirect('login.php');
    }

    
    if ($_SESSION['user_role'] !== $role) {
        $_SESSION['error'] = "Acceso restringido a usuarios con rol $role.";

         
        if ($_SESSION['user_role'] === 'admin') {
            redirect('dashboard_admin.php');
        } else {
            redirect('dashboard_user.php');
        }
    }
}
