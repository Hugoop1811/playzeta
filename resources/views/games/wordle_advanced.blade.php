@extends('layout')

@section('content')
<div class="text-center">
    <h2 class="text-3xl font-bold mb-4">Reto Diario - Wordle</h2>
    <div id="grid" class="grid grid-cols-5 gap-2 justify-center mb-6">
        <!-- Las letras se llenarán con JS -->
    </div>
    <form id="wordleForm" class="flex justify-center space-x-2">
        <input type="text" name="guess" maxlength="5" class="text-black p-2 rounded text-center uppercase font-mono tracking-widest" required autofocus>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded">Intentar</button>
    </form>
    <p id="mensaje" class="mt-4 text-lg font-semibold"></p>
</div>

<script>
    let intentos = 0;
    const maxIntentos = 6;
    const grid = document.getElementById('grid');
    const mensaje = document.getElementById('mensaje');

    document.getElementById('wordleForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const guess = e.target.guess.value.toUpperCase();

        if (guess.length !== 5) {
            mensaje.innerText = 'La palabra debe tener 5 letras';
            return;
        }

        fetch("{{ route('wordle.check') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({ guess })
        })
        .then(res => res.json())
        .then(data => {
            intentos++;
            mostrarResultado(data.guess, data.correct);

            if (data.correct) {
                mensaje.innerText = '¡Correcto! Has adivinado la palabra.';
                e.target.querySelector('input').disabled = true;
            } else if (intentos >= maxIntentos) {
                mensaje.innerText = 'Se acabaron los intentos. Inténtalo mañana.';
                e.target.querySelector('input').disabled = true;
            } else {
                mensaje.innerText = '';
            }

            e.target.reset();
        });
    });

    function mostrarResultado(guess, correcto, resultado) {
    resultado.forEach(letraInfo => {
        const box = document.createElement('div');
        box.innerText = letraInfo.letter;
        box.classList.add('w-14', 'h-14', 'flex', 'items-center', 'justify-center', 'rounded', 'font-bold', 'text-xl', 'uppercase', 'text-white', 'transition');

        switch (letraInfo.color) {
            case 'green':
                box.classList.add('bg-green-500');
                break;
            case 'yellow':
                box.classList.add('bg-yellow-400', 'text-black');
                break;
            default:
                box.classList.add('bg-gray-700');
        }

        grid.appendChild(box);
    });
}

</script>
@endsection