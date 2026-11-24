
<?php
require_once "db.php";

$error = "";

if (!empty($_SESSION['is_admin'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $password = $_POST['password'] ?? '';

    if (password_verify($password, ADMIN_PASSWORD_HASH)) {
        session_regenerate_id(true);

        $_SESSION['is_admin'] = true;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Incorrect admin password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - My Text Store</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<div class="container">
    <div class="form-card">

        <h1>🔐 Admin Access</h1>
        <p>Enter your secret password.</p>

        <?php if ($error !== ""): ?>
            <p class="error">
                <?php echo htmlspecialchars($error); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="admin.php">

            <input type="hidden" name="csrf_token"
                value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <input type="password" name="password"
                placeholder="Admin Password" required>

            <button type="submit">Login</button>

        </form>

        <a href="index.php">← Back to Website</a>

    </div>
</div>

</body>
</html>
