<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test Pusher</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Test de conexión a Pusher</h1>
    <p>Tu ID: {{ auth()->id() }}</p>
    <div id="log" style="white-space: pre-line; background: #f3f3f3; padding: 1rem;"></div>

    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
        const log = (msg) => {
            document.getElementById('log').textContent += msg + "\n";
            console.log(msg);
        };

        Pusher.logToConsole = true;

        const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }
        });

        const userId = {{ auth()->id() }};
        const gameId = 45; // usa un ID cualquiera para test

        const channel = pusher.subscribe('private-battleship.' + gameId);

        channel.bind('GameJoined', function(data) {
            log('📥 Evento GameJoined recibido: ' + JSON.stringify(data));
        });

        channel.bind('ShipsPlaced', function(data) {
            log('📥 Evento ShipsPlaced recibido: ' + JSON.stringify(data));
        });

        channel.bind('MoveMade', function(data) {
            log('📥 Evento MoveMade recibido: ' + JSON.stringify(data));
        });

        channel.bind('GameOver', function(data) {
            log('📥 Evento GameOver recibido: ' + JSON.stringify(data));
        });
    </script>
</body>
</html>