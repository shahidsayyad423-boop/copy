
<?php
require_once "db.php";
admin_required();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - My Text Store</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<div class="container">
    <div class="form-card">
        <h1>📚 Admin Dashboard</h1>
        <p>Welcome to My Text Store Admin Panel!</p>

        <p>
            <a href="add.php">➕ Add New Document</a>
        </p>

        <p>
            <a href="index.php">👁 View Document Library</a>
        </p>

        <p>
            <a href="logout.php">🚪 Logout</a>
        </p>
    </div>
</div>

</body>
</html>
