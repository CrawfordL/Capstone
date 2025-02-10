<?php
// index.php - Main Chatbot Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Welcome to the Chatbot</h1>
    <div id="chat-container">
        <div id="chat-box"></div>
        <input type="text" id="user-input" placeholder="Type a message...">
        <button onclick="sendMessage()">Send</button>
    </div>
    <footer>
        <button onclick="window.location.href='history.php'">View Conversation History</button>
    </footer>
    <script src="script.js"></script>
</body>
</html>
