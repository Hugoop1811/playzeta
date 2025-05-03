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

</div>

<script>
// JavaScript igual que antes con manejo de 'Ñ' como 'NN'
</script>
@endsection
