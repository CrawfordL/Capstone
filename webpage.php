<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}
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

    <!-- Chat window -->
    <div id="chat" style="max-height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;"></div>

    <!-- Input for the user to type messages -->
    <input type="text" id="userInput" placeholder="Type a message..." autocomplete="off">
    <button onclick="sendMessage()">Send</button>

    <footer>
        <!-- View Conversation History Button -->
        <button onclick="window.location.href='history.php'">View Conversation History</button>
    </footer>

    <script>
        // Listen for Enter keypress to send message
        document.getElementById("userInput").addEventListener("keypress", function(event) {
            if (event.key === "Enter") {
                sendMessage();
            }
        });

        function sendMessage() {
            let userInputField = document.getElementById("userInput");
            let userInput = userInputField.value.trim();
            let chatDiv = document.getElementById("chat");

            if (userInput === "") return;

            // Show user's message
            chatDiv.innerHTML += `<p><strong>You:</strong> ${userInput}</p>`;

            // Send message to Flask backend
            fetch("http://localhost:5000/chat", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        message: userInput
                    })
                })
                .then(response => response.json())
                .then(data => {
                    let aiReply = data.reply || "Sorry, I didn't get that.";
                    chatDiv.innerHTML += `<p><strong>AI:</strong> ${aiReply}</p>`;
                    chatDiv.scrollTop = chatDiv.scrollHeight;
                })
                .catch(error => {
                    console.error("Error:", error);
                    chatDiv.innerHTML += `<p><strong>AI:</strong> Something went wrong. Please try again.</p>`;
                    chatDiv.scrollTop = chatDiv.scrollHeight;
                });

            userInputField.value = "";
        }
    </script>
</body>

</html>