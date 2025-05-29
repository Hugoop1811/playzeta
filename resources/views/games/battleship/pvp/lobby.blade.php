{{-- resources/views/games/battleship/pvp/lobby.blade.php --}}
@extends('layout')

@section('content')
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Esperando rival…</h1>
    <p class="mb-4">Comparte este enlace con tu oponente para que se una:</p>

    <input type="text" readonly class="w-full p-2 border rounded mb-4 text-black"
    value="{{ route('battleship.pvp.join', $battleship_game->id) }}" onclick="this.select()" />

    <div id="status" class="text-lg text-gray-600">
      Esperando a que otro jugador entre a la partida…
    </div>
  </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  if (typeof window.Echo === 'undefined') {
    console.error('Echo no está disponible aún');
    return;
  }

  const gameId = {{ $battleship_game->id }};
  const redirectUrl = "{{ route('battleship.pvp.setup', $battleship_game->id) }}";

  window.Echo
    .private(`battleship.pvp.${gameId}`)
    .listen('GameJoined', () => {
      document.getElementById('status').textContent = '¡Rival conectado! Redirigiendo...';
      setTimeout(() => window.location.href = redirectUrl, 1000);
    });
});
</script>
@endpush