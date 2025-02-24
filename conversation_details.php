<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';

$conversation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($conversation_id <= 0) {
    die("Invalid conversation ID.");
}

try {
    $stmt = $pdo->prepare("SELECT sender, content, timestamp FROM messages WHERE conversation_id = :id ORDER BY timestamp ASC");
    $stmt->execute(['id' => $conversation_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching conversation: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversation Details</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .message {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 8px;
        }

        .user {
            background-color: #e0f7fa;
        }

        .ai {
            background-color: #f1f8e9;
        }

        .timestamp {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
        }

        .back-button {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 15px;
            background-color: #1976d2;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .back-button:hover {
            background-color: #1565c0;
        }
    </style>
</head>

<body>
    <h1>Conversation ID: <?= htmlspecialchars($conversation_id) ?></h1>
    <a href="history.php" class="back-button">&larr; Back to History</a>

    <?php if (count($messages) > 0): ?>
        <?php foreach ($messages as $msg): ?>
            <div class="message <?= $msg['sender'] === 'user' ? 'user' : 'ai' ?>">
                <strong><?= ucfirst(htmlspecialchars($msg['sender'])) ?>:</strong> <?= htmlspecialchars($msg['content']) ?><br>
                <div class="timestamp">Sent: <?= date('Y-m-d H:i:s', strtotime($msg['timestamp'])) ?></div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No messages found for this conversation.</p>
    <?php endif; ?>
</body>

</html>