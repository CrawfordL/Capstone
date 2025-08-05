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
    <!-- Shows a big heading that says "Welcome to Chatbot" -->

    <div id="chat"></div>
    <!-- Creates an empty box where all the chat messages will appear -->

    <div class="chat-controls">
        <!-- Creates a section for the input box and buttons -->
        <div class="input-row">
            <!-- Groups the text input and send button together -->
            <input type="text" id="userInput" placeholder="Type a message..." autocomplete="off">
            <!-- Creates a text box where users can type their messages -->
            <button onclick="sendMessage()">Send</button>
            <!-- Creates a "Send" button that runs the sendMessage function when clicked -->
        </div>

        <footer>
            <button onclick="saveConversation()">Save Conversation</button>
            <!-- Button that saves the chat when clicked -->
            <button onclick="window.location.href='history.php'">View Conversation History</button>
            <!-- Button that takes you to a different page to see old conversations -->
        </footer>
    </div>
    <script>
        document.getElementById("userInput").addEventListener("keypress", function(event) {
            //Listens for when someone presses a key in the text input box
            if (event.key === "Enter") {
                // Checks if the key pressed was the Enter key 
                sendMessage();
                // If Enter was pressed, send the message automatically 
            }
        });

        function sendMessage() {
            // Creates a function that handles sending messages
            const userInputField = document.getElementById("userInput");
            // Gets the text input box so we can work with it
            const userInput = userInputField.value.trim();
            // Gets whatever the user typed and removes extra spaces 
            const chatDiv = document.getElementById("chat");
            // Gets the chat area where messages will be displayed 

            if (userInput === "") return;
            // If the user didn't type anything, stop here and don't send

            const userMessage = document.createElement("p");
            // Creates a new paragraph element for the user's message 
            userMessage.classList.add("message", "user-message");
            // Adds CSS classes to style the user's message 
            userMessage.innerHTML = userInput;
            // Puts the user's text inside the paragraph 
            chatDiv.appendChild(userMessage);
            // Adds the user's message to the chat area 

            fetch("http://localhost:5000/chat", {
                    // Sends the user's message to the AI server 
                    method: "POST",
                    // Tells the server we're sending data 
                    headers: {
                        "Content-Type": "application/json"
                        // Tells the server the data is in JSON format 
                    },
                    body: JSON.stringify({
                        message: userInput
                        // Converts the user's message to JSON format for sending 
                    })
                })
                .then(response => {
                    // Waits for the server to respond 
                    if (response.status === 429) {
                        // Checks if the server says "too many requests" 
                        throw new Error("Rate limit exceeded. Please try again later.");
                        // Creates an error message about trying too often 
                    }
                    return response.json();
                    // Converts the server's response from JSON back to regular data 
                })
                .then(data => {
                    // Once we have the AI's response, do this: 
                    const aiReply = data.reply || "Sorry, I didn't get that.";
                    // Gets the AI's message, or uses a default if something went wrong 

                    const aiMessage = document.createElement("p");
                    // Creates a new paragraph for the AI's response 
                    aiMessage.classList.add("message", "ai-message");
                    // Adds CSS classes to style the AI's message differently 
                    aiMessage.innerHTML = aiReply;
                    // Puts the AI's text inside the paragraph 
                    chatDiv.appendChild(aiMessage);
                    // Adds the AI's message to the chat area 

                    chatDiv.scrollTop = chatDiv.scrollHeight;
                    // Automatically scrolls down to see the newest message 
                })
                .catch(error => {
                    // If something goes wrong with getting the AI response: 
                    console.error("Error:", error);
                    // Logs the error to the browser's console for debugging 
                    const errorMessage = document.createElement("p");
                    // Creates a paragraph to show the error 
                    errorMessage.classList.add("message", "ai-message");
                    // Styles it like an AI message 
                    errorMessage.innerHTML = `<strong>AI:</strong> ${error.message}`;
                    // Shows the error message with "AI:" in front 
                    chatDiv.appendChild(errorMessage);
                    // Adds the error message to the chat 
                    chatDiv.scrollTop = chatDiv.scrollHeight;
                    // Scrolls down to see the error message 
                });

            userInputField.value = "";
            // Clears the text input box so user can type a new message 
        }

        function saveConversation() {
            // Creates a function to save the entire chat conversation 
            const chatDiv = document.getElementById("chat");
            // Gets the chat area that contains all messages 
            const messages = Array.from(chatDiv.querySelectorAll("p")).map(p => p.textContent);
            // Gets all the message paragraphs and extracts just the text from each one 

            fetch("http://localhost:5000/save_conversation", {
                    // Sends the conversation to the server to be saved 
                    method: "POST",
                    // Tells server we're sending data 
                    headers: {
                        "Content-Type": "application/json"
                        // Tells server the data is in JSON format 
                    },
                    body: JSON.stringify({
                        conversation: messages
                        // Converts all the messages to JSON format for sending 
                    })
                })
                .then(response => response.json())
                // Waits for server response and converts it from JSON 
                .then(data => {
                    // Once we get the server's response: 
                    if (data.message) {
                        // If the server sent a success message 
                        alert(data.message);
                        // Show a popup with the success message 
                    } else if (data.error) {
                        // If the server sent an error message 
                        alert(`Error: ${data.error}`);
                        // Show a popup with the error 
                    }
                })
                .catch(error => {
                    // If something goes wrong while saving: 
                    console.error("Fetch error:", error);
                    // Log the error to console for debugging 
                    alert("Failed to save conversation.");
                    // Show a simple error popup to the user 
                });
        }
    </script>
</body>

</html>