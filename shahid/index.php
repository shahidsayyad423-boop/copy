
<?php
require_once "db.php";

$isAdmin = !empty($_SESSION['is_admin']);

$result = $conn->query(
    "SELECT id, title, description, file_type, created_at
     FROM documents ORDER BY id DESC"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Text Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<header class="navbar">
    <h2>📚 My Text Store</h2>

    <?php if ($isAdmin): ?>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="admin.php">Admin</a>
    <?php endif; ?>
</header>

<main class="container">

    <section class="hero">
        <h1>My Document Library</h1>
        <p>Read, explore and download documents anytime.</p>

        <input type="search" id="search"
               placeholder="Search documents...">
    </section>

    <h2>All Documents</h2>

    <div class="document-grid" id="documentList">

        <?php if ($result && $result->num_rows > 0): ?>

            <?php while ($doc = $result->fetch_assoc()): ?>

                <article class="document-card">

                    <div class="file-icon">📄</div>

                    <h3>
                        <?php echo htmlspecialchars($doc['title']); ?>
                    </h3>

                    <p>
                        <?php echo htmlspecialchars($doc['description'] ?? ''); ?>
                    </p>

                    <small>
                        <?php echo htmlspecialchars($doc['file_type'] ?? 'TEXT'); ?>
                        |
                        <?php echo htmlspecialchars($doc['created_at']); ?>
                    </small>

                    <div class="actions">

                        <a class="btn"
                           href="view.php?id=<?php echo (int)$doc['id']; ?>">
                            View
                        </a>

                        <a class="btn secondary"
                           href="download.php?id=<?php echo (int)$doc['id']; ?>">
                            Download
                        </a>

                        <?php if ($isAdmin): ?>

                            <a class="btn"
                               href="edit.php?id=<?php echo (int)$doc['id']; ?>">
                                ✏️ Edit
                            </a>

                            <form method="POST" action="delete.php"
                                  onsubmit="return confirm('Are you sure you want to delete this document?');">

                                <input type="hidden" name="csrf_token"
                                    value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                                <input type="hidden" name="id"
                                    value="<?php echo (int)$doc['id']; ?>">

                                <button type="submit" class="btn secondary">
                                    🗑 Delete
                                </button>

                            </form>

                        <?php endif; ?>

                    </div>

                </article>

            <?php endwhile; ?>

        <?php else: ?>

            <p>No documents available yet.</p>

        <?php endif; ?>

    </div>

</main>

<script>
const search = document.getElementById("search");

search.addEventListener("input", function () {
    const value = this.value.toLowerCase();

    document.querySelectorAll(".document-card").forEach(card => {
        card.style.display =
            card.innerText.toLowerCase().includes(value) ? "" : "none";
    });
});
</script>

<footer>
    © <?php echo date("Y"); ?> My Text Store
</footer>

</body>
</html>
