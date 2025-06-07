{{-- resources/views/games/battleship/create.blade.php --}}
@extends('layout')

@section('content')
<form method="POST" action="{{ route('battleship.store') }}">
  @csrf

  {{-- Modo de juego --}}
  <div class="mb-4">
    <label for="mode" class="block font-medium mb-1">Modo</label>
    <select id="mode" name="mode"
      class="w-full border border-gray-600 rounded px-3 py-2 bg-gray-800 text-white">
      <option value="IA">Solitario</option>
      <option value="PVP">Multijugador</option>
    </select>
  </div>

  {{-- Dificultad IA (solo si elige IA) --}}
  <div id="difficulty-group" class="mb-4 hidden">
    <label for="difficulty" class="block font-medium mb-1">Dificultad IA</label>
    <select id="difficulty" name="difficulty"
      class="w-full border border-gray-600 rounded px-3 py-2 bg-gray-800 text-white">
      @foreach($difficulties as $value => $label)
        <option value="{{ $value }}">{{ $label }}</option>
      @endforeach
    </select>
    @error('difficulty')
      <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
  </div>

  {{-- Resto de campos y botón --}}
  <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Crear partida</button>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const modeSelect   = document.getElementById('mode');
  const diffGroup    = document.getElementById('difficulty-group');

  function toggleDifficulty() {
    if (modeSelect.value === 'IA') {
      diffGroup.classList.remove('hidden');
    } else {
      diffGroup.classList.add('hidden');
    }
  }

  modeSelect.addEventListener('change', toggleDifficulty);
  // Ejecutar al cargar para ajustar según valor por defecto
  toggleDifficulty();
});
</script>
@endpush
