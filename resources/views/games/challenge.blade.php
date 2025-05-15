@extends('layout')

@section('title', 'Speed Click Challenge')

@section('content')
<style>
    html, body {
        margin: 0;
        padding: 0;
        background-color: #0d0d0d;
        color: #fff;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        height: 100%;
    }

    #game-area {
        position: absolute;
        top: 1;
        left: 0;
        width: 100%;
        height: calc(92vh - 60px); 
        margin-top: 0px; 
        background-color: #121212;
        overflow: hidden;
        z-index: 1;
    }

    #target {
        border-radius: 50%;
        background-color: #ff1744;
        position: absolute;
        cursor: pointer;
        display: none;
        user-select: none;
        outline: none;
        animation: popIn 0.15s ease-out;
        box-shadow: 0 0 10px rgba(255, 23, 68, 0.6);
        z-index: 10;
    }

    @keyframes popIn {
        0% {
            transform: scale(0.5);
            opacity: 0;
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .overlay-start {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(10, 10, 10, 0.9);
        color: white;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 100;
        text-align: center;
        padding: 20px;
    }

    .overlay-start h1 {
        font-size: 3rem;
        margin-bottom: 20px;
    }

    .overlay-start p {
        font-size: 1.2rem;
        margin-bottom: 30px;
    }

    .btn-start {
        background-color: #ff1744;
        color: white;
        border: none;
        padding: 15px 30px;
        font-size: 1.2rem;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.2s ease, transform 0.2s ease;
    }

    .btn-start:hover {
        background-color: #ff4569;
        transform: scale(1.05);
    }

    .hud {
        position: absolute;
        top: 20px;
        left: 20px;
        z-index: 20;
    }

    .hud p {
        margin: 5px 0;
        font-size: 18px;
    }

    .hud button {
        margin-top: 10px;
    }

    #result {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 28px;
        background: rgba(0, 0, 0, 0.8);
        padding: 20px;
        border-radius: 10px;
        display: none;
        z-index: 30;
    }

    .btn {
        padding: 8px 20px;
        border-radius: 6px;
        font-size: 14px;
        border: none;
        cursor: pointer;
    }

    .btn-success {
        background-color: #28a745;
        color: white;
    }

    .btn-primary {
        background-color: #007bff;
        color: white;
    }

    .btn:hover {
        opacity: 0.9;
    }

    select {
    padding: 10px;
    font-size: 1rem;
    border-radius: 6px;
    border: none;
    margin-bottom: 10px;
    background-color: #1e1e1e;
    color: white;
    width: 200px;
    text-align: center;
}
select:focus {
    outline: 2px solid #ff1744;
}

select option {
    background-color: #1e1e1e;
    color: white;
}



    label {
        font-weight: bold;
    }
</style>

<div id="game-area">
    <div id="target" tabindex="-1"></div>

    <div id="start-screen" class="overlay-start">
        <h1>🧠 Speed Click Challenge</h1>
        <p>Haz clic en los círculos lo más rápido posible. ¿Puedes con todos?</p>

        <div id="difficulty-settings">
            <h2>Configuración de dificultad</h2>

            <label for="size">Tamaño de diana:</label><br>
            <select id="size">
                <option value="30">Pequeño</option>
                <option value="40" selected>Medio</option>
                <option value="60">Grande</option>
            </select><br><br>

            <label for="speed">Velocidad de aparición:</label><br>
            <select id="speed">
                <option value="500">Rápida</option>
                <option value="1000" selected>Normal</option>
                <option value="1500">Lenta</option>
            </select><br><br>

            <label for="quantity">Cantidad de objetivos:</label><br>
            <select id="quantity">
                <option value="5">5</option>
                <option value="10" selected>10</option>
                <option value="20">20</option>
            </select>
        </div>

        <br>
        <button id="start-btn" class="btn-start">Comenzar</button>
    </div>

    <div class="hud">
        <p id="status"></p>
        <p id="counter"></p>
        <button id="play-again" class="btn btn-primary" style="display: none;">Volver a jugar</button>
    </div>

    <div id="result"></div>
</div>

<script>
    const gameArea = document.getElementById("game-area");
    const target = document.getElementById("target");
    const startBtn = document.getElementById("start-btn");
    const playAgainBtn = document.getElementById("play-again");
    const status = document.getElementById("status");
    const counter = document.getElementById("counter");
    const result = document.getElementById("result");
    const startScreen = document.getElementById("start-screen");

    const sizeSelect = document.getElementById("size");
    const speedSelect = document.getElementById("speed");
    const quantitySelect = document.getElementById("quantity");

    let clickCount = 0;
    let reactionTimes = [];
    let appearTime;
    let totalTargets = 10;
    let spawnDelay = 1000;
    let targetSize = 40;

    function getRandomPosition() {
        const maxLeft = gameArea.clientWidth - targetSize;
        const maxTop = gameArea.clientHeight - targetSize;
        const left = Math.floor(Math.random() * maxLeft);
        const top = Math.floor(Math.random() * maxTop);
        return { left, top };
    }

    function showTarget() {
        const { left, top } = getRandomPosition();
        target.style.left = left + "px";
        target.style.top = top + "px";
        target.style.width = targetSize + "px";
        target.style.height = targetSize + "px";
        target.style.display = "block";
        target.style.animation = "popIn 0.15s ease-out";
        appearTime = Date.now();
    }

    function startGame() {
        // Obtener dificultad
        targetSize = parseInt(sizeSelect.value);
        spawnDelay = parseInt(speedSelect.value);
        totalTargets = parseInt(quantitySelect.value);

        startScreen.style.display = "none";
        clickCount = 0;
        reactionTimes = [];
        result.style.display = "none";
        playAgainBtn.style.display = "none";
        status.textContent = "";
        counter.textContent = "";

        setTimeout(() => {
            nextClick();
        }, 1000);
    }

    function nextClick() {
        if (clickCount < totalTargets) {
            counter.textContent = `Objetivo ${clickCount + 1} de ${totalTargets}`;
            showTarget();
        } else {
            finishGame();
        }
    }

    function finishGame() {
        const sum = reactionTimes.reduce((a, b) => a + b, 0);
        const avg = Math.round(sum / reactionTimes.length);
        target.style.display = "none";
        status.textContent = "¡Reto completado!";
        result.textContent = `⏱️ Tiempo medio de reacción: ${avg} ms`;
        result.style.display = "block";
        playAgainBtn.style.display = "inline-block";
    }

    target.addEventListener("click", () => {
        const reactionTime = Date.now() - appearTime;
        reactionTimes.push(reactionTime);
        clickCount++;
        target.style.display = "none";
        setTimeout(nextClick, spawnDelay);
    });

    startBtn.addEventListener("click", startGame);

    playAgainBtn.addEventListener("click", () => {
        startScreen.style.display = "flex";
        result.style.display = "none";
        playAgainBtn.style.display = "none";
        status.textContent = "";
        counter.textContent = "";
    });
</script>
@endsection
