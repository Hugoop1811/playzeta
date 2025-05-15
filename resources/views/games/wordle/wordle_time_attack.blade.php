@extends('layouts.app')

@section('content')
<style>
    input#guessInput {
        border: 2px solid #9333ea;
        outline: none;
        padding: 0.5rem;
        font-size: 1.25rem;
        background-color: #1f2937;
        color: white;
        border-radius: 8px;
        text-align: center;
        width: 160px;
        transition: border-color 0.3s ease;
    }

    input#guessInput:focus {
        border-color: #c084fc;
    }

    #game-board div div.correct {
        background-color: #16a34a !important;
    }

    #game-board div div.wrong-place {
        background-color: #facc15 !important;
        color: #000;
    }

    #game-board div div.incorrect {
        background-color: #374151 !important;
    }

    .progress-circle {
        width: 140px;
        height: 140px;
    }

    .letra-caja {
        width: 3.5rem;
        height: 3.5rem;
        font-size: 1.5rem;
        font-weight: bold;
        border-radius: 0.5rem;
        transition: transform 0.2s ease, background-color 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .letra-caja.animar {
        transform: scale(1.2);
    }
</style>

<div class="container mx-auto text-white py-12 px-4">
    <div class="flex flex-col md:flex-row justify-center items-start md:items-center gap-10">

        <!-- Letras usadas -->
        <div id="letter-tracker" class="grid grid-cols-4 md:grid-cols-3 gap-3 text-center">
            <!-- letras por JS -->
        </div>

        <!-- Juego -->
        <div class="flex flex-col items-center flex-grow">
            <h2 class="text-4xl font-extrabold text-purple-400 mb-6">Wordle - Contrarreloj</h2>

            <div class="text-2xl text-indigo-300 mb-2" id="score">Puntuación: 0</div>

            <div id="game-board" class="flex flex-col items-center gap-2 mb-6"></div>

            <div class="mb-6 flex justify-center gap-4">
                <input type="text" id="guessInput" maxlength="5" class="uppercase" disabled>
                <button id="guessBtn" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 rounded text-white font-semibold shadow-lg transition-all" disabled>Intentar</button>
            </div>

            <button id="startBtn" class="bg-green-600 hover:bg-green-700 px-6 py-2 rounded text-white font-bold shadow-md transition-all">Comenzar</button>

            <div id="finalMessage" class="mt-8 text-2xl text-yellow-300 hidden"></div>

            <a href="{{ route('wordle.index') }}" class="mt-6 inline-flex items-center gap-2 px-5 py-2 bg-gray-700 hover:bg-gray-600 rounded text-white font-medium shadow-md transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Volver al Wordle clásico
            </a>
        </div>

        <!-- Reloj circular -->
        <div class="relative progress-circle">
            <svg class="absolute top-0 left-0 w-full h-full" viewBox="0 0 36 36">
                <path class="text-gray-700" stroke="currentColor" stroke-width="3" fill="none"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                <path id="timer-progress" class="text-purple-500" stroke="currentColor" stroke-width="3"
                    stroke-dasharray="100, 100" fill="none"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
            </svg>
            <div class="absolute inset-0 flex items-center justify-center text-white text-2xl font-bold" id="timer">300</div>
        </div>
    </div>

    <!-- Audios -->
    <audio id="bg-music" src="{{ asset('audio/suspense-pulse-tense-music-266060.mp3') }}" preload="auto" loop></audio>
    <audio id="correct-sfx" src="{{ asset('audio/correct.mp3') }}" preload="auto"></audio>
</div>

<script>
    let word = '';
    let score = 0;
    let timer = 300;
    let interval;
    let juegoActivo = false;
    const maxAttempts = 5;
    let currentAttempt = 0;

    const letras = "ABCDEFGHIJKLMNÑOPQRSTUVWXYZ";
    const letraEstados = {};
    const bgMusic = document.getElementById('bg-music');

    function renderEmptyBoard() {
        const board = document.getElementById('game-board');
        board.innerHTML = '';
        for (let i = 0; i < maxAttempts; i++) {
            const row = document.createElement('div');
            row.className = 'flex gap-1';
            for (let j = 0; j < 5; j++) {
                const box = document.createElement('div');
                box.className = 'w-12 h-12 border-2 border-gray-500 flex items-center justify-center text-xl font-bold bg-gray-800 text-white';
                row.appendChild(box);
            }
            board.appendChild(row);
        }
    }

    function renderLetrasUsadas() {
        const container = document.getElementById('letter-tracker');
        container.innerHTML = '';

        const letrasArray = letras.split('');

        const ordenadas = letrasArray.sort((a, b) => {
            const prioridad = { green: 0, yellow: 1, gray: 2, undefined: 3 };
            return prioridad[letraEstados[a]] - prioridad[letraEstados[b]];
        });

        ordenadas.forEach(l => {
            const div = document.createElement('div');
            div.innerText = l;
            div.classList.add('letra-caja', 'text-white', 'uppercase', 'bg-gray-700', 'animar');

            switch (letraEstados[l]) {
                case 'green':
                    div.classList.add('bg-green-600');
                    break;
                case 'yellow':
                    div.classList.add('bg-yellow-400', 'text-black');
                    break;
                case 'gray':
                    div.classList.add('bg-gray-600');
                    break;
            }

            container.appendChild(div);
            setTimeout(() => div.classList.remove('animar'), 200);
        });
    }

    async function fetchRandomWord() {
        const res = await fetch('/api/wordle/random');
        const data = await res.json();
        word = data.word.toUpperCase();
        renderEmptyBoard();
        renderLetrasUsadas();
        currentAttempt = 0;
    }

    function paintAttempt(guess) {
        const row = document.getElementById('game-board').children[currentAttempt];
        const targetLetters = word.split('');
        const guessLetters = guess.split('');
        const letterCount = {};

        for (let l of targetLetters) {
            letterCount[l] = (letterCount[l] || 0) + 1;
        }

        for (let i = 0; i < 5; i++) {
            const box = row.children[i];
            box.textContent = guessLetters[i];
            box.classList.add('text-white');

            const letra = guessLetters[i];

            if (letra === targetLetters[i]) {
                box.classList.add('correct');
                letraEstados[letra] = 'green';
                letterCount[letra]--;
            } else {
                box.classList.add('incorrect');
            }
        }

        for (let i = 0; i < 5; i++) {
            const letra = guessLetters[i];
            const box = row.children[i];

            if (letra !== targetLetters[i] &&
                targetLetters.includes(letra) &&
                letterCount[letra] > 0) {
                box.classList.remove('incorrect');
                box.classList.add('wrong-place');
                if (letraEstados[letra] !== 'green') {
                    letraEstados[letra] = 'yellow';
                }
                letterCount[letra]--;
            } else if (!targetLetters.includes(letra)) {
                if (!letraEstados[letra]) letraEstados[letra] = 'gray';
            }
        }

        renderLetrasUsadas();
    }

    async function intentarPalabra() {
        if (!juegoActivo) return;

        const input = document.getElementById('guessInput');
        const guess = input.value.toUpperCase();
        if (guess.length !== 5 || currentAttempt >= maxAttempts) return;

        const response = await fetch('/wordle/contrarreloj/check', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ guess: guess })
        });

        const data = await response.json();
        if (!data.valid) {
            alert('La palabra no es válida');
            return;
        }

        paintAttempt(guess);

        if (guess === word) {
            score += 100;
            document.getElementById('score').textContent = 'Puntuación: ' + score;
            document.getElementById('correct-sfx').play().catch(() => {});
            fetchRandomWord();
            return;
        }

        currentAttempt++;
        if (currentAttempt === maxAttempts) {
            document.getElementById('finalMessage').textContent = `Fallaste. La palabra era: ${word}`;
            document.getElementById('finalMessage').classList.remove('hidden');
            setTimeout(() => {
                document.getElementById('finalMessage').classList.add('hidden');
                fetchRandomWord();
            }, 2500);
        }

        input.value = '';
        input.focus();
    }

    function actualizarRelojCircular() {
        const porcentaje = (timer / 300) * 100;
        document.getElementById('timer-progress').setAttribute('stroke-dasharray', `${porcentaje}, 100`);
    }

    function ajustarVelocidadMusica() {
        if (timer <= 30) {
            bgMusic.playbackRate = 2.0;
        } else if (timer <= 100) {
            bgMusic.playbackRate = 1.5;
        } else if (timer <= 200) {
            bgMusic.playbackRate = 1.2;
        } else {
            bgMusic.playbackRate = 1.0;
        }
    }

    function endGame() {
        juegoActivo = false;
        clearInterval(interval);
        document.getElementById('finalMessage').textContent = `¡Fin del juego! Puntos: ${score}`;
        document.getElementById('finalMessage').classList.remove('hidden');
        document.getElementById('guessInput').disabled = true;
        document.getElementById('guessBtn').disabled = true;
        bgMusic.pause();

        fetch('/api/wordle/time-attack-score', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ score: score })
        }).catch(() => console.warn("No se pudo guardar la puntuación."));
    }

    document.getElementById('guessBtn').addEventListener('click', intentarPalabra);

    document.getElementById('guessInput').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') intentarPalabra();
    });

    document.getElementById('startBtn').addEventListener('click', () => {
        score = 0;
        timer = 300;
        juegoActivo = true;
        actualizarRelojCircular();
        ajustarVelocidadMusica();
        document.getElementById('timer').textContent = timer;
        document.getElementById('score').textContent = 'Puntuación: 0';
        document.getElementById('finalMessage').classList.add('hidden');
        document.getElementById('guessInput').disabled = false;
        document.getElementById('guessBtn').disabled = false;
        document.getElementById('guessInput').focus();
        document.getElementById('startBtn').classList.add('hidden');
        bgMusic.currentTime = 0;
        bgMusic.play().catch(e => console.warn("Autoplay bloqueado"));
        fetchRandomWord();

        interval = setInterval(() => {
            timer--;
            document.getElementById('timer').textContent = timer;
            actualizarRelojCircular();
            ajustarVelocidadMusica();
            if (timer <= 0) endGame();
        }, 1000);
    });

    renderEmptyBoard();
</script>
@endsection
