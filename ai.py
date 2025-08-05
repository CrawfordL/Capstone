from flask import Flask, request, jsonify, session  # Flask helps us create a web server
from flask_cors import CORS  # CORS allows websites to talk to our server from different domains
from openai import OpenAI  # OpenAI helps us talk to AI chatbots
import sqlite3  # SQLite lets us save data in a simple database
import json  # JSON helps us convert data to a format computers can easily share
import re  # Regular expressions help us clean up user input
from flask_limiter import Limiter  # Limiter prevents people from spamming our server
from flask_limiter.util import get_remote_address  # This helps identify who's making requests

app = Flask(__name__)
# Set a secret key to keep user sessions secure (like a password for the server)
app.secret_key = 'super_secret_key'  
# Allow websites from other domains to use our chatbot
CORS(app, supports_credentials=True)
CORS(app)

# Set up rate limiting so people can't spam our server with too many requests
limiter = Limiter(
    app=app,
    key_func=get_remote_address,  # Identify users by their IP address
    default_limits=["15 per minute"]  # Only allow 15 requests per minute per person
)

def init_db():
    """
    Creates the database and table if they don't exist yet.
    This is like setting up a filing cabinet to store conversations.
    """
    # Connect to our database file (creates it if it doesn't exist)
    conn = sqlite3.connect("chat_history.db")
    cursor = conn.cursor()
    # Create a table to store conversations if it doesn't already exist
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS conversations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            conversation_data TEXT
        );
    ''')
    # Save the changes to the database
    conn.commit()
    # Close the database connection to free up resources
    conn.close()

# Run the database setup function when the server starts
init_db()

# Set up connection to the AI service (OpenRouter in this case)
client = OpenAI(
    base_url="https://openrouter.ai/api/v1",  # The AI service we're using
    api_key="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",  # Our API key to access the service
)

def sanitize_input(user_input):
    """
    Cleans up user input to prevent hackers from injecting malicious code.
    It's like having a security guard check what people bring into a building.
    """
    # Remove any characters that aren't letters, numbers, spaces, or basic punctuation
    sanitized_input = re.sub(r"[^a-zA-Z0-9\s.,!?]", "", user_input)
    # Remove extra whitespace from the beginning and end
    return sanitized_input.strip()

# This handles when someone sends a chat message to our server
@app.route("/chat", methods=["POST"])
@limiter.limit("15 per minute")  # Apply rate limiting to prevent spam
def chat():
    # Get the JSON data that was sent to our server
    data = request.get_json()
    # Extract the message from the data, or use empty string if none provided
    user_message = data.get("message", "").strip()
    
    # If the user didn't actually send a message, return an error
    if not user_message:
        return jsonify({"error": "No message provided"}), 400

    # Clean the user's message to prevent security issues
    user_message = sanitize_input(user_message)

    # If this user doesn't have a conversation history yet, create an empty one
    if 'conversation' not in session:
        session['conversation'] = []

    try:
        # Send the user's message (and conversation history) to the AI
        completion = client.chat.completions.create(
            model="qwen/qwen2.5-vl-72b-instruct:free",  # Which AI model to use
            # Build the conversation history plus the new message
            messages=[{"role": "user", "content": msg["user"]} for msg in session['conversation']] + [{"role": "user", "content": user_message}]
        )
        # Get the AI's response, or use a default message if something went wrong
        ai_reply = completion.choices[0].message.content if completion.choices else "No response from AI."

        # Add this exchange to the user's conversation history
        session['conversation'].append({"user": user_message, "ai": ai_reply})
        # Tell Flask that we changed the session data
        session.modified = True

        # Send the AI's reply back to the website
        return jsonify({"reply": ai_reply})

    except Exception as e:
        # If anything goes wrong, send back an error message
        return jsonify({"error": f"Failed to get AI response: {str(e)}"}), 500

# This handles when someone wants to save their conversation
@app.route("/save_conversation", methods=["POST"])
def save_conversation():
    # Get the conversation data that was sent to us
    data = request.get_json()
    conversation = data.get("conversation", [])

    # If there's no conversation to save, return an error
    if not conversation:
        return jsonify({"error": "No conversation to save."}), 400

    try:
        # Connect to our database
        conn = sqlite3.connect("chat_history.db")
        cursor = conn.cursor()
        # Insert the conversation into the database (convert it to JSON text first)
        cursor.execute(
            "INSERT INTO conversations (conversation_data) VALUES (?)",
            (json.dumps(conversation),)  # Convert the conversation to JSON format
        )
        # Save the changes
        conn.commit()
        # Tell the user it worked
        return jsonify({"message": "Conversation saved successfully."})
    except sqlite3.Error as e:
        # If the database has problems, return an error
        return jsonify({"error": f"Database error: {str(e)}"}), 500
    finally:
        # Always close the database connection, even if there was an error
        if conn:
            conn.close()

# This handles when someone wants to see their conversation history
@app.route("/history", methods=["GET"])
def get_history():
    # Connect to our database
    conn = sqlite3.connect("chat_history.db")
    cursor = conn.cursor()
    # Get all conversations, newest first
    cursor.execute("SELECT conversation_data FROM conversations ORDER BY timestamp DESC")
    conversations = cursor.fetchall()
    # Close the database connection
    conn.close()

    # Convert each conversation from JSON text back to Python data
    history = [json.loads(convo[0]) for convo in conversations]
    # Send all the conversations back to the website
    return jsonify({"history": history})

# This runs when we start the Python script directly (not when imported by another file)
if __name__ == "__main__":
    # Start the web server
    # debug=True means show detailed error messages (helpful for development)
    app.run(host="0.0.0.0", port=5000, debug=True)
    # host="0.0.0.0" means accept connections from any computer
    # port=5000 means use port 5000