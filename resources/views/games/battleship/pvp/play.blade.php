@extends('layout')

@section('content')
  <audio id="bg-music" src="{{ asset('audio/MusicaCombateBattleship.mp3') }}" loop preload="auto"></audio>
  <audio id="cannon-sound" src="{{ asset('audio/DisparoCannon.mp3') }}" preload="auto"></audio>
  <audio id="impact-sound" src="{{ asset('audio/ImpactoBala.mp3') }}" preload="auto"></audio>
  <audio id="water-sound" src="{{ asset('audio/CaidaAlAgua.mp3') }}" preload="auto"></audio>
  <audio id="hundido-sound" src="{{ asset('audio/BarcoHundido.mp3') }}" preload="auto"></audio>

  @php
    $volDecimal = session('battleship_bg_volume', 0.3);
    $volPercent = (int) round($volDecimal * 100);
    @endphp
  <div class="absolute bottom-4 right-4">
    <button id="music-toggle" class="px-3 py-1 bg-gray-700 text-white rounded">🔊</button>
    <input id="volume-slider" type="range" min="0" max="100" step="1" value="{{ $volPercent }}" class="h-1 w-24">
  </div>

  <div class="grid justify-center mx-auto p-6 min-h-screen">
    <div class="bg-gray-900 p-6">
    <h1 class="text-2xl font-bold mb-4 text-white">Hundir la Flota (PvP)</h1>
    <p id="info" class="mb-4 text-gray-300">
      Turno: <span id="turn-indicator" class="font-semibold text-white">...</span>
    </p>
    <div class="flex gap-[41px]">
      <div>
      <h2 class="text-lg font-medium text-white mb-2">Tu tablero</h2>
      <div id="player-board" class="grid grid-rows-10 grid-cols-10 gap-[0.06rem]">
        @for($y = 0; $y < 10; $y++)
        @for($x = 0; $x < 10; $x++)
        @php
      $occupied = collect($playerBoard->ships ?? [])->contains(fn($s) => in_array([$x, $y], $s['cells']));
      $hit = in_array([$x, $y], $playerBoard->hits ?? []);
      $bg = $occupied ? ($hit ? 'bg-red-600' : 'bg-gray-500') : ($hit ? 'bg-blue-400' : 'bg-blue-700');
      @endphp
        <div class="w-10 h-10 {{ $bg }}" data-x="{{ $x }}" data-y="{{ $y }}"></div>
      @endfor
      @endfor
      </div>
      </div>

      <div>
      <h2 class="text-lg font-medium text-white mb-2">Tablero rival</h2>
      <div id="opponent-board" class="grid grid-rows-10 grid-cols-10 gap-[0.06rem]">
        @for($y = 0; $y < 10; $y++)
        @for($x = 0; $x < 10; $x++)
        @php
      $occupied = collect($oppBoard->ships ?? [])->contains(fn($s) => in_array([$x, $y], $s['cells']));
      $hit = in_array([$x, $y], $oppBoard->hits ?? []);
      $bg = $hit ? ($occupied ? 'bg-red-600' : 'bg-blue-400') : 'bg-gray-700 hover:bg-gray-600 cursor-pointer cell-clickable';
      @endphp
        <div class="w-10 h-10 {{ $bg }}" data-x="{{ $x }}" data-y="{{ $y }}"></div>
      @endfor
      @endfor
      </div>
      </div>
    </div>

    <div id="status" class="mt-6 text-center text-lg text-yellow-400"></div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', () => {
    const oppShips = @json($oppBoard->ships ?? []);
    const gameId = {{ $battleship_game->id }};
    const userId = {{ auth()->id() }};
    const shooter = {{ $battleship_game->user_id }} === userId ? 'player' : 'opponent';
    let myTurn = '{{ $battleship_game->turn }}' === shooter;

    const bgMusic = document.getElementById('bg-music');
    const cannonSound = document.getElementById('cannon-sound');
    const impactSound = document.getElementById('impact-sound');
    const waterSound = document.getElementById('water-sound');
    const hundidoSound = document.getElementById('hundido-sound');

    const turnEl = document.getElementById('turn-indicator');
    const statusEl = document.getElementById('status');
    const oppBoardEl = document.getElementById('opponent-board');
    const playerBoardEl = document.getElementById('player-board');
    const moveUrl = "{{ route('battleship.pvp.move', $battleship_game) }}";

    const volumeSlider = document.getElementById('volume-slider');
    const musicToggle = document.getElementById('music-toggle');

    // Inicializar volumen
    bgMusic.volume = volumeSlider.value / 100;
    // Solo si la página fue recargada (F5 o Ctrl+R)
    bgMusic.play().catch(() => { }); // Autoplay puede fallar si el usuario no ha interactuado
    const navType = performance.getEntriesByType("navigation")[0]?.type;
    if (navType === 'reload') {
      function startMusicOnce() {
      bgMusic.play().catch(() => { });
      document.removeEventListener('click', startMusicOnce);
      }
      document.addEventListener('click', startMusicOnce);
    }

    document.addEventListener('click', startMusicOnce);
    musicToggle.addEventListener('click', () => {
      if (bgMusic.paused) {
      bgMusic.play();
      musicToggle.textContent = '🔊';
      } else {
      bgMusic.pause();
      musicToggle.textContent = '🔇';
      }
    });

    volumeSlider.addEventListener('input', async () => {
      const vol = volumeSlider.value / 100;
      bgMusic.volume = vol;
      try {
      await fetch('/api/battleship/audio', {
        method: 'POST',
        headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ volume: vol })
      });
      } catch (e) {
      console.warn('No se pudo guardar el volumen.');
      }
    });

    updateTurn();

    function updateTurn() {
      turnEl.textContent = myTurn ? '¡Es tu turno!' : 'Turno del rival…';
    }

    oppBoardEl.querySelectorAll('.cell-clickable').forEach(cell => {
      cell.addEventListener('click', async () => {
      if (!myTurn) return;
      const x = +cell.dataset.x, y = +cell.dataset.y;
      cell.classList.remove('cell-clickable');

      cannonSound.currentTime = 0;
      cannonSound.play();

      const res = await fetch(moveUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ x, y })
      });

      const json = await res.json();
      if (json.error) {
        statusEl.textContent = 'Error: ' + json.message;
        return;
      }

      myTurn = false;
      updateTurn();
      });
    });

    window.Echo
      .private(`battleship.pvp.${gameId}`)
      .listen('.MoveMade', (data) => {
      const [x, y] = data.coordinates;
      const targetEl = data.shooter === shooter
        ? document.querySelector(`#opponent-board [data-x='${x}'][data-y='${y}']`)
        : document.querySelector(`#player-board [data-x='${x}'][data-y='${y}']`);

      if (data.result === 'agua') {
        waterSound.currentTime = 0; waterSound.play();
        targetEl.classList.remove('bg-blue-700', 'bg-gray-700', 'bg-red-600', 'bg-green-500', 'hover:bg-gray-600', 'cursor-pointer', 'cell-clickable');
        targetEl.classList.add('bg-blue-400');
      } else if (data.result === 'tocado') {
        impactSound.currentTime = 0; impactSound.play();
        targetEl.classList.remove('bg-blue-700', 'bg-gray-700', 'bg-blue-400', 'bg-green-500', 'hover:bg-gray-600', 'cursor-pointer', 'cell-clickable');
        targetEl.classList.add('bg-red-600');
      } else if (data.result === 'hundido') {
        hundidoSound.currentTime = 0; hundidoSound.play();
        targetEl.classList.remove('bg-blue-700', 'bg-gray-700', 'bg-blue-400', 'bg-green-500', 'hover:bg-gray-600', 'cursor-pointer', 'cell-clickable');
        targetEl.classList.add('bg-green-500');
      }


      if (data.gameOver) {
        statusEl.textContent = data.winner === shooter
        ? '🎉 ¡Has ganado!' : '💀 Has perdido';

        // Mostrar barcos del oponente si has perdido
        if (data.opponentShips && data.winner !== shooter) {
        oppBoardEl.style.pointerEvents = 'none';
        data.opponentShips.forEach(ship => {
          ship.cells.forEach(([sx, sy]) => {
          const c = oppBoardEl.querySelector(`[data-x="${sx}"][data-y="${sy}"]`);
          if (!c) return;
          if (c.classList.contains('bg-gray-700')) {
            c.classList.replace('bg-gray-700', 'bg-gray-500');
          }
          c.classList.remove('hover:bg-gray-600', 'cursor-pointer', 'cell-clickable');
          });
        });
        }

        return;
      } else {
        myTurn = data.shooter !== shooter;
        updateTurn();
      }
      });
    });
  </script>
@endpush