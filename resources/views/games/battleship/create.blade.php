{{-- resources/views/games/battleship/create.blade.php --}}
@extends('layout')

@section('content')
<div class="container mx-auto p-6 max-w-md">
  <h1 class="text-2xl font-bold mb-4">Crear Nueva Partida</h1>
  <form method="POST" action="{{ route('battleship.store') }}" class="space-y-4">
    @csrf

    {{-- Modo de juego --}}
    <div>
      <label for="mode" class="block font-medium mb-1">Modo de juego</label>
      <select id="mode" name="mode" required
        class="w-full border border-gray-600 rounded px-3 py-2 bg-gray-800 text-white">
        @foreach($modes as $value => $label)
          <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
      </select>
    </div>

    {{-- Dificultad IA (solo si elige IA) --}}
    <div id="difficulty-group" class="hidden">
      <label for="difficulty" class="block font-medium mb-1">Dificultad IA</label>
      <select id="difficulty" name="difficulty"
        class="w-full border border-gray-600 rounded px-3 py-2 bg-gray-800 text-white">
        @foreach($difficulties as $value => $label)
          <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
      </select>
    </div>

    <button type="submit"
      class="w-full px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
      Crear Partida
    </button>
  </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const modeEl = document.getElementById('mode');
  const diffGroup = document.getElementById('difficulty-group');

  function toggleDifficulty() {
    diffGroup.classList.toggle('hidden', modeEl.value !== 'IA');
  }

  modeEl.addEventListener('change', toggleDifficulty);
  toggleDifficulty(); // al cargar la página
});
</script>
@endpush