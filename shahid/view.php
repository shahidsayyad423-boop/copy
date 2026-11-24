
<?php
require_once "db.php";

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM documents WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$doc = $stmt->get_result()->fetch_assoc();

if (!$doc) {
    http_response_code(404);
    exit("Document not found.");
}

$codeExtensions = [
    'PY', 'PYW', 'PHP', 'PHTML',
    'JAVA', 'C', 'H', 'CPP', 'HPP', 'CC',
    'CS', 'JS', 'MJS', 'HTML', 'HTM', 'CSS',
    'SQL', 'XML', 'JSON', 'MD', 'SH', 'BAT',
    'PS1', 'RB', 'GO', 'RS', 'SWIFT', 'KT',
    'DART', 'TS', 'TSX', 'JSX', 'VUE',
    'YML', 'YAML', 'TOML', 'INI', 'CFG',
    'R', 'PL', 'LUA', 'ASM'
];

$type = strtoupper($doc['file_type'] ?? '');

$isCode = in_array($type, $codeExtensions, true);

$fileContent = null;

if ($doc['stored_name'] && $isCode) {
    $filePath = __DIR__ . "/uploads/" . basename($doc['stored_name']);

    if (is_file($filePath)) {
        $fileContent = file_get_contents($filePath);
    }
} elseif ($doc['content'] !== null) {
    $fileContent = $doc['content'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View - My Text Store</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        .code-box {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: 10px;
            overflow: auto;
            white-space: pre;
            font-family: Consolas, monospace;
            font-size: 14px;
            line-height: 1.6;
            max-height: 650px;
            text-align: left;
        }

        .code-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .code-actions button,
        .code-actions a {
            padding: 10px 16px;
            cursor: pointer;
        }

        .document-preview {
            width: 100%;
            height: 650px;
            border: 0;
        }
    </style>
</head>

<body>

<div class="container">
    <div class="form-card">

        <h1><?php echo htmlspecialchars($doc['title']); ?></h1>

        <p>
            <?php echo nl2br(htmlspecialchars($doc['description'] ?? '')); ?>
        </p>

        <?php if ($type === 'PDF' && $doc['stored_name']): ?>

            <iframe
                class="document-preview"
                src="download.php?id=<?php echo $id; ?>&inline=1">
            </iframe>

        <?php elseif ($isCode && $fileContent !== null): ?>

            <h3>💻 Code Preview (<?php echo htmlspecialchars($type); ?>)</h3>

            <pre class="code-box" id="codeText"><?php
                echo htmlspecialchars($fileContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            ?></pre>

            <div class="code-actions">
                <button type="button" onclick="copyCode()">📋 Copy Code</button>

                <a class="btn" href="download.php?id=<?php echo $id; ?>">
                    ⬇ Download Code
                </a>
            </div>

        <?php elseif ($doc['content'] !== null): ?>

            <h3>📝 Text Preview</h3>

            <textarea id="copyText" readonly><?php
                echo htmlspecialchars($doc['content']);
            ?></textarea>

            <button type="button" onclick="copyText()">📋 Copy Text</button>

        <?php elseif ($doc['stored_name']): ?>

            <p>This document type cannot be previewed in the browser.</p>

        <?php else: ?>

            <p>Document content is not available.</p>

        <?php endif; ?>

        <?php if ($doc['stored_name']): ?>
            <br><br>
            <a class="btn" href="download.php?id=<?php echo $id; ?>">
                ⬇ Download Document
            </a>
        <?php endif; ?>

        <br><br>
        <a href="index.php">← Back to Documents</a>

    </div>
</div>

<script>
async function copyCode() {
    const code = document.getElementById("codeText").innerText;

    try {
        await navigator.clipboard.writeText(code);
        alert("Code copied successfully!");
    } catch (error) {
        alert("Copy failed. Please select the code and press Ctrl+C.");
    }
}

async function copyText() {
    const text = document.getElementById("copyText").value;

    try {
        await navigator.clipboard.writeText(text);
        alert("Text copied successfully!");
    } catch (error) {
        alert("Copy failed. Please copy manually.");
    }
}
</script>

</body>
</html>
