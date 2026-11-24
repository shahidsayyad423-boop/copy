
<?php
$conn = new mysqli(
    "localhost",
    "root",
    "",
    "my_text_store"
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

if (session_status() === PHP_SESSION_NONE) {
    ini_set("session.use_strict_mode", "1");

    session_set_cookie_params([
        "httponly" => true,
        "secure" => !empty($_SERVER['HTTPS']),
        "samesite" => "Lax"
    ]);

    session_start();
}

define('ADMIN_PASSWORD_HASH', '$2y$12$BIBExl.Yk4xE9Mhz2XG7y.ssft3y6jMoj4j4oa1Pti2oFd9xfFTDW');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_check() {
    if (
        $_SERVER['REQUEST_METHOD'] !== 'POST' ||
        !hash_equals(
            $_SESSION['csrf_token'] ?? '',
            $_POST['csrf_token'] ?? ''
        )
    ) {
        http_response_code(403);
        exit("Invalid request. Please go back and try again.");
    }
}

function admin_required() {
    if (empty($_SESSION['is_admin'])) {
        header("Location: admin.php");
        exit;
    }
}
?>
