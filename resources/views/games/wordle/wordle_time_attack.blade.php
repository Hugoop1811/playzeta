@extends('layouts.app')

@section('content')
<div class="container text-center text-white py-8">
    <h2 class="text-3xl font-bold mb-4">Wordle - Contrarreloj</h2>
    <div id="timer" class="text-xl mb-2">300</div>
    <div id="score" class="mb-4">Puntuación: 0</div>

    <div id="game-board" class="flex flex-col items-center gap-2 mb-4"></div>

    <div class="mb-4">
        <input type="text" id="guessInput" maxlength="5" class="text-black text-center uppercase w-32" autofocus>
        <button id="guessBtn" class="ml-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 rounded">Intentar</button>
    </div>

    <div>
        <button id="startBtn" class="bg-green-600 px-4 py-2 rounded hover:bg-green-700">Comenzar</button>
    </div>

    <div id="finalMessage" class="mt-6 text-xl hidden"></div>
</div>

<script>
    let word = '';
    let score = 0;
    let timer = 300;
    let interval;
    const maxAttempts = 5;
    let currentAttempt = 0;

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

    async function fetchRandomWord() {
        const res = await fetch('/api/wordle/random');
        const data = await res.json();
        word = data.word.toUpperCase();
        renderEmptyBoard();
        currentAttempt = 0;
        console.log('Palabra actual:', word);
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

            if (guessLetters[i] === targetLetters[i]) {
                box.style.backgroundColor = 'green';
                letterCount[guessLetters[i]]--;
            } else {
                box.style.backgroundColor = 'gray';
            }
        }

        for (let i = 0; i < 5; i++) {
            const box = row.children[i];
            if (guessLetters[i] !== targetLetters[i] && targetLetters.includes(guessLetters[i]) && letterCount[guessLetters[i]] > 0) {
                box.style.backgroundColor = 'gold';
                letterCount[guessLetters[i]]--;
            }
        }
    }

    async function intentarPalabra() {
        const input = document.getElementById('guessInput');
        const guess = input.value.toUpperCase();
        if (guess.length !== 5 || currentAttempt >= maxAttempts) return;

        // VALIDACIÓN con el backend
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

    document.getElementById('guessBtn').addEventListener('click', intentarPalabra);

    document.getElementById('guessInput').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            intentarPalabra();
        }
    });

    document.getElementById('startBtn').addEventListener('click', () => {
        score = 0;
        timer = 300;
        document.getElementById('timer').textContent = timer;
        document.getElementById('score').textContent = 'Puntuación: 0';
        document.getElementById('finalMessage').classList.add('hidden');
        fetchRandomWord();

        interval = setInterval(() => {
            timer--;
            document.getElementById('timer').textContent = timer;
            if (timer <= 0) {
                clearInterval(interval);
                endGame();
            }
        }, 1000);
    });

    async function endGame() {
        document.getElementById('finalMessage').textContent = `¡Fin del juego! Puntos: ${score}`;
        document.getElementById('finalMessage').classList.remove('hidden');

        try {
            await fetch('/api/wordle/time-attack-score', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ score: score })
            });
        } catch (e) {
            console.warn("Puntuación no guardada (usuario no autenticado).");
        }
    }

    renderEmptyBoard(); // para que se vea desde el principio
</script>
@endsection
