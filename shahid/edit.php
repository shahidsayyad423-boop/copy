
<?php
require_once "db.php";
admin_required();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM documents WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$doc = $stmt->get_result()->fetch_assoc();

if (!$doc) {
    http_response_code(404);
    exit("Document not found.");
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '') {
        $message = "Document title is required.";
    } else {
        if ($doc['content'] !== null) {
            $content = $_POST['content'] ?? '';

            $update = $conn->prepare(
                "UPDATE documents
                 SET title = ?, description = ?, content = ?
                 WHERE id = ?"
            );

            $update->bind_param(
                "sssi",
                $title, $description, $content, $id
            );
        } else {
            $update = $conn->prepare(
                "UPDATE documents
                 SET title = ?, description = ?
                 WHERE id = ?"
            );

            $update->bind_param(
                "ssi",
                $title, $description, $id
            );
        }

        if ($update->execute()) {
            header("Location: index.php");
            exit;
        } else {
            $message = "Unable to update document.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Document</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container">
    <div class="form-card">

        <h1>✏️ Edit Document</h1>

        <?php if ($message !== ''): ?>
            <p class="error">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <form method="POST">

            <input type="hidden" name="id"
                   value="<?php echo $id; ?>">

            <input type="hidden" name="csrf_token"
                   value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <label>Document Title</label>

            <input type="text" name="title"
                   value="<?php echo htmlspecialchars($doc['title']); ?>"
                   required>

            <label>Description</label>

            <textarea name="description"><?php
                echo htmlspecialchars($doc['description'] ?? '');
            ?></textarea>

            <?php if ($doc['content'] !== null): ?>

                <label>Document Content</label>

                <textarea name="content" rows="12"><?php
                    echo htmlspecialchars($doc['content']);
                ?></textarea>

            <?php else: ?>

                <p>Uploaded file: 
                    <?php echo htmlspecialchars($doc['original_name'] ?? ''); ?>
                </p>
                <p>File replacement is not available in this edit form.</p>

            <?php endif; ?>

            <button type="submit">Update Document</button>

        </form>

        <br>
        <a href="index.php">← Back to Documents</a>

    </div>
</div>

</body>
</html>
