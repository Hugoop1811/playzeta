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
    console.error('❌ Echo no está definido. Revisa que tu app.js (con Echo) se cargue antes de este script.');
    return;
  }

  const gameId = {{ $battleship_game->id }};
  console.log('🔍 DEBUG PVP channel for gameId:', gameId);
  console.log('Echo.connector:', window.Echo.connector);

  // Pusher / Reverb etc.
  if (window.Echo.connector.pusher) {
    const conn = window.Echo.connector.pusher.connection;
    conn.bind('connected',    () => console.log('✔️ Pusher connected'));
    conn.bind('error',        e => console.error('❌ Pusher error', e));
    conn.bind('disconnected', () => console.log('⚠️ Pusher disconnected'));
  }
  if (window.Echo.connector.socket) {
    const sock = window.Echo.connector.socket;
    sock.on('connect',       () => console.log('✔️ WS connected'));
    sock.on('connect_error', e => console.error('❌ WS connect_error', e));
    sock.on('reconnect',     () => console.log('🔄 WS reconnected'));
    sock.onmessage = ({ data }) => {
      console.log('📨 Raw WS message:', data);
      try { console.log('📦 Parsed WS message:', JSON.parse(data)); }
      catch {}
    };
  }

  const channelName = `battleship.pvp.${gameId}`;
  const channel = window.Echo.private(channelName);
  channel.subscribed(() => console.log(`✅ Subscribed to ${channelName}`));
  channel.error(err => console.error('❌ Channel error:', err));
  channel.listen('GameJoined',   e => console.log('🔔 GameJoined:', e));
  channel.listen('ShipsPlaced',  e => console.log('🔔 ShipsPlaced:', e));
  channel.listen('MoveMade',     e => console.log('🔔 MoveMade:', e));
  channel.listen('.**',          e => console.log('🔔 ANY event:', e));
});
</script>
@endpush