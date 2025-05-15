@extends('layout')

@section('content')
<div class="text-center">
<a href="/" class="absolute top-4 left-4 bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-600">
    ← Volver al inicio
</a>

    <h2 class="text-3xl font-bold mb-4">Wordle - Reto Diario</h2>

    <div id="grid" class="grid grid-cols-5 gap-2 justify-center mb-6 flex-wrap max-w-md mx-auto">
        <!-- Aquí se mostrarán los intentos -->
    </div>

    <div id="guessBoxes" class="flex justify-center mb-4 space-x-2">
    <!-- Las casillas se llenan por JS -->
</div>

    <div class="mb-4">
        <button id="submitBtn" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded">Intentar</button>
    </div>

    <div id="mensaje" class="mb-4 text-lg font-semibold"></div>

    <div id="keyboard" class="grid grid-cols-10 gap-2 justify-center text-white font-bold max-w-xl mx-auto">
        <!-- Se genera por JS -->
    </div>
    <input type="hidden" id="guessInput">
  <a href="{{ route('wordle.time') }}" class="mt-4 inline-block text-indigo-400 hover:text-indigo-300 underline">
    Probar el modo contrarreloj ⏱️
</a>

</a>


</div>

<script>
    const letras = "QWERTYUIOPASDFGHJKLÑZXCVBNM";
    const keyboard = document.getElementById('keyboard');
    const grid = document.getElementById('grid');
    const mensaje = document.getElementById('mensaje');
    const guessInput = document.getElementById('guessInput');
    let intentoActual = '';
    let intentos = 0;
    const maxIntentos = 6;
    const teclas = {};

    // Crear botones del teclado
    letras.split('').forEach(letra => {
        const btn = document.createElement('button');
        btn.innerText = letra;
        btn.classList.add('bg-gray-700', 'rounded', 'p-3');
        btn.addEventListener('click', () => añadirLetra(letra));
        keyboard.appendChild(btn);
        teclas[letra] = btn;
    });

    // Tecla borrar
    const back = document.createElement('button');
    back.innerText = '⌫';
    back.classList.add('bg-red-500', 'rounded', 'p-3', 'col-span-2');
    back.addEventListener('click', borrarLetra);
    keyboard.appendChild(back);

    function añadirLetra(letra) {
        if (intentoActual.length < 5) {
            intentoActual += letra;
            actualizarInput();
        }
    }

    function borrarLetra() {
        intentoActual = intentoActual.slice(0, -1);
        actualizarInput();
    }

    function actualizarInput() {
    guessInput.value = intentoActual;
    renderCasillas();
}


    function renderCasillas() {
    const boxContainer = document.getElementById('guessBoxes');
    boxContainer.innerHTML = '';
    for (let i = 0; i < 5; i++) {
        const char = intentoActual[i] || '';
        const box = document.createElement('div');
        box.innerText = char;
        box.classList.add('w-12', 'h-12', 'border-2', 'border-gray-500', 'rounded', 'flex', 'items-center', 'justify-center', 'text-white', 'text-xl', 'font-bold', 'uppercase');
        boxContainer.appendChild(box);
    }
}


    document.getElementById('submitBtn').addEventListener('click', () => {
        if (intentoActual.length !== 5) {
            mensaje.innerText = 'La palabra debe tener 5 letras.';
            return;
        }

        fetch("{{ route('wordle.check') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({ guess: intentoActual })
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                mensaje.innerText = data.error;
                return;
            }

            intentos++;
            mostrarResultado(data.result);

            if (data.correct) {
                mensaje.innerText = '¡Correcto! Has adivinado la palabra.';
                bloquearTeclado();
            } else if (intentos >= maxIntentos) {
                mensaje.innerText = `Se acabaron los intentos. La palabra era: ${data.target}`;
                bloquearTeclado();
            } else {
                mensaje.innerText = '';
            }

            intentoActual = '';
            actualizarInput();
        });
    });

    function mostrarResultado(resultado) {
        resultado.forEach(letraInfo => {
            const box = document.createElement('div');
            box.innerText = letraInfo.letter;
            box.classList.add('w-14', 'h-14', 'flex', 'items-center', 'justify-center', 'rounded', 'font-bold', 'text-xl', 'uppercase', 'text-white', 'transition', 'shadow');

            switch (letraInfo.color) {
                case 'green':
                    box.classList.add('bg-green-500');
                    actualizarTecla(letraInfo.letter, 'green');
                    break;
                case 'yellow':
                    box.classList.add('bg-yellow-400', 'text-black');
                    actualizarTecla(letraInfo.letter, 'yellow');
                    break;
                default:
                    box.classList.add('bg-gray-700');
                    actualizarTecla(letraInfo.letter, 'gray');
            }

            grid.appendChild(box);
        });
    }

    function actualizarTecla(letra, color) {
        const tecla = teclas[letra];
        if (!tecla) return;

        if (color === 'green' || 
           (color === 'yellow' && !tecla.classList.contains('bg-green-500')) || 
           (color === 'gray' && !tecla.classList.contains('bg-green-500') && !tecla.classList.contains('bg-yellow-400'))) {
            tecla.className = 'rounded p-3';
            if (color === 'green') tecla.classList.add('bg-green-500');
            else if (color === 'yellow') tecla.classList.add('bg-yellow-400', 'text-black');
            else tecla.classList.add('bg-gray-700');
        }
    }

    function bloquearTeclado() {
        Object.values(teclas).forEach(b => b.disabled = true);
    }
</script>
@endsection
