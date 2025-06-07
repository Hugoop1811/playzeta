<!DOCTYPE html>
<html>
<head>
  <title>Emisor</title>
</head>
<body>
  <h1>Enviar un mensaje</h1>
  <form method="POST" action="/pusher-send-message">
    @csrf
    <input type="text" name="message" placeholder="Escribe tu mensaje" required>
    <button type="submit">Enviar</button>
  </form>
</body>
</html>
