<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <h1>Welcome to Chatbot</h1>

    <div id="chat"></div>

    <div class="chat-controls">
        <div class="input-row">
            <input type="text" id="userInput" placeholder="Type a message..." autocomplete="off">
            <button onclick="sendMessage()">Send</button>
        </div>

        <footer>
            <button onclick="saveConversation()">Save Conversation</button>
            <button onclick="window.location.href='history.php'">View Conversation History</button>
        </footer>
    </div>
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

            const userMessage = document.createElement("p");
            userMessage.classList.add("message", "user-message");
            userMessage.innerHTML = userInput;
            chatDiv.appendChild(userMessage);

            fetch("http://localhost:5000/chat", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        message: userInput
                    })
                })
                .then(response => {
                    if (response.status === 429) {
                        throw new Error("Rate limit exceeded. Please try again later.");
                    }
                    return response.json();
                })
                .then(data => {
                    const aiReply = data.reply || "Sorry, I didn't get that.";

                    const aiMessage = document.createElement("p");
                    aiMessage.classList.add("message", "ai-message");
                    aiMessage.innerHTML = aiReply;
                    chatDiv.appendChild(aiMessage);

                    chatDiv.scrollTop = chatDiv.scrollHeight;
                })
                .catch(error => {
                    console.error("Error:", error);
                    const errorMessage = document.createElement("p");
                    errorMessage.classList.add("message", "ai-message");
                    errorMessage.innerHTML = `<strong>AI:</strong> ${error.message}`;
                    chatDiv.appendChild(errorMessage);
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