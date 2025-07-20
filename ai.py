from flask import Flask, request, jsonify, session
from flask_cors import CORS
from openai import OpenAI
import sqlite3
import json
import re  
from flask_limiter import Limiter  
from flask_limiter.util import get_remote_address  

app = Flask(__name__)
app.secret_key = 'super_secret_key'  
CORS(app, supports_credentials=True)
CORS(app)

limiter = Limiter(
    app=app,
    key_func=get_remote_address,  
    default_limits=["15 per minute"]  
)

def init_db():
    conn = sqlite3.connect("chat_history.db")
    cursor = conn.cursor()
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS conversations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            conversation_data TEXT
        );
    ''')
    conn.commit()
    conn.close()

init_db()

client = OpenAI(
    base_url="https://openrouter.ai/api/v1",
    api_key="Add Your Own Key",
)

def sanitize_input(user_input):
    """
    Sanitizes user input to prevent XSS and injection attacks.
    """
    sanitized_input = re.sub(r"[^a-zA-Z0-9\s.,!?]", "", user_input)
    return sanitized_input.strip()

@app.route("/chat", methods=["POST"])
@limiter.limit("15 per minute")
def chat():
    data = request.get_json()
    user_message = data.get("message", "").strip()
    
    if not user_message:
        return jsonify({"error": "No message provided"}), 400

    user_message = sanitize_input(user_message)

    if 'conversation' not in session:
        session['conversation'] = []

    try:
        completion = client.chat.completions.create(
            model="qwen/qwen2.5-vl-72b-instruct:free",
            messages=[{"role": "user", "content": msg["user"]} for msg in session['conversation']] + [{"role": "user", "content": user_message}]
        )
        ai_reply = completion.choices[0].message.content if completion.choices else "No response from AI."

        session['conversation'].append({"user": user_message, "ai": ai_reply})
        session.modified = True

        return jsonify({"reply": ai_reply})

    except Exception as e:
        return jsonify({"error": f"Failed to get AI response: {str(e)}"}), 500

@app.route("/save_conversation", methods=["POST"])
def save_conversation():
    data = request.get_json()
    conversation = data.get("conversation", [])

    if not conversation:
        return jsonify({"error": "No conversation to save."}), 400

    try:
        conn = sqlite3.connect("chat_history.db")
        cursor = conn.cursor()
        cursor.execute(
            "INSERT INTO conversations (conversation_data) VALUES (?)",
            (json.dumps(conversation),)
        )
        conn.commit()
        return jsonify({"message": "Conversation saved successfully."})
    except sqlite3.Error as e:
        return jsonify({"error": f"Database error: {str(e)}"}), 500
    finally:
        if conn:
            conn.close()

@app.route("/history", methods=["GET"])
def get_history():
    conn = sqlite3.connect("chat_history.db")
    cursor = conn.cursor()
    cursor.execute("SELECT conversation_data FROM conversations ORDER BY timestamp DESC")
    conversations = cursor.fetchall()
    conn.close()

    history = [json.loads(convo[0]) for convo in conversations]
    return jsonify({"history": history})

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=True)