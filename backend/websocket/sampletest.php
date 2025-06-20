<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>WebSocket Test Client</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-5">
  <div class="container">
    <h2>WebSocket Test Client</h2>
    <div class="mb-3">
      <label for="messageInput" class="form-label">Message:</label>
      <input type="text" id="messageInput" class="form-control" placeholder="Type a message">
    </div>
    <button id="sendBtn" class="btn btn-primary">Send Message</button>

    <hr>
    <h4>Messages:</h4>
    <ul id="messages" class="list-group mt-3"></ul>
  </div>

  <script>
    const socket = new WebSocket("ws://localhost:8080");

    socket.onopen = () => {
      console.log("WebSocket connected.");
      appendMessage("Connected to WebSocket server.");
    };

    socket.onmessage = (event) => {
      appendMessage("Received: " + event.data);
    };

    socket.onclose = () => {
      console.log("WebSocket disconnected.");
      appendMessage("Disconnected from WebSocket.");
    };

    socket.onerror = (error) => {
      console.error("WebSocket Error:", error);
      appendMessage("Error: Could not connect.");
    };

    document.getElementById("sendBtn").onclick = () => {
      const input = document.getElementById("messageInput");
      const msg = input.value.trim();
      if (msg !== "") {
        socket.send(msg);
        appendMessage("Sent: " + msg);
        input.value = "";
      }
    };

    function appendMessage(message) {
      const li = document.createElement("li");
      li.className = "list-group-item";
      li.textContent = message;
      document.getElementById("messages").appendChild(li);
    }
  </script>
</body>
</html>
