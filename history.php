<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversation History</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .conversation { border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; border-radius: 8px; }
        /* Each conversation gets a border, padding, and rounded corners */
        .message { margin: 5px 0; }
        /* Each message gets some space above and below */
        .user { font-weight: bold; color: #1976d2; }
        /* User messages are bold and blue */
        .ai { font-weight: bold; color: #388e3c; }
        /* AI messages are bold and green */
    </style>
</head>
<body>
    <h1>Past Conversations</h1>
    <!-- Shows a big heading that says "Past Conversations" -->
    <div id="conversationList">Loading conversations...</div>
    <!-- Creates a container where all the old conversations will appear -->

    <script>
        // Ask the server for all saved conversations
        fetch("http://localhost:5000/history")
            // Wait for the server to respond
            .then(function(response) {
                // Convert the server's response from JSON to regular data
                return response.json();
            })
            // Once we have the conversation data, display it
            .then(function(data) {
                // Get the container where conversations will be shown
                var conversationList = document.getElementById("conversationList");
                
                // Check if there are any conversations to show
                if (data.history && data.history.length > 0) {
                    // Clear the "Loading..." message
                    conversationList.innerHTML = "";

                    // Go through each conversation one by one
                    data.history.forEach(function(conversation, index) {
                        // Create a container for this conversation
                        var conversationDiv = document.createElement("div");
                        conversationDiv.className = "conversation";

                        // Go through each message in this conversation
                        conversation.forEach(function(message) {
                            // Create a container for this message
                            var messageDiv = document.createElement("div");
                            messageDiv.className = "message";
                            
                            // Check if this message is from the user
                            if (message.startsWith("You:")) {
                                // Style it as a user message (blue)
                                messageDiv.innerHTML = "<span class=\"user\">" + message + "</span>";
                            } 
                            // Check if this message is from the AI
                            else if (message.startsWith("AI:")) {
                                // Style it as an AI message (green)
                                messageDiv.innerHTML = "<span class=\"ai\">" + message + "</span>";
                            } 
                            // If it's neither user nor AI message
                            else {
                                // Just display it as plain text
                                messageDiv.textContent = message;
                            }
                            // Add this message to the conversation container
                            conversationDiv.appendChild(messageDiv);
                        });

                        // Add this whole conversation to the page
                        conversationList.appendChild(conversationDiv);
                    });
                } 
                // If there are no conversations saved
                else {
                    // Tell the user there's nothing to show
                    conversationList.textContent = "No conversations found.";
                }
            })
            // If something goes wrong while getting the conversations
            .catch(function(error) {
                // Log the error to the console for debugging
                console.error("Error fetching conversation history:", error);
                // Show an error message to the user
                document.getElementById("conversationList").textContent = "Failed to load conversations.";
            });
    </script>
</body>
</html>