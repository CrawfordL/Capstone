from flask import Flask, request, jsonify
from flask_cors import CORS
from openai import OpenAI

app = Flask(__name__)
CORS(app)  # Enable CORS to allow requests from your PHP frontend

# OpenRouter AI API configuration
client = OpenAI(
    base_url="https://openrouter.ai/api/v1",
    api_key="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",
)

@app.route("/chat", methods=["POST"])
def chat():
    data = request.get_json()
    user_message = data.get("message", "")

    if not user_message:
        return jsonify({"error": "No message provided"}), 400

    try:
        completion = client.chat.completions.create(
            model="qwen/qwen2.5-vl-72b-instruct:free",
            messages=[{"role": "user", "content": user_message}]
        )

        ai_reply = completion.choices[0].message.content if completion.choices else "No response from AI."
        return jsonify({"reply": ai_reply})

    except Exception as e:
        return jsonify({"error": f"Failed to get AI response: {str(e)}"}), 500

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=True)
