<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversation History</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .conversation { border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; border-radius: 8px; }
        .message { margin: 5px 0; }
        .user { font-weight: bold; color: #1976d2; }
        .ai { font-weight: bold; color: #388e3c; }
    </style>
</head>
<body>
    <h1>Past Conversations</h1>
    <div id="conversationList">Loading conversations...</div>

    <script>
        fetch("http://localhost:5000/history")
            .then(response => response.json())
            .then(data => {
                const conversationList = document.getElementById("conversationList");
                if (data.history && data.history.length > 0) {
                    conversationList.innerHTML = "";

                    data.history.forEach((conversation, index) => {
                        const conversationDiv = document.createElement("div");
                        conversationDiv.className = "conversation";

                        conversation.forEach(message => {
                            const messageDiv = document.createElement("div");
                            messageDiv.className = "message";
                            if (message.startsWith("You:")) {
                                messageDiv.innerHTML = `<span class="user">${message}</span>`;
                            } else if (message.startsWith("AI:")) {
                                messageDiv.innerHTML = `<span class="ai">${message}</span>`;
                            } else {
                                messageDiv.textContent = message;
                            }
                            conversationDiv.appendChild(messageDiv);
                        });

                        conversationList.appendChild(conversationDiv);
                    });
                } else {
                    conversationList.textContent = "No conversations found.";
                }
            })
            .catch(error => {
                console.error("Error fetching conversation history:", error);
                document.getElementById("conversationList").textContent = "Failed to load conversations.";
            });
    </script>
</body>
</html>