
<?php
require_once "db.php";
admin_required();

$message = "";

$allowed = [
    'pdf', 'doc', 'docx', 'txt',
    'py', 'pyw', 'php', 'phtml',
    'java', 'c', 'h', 'cpp', 'hpp', 'cc',
    'cs', 'js', 'mjs', 'html', 'htm', 'css',
    'sql', 'xml', 'json', 'md', 'sh', 'bat',
    'ps1', 'rb', 'go', 'rs', 'swift', 'kt',
    'dart', 'ts', 'tsx', 'jsx', 'vue',
    'yml', 'yaml', 'toml', 'ini', 'cfg',
    'r', 'ipynb', 'pl', 'lua', 'asm'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $content = trim($_POST['content'] ?? '');

    $original = null;
    $stored = null;
    $type = "TEXT";
    $saved_content = null;

    if ($title === "") {
        $message = "Enter document title.";

    } elseif (!empty($_FILES['document']['name']) && $content !== "") {
        $message = "Add text or upload a file, not both.";

    } elseif (empty($_FILES['document']['name']) && $content === "") {
        $message = "Enter text or select a document.";

    } else {

        if (!empty($_FILES['document']['name'])) {
            $file = $_FILES['document'];

            $ext = strtolower(
                pathinfo($file['name'], PATHINFO_EXTENSION)
            );

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $message = "File upload error.";

            } elseif ($file['size'] > 10 * 1024 * 1024) {
                $message = "Maximum file size is 10 MB.";

            } elseif (!in_array($ext, $allowed, true)) {
                $message = "Invalid file extension.";

            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($file['tmp_name']);

                // Text/code files are stored as downloads, never executed.
                $isTextCode = !in_array(
                    $ext,
                    ['pdf', 'doc', 'docx'],
                    true
                );

                $validMime = false;

                if ($ext === 'pdf') {
                    $validMime = ($mime === 'application/pdf');

                } elseif ($ext === 'doc') {
                    $validMime = in_array($mime, [
                        'application/msword',
                        'application/x-ole-storage',
                        'application/CDFV2'
                    ], true);

                } elseif ($ext === 'docx') {
                    $validMime = in_array($mime, [
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/zip',
                        'application/x-zip'
                    ], true);

                } elseif ($isTextCode) {
                    $validMime = in_array($mime, [
                        'text/plain',
                        'text/x-script',
                        'application/json',
                        'application/xml',
                        'text/xml',
                        'application/javascript',
                        'text/javascript',
                        'text/html',
                        'text/css',
                        'application/x-empty',
                        'inode/x-empty'
                    ], true);
                }

                if (!$validMime) {
                    $message = "File type does not match or is not supported.";

                } else {
                    $original = basename($file['name']);
                    $stored = bin2hex(random_bytes(16)) . "." . $ext;
                    $type = strtoupper($ext);

                    $uploadDir = __DIR__ . "/uploads/";

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    if (!move_uploaded_file(
                        $file['tmp_name'],
                        $uploadDir . $stored
                    )) {
                        $message = "Upload failed.";
                        $stored = null;
                    }
                }
            }

        } else {
            $saved_content = $content;
        }

        if ($message === "") {
            $stmt = $conn->prepare(
                "INSERT INTO documents
                (title, description, original_name, stored_name, file_type, content)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssssss",
                $title,
                $description,
                $original,
                $stored,
                $type,
                $saved_content
            );

            if ($stmt->execute()) {
                header("Location: index.php");
                exit;
            }

            if ($stored) {
                unlink(__DIR__ . "/uploads/" . $stored);
            }

            $message = "Unable to save document.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Document - My Text Store</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<div class="container">
    <div class="form-card">

        <h1>➕ Add New Document</h1>

        <?php if ($message !== ""): ?>
            <p class="error">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <input type="hidden" name="csrf_token"
                value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <input type="text" name="title"
                placeholder="Document Title" required>

            <textarea name="description"
                placeholder="Description"></textarea>

            <h3>Write or paste text/code</h3>

            <textarea name="content"
                placeholder="Write your notes or code here..."></textarea>

            <h3>OR Upload a document/code file</h3>

            <input type="file" name="document"
                accept=".pdf,.doc,.docx,.txt,.py,.pyw,.php,.phtml,.java,.c,.h,.cpp,.hpp,.cc,.cs,.js,.mjs,.html,.htm,.css,.sql,.xml,.json,.md,.sh,.bat,.ps1,.rb,.go,.rs,.swift,.kt,.dart,.ts,.tsx,.jsx,.vue,.yml,.yaml,.toml,.ini,.cfg,.r,.ipynb,.pl,.lua,.asm">

            <p>Maximum file size: 10 MB</p>

            <button type="submit">Save Document</button>

        </form>

        <a href="dashboard.php">← Back to Dashboard</a>

    </div>
</div>

</body>
</html>
