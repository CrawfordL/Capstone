<?php
$databaseFile = "chat_history.db";
try {
    $pdo = new PDO("sqlite:$databaseFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to database successfully!";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>