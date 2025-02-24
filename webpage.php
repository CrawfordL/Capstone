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
    <title>Chatbot</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <h1>Welcome to the Chatbot</h1>

    <div id="chat" style="max-height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;"></div>

    <input type="text" id="userInput" placeholder="Type a message..." autocomplete="off">
    <button onclick="sendMessage()">Send</button>

    <footer>
        <button onclick="saveConversation()">Save Conversation</button>
        <button onclick="window.location.href='history.php'">View Conversation History</button>
    </footer>

    <script>
        document.getElementById("userInput").addEventListener("keypress", function(event) {
            if (event.key === "Enter") {
                sendMessage();
            }
        });

        function sendMessage() {
            const userInputField = document.getElementById("userInput");
            const userInput = userInputField.value.trim();
            const chatDiv = document.getElementById("chat");

            if (userInput === "") return;

            chatDiv.innerHTML += `<p><strong>You:</strong> ${userInput}</p>`;

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
                    const aiReply = data.reply || "Sorry, I didn't get that.";
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

        function saveConversation() {
            const chatDiv = document.getElementById("chat");
            const messages = Array.from(chatDiv.querySelectorAll("p")).map(p => p.textContent);

            fetch("http://localhost:5000/save_conversation", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        conversation: messages
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        alert(data.message);
                    } else if (data.error) {
                        alert(`Error: ${data.error}`);
                    }
                })
                .catch(error => {
                    console.error("Fetch error:", error);
                    alert("Failed to save conversation.");
                });
        }
    </script>
</body>

</html>