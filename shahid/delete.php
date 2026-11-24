
<?php
require "db.php";
admin_required();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method not allowed.");
}

csrf_check();

$id = (int)($_POST['id'] ?? 0);

$stmt = $conn->prepare(
    "SELECT stored_name FROM documents WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();

$doc = $stmt->get_result()->fetch_assoc();

if ($doc) {
    $delete = $conn->prepare("DELETE FROM documents WHERE id = ?");
    $delete->bind_param("i", $id);
    $delete->execute();

    if ($delete->affected_rows > 0 && !empty($doc['stored_name'])) {
        $path = __DIR__ . "/uploads/" . basename($doc['stored_name']);

        if (is_file($path)) {
            unlink($path);
        }
    }
}

header("Location: index.php");
exit;
?>
