{{-- resources/views/games/battleship/play.blade.php --}}
@extends('layout')

@section('content')

  <audio id="bg-music" src="{{ asset('audio/MusicaCombateBattleship.mp3') }}" loop preload="auto"></audio>
  <audio id="cannon-sound" src="{{ asset('audio/DisparoCannon.mp3') }}" preload="auto"></audio>
  <audio id="impact-sound" src="{{ asset('audio/ImpactoBala.mp3') }}" preload="auto"></audio>
  <audio id="water-sound" src="{{ asset('audio/CaidaAlAgua.mp3') }}" preload="auto"></audio>
  <audio id="hundido-sound" src="{{ asset('audio/BarcoHundido.mp3') }}" preload="auto"></audio>

  {{-- Toggle música --}}
  @php
    // Obtenemos el volumen guardado (0.0–1.0), por defecto 0.3
    $volDecimal = session('battleship_bg_volume', 0.3);
    // Lo convertimos a porcentaje entero 0–100
    $volPercent = (int) round($volDecimal * 100);
    @endphp
  <div class="absolute bottom-4 right-4">
    <button id="music-toggle" class="px-3 py-1 bg-gray-700 text-white rounded">🔊</button>
    <input id="volume-slider" type="range" min="0" max="100" step="1" value="{{ $volPercent }}" class="h-1 w-24">
  </div>

  @php
    $hitsP = $playerBoard->hits ?? [];
    $shipsP = $playerBoard->ships ?? [];
    $hitsO = $oppBoard->hits ?? [];
    $level = $battleship_game->difficulty;
    $oppShips = $oppBoard->ships ?? [];
    @endphp

  <div class="grid justify-center mx-auto p-6 min-h-screen">
    <div class="bg-gray-900 p-6">
    <h1 class="text-2xl font-bold mb-4 text-white">Hundir la Flota</h1>
    <p id="info" class="mb-4 text-gray-300">
      Modo: <span class="font-semibold text-white">{{ ucfirst($level) }}</span>
    </p>
    @php $diff = strtolower($battleship_game->difficulty) @endphp
    <div class="flex items-center mb-4">
      <p class="text-gray-300 mr-4">
      Dificultad: <strong>{{ ucfirst($diff) }}</strong>
      </p>
      @if($diff === 'hard')
      <div id="timer" class="text-white font-mono text-lg bg-gray-700 px-2 py-1 rounded">
      5
      </div>
    @endif
    </div>

    <div class="flex gap-[41px]">
      {{-- Tu tablero --}}
      <div>
      <h2 class="text-lg font-medium text-white mb-2">Tu tablero</h2>
      <div id="player-board" class="grid grid-rows-10 grid-cols-10 gap-[0.06rem]">
        @for($y = 0; $y < 10; $y++)
        @for($x = 0; $x < 10; $x++)
        @php
      $occ = collect($shipsP)->contains(fn($s) => in_array([$x, $y], $s['cells']));
      $hit = in_array([$x, $y], $hitsP);
      $bg = $occ
      ? ($hit ? 'bg-red-600' : 'bg-gray-500')
      : ($hit ? 'bg-blue-400' : 'bg-blue-700');
      @endphp
        <div class="w-10 h-10 {{ $bg }}" data-x="{{ $x }}" data-y="{{ $y }}"></div>
      @endfor
      @endfor
      </div>
      </div>

      {{-- Tablero rival --}}
      <div margin-left="45px">
      <h2 class="text-lg font-medium text-white mb-2">Tablero rival</h2>
      <div id="opponent-board" class="grid grid-rows-10 grid-cols-10 gap-[0.06rem]">
        @for($y = 0; $y < 10; $y++)
        @for($x = 0; $x < 10; $x++)
        @php
      $hit = in_array([$x, $y], $hitsO);
      $hitShip = collect($oppBoard->ships)
      ->contains(fn($s) => in_array([$x, $y], $s['cells']));
      $bg = $hit
      ? ($hitShip ? 'bg-red-600' : 'bg-blue-400')
      : 'bg-gray-700 hover:bg-gray-600 cursor-pointer cell-clickable';
      @endphp
        <div class="w-10 h-10 {{ $bg }}" data-x="{{ $x }}" data-y="{{ $y }}"></div>
      @endfor
      @endfor
      </div>
      </div>
    </div>

    <div id="status" class="mt-6 text-center text-lg text-yellow-400"></div>
    <div class="mt-4 text-center">
      <a href="{{ route('battleship.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-500">
      Volver a partidas
      </a>
    </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const gameId        = {{ $battleship_game->id }};
  const moveUrl       = "{{ route('battleship.pvp.move', $battleship_game) }}";
  const oppBoardEl    = document.getElementById('opponent-board');
  const playerBoardEl = document.getElementById('player-board');
  const statusEl      = document.getElementById('status');

  // Audios que ya tenías
  const cannonSound  = document.getElementById('cannon-sound');
  const waterSound   = document.getElementById('water-sound');
  const impactSound  = document.getElementById('impact-sound');
  const hundidoSound = document.getElementById('hundido-sound');

  function enableClicks() {
    oppBoardEl.querySelectorAll('.cell-clickable')
      .forEach(cell => {
        cell.style.pointerEvents = '';
        cell.addEventListener('click', handleClick, { once: true });
      });
  }
  function disableClicks() {
    oppBoardEl.querySelectorAll('.cell-clickable')
      .forEach(cell => {
        cell.removeEventListener('click', handleClick);
        cell.style.pointerEvents = 'none';
      });
  }

  async function handleClick(e) {
    const cell = e.currentTarget;
    const x = +cell.dataset.x, y = +cell.dataset.y;

    // sonido cañón y desactivar clicks
    cannonSound.currentTime = 0; cannonSound.play();
    disableClicks();

    // enviamos jugada, sin esperar respuesta
    fetch(moveUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type':'application/json',
        'Accept':'application/json',
        'X-CSRF-TOKEN':'{{ csrf_token() }}'
      },
      body: JSON.stringify({ x, y })
    }).catch(err => {
      console.error('Error al enviar jugada:', err);
      statusEl.textContent = 'Error de red.';
      enableClicks();
    });
  }

  // Suscripción al canal privado PVP
  const channel = Echo.private(`battleship.pvp.${gameId}`);

  channel.listen('MoveMade', e => {
    // e: { shooter, coords, result, sunkCells, gameOver, winner }
    const isMe = e.shooter === 'player';
    const [cx, cy] = e.coords;

    if (isMe) {
      // Pintar en tablero del rival
      const c = oppBoardEl.querySelector(`.cell-clickable[data-x="${cx}"][data-y="${cy}"]`);
      if (e.result === 'agua') {
        waterSound.currentTime = 0; waterSound.play();
        c.classList.replace('bg-gray-700','bg-blue-400');
      } else if (e.result === 'tocado') {
        impactSound.currentTime = 0; impactSound.play();
        c.classList.replace('bg-gray-700','bg-red-600');
      } else {
        hundidoSound.currentTime = 0; hundidoSound.play();
        // hundido: pinta todas las celdas del barco
        e.sunkCells.forEach(([sx,sy]) => {
          const cc = oppBoardEl.querySelector(`[data-x="${sx}"][data-y="${sy}"]`);
          cc.classList.replace('bg-gray-700','bg-green-500');
        });
      }
      // mensaje
      statusEl.textContent = (e.result==='agua' ? 'Agua'
        : e.result==='tocado' ? 'Tocado' : 'Tocado y hundido');
      statusEl.className = `mt-6 text-center text-lg ${
        e.result==='agua'? 'text-blue-400'
      : e.result==='tocado'? 'text-red-600'
      : 'text-green-500' }`;
    } else {
      // Pintar disparo del rival en tu tablero
      const c = playerBoardEl.querySelector(`[data-x="${cx}"][data-y="${cy}"]`);
      if (e.result === 'agua') {
        waterSound.currentTime = 0; waterSound.play();
        c.classList.replace('bg-gray-500','bg-blue-400');
      } else if (e.result === 'tocado') {
        impactSound.currentTime = 0; impactSound.play();
        c.classList.replace('bg-gray-500','bg-red-600');
      } else {
        hundidoSound.currentTime = 0; hundidoSound.play();
        // hundido: todas las celdas
        e.sunkCells.forEach(([sx,sy]) => {
          const cc = playerBoardEl.querySelector(`[data-x="${sx}"][data-y="${sy}"]`);
          cc.classList.replace('bg-gray-500','bg-green-500');
        });
      }
    }

    if (e.gameOver) {
      // Fin de partida
      statusEl.textContent = e.winner==='player' ? '¡Has ganado! 🎉' : '¡Has perdido! 💥';
      disableClicks();
    } else if (!isMe) {
      // Si acaba de mover el rival, ahora te toca
      enableClicks();
    }
  });

  channel.listen('GameOver', e => {
    // Por si quieres doble refuerzo
    statusEl.textContent = e.winner==='player' ? '¡Has ganado! 🎉' : '¡Has perdido! 💥';
    disableClicks();
  });

  // Arranca permitiendo clicks si te toca
  if ("{{ $battleship_game->turn }}" === 'player') {
    enableClicks();
  }
});
</script>
@endpush