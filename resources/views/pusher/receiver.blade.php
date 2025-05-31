<!DOCTYPE html>
<html>
<head>
  <title>Receptor</title>
  <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
</head>
<body>
  <h1>Escuchando mensaje...</h1>
  <div id="output"></div>

  <script>
    Pusher.logToConsole = true;

    const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
            authEndpoint: '/broadcasting/auth',
        });

    const channel = pusher.subscribe('test-channel');

    channel.bind('test-event', function(data) {
      console.log('Mensaje recibido:', data);
      document.getElementById('output').innerText = 'Mensaje: ' + data.message;
    });
  </script>
</body>
</html>
