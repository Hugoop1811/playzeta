@extends('layout')

@section('content')
<div class="p-6">
  <h1 class="text-2xl font-bold mb-4">Esperando rival…</h1>
  <p class="mb-4">
    Comparte este enlace con tu oponente para que se una:
  </p>
  <input
    type="text"
    readonly
    class="w-full p-2 border rounded mb-4 text-black"
    value="{{ route('battleship.join', $battleship_game) }}"
    onclick="this.select()"
  />

  <div id="status" class="text-lg text-gray-600">
    Esperando a que otro jugador entre a la partida…
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const statusEl = document.getElementById('status');
  const gameId   = {{ $battleship_game->id }};
  // Polling para detectar cuando opponent_id deja de ser null
  setInterval(async () => {
    const res = await fetch("{{ route('battleship.state', $battleship_game) }}", {
      headers: {'Accept':'application/json'}
    });
    const data = await res.json();
    if (data.opponent_id) {
      // ambos en lobby: pasamos al setup
      window.location.href = "{{ route('battleship.setup.view', $battleship_game) }}";
    }
  }, 2000);
});
</script>
@endpush