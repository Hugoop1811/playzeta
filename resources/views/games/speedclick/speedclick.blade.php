@extends('layout')

@section('title', 'Speed Click')

@section('content')

<div class="container text-center mt-5">
    <h1 class="mb-4">Speed Click - Test de Reacción</h1>

    <div id="game-container" class="waiting mb-3">
        <h2 id="message">Haz clic para comenzar</h2>
    </div>

    <div id="result" class="alert alert-info" style="display: none;"></div>

<button id="try-again" class="btn-custom" style="display: none;">Volver a jugar</button>
<button id="view-leaderboard" class="btn-custom" style="display: none;">Ver Mejores Puntuaciones</button>


</div>

<style>
    #game-container {
        width: 100%;
        height: 300px;
        background-color: #ccc;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        border-radius: 10px;
    }

    #game-container.ready {
        background-color: #f39c12;
    }

    #game-container.now {
        background-color: #2ecc71;
    }

    #game-container.too-soon {
        background-color: #e74c3c;
    }

    .btn-custom {
        display: inline-block;
        padding: 10px 15px;
        background-color: #3498db;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        border: none;
        cursor: pointer;
        margin: 10px;
        transition: background-color 0.3s ease, transform 0.1s ease;
    }

    .btn-custom:hover {
        background-color: #2980b9;
        transform: scale(1.05);
    }

    .btn-custom:active {
        background-color: #2471a3;
        transform: scale(0.98);
    }
</style>


<script>
    let startTime;
    let timeout;
    let gameState = "waiting";

    const container = document.getElementById("game-container");
    const message = document.getElementById("message");
    const result = document.getElementById("result");
    const tryAgain = document.getElementById("try-again");

    container.addEventListener("click", () => {
        if (gameState === "waiting") {
            message.textContent = "Espera a que cambie el color...";
            gameState = "ready";
            container.className = "ready";

            const randomDelay = Math.floor(Math.random() * 3000) + 2000;
            timeout = setTimeout(() => {
                container.className = "now";
                message.textContent = "¡Haz clic!";
                startTime = Date.now();
                gameState = "now";
            }, randomDelay);
        } else if (gameState === "ready") {
            clearTimeout(timeout);
            container.className = "too-soon";
            message.textContent = "¡Demasiado pronto!";
            gameState = "waiting";
            tryAgain.style.display = "inline-block";
        } else if (gameState === "now") {
    const reactionTime = Date.now() - startTime;
    message.textContent = `Tu tiempo de reacción fue ${reactionTime} ms`;
    result.textContent = `🎯 Tiempo: ${reactionTime} ms`;
    result.style.display = "block";
    tryAgain.style.display = "inline-block";
    document.getElementById("view-leaderboard").style.display = "inline-block"; // Mostrar el nuevo botón
    gameState = "waiting";

    // Enviar la puntuación al backend
    fetch('/speedclick/score', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            reaction_time_ms: reactionTime
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Puntuación guardada:', data);
    })
    .catch(error => {
        console.error('Error al guardar la puntuación:', error);
    });
}

    });

    document.getElementById("view-leaderboard").addEventListener("click", () => {
    window.location.href = "/speedclick/leaderboard";
});

    tryAgain.addEventListener("click", () => {
        result.style.display = "none";
        tryAgain.style.display = "none";
        message.textContent = "Haz clic para comenzar";
        container.className = "waiting";
    });
</script>

@endsection
